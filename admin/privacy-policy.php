<?php
/**
 * Admin Privacy Policy Editor
 */
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/email_helper.php';
require_once __DIR__ . '/../app/security_notifier.php';

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_policy') {
  if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
 $message = 'Invalid request. Please refresh and try again.';
 $message_type = 'error';
 } else {
 $policy_content = $_POST['privacy_policy'] ?? '';

 $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('privacy_policy', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
 $stmt->execute([$policy_content, $policy_content]);

 logAudit($conn, 'UPDATE_PRIVACY_POLICY', 'Updated privacy policy content', $_SESSION['admin_id']);
 notifySecurity('UPDATE_PRIVACY_POLICY', 'Privacy policy updated', $_SESSION['admin_id']);

 $message = 'Privacy Policy saved successfully!';
 $message_type = 'success';
 }
}

// Fetch current policy
$stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = 'privacy_policy'");
$stmt->execute();
$row = $stmt->fetch();
$current_policy = $row ? $row['setting_value'] : '';

$csrf_token = generateCSRFToken();
require_once __DIR__ . '/../app/Views/admin/privacy-policy.php';
