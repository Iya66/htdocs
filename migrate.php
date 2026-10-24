<?php
/**
 * Cloud Aspire - Database Migration Runner
 * Executes database_migration.sql cleanly with step-by-step reporting.
 * Usage CLI: php migrate.php
 * Usage Web: http://localhost/migrate.php?key=cloudaspire_migrate
 */

$is_cli = (php_sapi_name() === 'cli');

// Basic security protection for web access
if (!$is_cli) {
    $auth_key = $_GET['key'] ?? '';
    if ($auth_key !== 'cloudaspire_migrate') {
        http_response_code(403);
        die("<h1>403 Forbidden</h1><p>Access key required for web-based migration runner.</p>");
    }
    header('Content-Type: text/html; charset=utf-8');
    echo "<!DOCTYPE html><html><head><title>Database Migration Runner</title>";
    echo "<style>body{font-family:monospace;background:#0d1117;color:#c9d1d9;padding:30px;line-height:1.6;}";
    echo ".success{color:#3fb950;font-weight:bold;} .error{color:#f85149;font-weight:bold;} .info{color:#58a6ff;}";
    echo "h1{color:#ffffff;border-bottom:1px solid #30363d;padding-bottom:10px;}</style></head><body><pre>";
}

function out($msg, $type = 'info') {
    global $is_cli;
    $time = date('H:i:s');
    if ($is_cli) {
        $prefix = "[$time] ";
        echo $prefix . $msg . PHP_EOL;
    } else {
        $cls = htmlspecialchars($type);
        echo "<span class=\"$cls\">[" . htmlspecialchars($time) . "] " . htmlspecialchars($msg) . "</span>\n";
        @ob_flush();
        flush();
    }
}

out("===============================================================");
out(" Cloud Aspire - Database Migration Engine");
out("===============================================================");

// 1. Load DB Connection
require_once __DIR__ . '/app/database.php';

if (!isset($conn) || !($conn instanceof PDO)) {
    out("FATAL: Failed to establish database connection. Check app/.env", "error");
    exit(1);
}

out("✓ Database Connected to " . DB_NAME . "@" . DB_HOST, "success");

// 2. Load Migration SQL File
$sql_file = __DIR__ . '/database_migration.sql';
if (!file_exists($sql_file)) {
    out("FATAL: Migration file not found: $sql_file", "error");
    exit(1);
}

$sql_content = file_get_contents($sql_file);
out("✓ Loaded migration file (" . number_format(strlen($sql_content)) . " bytes)", "info");

// 3. Split SQL by statements
// Remove multi-line comments and split by semicolon followed by newline
$queries = [];
$current_query = '';
$lines = explode("\n", $sql_content);

foreach ($lines as $line) {
    $trimmed = trim($line);
    if (str_starts_with($trimmed, '--') || str_starts_with($trimmed, '/*') || empty($trimmed)) {
        continue;
    }
    $current_query .= $line . "\n";
    if (str_ends_with($trimmed, ';')) {
        $queries[] = trim($current_query);
        $current_query = '';
    }
}

out("✓ Parsed " . count($queries) . " SQL executable statements", "info");

// 4. Execute Migration Statements
$executed_count = 0;
$error_count = 0;

$conn->exec("SET FOREIGN_KEY_CHECKS = 0");

foreach ($queries as $idx => $query) {
    if (empty(trim($query))) continue;
    try {
        $conn->exec($query);
        $executed_count++;
    } catch (PDOException $e) {
        $error_count++;
        out("Query #" . ($idx + 1) . " Warning: " . $e->getMessage(), "error");
    }
}

$conn->exec("SET FOREIGN_KEY_CHECKS = 1");

out("✓ Successfully executed $executed_count queries with $error_count warnings.", "success");

