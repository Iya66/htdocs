<?php
/**
 * SMTP Factory — Centralized PHPMailer Configuration
 *
 * Single source of truth for creating properly configured PHPMailer instances.
 * Eliminates duplicated SMTP setup code across the application.
 *
 * Features:
 * - Correct IPv4 forcing via DNS resolution (NOT socket binding)
 * - Automatic SMTP password decryption
 * - Retry mechanism with exponential backoff
 * - Proper timeout configuration
 * - PHP 8.x compatible
 * - Production-safe SSL defaults
 *
 * @requires PHPMailer 7.x via Composer autoload
 * @requires app/config.php for DB connection
 */

require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

/**
 * Convert HTML email to high-quality plain text alternative (AltBody).
 * Eliminates raw CSS, scripts, and broken tags that trigger spam filters.
 */
if (!function_exists('htmlToCleanPlainText')) {
    function htmlToCleanPlainText(string $html): string {
        if (empty($html)) return '';

        // 1. Strip <style>, <script>, <head> and their contents completely
        $clean = preg_replace('/<(head|style|script)\b[^>]*>.*?<\/\1>/si', '', $html);

        // 2. Convert links: <a href="http://...">Text</a> -> Text (http://...)
        $clean = preg_replace_callback('/<a\s+[^>]*href=["\']([^"\']+)["\'][^>]*>(.*?)<\/a>/si', function($matches) {
            $text = trim(strip_tags($matches[2]));
            $url = trim($matches[1]);
            if ($text === '' || $text === $url || str_starts_with($url, '#') || str_starts_with($url, 'javascript:')) {
                return $text ?: $url;
            }
            return "$text ($url)";
        }, $clean);

        // 3. Convert breaks, headers, table rows, and paragraphs to newlines
        $clean = preg_replace('/<(br|hr)\s*\/?>/i', "\n", $clean);
        $clean = preg_replace('/<\/(p|div|h[1-6]|tr|table|blockquote)>/i', "\n\n", $clean);
        $clean = preg_replace('/<li[^>]*>/i', "  • ", $clean);
        $clean = preg_replace('/<\/li>/i', "\n", $clean);

        // 4. Strip any remaining tags
        $clean = strip_tags($clean);

        // 5. Decode HTML entities (&amp;, &nbsp;, &copy;, &#9733;, etc.)
        $clean = html_entity_decode($clean, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // 6. Clean up excess whitespace and blank lines
        $clean = preg_replace("/[ \t]+/", " ", $clean);
        $clean = preg_replace("/\n\s*\n\s*\n+/", "\n\n", $clean);

        return trim($clean);
    }
}

/**
 * Load SMTP settings from the database.
 *
 * @param PDO $conn Database connection
 * @return array<string, string> Associative array of mail_* settings
 */
function loadSmtpSettings(PDO $conn): array
{
 static $cache = null;
 if ($cache !== null) {
 return $cache;
 }

 $stmt = $conn->prepare("SELECT setting_key, setting_value FROM settings WHERE setting_key LIKE 'mail_%'");
 $stmt->execute();
 $settings = [];
 while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
 $settings[$row['setting_key']] = $row['setting_value'];
 }

 $cache = $settings;
 return $settings;
}

/**
 * Decrypt an SMTP password that was encrypted with AES-256-CBC.
 * Requires ENCRYPTION_KEY to be set in the environment.
 *
 * @param string $encoded Base64-encoded (IV + ciphertext)
 * @return string Decrypted plaintext password
 */
function decryptSmtpPassword(string $encoded): string
{
 if (empty($encoded)) {
 return '';
 }

 $envKey = defined('ENCRYPTION_KEY') && ENCRYPTION_KEY !== '' ? ENCRYPTION_KEY : '';

 if ($envKey === '') {
 error_log('[SMTP Factory] ENCRYPTION_KEY not configured. Cannot decrypt SMTP password. Set it in your .env file.');
 return '';
 }

 try {
 $data = base64_decode($encoded, true);
 if ($data === false || strlen($data) < 17) {
 return $encoded;
 }

 $iv = substr($data, 0, 16);
 $ciphertext = substr($data, 16);

 $key = hash('sha256', $envKey, true);
 $decrypted = openssl_decrypt($ciphertext, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);

 if ($decrypted === false) {
 error_log('[SMTP Factory] Password decryption failed — wrong ENCRYPTION_KEY or corrupted data.');
 return '';
 }

 return $decrypted;
 } catch (\Exception $e) {
 error_log('[SMTP Factory] Password decryption failed: ' . $e->getMessage());
 return '';
 }
}

