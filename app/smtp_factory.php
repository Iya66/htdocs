<?php
/**
 * SMTP Factory — Centralized PHPMailer Configuration
 *
 * Single source of truth for creating properly configured PHPMailer instances.
 * Eliminates duplicated SMTP setup code across the application.
 *
 * Deliverability & Anti-Spam Optimizations:
 * - Domain alignment: Envelope Sender (Return-Path) matches From address.
 * - Proper HELO/EHLO domain greeting and Message-ID under authenticated domain.
 * - Suppresses X-Mailer identification header.
 * - Safe error message sanitization (no internal paths or credentials leaked).
 * - IPv4 resolution fallback for hosts with broken IPv6 routing.
 * - Retry mechanism with exponential backoff.
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
 * Requires ENCRYPTION_KEY to be set in the environment or config.
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
 * Format a sanitized, user-friendly error message for the Admin Panel,
 * preventing exposure of internal paths, credentials, and raw stack traces.
 */
function formatSmtpUserErrorMessage(\Throwable $e, ?PHPMailer $mail = null, string $configuredHost = ''): string
{
    $msg = $e->getMessage();
    $errorInfo = ($mail && !empty($mail->ErrorInfo)) ? $mail->ErrorInfo : '';
    $combined = $msg . ' ' . $errorInfo;

    $hostLabel = $configuredHost !== '' ? " ('" . htmlspecialchars($configuredHost, ENT_QUOTES, 'UTF-8') . "')" : '';

    if (stripos($combined, 'getaddrinfo') !== false || stripos($combined, 'php_network_getaddresses') !== false) {
        return "DNS resolution failed: The SMTP server host{$hostLabel} could not be resolved. Please verify the hostname and your network DNS settings.";
    }

    if (stripos($combined, 'Connection refused') !== false) {
        return "Connection refused: The SMTP server{$hostLabel} rejected the connection. Please verify the port number and firewall rules.";
    }

    if (stripos($combined, 'timed out') !== false || stripos($combined, 'Operation timed out') !== false || stripos($combined, 'Maximum execution time') !== false) {
        return "Connection timed out: The SMTP server{$hostLabel} did not respond within the timeout window. Check network connectivity or try an alternative port.";
    }

    if (stripos($combined, 'Could not authenticate') !== false || stripos($combined, '535') !== false || stripos($combined, 'Username and Password not accepted') !== false || stripos($combined, 'BadCredentials') !== false) {
        return "Authentication failed: Invalid SMTP username or password. Please verify the credentials.";
    }

    if (stripos($combined, 'STARTTLS') !== false || stripos($combined, 'stream_socket_enable_crypto') !== false || stripos($combined, 'certificate') !== false || stripos($combined, 'handshake') !== false) {
        return "Encryption negotiation failed: Could not establish a secure TLS/SSL handshake with{$hostLabel}. Verify the protocol (TLS on 587 or SSL on 465).";
    }

    if (stripos($combined, '550') !== false || stripos($combined, '553') !== false || stripos($combined, '554') !== false || stripos($combined, 'Relay access denied') !== false) {
        return "Mail delivery rejected by SMTP server: Relaying was denied or recipient was rejected.";
    }

    if (stripos($combined, 'Could not connect to SMTP host') !== false) {
        return "Could not connect to SMTP server{$hostLabel}. Please check that the server name and port are correct and the host is accessible.";
    }

    // Default sanitized message without file paths or raw code traces
    $sanitized = preg_replace('/(\/[a-zA-Z0-9_\.\-]+)+/', '', $msg);
    $sanitized = preg_replace('/([A-Z]:\\\\[a-zA-Z0-9_\.\-\\\\]+)/', '', $sanitized);
    $sanitized = trim(strip_tags($sanitized));

    return "SMTP connection error: " . ($sanitized ?: 'Unable to communicate with SMTP server. Check server error logs for details.');
}

