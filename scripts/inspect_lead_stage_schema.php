<?php
require_once __DIR__ . '/../app/config.php';

echo "=== COLUMNS IN 'leads' TABLE ===\n";
$cols = $conn->query("SHOW COLUMNS FROM leads")->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $col) {
    echo sprintf("%-20s %-45s Null:%-4s Default:%s\n", $col['Field'], $col['Type'], $col['Null'], var_export($col['Default'], true));
}

echo "\n=== COLUMNS IN 'admin_activities' TABLE ===\n";
$cols2 = $conn->query("SHOW COLUMNS FROM admin_activities")->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols2 as $col) {
    echo sprintf("%-20s %-45s Null:%-4s Default:%s\n", $col['Field'], $col['Type'], $col['Null'], var_export($col['Default'], true));
}

echo "\n=== SAMPLE admin_activities ROWS ===\n";
$sample = $conn->query("SELECT * FROM admin_activities ORDER BY id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
print_r($sample);

