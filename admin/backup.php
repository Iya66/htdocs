<?php
/**
 * Backup Manager - Admin View
 */
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/email_helper.php';
require_once __DIR__ . '/../app/security_notifier.php';
require_once __DIR__ . '/model/backup/BackupModel.php';

// Admin-only access
$stmt = $conn->prepare("SELECT role FROM admin WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$cu = $stmt->fetch();
if (!$cu || $cu['role'] !== 'admin') {
    $_SESSION['error'] = 'Access denied.';
    header('Location: dashboard.php');
    exit;
}

$csrf_token = generateCSRFToken();
$backupModel = new BackupModel($conn);

// Trigger scheduled auto-backup check on page view
try {
    $backupModel->checkAndTriggerSchedule();
} catch (Throwable $e) {}

// Save backup settings
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_backup_settings'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error'] = 'Invalid CSRF token.';
    } else {
        $frequency = $_POST['backup_frequency'] ?? 'off';
        $type = $_POST['backup_type'] ?? 'database';
        $sync_url = $_POST['backup_sync_url'] ?? '';
        
        $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('backup_schedule_frequency', ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        $stmt->execute([$frequency]);
        
        $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('backup_schedule_type', ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        $stmt->execute([$type]);
        
        $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('backup_cloud_sync_url', ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        $stmt->execute([$sync_url]);
        
        $_SESSION['success'] = 'Backup policies and cloud sync URL updated successfully!';
        header('Location: backup.php');
        exit;
    }
}

// Fetch current backup settings
$stmt = $conn->query("SELECT setting_key, setting_value FROM settings WHERE setting_key LIKE 'backup_%'");
$backupSettings = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $backupSettings[$row['setting_key']] = $row['setting_value'];
}
$frequency = $backupSettings['backup_schedule_frequency'] ?? 'off';
$type = $backupSettings['backup_schedule_type'] ?? 'database';
$sync_url = $backupSettings['backup_cloud_sync_url'] ?? '';
$last_run = $backupSettings['backup_schedule_last_run'] ?? '';

$message = '';
$message_type = '';
if (isset($_SESSION['success'])) {
    $message = $_SESSION['success'];
    $message_type = 'success';
    unset($_SESSION['success']);
} elseif (isset($_SESSION['error'])) {
    $message = $_SESSION['error'];
    $message_type = 'error';
    unset($_SESSION['error']);
}

$backups = $backupModel->listBackups();
$totalSize = array_sum(array_column($backups, 'size'));
$dbCount = count(array_filter($backups, fn($b) => $b['type'] === 'database'));
$fileCount = count(array_filter($backups, fn($b) => $b['type'] === 'files'));

require_once __DIR__ . '/../app/Views/admin/backup.php';
