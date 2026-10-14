<?php
require_once __DIR__ . '/app/config.php';
$admin_path = 'admin';
if (isset($conn)) {
    $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = 'admin_url_path'");
    $stmt->execute();
    $db_val = $stmt->fetchColumn();
    if ($db_val) {
        $admin_path = trim($db_val);
    }
}
$base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
header('Location: ' . ($base ? $base : '') . '/' . ltrim($admin_path, '/') . '/leads.php');
exit;