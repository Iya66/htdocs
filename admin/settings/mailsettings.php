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
        $adminEmail = trim($_POST['admin_email'] ?? '');
        $smtpEmail = trim($_POST['smtp_email'] ?? '');
        $smtpServer = trim($_POST['smtp_server'] ?? '');
        $smtpPort = trim($_POST['smtp_port'] ?? '');
        $smtpProtocol = strtolower(trim($_POST['smtp_protocol'] ?? 'tls'));
        $fromName = trim($_POST['from_name'] ?? 'Cloud Aspire');
        $forceIpv4 = isset($_POST['force_ipv4']) ? '1' : '0';

        // Auto-correct if user accidentally pasted their email into the SMTP Server field
        if (str_contains($smtpServer, '@')) {
            $domainPart = strtolower(trim(substr(strrchr($smtpServer, '@'), 1)));
            if ($domainPart === 'gmail.com' || $domainPart === 'googlemail.com') {
                $smtpServer = 'smtp.gmail.com';
            } else {
                $smtpServer = 'mail.' . $domainPart;
            }
        }
        // Strip protocol prefix (ssl:// or tls://) and trailing slashes if typed
        $smtpServer = preg_replace('/^(ssl|tls):\/\//i', '', rtrim($smtpServer, '/'));

        // Auto-correct mistyped Gmail hosts
        if (in_array(strtolower($smtpServer), ['mail.gmail.com', 'gmail.com', 'googlemail.com', 'mail.google.com'], true)) {
            $smtpServer = 'smtp.gmail.com';
        }

        // If authenticated SMTP email is on cloudaspiresolutions.in, ensure host matches domain mail server
        if (str_contains(strtolower($smtpEmail), '@cloudaspiresolutions.in') && str_contains(strtolower($smtpServer), 'gmail')) {
            $smtpServer = 'mail.cloudaspiresolutions.in';
        }

        // Auto-align protocol if port 465 (SMTPS) is selected
        if ((int)$smtpPort === 465 && ($smtpProtocol === 'tls' || empty($smtpProtocol))) {
            $smtpProtocol = 'ssl';
        }

        // Validation
        $validationError = '';
        if (empty($adminEmail) || !filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
            $validationError = 'Please enter a valid Admin Email address.';
        } elseif (empty($smtpEmail) || !filter_var($smtpEmail, FILTER_VALIDATE_EMAIL)) {
            $validationError = 'Please enter a valid SMTP Email address.';
        } elseif (empty($smtpServer)) {
            $validationError = 'SMTP Server hostname is required.';
        } elseif (preg_match('/[\s\/\\:]/', $smtpServer)) {
            $validationError = 'SMTP Server hostname contains invalid characters or port numbers (e.g. use mail.cloudaspiresolutions.in).';
        } elseif ((int)$smtpPort < 1 || (int)$smtpPort > 65535) {
            $validationError = 'SMTP Port must be a valid number between 1 and 65535 (usually 465 or 587).';
        }

        if ($validationError !== '') {
            $message = $validationError;
            $message_type = 'error';
        } else {
            $settings = [
                'mail_admin_email' => $adminEmail,
                'mail_smtp_email' => $smtpEmail,
                'mail_smtp_server' => $smtpServer,
                'mail_smtp_port' => $smtpPort,
                'mail_smtp_protocol' => in_array($smtpProtocol, ['tls', 'ssl', 'none'], true) ? $smtpProtocol : 'tls',
                'mail_from_name' => $fromName,
                'mail_force_ipv4' => $forceIpv4,
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
            $mailSettings = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $mailSettings[$row['setting_key']] = $row['setting_value'];
            }
        }
    }
}

