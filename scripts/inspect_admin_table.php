<?php
require_once __DIR__ . '/../app/database.php';

try {
    $stmt = $conn->query("DESCRIBE admin");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Columns in `admin` table:\n";
    $hasAvatar = false;
    foreach ($columns as $c) {
        echo " - " . $c['Field'] . " (" . $c['Type'] . ", Null: " . $c['Null'] . ", Default: " . var_export($c['Default'], true) . ")\n";
        if ($c['Field'] === 'avatar') {
            $hasAvatar = true;
        }
    }
    echo "\nHas avatar column: " . ($hasAvatar ? "YES" : "NO") . "\n";

    $stmt = $conn->query("SELECT id, name, username, email, role, avatar FROM admin LIMIT 5");
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "\nCurrent admins in database:\n";
    foreach ($admins as $a) {
        echo " ID {$a['id']}: {$a['name']} ({$a['username']}, {$a['email']}, role: {$a['role']}, avatar: " . var_export($a['avatar'], true) . ")\n";
    }

    try {
        $accCount = $conn->query("SELECT COUNT(*) FROM accounts")->fetchColumn();
        echo "\nAccounts table exists. Row count: $accCount\n";
    } catch (Exception $e) {
        echo "\nAccounts table check: " . $e->getMessage() . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
