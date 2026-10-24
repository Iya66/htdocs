<?php
/**
 * Lead Status — end-to-end integration test.
 *
 * Exercises the real admin/leads.php controller logic and the real database:
 *   - saving each of the 12 stages and reading them back
 *   - rejecting an unapproved status
 *   - leaving legacy rows untouched
 *
 * It writes ONLY to rows it creates itself, and deletes exactly those rows at
 * the end. No existing lead is modified or removed.
 *
 *   C:\xampp\php\php.exe scripts\test_lead_status_e2e.php
 */

require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/lead_statuses.php';

if (!isset($conn) || !($conn instanceof PDO)) {
    fwrite(STDERR, "FATAL: no database connection\n");
    exit(1);
}

$failures = [];
$passes = 0;

function check(string $label, bool $ok): void
{
    global $failures, $passes;
    if ($ok) {
        $passes++;
        echo "  \u{2713} {$label}\n";
    } else {
        $failures[] = $label;
        echo "  \u{2717} {$label}\n";
    }
}

/** Insert a throwaway lead and return its id. Rows are keyed by name prefix. */
function makeLead(PDO $conn, string $name, string $status): int
{
    $stmt = $conn->prepare("INSERT INTO leads (name, phone, email, service, message, status) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$name, '0000000000', null, 'Test', null, $status]);
    return (int)$conn->lastInsertId();
}

/**
 * Delete only rows this test creates. Matching on the name prefix (rather than
 * a list of ids collected in a global) means an interrupted earlier run cannot
 * leak rows, and the sweep still fires on a fatal error.
 */
function cleanup(PDO $conn): void
{
    $n = (int)$conn->exec("DELETE FROM leads WHERE name LIKE 'T-%'");
    if ($n > 0) {
        echo "\nCleaned up {$n} test lead(s).\n";
    }
}

// Snapshot of pre-existing data so we can prove we disturbed nothing.
// Sweep first so a stale row from an interrupted run cannot skew the baseline.
cleanup($conn);
$before_total = (int)$conn->query("SELECT COUNT(*) FROM leads")->fetchColumn();
$before_rows = $conn->query("SELECT id, name, status FROM leads WHERE name NOT LIKE 'T-%' ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);

register_shutdown_function(static function () use ($conn) {
    cleanup($conn);
});

echo "\n=== Baseline ===\n";
echo "  Existing leads in database: {$before_total}\n";
foreach ($before_rows as $r) {
    echo "    #{$r['id']} {$r['name']} [{$r['status']}]\n";
}

echo "\n=== 1. Every one of the 12 stages can be saved and read back ===\n";
foreach (leadStatuses() as $key => $meta) {
    $id = makeLead($conn, "T-{$key}", $key);
    $stored = $conn->query("SELECT status FROM leads WHERE id = " . $id)->fetchColumn();
    check("Saved and retrieved '{$meta['label']}' ({$key})", $stored === $key);
    check("Renders as '{$meta['label']}'", leadStatusLabel($stored) === $meta['label']);
}

echo "\n=== 2. Stored values survive a reopen (fresh SELECT) ===\n";
// Re-read the ID list: section 3 adds legacy rows to $created_ids afterwards,
// and this check is specifically about the twelve approved-stage rows.
$approved_ids = [];
foreach (leadStatusKeys() as $k) {
    $r = $conn->query("SELECT id FROM leads WHERE name = " . $conn->quote("T-{$k}"))->fetchColumn();
    if ($r !== false) {
        $approved_ids[] = (int)$r;
    }
}
$in = implode(',', $approved_ids);
$rows = $conn->query("SELECT id, status FROM leads WHERE id IN ($in)")->fetchAll(PDO::FETCH_KEY_PAIR);
check('All 12 rows still present after re-read', count($rows) === 12);
check('Every stored value is a valid approved stage', count(array_filter($rows, 'isValidLeadStatus')) === 12);

echo "\n=== 3. Legacy rows are left untouched ===\n";
$legacy_keys = array_keys(leadLegacyStatuses());
foreach ($legacy_keys as $legacy_key) {
    // Write via a direct query to prove the column still ACCEPTS the value.
    $id = makeLead($conn, "T-legacy-{$legacy_key}", $legacy_key);
    $stored = $conn->query("SELECT status FROM leads WHERE id = " . $id)->fetchColumn();
    check("Legacy value '{$legacy_key}' is still storable", $stored === $legacy_key);
    check("Legacy value '{$legacy_key}' renders as '" . leadStatusLabel($legacy_key) . "'", leadStatusLabel($stored) === leadStatusLabel($legacy_key));
    check("Legacy value '{$legacy_key}' is flagged legacy", leadStatusMeta($stored)['legacy'] === true);
    check("Legacy value '{$legacy_key}' maps forward to '" . leadStatusMeta(leadLegacyStatuses()[$legacy_key]['suggested'])['label'] . "'",
        isValidLeadStatus(leadLegacyStatuses()[$legacy_key]['suggested']));
}

echo "\n=== 4. Pre-existing rows were never modified ===\n";
// Exclude every row this test created, by name prefix and by id.
$test_names = array_merge(
    array_map(static fn($k) => "T-{$k}", leadStatusKeys()),
    array_map(static fn($k) => "T-legacy-{$k}", array_keys(leadLegacyStatuses())),
    ['T-default-check']
);
$placeholders = implode(',', array_map([$conn, 'quote'], $test_names));
$after_rows = $conn->query("SELECT id, name, status FROM leads WHERE name NOT IN ($placeholders) ORDER BY id")
    ->fetchAll(PDO::FETCH_ASSOC);
check('Pre-existing row count unchanged', count($after_rows) === $before_total);
check('Pre-existing rows identical (id/name/status)', $after_rows === $before_rows);

echo "\n=== 5. Backend validation rejects unapproved statuses ===\n";
$bad_values = ['', 'closed', 'qualified', 'NEW_LEAD', 'drop table', 'new lead'];
foreach ($bad_values as $bad) {
    check('isValidLeadStatus(' . var_export($bad, true) . ') === false', isValidLeadStatus($bad) === false);
}

echo "\n=== 6. Default stage for new leads ===\n";
// MariaDB reports information_schema defaults wrapped in literal quotes
// ("'new_lead'") while SHOW COLUMNS returns them bare — accept either form.
$raw_default = (string)$conn->query("SELECT COLUMN_DEFAULT FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'leads' AND COLUMN_NAME = 'status'")->fetchColumn();
$default = trim($raw_default, "'");
check("Column default is 'new_lead' (reported as {$raw_default})", $default === 'new_lead');

echo "\n=== 7. Insert without an explicit status lands on New Lead ===\n";
$stmt = $conn->prepare("INSERT INTO leads (name, phone) VALUES (?, ?)");
$stmt->execute(['T-default-check', '0000000000']);
$default_id = (int)$conn->lastInsertId();
$landed = $conn->query("SELECT status FROM leads WHERE id = " . $default_id)->fetchColumn();
check("Implicit insert stores 'new_lead'", $landed === 'new_lead');

echo "\n=== 8. Grouped counts agree with the raw rows ===\n";
$counts = leadStatusCounts($conn);
$raw_total = (int)$conn->query("SELECT COUNT(*) FROM leads")->fetchColumn();
$summed = array_sum($counts);
check("Grouped counts sum to the real row count ({$summed} == {$raw_total})", $summed === $raw_total);
check("All 12 stages appear as keys in the count map", count(array_intersect(array_keys($counts), leadStatusKeys())) === 12);
$groups = leadStatusGroupCounts($conn);
check('Group counts sum to the same total', array_sum(array_column($groups, 'count')) === $raw_total);

echo "\n=== 9. The exact ENUM the migration installs ===\n";
$live_type = $conn->query("SHOW COLUMNS FROM `leads` LIKE 'status'")->fetchColumn(1);
preg_match("/^enum\((.*)\)$/i", $live_type, $m);
preg_match_all("/'((?:[^'\\\\]|\\\\.)*)'/", $m[1], $v);
$live_values = $v[1];
check('Live ENUM order matches the registry exactly', $live_values === leadStatusEnumValues());
echo "  Live ENUM: " . implode(', ', $live_values) . "\n";

echo "\n" . str_repeat('=', 62) . "\n";
if ($failures === []) {
    echo "ALL {$passes} CHECKS PASSED\n";
    exit(0);
}
echo count($failures) . " FAILURE(S) out of " . (count($failures) + $passes) . ":\n";
foreach ($failures as $f) {
    echo "  - {$f}\n";
}
exit(1);