// Test SMTP Connection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_smtp'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid request. Please refresh and try again.';
        $message_type = 'error';
    } else {
        $serverHost = getSettingValue('mail_smtp_server');
        $serverEmail = getSettingValue('mail_smtp_email');

        if (empty($serverHost) || empty($serverEmail)) {
            $message = 'SMTP settings are not configured. Please enter and save your SMTP server and email first.';
            $message_type = 'error';
        } else {
            $debugOutput = '';
            $mail = null;
            try {
                $mail = createMailer($conn, PHPMailer\PHPMailer\SMTP::DEBUG_CONNECTION, function($str, $level) use (&$debugOutput) {
                    $debugOutput .= $str . "\n";
                });

                if (!$mail->smtpConnect()) {
                    throw new Exception('Could not connect to SMTP server');
                }

                $mail->smtpClose();
                $message = 'SMTP connection successful! Connected and authenticated with ' . htmlspecialchars($mail->Host) . ' on port ' . $mail->Port . '.';
                $message_type = 'success';
            } catch (\Throwable $e) {
                // Log full technical trace in protected server error logs
                error_log("[SMTP Connection Test Failed] Host: " . ($mail->Host ?? $serverHost) . " | Exception: " . $e->getMessage() . (!empty($mail->ErrorInfo) ? " | ErrorInfo: " . $mail->ErrorInfo : "") . "\nDebug trace: " . $debugOutput);

                // Show clean, user-friendly error in the Admin Panel
                $message = formatSmtpUserErrorMessage($e, $mail, $mail->Host ?? $serverHost);
                $message_type = 'error';
            }
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

        if (empty($test_email) || !filter_var($test_email, FILTER_VALIDATE_EMAIL)) {
            $message = 'Please enter a valid recipient email address for testing.';
            $message_type = 'error';
        } else {
            $serverHost = getSettingValue('mail_smtp_server');
            $smtpEmail = getSettingValue('mail_smtp_email');
            $fromName = getSettingValue('mail_from_name', 'Cloud Aspire');

            if (empty($serverHost) || empty($smtpEmail)) {
                $message = 'SMTP settings are not configured. Please save your SMTP settings first.';
                $message_type = 'error';
            } else {
                $debugOutput = '';
                $mail = null;
                try {
                    $mail = createMailer($conn, PHPMailer\PHPMailer\SMTP::DEBUG_CONNECTION, function($str, $level) use (&$debugOutput) {
                        $debugOutput .= $str . "\n";
                    });

                    $mail->setFrom($smtpEmail, $fromName);
                    $mail->addAddress($test_email);
                    $mail->isHTML(true);
                    $mail->Subject = 'Test Email from Cloud Aspire Admin';
                    $mail->Body = '<h2>SMTP Delivery Verified!</h2><p>This is a test email sent from the Cloud Aspire administration panel.</p><p>If you received this message, your outbound SMTP configuration and delivery pipeline are working properly.</p>';
                    $mail->AltBody = 'SMTP Delivery Verified! This is a test email sent from the Cloud Aspire administration panel. Your outbound SMTP configuration is working properly.';

                    sendWithRetry($mail, 2);

                    try {
                        $stmtLog = $conn->prepare("INSERT INTO mail_logs (recipient, subject, status, error_message, sent_at) VALUES (?, ?, 'sent', NULL, NOW())");
                        $stmtLog->execute([$test_email, 'Test Email from Cloud Aspire Admin']);
                    } catch (\Exception $logEx) {}

                    $message = 'Test email accepted by SMTP server and delivered successfully to ' . htmlspecialchars($test_email) . '.';
                    $message_type = 'success';
                } catch (\Throwable $e) {
                    error_log("[SMTP Send Test Email Failed] Recipient: $test_email | Host: " . ($mail->Host ?? $serverHost) . " | Exception: " . $e->getMessage() . (!empty($mail->ErrorInfo) ? " | ErrorInfo: " . $mail->ErrorInfo : "") . "\nDebug trace: " . $debugOutput);

                    try {
                        $stmtLog = $conn->prepare("INSERT INTO mail_logs (recipient, subject, status, error_message, sent_at) VALUES (?, ?, 'failed', ?, NOW())");
                        $stmtLog->execute([$test_email, 'Test Email from Cloud Aspire Admin', substr($e->getMessage(), 0, 500)]);
                    } catch (\Exception $logEx) {}

                    $message = 'Failed to send test email: ' . formatSmtpUserErrorMessage($e, $mail, $mail->Host ?? $serverHost);
                    $message_type = 'error';
                }
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
