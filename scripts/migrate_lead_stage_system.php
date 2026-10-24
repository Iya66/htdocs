<?php
require_once __DIR__ . '/../app/config.php';

if (!isset($conn) || !($conn instanceof PDO)) {
    fwrite(STDERR, "Database connection not available.\n");
    exit(1);
}

echo "=== MIGRATING LEADS TABLE FOR ADVANCED STAGE MANAGEMENT ===\n";

$existingColumns = $conn->query("SHOW COLUMNS FROM leads")->fetchAll(PDO::FETCH_COLUMN);

$columnsToAdd = [
    'website' => "VARCHAR(255) NULL DEFAULT NULL AFTER email",
    'follow_up_date' => "DATETIME NULL DEFAULT NULL AFTER status",
    'stage_notes' => "TEXT NULL DEFAULT NULL AFTER follow_up_date",
    'proposal_reference' => "VARCHAR(150) NULL DEFAULT NULL AFTER stage_notes",
    'lost_reason' => "VARCHAR(255) NULL DEFAULT NULL AFTER proposal_reference",
    'converted_at' => "DATETIME NULL DEFAULT NULL AFTER lost_reason"
];

foreach ($columnsToAdd as $col => $definition) {
    if (!in_array($col, $existingColumns, true)) {
        echo "Adding column '{$col}'... ";
        $conn->exec("ALTER TABLE leads ADD COLUMN {$col} {$definition}");
        echo "Done.\n";
    } else {
        echo "Column '{$col}' already exists.\n";
    }
}

// Check and add indexes
$indices = $conn->query("SHOW INDEX FROM leads")->fetchAll(PDO::FETCH_ASSOC);
$indexNames = array_column($indices, 'Key_name');

if (!in_array('idx_leads_followup', $indexNames, true)) {
    echo "Adding index idx_leads_followup... ";
    $conn->exec("ALTER TABLE leads ADD INDEX idx_leads_followup (follow_up_date)");
    echo "Done.\n";
} else {
    echo "Index idx_leads_followup already exists.\n";
}

if (!in_array('idx_leads_status', $indexNames, true)) {
    echo "Adding index idx_leads_status... ";
    $conn->exec("ALTER TABLE leads ADD INDEX idx_leads_status (status)");
    echo "Done.\n";
} else {
    echo "Index idx_leads_status already exists.\n";
}

echo "Migration completed successfully!\n";