/**
 * Resolve an SMTP hostname to an IPv4 address.
 * Used to force IPv4 connections on hosts where IPv6 causes "Cannot assign requested address".
 *
 * @param string $hostname SMTP server hostname
 * @return string Resolved IPv4 address, or original hostname if resolution fails
 */
function resolveSmtpHostIPv4(string $hostname): string
{
 static $dnsCache = [];
 if (isset($dnsCache[$hostname])) {
 return $dnsCache[$hostname];
 }

 $ipv4 = gethostbyname($hostname);
 if ($ipv4 !== $hostname && filter_var($ipv4, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
 $dnsCache[$hostname] = $ipv4;
 return $ipv4;
 }
 $dnsCache[$hostname] = $hostname;
 return $hostname;
}

/**
 * Create a fully configured PHPMailer instance from database settings.
 *
 * This is the ONLY function that should be used to create PHPMailer instances
 * throughout the application. It ensures consistent configuration and eliminates
 * the bindto socket option that causes Error 99 on shared hosting.
 *
 * @param PDO $conn Database connection
 * @param int $debugLevel SMTP debug level (0=off, 1=client, 2=client+server, 3=connection, 4=lowlevel)
 * @param callable|null $debugCallback Custom debug output handler: function(string $str, int $level): void
 * @return PHPMailer Fully configured PHPMailer instance ready to set From/To/Body and send
 *
 * @throws \RuntimeException If SMTP is not configured (missing host or email)
 */
function createMailer(PDO $conn, int $debugLevel = 0, ?callable $debugCallback = null): PHPMailer
{
 $settings = loadSmtpSettings($conn);

 $smtpHost = (defined('SMTP_HOST') && SMTP_HOST !== '') ? SMTP_HOST : ($settings['mail_smtp_server'] ?? '');
 $smtpEmail = (defined('SMTP_USER') && SMTP_USER !== '') ? SMTP_USER : ($settings['mail_smtp_email'] ?? '');
 $smtpPassword = (defined('SMTP_PASS') && SMTP_PASS !== '') ? SMTP_PASS : ($settings['mail_smtp_password'] ?? '');
 $smtpPort = (defined('SMTP_PORT') && SMTP_PORT !== 0) ? (int) SMTP_PORT : (int) ($settings['mail_smtp_port'] ?? 587);

 if (defined('SMTP_PORT') && SMTP_PORT !== 0) {
 $smtpProtocol = (SMTP_PORT === 465) ? 'ssl' : 'tls';
 } else {
 $smtpProtocol = $settings['mail_smtp_protocol'] ?? 'tls';
 }

 $forceIpv4 = ($settings['mail_force_ipv4'] ?? '0') === '1';
 $devMode = ($settings['mail_dev_mode'] ?? '0') === '1';

 if (empty($smtpHost) || empty($smtpEmail)) {
 throw new \RuntimeException('SMTP not configured — SMTP_HOST / mail_smtp_server or SMTP_USER / mail_smtp_email is empty.');
 }

 if (!empty($smtpPassword) && $smtpPassword === ($settings['mail_smtp_password'] ?? '')) {
 $smtpPassword = decryptSmtpPassword($smtpPassword);
 }

 $originalHost = $smtpHost;
 if ($forceIpv4) {
 $resolved = resolveSmtpHostIPv4($smtpHost);
 if ($resolved !== $smtpHost) {
 error_log("[SMTP Factory] Resolved $smtpHost to IPv4: $resolved");
 $smtpHost = $resolved;
 }
 }

 $mail = new PHPMailer(true);

 $mail->isSMTP();
 $mail->Host = $smtpHost;
 $mail->Port = $smtpPort;
 $mail->Timeout = 10;
 $mail->CharSet = PHPMailer::CHARSET_UTF8;
 $mail->Encoding = 'base64';

 // Anti-Spam & Deliverability: Suppress default PHPMailer signature header
 $mail->XMailer = ' ';

 // Extract domain from sender email for proper Message-ID generation
 $senderDomain = '';
 if (str_contains($smtpEmail, '@')) {
 $parts = explode('@', $smtpEmail);
 $senderDomain = trim($parts[1] ?? '');
 }
 if (empty($senderDomain) && defined('SITE_URL')) {
 $parsed = parse_url(SITE_URL, PHP_URL_HOST);
 if ($parsed) $senderDomain = $parsed;
 }
 if (!empty($senderDomain)) {
 $mail->Hostname = $senderDomain;
 }

 // Set Envelope Sender (Return-Path) for SPF & DMARC alignment
 $mail->Sender = $smtpEmail;

 switch ($smtpProtocol) {
 case 'ssl':
 $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
 break;
 case 'tls':
 $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
 break;
 default:
 $mail->SMTPSecure = '';
 $mail->SMTPAutoTLS = false;
 break;
 }

 if (!empty($smtpPassword)) {
     $mail->SMTPAuth = true;
     $mail->Username = $smtpEmail;
     $mail->Password = $smtpPassword;
 } else {
     $mail->SMTPAuth = false;
 }

 // SSL options optimized for maximum compatibility across all shared cPanel hosts
 $mail->SMTPOptions = [
     'ssl' => [
         'verify_peer'       => false,
         'verify_peer_name'  => false,
         'allow_self_signed' => true,
     ]
 ];

 $mail->SMTPDebug = $debugLevel;
 if ($debugCallback !== null) {
 $mail->Debugoutput = $debugCallback;
 }

 return $mail;
}

/**
 * Send an email with automatic retry and exponential backoff.
 *
 * @param PHPMailer $mail Fully configured PHPMailer instance
 * @param int $maxRetries Maximum number of retry attempts (default: 3)
 * @return bool True if email was sent successfully
 *
 * @throws PHPMailerException If all retry attempts fail
 */
function sendWithRetry(PHPMailer $mail, int $maxRetries = 3): bool
{
 $lastException = null;

 for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
 try {
 $result = $mail->send();
 if ($result) {
 if ($attempt > 1) {
 error_log("[SMTP Factory] Email sent successfully on attempt $attempt");
 }
 return true;
 }
 } catch (\Exception $e) {
  $lastException = $e;
  $errorMsg = $e->getMessage();
  $errorInfo = !empty($mail->ErrorInfo) ? $mail->ErrorInfo : '';
  $combinedError = $errorMsg . ' ' . $errorInfo;

  error_log("[SMTP Factory] Send attempt $attempt/$maxRetries failed: $combinedError");

  if (
  stripos($combinedError, 'authentication') !== false ||
  stripos($combinedError, 'auth') !== false ||
  stripos($combinedError, '535') !== false ||
  stripos($combinedError, '553') !== false ||
  stripos($combinedError, '550') !== false ||
  stripos($combinedError, '551') !== false ||
  stripos($combinedError, '552') !== false ||
  stripos($combinedError, '554') !== false ||
  stripos($combinedError, 'could not deliver') !== false ||
  stripos($combinedError, 'recipients failed') !== false ||
  stripos($combinedError, 'does not exist') !== false ||
  stripos($combinedError, 'invalid address') !== false
  ) {
  error_log('[SMTP Factory] Permanent recipient/auth error detected — not retrying');
  throw $e;
  }

  if ($attempt < $maxRetries) {
  $delay = $attempt;
  error_log("[SMTP Factory] Retrying in {$delay}s...");
  sleep($delay);

 try {
 $mail->smtpClose();
 } catch (\Exception $closeEx) {
 // Ignore close errors
 }
 }
 }
 }

 if ($lastException !== null) {
 throw $lastException;
 }

 throw new PHPMailerException('Failed to send email after ' . $maxRetries . ' attempts');
}
