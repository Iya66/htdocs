<?php
/**
 * Mail Settings - PHPMailer Integration with CRUD Operations
 */
@ini_set('max_execution_time', '180');
@set_time_limit(180);

require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../app/config.php';
require_once __DIR__ . '/../../app/smtp_factory.php';
require_once __DIR__ . '/../../app/email_helper.php';
require_once __DIR__ . '/../../app/security_notifier.php';

// Admin-only access
$stmt = $conn->prepare("SELECT role FROM admin WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$cu = $stmt->fetch();
if (!$cu || $cu['role'] !== 'admin') {
 $_SESSION['error'] = 'Access denied. Admin privileges required.';
 header('Location: ../dashboard.php');
 exit;
}

$message = '';
$message_type = '';

// Get all mail settings (fetch first)
$mailSettings = [];
$stmt = $conn->prepare("SELECT setting_key, setting_value FROM settings WHERE setting_key LIKE 'mail_%'");
$stmt->execute();
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
 $mailSettings[$row['setting_key']] = $row['setting_value'];
}

if (!function_exists('getSettingValue')) {
 function getSettingValue(string $key, $default = '') {
 global $mailSettings;
 if (isset($mailSettings[$key])) return $mailSettings[$key];
 if (defined('SMTP_HOST')) {
 $map = ['mail_smtp_server' => 'SMTP_HOST', 'mail_smtp_email' => 'SMTP_USER', 'mail_smtp_password' => 'SMTP_PASS', 'mail_smtp_port' => 'SMTP_PORT'];
 if (isset($map[$key]) && defined($map[$key]) && constant($map[$key]) !== '') {
 return constant($map[$key]);
 }
 }
 return $default;
 }
}

$csrf_token = generateCSRFToken();

// Simple encryption for SMTP password (AES-256-CBC)
function encryptPassword(string $password): string {
 $envKey = defined('ENCRYPTION_KEY') && ENCRYPTION_KEY !== '' ? ENCRYPTION_KEY : '';

 if ($envKey === '') {
 error_log('[MailSettings] ENCRYPTION_KEY not configured. Cannot encrypt SMTP password. Set it in your .env file.');
 return $password;
 }

 $key = hash('sha256', $envKey, true);
 $iv = random_bytes(16);
 $encrypted = openssl_encrypt($password, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
 return base64_encode($iv . $encrypted);
}

function decryptPassword(string $encoded): string {
 if (empty($encoded)) return '';

 $envKey = defined('ENCRYPTION_KEY') && ENCRYPTION_KEY !== '' ? ENCRYPTION_KEY : '';

 if ($envKey === '') {
 error_log('[MailSettings] ENCRYPTION_KEY not configured. Cannot decrypt SMTP password.');
 return '';
 }

 try {
 $data = base64_decode($encoded, true);
 if ($data === false) return $encoded;
 $iv = substr($data, 0, 16);
 $encrypted = substr($data, 16);

 $key = hash('sha256', $envKey, true);
 $decrypted = openssl_decrypt($encrypted, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);

 if ($decrypted === false) {
 error_log('[MailSettings] Password decryption failed — wrong ENCRYPTION_KEY or corrupted data.');
 return '';
 }

 return $decrypted;
 } catch (Exception $e) {
 error_log('[MailSettings] Password decryption failed: ' . $e->getMessage());
 return '';
 }
}

// SMTP Options builder — NO bindto socket option.
function getMailSMTPOptions(array $mailSettings): array {
 $options = [];
 $devMode = $mailSettings['mail_dev_mode'] ?? '0';
 if ($devMode === '1') {
 $options['ssl'] = [
 'verify_peer' => false,
 'verify_peer_name' => false,
 'allow_self_signed' => true
 ];
 }
 return $options;
}

// Save Mail Settings
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_mail_settings'])) {
 if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
 $message = 'Invalid request. Please refresh and try again.';
 $message_type = 'error';
 } else {
 $settings = [
 'mail_admin_email' => trim($_POST['admin_email'] ?? ''),
 'mail_smtp_email' => trim($_POST['smtp_email'] ?? ''),
 'mail_smtp_server' => trim($_POST['smtp_server'] ?? ''),
 'mail_smtp_port' => trim($_POST['smtp_port'] ?? ''),
 'mail_smtp_protocol' => trim($_POST['smtp_protocol'] ?? 'tls'),
 'mail_from_name' => trim($_POST['from_name'] ?? 'CloudASPIRE'),
 'mail_force_ipv4' => isset($_POST['force_ipv4']) ? '1' : '0',
 ];

 if (!empty($_POST['smtp_password'])) {
 $settings['mail_smtp_password'] = encryptPassword(trim($_POST['smtp_password']));
 }

 foreach ($settings as $key => $value) {
 $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
 $stmt->execute([$key, $value]);
 }

 logAudit($conn, 'UPDATE_MAIL_SETTINGS', 'Updated mail/SMTP settings', $_SESSION['admin_id']);
 notifySecurity('UPDATE_MAIL_SETTINGS', 'Updated mail/SMTP settings', $_SESSION['admin_id']);

 $message = 'Mail settings saved successfully!';
 $message_type = 'success';

 $stmt = $conn->prepare("SELECT setting_key, setting_value FROM settings WHERE setting_key LIKE 'mail_%'");
 $stmt->execute();
 while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
 $mailSettings[$row['setting_key']] = $row['setting_value'];
 }
 }
}

