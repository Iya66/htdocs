<?php
/**
 * Admin Terms and Conditions Editor
 */
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/email_helper.php';
require_once __DIR__ . '/../app/security_notifier.php';

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_terms') {
 if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
 $message = 'Invalid request. Please refresh and try again.';
 $message_type = 'error';
 } else {
 $terms_content = $_POST['terms_conditions'] ?? '';

 $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('terms_conditions', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
 $stmt->execute([$terms_content, $terms_content]);

 logAudit($conn, 'UPDATE_TERMS', 'Updated terms and conditions content', $_SESSION['admin_id']);
 notifySecurity('UPDATE_TERMS', 'Terms and conditions updated', $_SESSION['admin_id']);

 $message = 'Terms and Conditions saved successfully!';
 $message_type = 'success';
 }
}

// Fetch current terms
$stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = 'terms_conditions'");
$stmt->execute();
$row = $stmt->fetch();
$current_terms = $row ? $row['setting_value'] : '';

$csrf_token = generateCSRFToken();
require_once __DIR__ . '/../app/Views/admin/terms-and-conditions.php';