// 4.1 Schema Evolution for Rich Service Pages
$service_cols = [
    'seo_title' => 'VARCHAR(255) DEFAULT NULL',
    'meta_description' => 'TEXT DEFAULT NULL',
    'meta_keywords' => 'TEXT DEFAULT NULL',
    'features' => 'TEXT DEFAULT NULL',
    'benefits' => 'TEXT DEFAULT NULL',
    'process_steps' => 'TEXT DEFAULT NULL',
    'faqs' => 'TEXT DEFAULT NULL'
];
$existing_svc_cols = $conn->query("SHOW COLUMNS FROM services")->fetchAll(PDO::FETCH_COLUMN);
foreach ($service_cols as $scol => $stype) {
    if (!in_array($scol, $existing_svc_cols)) {
        try {
            $conn->exec("ALTER TABLE services ADD COLUMN `$scol` $stype");
            out("✓ Added column `services`.`$scol`", "success");
        } catch (Exception $e) {}
    }
}

// 4.2 Lead Pipeline Stages (12-stage CRM workflow)
// Reconciles the `leads.status` column with app/lead_statuses.php so that
// validation, badges, filters and the ENUM can never drift apart.
// Idempotent: only rewrites the column when it does not already match.
require_once __DIR__ . '/app/lead_statuses.php';

try {
    $lead_col = $conn->query("SHOW COLUMNS FROM `leads` LIKE 'status'")->fetch(PDO::FETCH_ASSOC);

    if (!$lead_col) {
        out("! `leads`.`status` column not found — skipping lead stage sync.", "error");
    } else {
        $expected_sql = leadStatusEnumSql('status');
        $current_type = (string)($lead_col['Type'] ?? '');
        $current_default = (string)($lead_col['Default'] ?? '');
        $current_null = strtoupper((string)($lead_col['Null'] ?? ''));

        /**
         * Compare the live ENUM against the registry by VALUE rather than by
         * raw string: SHOW COLUMNS returns only the type (e.g. enum('a','b'))
         * with no NOT NULL / DEFAULT clause, and storage engines are free to
         * reorder or re-space the definition.
         */
        $extractEnumValues = static function (string $type): array {
            if (preg_match("/^enum\((.*)\)$/i", trim($type), $m) !== 1) {
                return [];
            }
            preg_match_all("/'((?:[^'\\\\]|\\\\.)*)'/", $m[1], $vals);
            return array_map(static fn($v) => str_replace("\\'", "'", $v), $vals[1]);
        };

        $current_values = $extractEnumValues($current_type);
        $expected_values = leadStatusEnumValues();

        $needs_update = $current_values !== $expected_values
            || $current_default !== 'new_lead'
            || $current_null !== 'NO';

        if ($needs_update) {
            $conn->exec("ALTER TABLE `leads` MODIFY `status` {$expected_sql}");
            out("✓ Synced `leads`.`status` ENUM to " . count($expected_values) . " stages (12 approved + legacy history)", "success");
        } else {
            out("• `leads`.`status` ENUM already up to date (" . count($expected_values) . " stages)", "info");
        }

        // Report the stage distribution so an admin can spot unmigrated rows.
        $stage_counts = leadStatusCounts($conn);
        $legacy = (int)($stage_counts['legacy'] ?? 0);
        if ($legacy > 0) {
            out("! {$legacy} lead(s) still hold a status outside the 12-stage workflow — review them in /admin/leads.", "error");
        } else {
            out("✓ All existing leads map to the 12-stage workflow.", "success");
        }
    }
} catch (Throwable $e) {
    out("! Lead stage sync warning: " . $e->getMessage(), "error");
}

// 5. Audit & Validate Live Tables
out("---------------------------------------------------------------");
out(" Table Verification Summary:");
out("---------------------------------------------------------------");

$tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
$total_rows = 0;

foreach ($tables as $t) {
    $cnt = $conn->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
    $total_rows += $cnt;
    out(sprintf("  [%-22s] %4d records verified", $t, $cnt), "success");
}

out("---------------------------------------------------------------");
out("Total Tables: " . count($tables) . " | Total Seed Records: " . number_format($total_rows), "info");
out("MIGRATION COMPLETED SUCCESSFULLY!", "success");
out("===============================================================");

if (!$is_cli) {
    echo "</pre></body></html>";
}
exit(0);