// Test SMTP Connection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_smtp'])) {
 if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
 $message = 'Invalid request. Please refresh and try again.';
 $message_type = 'error';
 } else {
 try {
 $debugOutput = '';
 $mail = createMailer($conn, PHPMailer\PHPMailer\SMTP::DEBUG_CONNECTION, function($str, $level) use (&$debugOutput) {
 $debugOutput .= $str . "\n";
 });

 if (!$mail->smtpConnect()) {
 throw new Exception('Could not connect to SMTP server');
 }

 $mail->smtpClose();
 $message = 'SMTP connection successful! Server is reachable.';
 $message_type = 'success';
 } catch (Exception $e) {
 $errorDetail = htmlspecialchars($e->getMessage());
 if (!empty($mail->ErrorInfo)) $errorDetail .= " | ErrorInfo: " . htmlspecialchars($mail->ErrorInfo);
 if (!empty($debugOutput)) $errorDetail .= "<br><br><b>Debug Log:</b><br><pre style='font-size: 11px; white-space: pre-wrap;'>" . htmlspecialchars($debugOutput) . "</pre>";

 $message = 'SMTP connection failed: ' . $errorDetail;
 $message_type = 'error';
 }
 }
}

// Send Test Email
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_test_email'])) {
 if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
 $message = 'Invalid request. Please refresh and try again.';
 $message_type = 'error';
 } else {
 $test_email = trim($_POST['test_email'] ?? '');

 if (empty($test_email)) {
 $message = 'Please enter a test email address.';
 $message_type = 'error';
 } else {
 try {
 $debugOutput = '';
 $from_name = getSettingValue('mail_from_name', 'CloudASPIRE');
 $smtp_email = getSettingValue('mail_smtp_email');

 if (empty($smtp_email)) {
 throw new Exception('SMTP settings are not configured. Please save settings first.');
 }

 $mail = createMailer($conn, PHPMailer\PHPMailer\SMTP::DEBUG_CONNECTION, function($str, $level) use (&$debugOutput) {
 $debugOutput .= $str . "\n";
 });

 $mail->setFrom($smtp_email, $from_name);
 $mail->addAddress($test_email);
 $mail->isHTML(true);
 $mail->Subject = 'Test Email from CloudASPIRE';
 $mail->Body = '<h2>Test Email Successful!</h2><p>This is a test email from CloudASPIRE admin panel.</p><p>If you received this, your SMTP settings are configured correctly.</p>';
 $mail->AltBody = 'Test Email Successful! This is a test email from CloudASPIRE admin panel.';

 sendWithRetry($mail, 3);

 $message = 'Test email sent successfully to ' . htmlspecialchars($test_email);
 $message_type = 'success';
 } catch (Exception $e) {
 $errorDetail = htmlspecialchars($e->getMessage());
 if (!empty($mail->ErrorInfo)) $errorDetail .= " | ErrorInfo: " . htmlspecialchars($mail->ErrorInfo);
 if (!empty($debugOutput)) $errorDetail .= "<br><br><b>Debug Log:</b><br><pre style='font-size: 11px; white-space: pre-wrap;'>" . htmlspecialchars($debugOutput) . "</pre>";

 $message = 'Failed to send test email: ' . $errorDetail;
 $message_type = 'error';
 }
 }
 }
}

// Process Mail Queue Manually
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['process_mail_queue'])) {
 if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
 $message = 'Invalid request. Please refresh and try again.';
 $message_type = 'error';
 } else {
 $res = processMailQueue($conn, 20);
 if ($res['processed'] > 0) {
 $message = "Mail queue processed: {$res['sent']} sent successfully, {$res['failed']} failed.";
 $message_type = ($res['failed'] === 0) ? 'success' : 'warning';
 } else {
 $message = 'Mail queue is currently empty. No pending emails to process.';
 $message_type = 'info';
 }
 }
}

// Retry Failed Queue Items
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['retry_failed_queue'])) {
 if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
 $message = 'Invalid request. Please refresh and try again.';
 $message_type = 'error';
 } else {
 $count = retryFailedEmails($conn);
 if ($count > 0) {
 $message = "Reset $count failed email(s) back to pending. Processing started in background.";
 $message_type = 'success';
 } else {
 $message = 'No failed emails found to retry.';
 $message_type = 'info';
 }
 }
}

// Clear Processed / Failed Queue
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clear_mail_queue'])) {
 if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
 $message = 'Invalid request. Please refresh and try again.';
 $message_type = 'error';
 } else {
 $count = clearCompletedQueue($conn, 0);
 $message = "Cleared $count sent/failed item(s) from mail queue.";
 $message_type = 'success';
 }
}

// Delete Specific Setting (POST + CSRF protected)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_setting'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid request.';
        $message_type = 'error';
    } else {
        $key = trim($_POST['setting_key'] ?? '');
        $allowed = ['mail_admin_email', 'mail_smtp_email', 'mail_smtp_password', 'mail_smtp_server', 'mail_smtp_port', 'mail_smtp_protocol', 'mail_from_name'];
        if (in_array($key, $allowed, true)) {
            $stmt = $conn->prepare("DELETE FROM settings WHERE setting_key = ?");
            $stmt->execute([$key]);
            logAudit($conn, 'DELETE_MAIL_SETTING', "Deleted mail setting: $key", $_SESSION['admin_id']);
            $message = 'Setting deleted successfully!';
            $message_type = 'success';

            // Refresh cached mail settings
            $stmt = $conn->prepare("SELECT setting_key, setting_value FROM settings WHERE setting_key LIKE 'mail_%'");
            $stmt->execute();
            $mailSettings = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $mailSettings[$row['setting_key']] = $row['setting_value'];
            }
        }
    }
}

require_once __DIR__ . '/../../app/Views/admin/settings/mailsettings.php';
