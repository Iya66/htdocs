<?php
/**
 * Save Email Notification Settings
 */
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../../app/config.php';

// Admin-only access
$stmt = $conn->prepare("SELECT role FROM admin WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$cu = $stmt->fetch();
if (!$cu || $cu['role'] !== 'admin') {
    $_SESSION['error'] = 'Access denied. Admin privileges required.';
    header('Location: ../dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../reports.php');
    exit;
}

if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $_SESSION['error'] = 'Invalid request.';
    header('Location: ../reports.php');
    exit;
}

$settings_to_save = [
    'notif_staff' => isset($_POST['notif_staff']) ? '1' : '0',
    'notif_settings' => isset($_POST['notif_settings']) ? '1' : '0',
    'notif_content' => isset($_POST['notif_content']) ? '1' : '0',
    'notif_login' => isset($_POST['notif_login']) ? '1' : '0',
    'notif_email' => trim($_POST['notif_email'] ?? '')
];

$stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
    ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");

foreach ($settings_to_save as $key => $value) {
    $stmt->execute([$key, $value]);
}

$_SESSION['success'] = 'Notification settings saved successfully!';
header('Location: ../reports.php?tab=settings');
exit;