/**
 * Create a fully configured PHPMailer instance.
 *
 * Precedence rule: Database settings (mail_*) take priority when set.
 * If empty or unconfigured in the database, fallback constants from .env (SMTP_*) are used.
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

    // Precedence: Database settings take priority, falling back to environment constants.
    $smtpHost = !empty($settings['mail_smtp_server']) ? trim($settings['mail_smtp_server']) : (defined('SMTP_HOST') ? trim(SMTP_HOST) : '');
    $smtpEmail = !empty($settings['mail_smtp_email']) ? trim($settings['mail_smtp_email']) : (defined('SMTP_USER') ? trim(SMTP_USER) : '');
    
    // Decrypt database password if present, otherwise fall back to environment constant
    if (!empty($settings['mail_smtp_password'])) {
        $smtpPassword = decryptSmtpPassword($settings['mail_smtp_password']);
    } elseif (defined('SMTP_PASS') && SMTP_PASS !== '') {
        $smtpPassword = SMTP_PASS;
    } else {
        $smtpPassword = '';
    }

    if (!empty($settings['mail_smtp_port'])) {
        $smtpPort = (int)$settings['mail_smtp_port'];
    } elseif (defined('SMTP_PORT') && (int)SMTP_PORT !== 0) {
        $smtpPort = (int)SMTP_PORT;
    } else {
        $smtpPort = 465;
    }

    if (!empty($settings['mail_smtp_protocol'])) {
        $smtpProtocol = strtolower(trim($settings['mail_smtp_protocol']));
    } elseif ($smtpPort === 465) {
        $smtpProtocol = 'ssl';
    } else {
        $smtpProtocol = 'tls';
    }

    $forceIpv4 = ($settings['mail_force_ipv4'] ?? '0') === '1';
    $timeout = !empty($settings['mail_smtp_timeout']) ? (int)$settings['mail_smtp_timeout'] : 15;

    // Auto-correct invalid or mistyped hostnames like 'mail.gmail.com'
    if (in_array(strtolower($smtpHost), ['mail.gmail.com', 'gmail.com', 'googlemail.com', 'mail.google.com'], true)) {
        if (!empty($smtpEmail) && str_contains(strtolower($smtpEmail), '@cloudaspiresolutions.in')) {
            $smtpHost = 'mail.cloudaspiresolutions.in';
        } else {
            $smtpHost = 'smtp.gmail.com';
        }
    } elseif (str_contains(strtolower($smtpHost), 'gmail') && !empty($smtpEmail) && str_contains(strtolower($smtpEmail), '@cloudaspiresolutions.in')) {
        $smtpHost = 'mail.cloudaspiresolutions.in';
    }

    if (empty($smtpHost) || empty($smtpEmail)) {
        throw new \RuntimeException('SMTP not configured — please configure SMTP Server and SMTP Email in Admin Settings or .env.');
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
    $mail->Timeout = $timeout;
    $mail->CharSet = PHPMailer::CHARSET_UTF8;
    $mail->Encoding = 'base64';

    // Anti-Spam & Deliverability: Suppress default PHPMailer signature header cleanly
    $mail->XMailer = null;

    // Determine canonical domain for proper HELO/EHLO greeting and Message-ID domain
    // Always use the authenticated domain (e.g., cloudaspiresolutions.in) rather than localhost/IP
    $domainHost = '';
    if (!empty($smtpEmail) && str_contains($smtpEmail, '@')) {
        $domainHost = strtolower(trim(substr(strrchr($smtpEmail, '@'), 1)));
    }
    if (empty($domainHost) && defined('SITE_URL') && !empty(SITE_URL)) {
        $parsed = parse_url(SITE_URL, PHP_URL_HOST);
        if ($parsed && !in_array($parsed, ['localhost', '127.0.0.1']) && str_contains($parsed, '.')) {
            $domainHost = $parsed;
        }
    }
    if (empty($domainHost)) {
        $domainHost = 'cloudaspiresolutions.in';
    }
    $mail->Hostname = $domainHost;

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

    // SSL / TLS verification configuration
    $allowSelfSigned = ($settings['mail_allow_self_signed'] ?? '0') === '1';
    if ($allowSelfSigned) {
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer'       => false,
                'verify_peer_name'  => false,
                'allow_self_signed' => true,
            ]
        ];
    } else {
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer'       => true,
                'verify_peer_name'  => true,
                'allow_self_signed' => false,
            ]
        ];
    }

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
