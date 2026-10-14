<?php
/**
 * Email Helper - Send emails using PHPMailer with settings from database
 * 
 * Uses centralized SMTP Factory for consistent PHPMailer configuration.
 * All SMTP setup is handled by smtp_factory.php — no inline config here.
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/smtp_factory.php';
require_once __DIR__ . '/mail_queue.php';


// SMTP Password Decryption — delegates to smtp_factory.php
// Kept as alias for backward compatibility with any external callers
if (!function_exists('decryptSMTPPassword')) {
    function decryptSMTPPassword(string $encoded): string {
        return decryptSmtpPassword($encoded);
    }
}


function safe_html($string) {
    return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8', false);
}

// Error logging for debugging
function logEmailError(string $function, string $message) {
    error_log("[Email Error - $function] $message");
    global $conn;
    if (isset($conn)) {
        try {
            $conn->prepare("INSERT INTO admin_activities (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)")
                ->execute([$_SESSION['admin_id'] ?? 0, 'EMAIL_ERROR', "$function: $message", $_SERVER['REMOTE_ADDR'] ?? '']);
        } catch (Exception $e) {
            // Silently fail
        }
    }
}

/**
 * Send an email either asynchronously via the mail queue (default, instant response)
 * or synchronously (direct connection).
 *
 * @param mixed  $to       Recipient email address(es)
 * @param string $subject  Email subject
 * @param string $body     HTML message body
 * @param string $altBody  Plain text alternative
 * @param bool   $async    True to queue and send in background (instant), false for direct send
 * @return array{success: bool, message: string, queued?: bool}
 */
function sendEmail(mixed $to, string $subject, string $body, string $altBody = '', bool $async = false): array {
    global $conn;

    if ($async) {
        return enqueueEmail($conn, $to, $subject, $body, $altBody, true);
    }

    return sendEmailDirect($to, $subject, $body, $altBody);
}

/**
 * Explicit helper to enqueue an email for asynchronous delivery.
 */
function queueEmail(mixed $to, string $subject, string $body, string $altBody = ''): array {
    global $conn;
    return enqueueEmail($conn, $to, $subject, $body, $altBody, true);
}

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
 * Direct synchronous email sending via PHPMailer.
 * Used for interactive SMTP diagnostics (Test SMTP / Send Test Email) where
 * immediate inline confirmation or debug output is required.
 */
function sendEmailDirect(mixed $to, string $subject, string $body, string $altBody = ''): array {
    global $conn;

    $recipientStr = is_array($to) ? implode(', ', $to) : $to;

    try {
        // Validate recipient
        if (empty($to) || (is_array($to) && count(array_filter($to)) === 0)) {
            $msg = 'No recipient email address provided';
            logEmailError('sendEmailDirect', $msg);
            try {
                $stmtLog = $conn->prepare("INSERT INTO mail_logs (recipient, subject, status, error_message, sent_at) VALUES (?, ?, 'failed', ?, NOW())");
                $stmtLog->execute([$recipientStr, $subject, $msg]);
            } catch (Exception $logEx) {}
            return ['success' => false, 'message' => $msg];
        }

        // Load settings for from_name and admin_email (factory handles SMTP config)
        $settings = loadSmtpSettings($conn);
        $smtp_email  = $settings['mail_smtp_email'] ?? '';
        $from_name   = $settings['mail_from_name'] ?? (defined('SMTP_FROM_NAME') ? SMTP_FROM_NAME : 'Cloud Aspire');
        $admin_email = $settings['mail_admin_email'] ?? $smtp_email;

        // Create mailer via centralized factory (handles host, port, auth, encryption, IPv4, timeouts)
        $mail = createMailer($conn);

        // Sender & Return-Path alignment for SPF/DMARC
        $mail->setFrom($smtp_email, $from_name);
        $mail->Sender = $smtp_email;

        // Anti-Spam / Deliverability Headers
        $mail->addCustomHeader('Auto-Submitted', 'auto-generated');
        $mail->addCustomHeader('X-Auto-Response-Suppress', 'All');

        // Handle multiple recipients
        if (is_array($to)) {
            foreach ($to as $recipient) {
                if (!empty($recipient) && filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
                    $mail->addAddress($recipient);
                }
            }
        } else {
            if (!empty($to) && filter_var($to, FILTER_VALIDATE_EMAIL)) {
                $mail->addAddress($to);
            }
        }

        if (!empty($admin_email)) {
            $mail->addReplyTo($admin_email, $from_name);
        }

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = !empty($altBody) ? $altBody : htmlToCleanPlainText($body);

        // Send with automatic retry (2 attempts)
        sendWithRetry($mail, 2);

        error_log("[Email Success] Sent to: $recipientStr");

        try {
            $stmtLog = $conn->prepare("INSERT INTO mail_logs (recipient, subject, status, sent_at) VALUES (?, ?, 'sent', NOW())");
            $stmtLog->execute([$recipientStr, $subject]);
        } catch (Exception $logEx) {}

        return ['success' => true, 'message' => 'Email sent successfully'];
    } catch (Exception $e) {
        $errorMsg = $e->getMessage();
        if (isset($mail) && !empty($mail->ErrorInfo)) {
            $errorMsg .= ' | ' . $mail->ErrorInfo;
        }
        logEmailError('sendEmailDirect', $errorMsg);
        error_log("[Email Error] " . $errorMsg);

        try {
            $stmtLog = $conn->prepare("INSERT INTO mail_logs (recipient, subject, status, error_message, sent_at) VALUES (?, ?, 'failed', ?, NOW())");
            $stmtLog->execute([$recipientStr, $subject, $errorMsg]);
        } catch (Exception $logEx) {}

        return ['success' => false, 'message' => $errorMsg];
    }
}

/**
 * Build a modern, professional branded HTML email layout optimized for 100% inbox deliverability.
 *
 * @param array $options Configuration options:
 *                       - title (string): Main headline
 *                       - subtitle (string, optional): Header subtext
 *                       - badge (string, optional): Status/category tag
 *                       - badge_color (string, optional): Hex color for badge
 *                       - content_html (string): Main body content HTML
 *                       - button_text (string, optional): CTA button label
 *                       - button_url (string, optional): CTA button URL
 *                       - footer_note (string, optional): Special note in footer
 * @return string Complete responsive HTML document
 */
function buildProfessionalEmailTemplate(array $options): string {
    global $conn;

    $siteName  = defined('SITE_NAME') ? SITE_NAME : 'Cloud Aspire';
    $siteUrl   = defined('SITE_URL') ? rtrim(SITE_URL, '/') : 'https://cloudaspire.in';
    $siteLogo  = '';
    $contactEmail = 'cloudaspire2025@gmail.com';

    if (isset($conn)) {
        try {
            $stmt = $conn->query("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('site_name', 'site_logo', 'contact_email', 'mail_from_name')");
            $s = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            if (!empty($s['site_name'])) $siteName = $s['site_name'];
            if (!empty($s['site_logo'])) $siteLogo = $s['site_logo'];
            if (!empty($s['contact_email'])) $contactEmail = $s['contact_email'];
        } catch (Exception $e) {}
    }

    $title        = $options['title'] ?? $siteName;
    $subtitle     = $options['subtitle'] ?? '';
    $badge        = $options['badge'] ?? '';
    $badgeColor   = $options['badge_color'] ?? '#4f46e5';
    $contentHtml  = $options['content_html'] ?? '';
    $buttonText   = $options['button_text'] ?? '';
    $buttonUrl    = $options['button_url'] ?? '';
    $footerNote   = $options['footer_note'] ?? '';

    // Build logo URL
    $logoHtml = '';
    if (!empty($siteLogo)) {
        $logoUrl = (str_starts_with($siteLogo, 'http://') || str_starts_with($siteLogo, 'https://'))
            ? $siteLogo
            : $siteUrl . '/' . ltrim($siteLogo, '/');
        $logoHtml = '<img src="' . safe_html($logoUrl) . '" alt="' . safe_html($siteName) . '" style="max-height: 48px; max-width: 200px; height: auto; width: auto; object-fit: contain; vertical-align: middle; display: inline-block; margin-bottom: 8px;">';
    } else {
        $logoHtml = '<div style="font-size: 24px; font-weight: 800; color: #1e1b4b; letter-spacing: -0.5px; margin-bottom: 4px;">' . safe_html($siteName) . '</div>';
    }

    $buttonHtml = '';
    if (!empty($buttonText) && !empty($buttonUrl)) {
        $buttonHtml = '
        <div style="text-align: center; margin: 30px 0 10px 0;">
            <a href="' . safe_html($buttonUrl) . '" target="_blank" style="background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%); color: #ffffff; padding: 13px 32px; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 14px; display: inline-block; letter-spacing: 0.2px; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);">' . safe_html($buttonText) . ' &rarr;</a>
        </div>';
    }

    $badgeHtml = '';
    if (!empty($badge)) {
        $badgeHtml = '<div style="display: inline-block; padding: 5px 14px; background: ' . safe_html($badgeColor) . '; color: #ffffff; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 12px;">' . safe_html($badge) . '</div>';
    }

    return '<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>' . safe_html($title) . '</title>
    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <![endif]-->
</head>
<body style="margin: 0; padding: 0; background-color: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased; color: #334155; line-height: 1.6;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #f1f5f9; padding: 32px 16px;">
        <tr>
            <td align="center">
                <!-- Email Container -->
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width: 600px; width: 100%; margin: 0 auto;">
                    <!-- Brand Header -->
                    <tr>
                        <td align="center" style="padding-bottom: 20px;">
                            <a href="' . safe_html($siteUrl) . '" target="_blank" style="text-decoration: none; display: inline-block;">
                                ' . $logoHtml . '
                            </a>
                        </td>
                    </tr>
                    
                    <!-- Main Card -->
                    <tr>
                        <td>
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06); border: 1px solid #e2e8f0;">
                                <!-- Card Header Banner -->
                                <tr>
                                    <td style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #4338ca 100%); padding: 32px 28px; text-align: center;">
                                        ' . $badgeHtml . '
                                        <h1 style="color: #ffffff; margin: 0; font-size: 22px; font-weight: 700; line-height: 1.3;">' . safe_html($title) . '</h1>
                                        ' . (!empty($subtitle) ? '<p style="color: #cbd5e1; margin: 8px 0 0 0; font-size: 14px; line-height: 1.5;">' . safe_html($subtitle) . '</p>' : '') . '
                                    </td>
                                </tr>

                                <!-- Card Body -->
                                <tr>
                                    <td style="padding: 32px 28px; font-size: 15px; line-height: 1.6; color: #334155;">
                                        ' . $contentHtml . '
                                        ' . $buttonHtml . '
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Footer (CAN-SPAM & Anti-Spam Compliant) -->
                    <tr>
                        <td style="padding: 24px 16px 12px 16px; text-align: center; color: #64748b; font-size: 12px; line-height: 1.6;">
                            ' . (!empty($footerNote) ? '<p style="margin: 0 0 10px 0; color: #64748b; font-size: 11px;">' . safe_html($footerNote) . '</p>' : '') . '
                            <p style="margin: 0 0 6px 0; color: #475569; font-size: 11px;">
                                You are receiving this automated notification because a service inquiry or review was submitted on <a href="' . safe_html($siteUrl) . '" target="_blank" style="color: #4f46e5; text-decoration: none;">' . safe_html($siteName) . '</a>.
                            </p>
                            <p style="margin: 0 0 8px 0; color: #334155; font-weight: 600;">&copy; ' . date('Y') . ' ' . safe_html($siteName) . ' &bull; Pondicherry, India</p>
                            <p style="margin: 0;">
                                <a href="' . safe_html($siteUrl) . '" target="_blank" style="color: #4f46e5; text-decoration: none; margin: 0 8px;">Official Website</a> &bull;
                                <a href="' . safe_html($siteUrl) . '/privacy-policy" target="_blank" style="color: #4f46e5; text-decoration: none; margin: 0 8px;">Privacy Policy</a> &bull;
                                <a href="mailto:' . safe_html($contactEmail) . '" style="color: #4f46e5; text-decoration: none; margin: 0 8px;">Support Contact</a>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>';
}

function sendLeadNotification(array $lead_data, bool $async = true) {
    global $conn;

    $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = 'mail_admin_email'");
    $stmt->execute();
    $admin_email = $stmt->fetchColumn() ?: '';

    if (empty($admin_email)) {
        $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = 'contact_email'");
        $stmt->execute();
        $admin_email = $stmt->fetchColumn() ?: 'cloudaspire2026@gmail.com';
    }

    $subject = 'New Lead Received - ' . ($lead_data['name'] ?? 'Unknown');
    
    $content = '
    <p style="margin-top: 0;">A new lead inquiry has been submitted through your website:</p>
    <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 18px; margin: 20px 0;">
        <table width="100%" cellspacing="0" cellpadding="6" style="font-size: 14px;">
            <tr><td style="color: #64748b; width: 30%;"><strong>Name:</strong></td><td style="color: #0f172a; font-weight: 600;">' . safe_html($lead_data['name'] ?? 'N/A') . '</td></tr>
            <tr><td style="color: #64748b;"><strong>Email:</strong></td><td><a href="mailto:' . safe_html($lead_data['email'] ?? '') . '" style="color: #4f46e5; text-decoration: none;">' . safe_html($lead_data['email'] ?? 'N/A') . '</a></td></tr>
            <tr><td style="color: #64748b;"><strong>Phone:</strong></td><td style="color: #0f172a;">' . safe_html($lead_data['phone'] ?? 'N/A') . '</td></tr>
            <tr><td style="color: #64748b;"><strong>Service:</strong></td><td><span style="background: #e0e7ff; color: #3730a3; padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight: 600;">' . safe_html($lead_data['service'] ?? 'General') . '</span></td></tr>
            <tr><td style="color: #64748b; vertical-align: top;"><strong>Message:</strong></td><td style="color: #334155; line-height: 1.5;">' . nl2br(safe_html($lead_data['message'] ?? 'N/A')) . '</td></tr>
        </table>
    </div>
    <p style="color: #64748b; font-size: 13px; margin: 0;">Received at ' . date('Y-m-d H:i:s') . '</p>';

    $body = buildProfessionalEmailTemplate([
        'title'        => 'New Lead Submission',
        'subtitle'     => 'An inquiry was submitted via the contact/lead form',
        'badge'        => 'NEW LEAD',
        'badge_color'  => '#10b981',
        'content_html' => $content,
        'button_text'  => 'Open Admin Panel',
        'button_url'   => (defined('SITE_URL') ? SITE_URL : 'https://cloudaspire.in') . '/admin/leads.php'
    ]);

    return sendEmail($admin_email, $subject, $body, '', $async);
}

function sendContactNotification(array $contact_data, bool $async = true) {
    global $conn;

    $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = 'mail_admin_email'");
    $stmt->execute();
    $admin_email = $stmt->fetchColumn() ?: '';

    if (empty($admin_email)) {
        $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = 'contact_email'");
        $stmt->execute();
        $admin_email = $stmt->fetchColumn() ?: 'cloudaspire2026@gmail.com';
    }

    $subject = 'New Contact Form Submission - ' . ($contact_data['name'] ?? 'Unknown');

    $content = '
    <p style="margin-top: 0;">A visitor sent a new message from the contact form:</p>
    <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 18px; margin: 20px 0;">
        <table width="100%" cellspacing="0" cellpadding="6" style="font-size: 14px;">
            <tr><td style="color: #64748b; width: 30%;"><strong>Name:</strong></td><td style="color: #0f172a; font-weight: 600;">' . safe_html($contact_data['name'] ?? 'N/A') . '</td></tr>
            <tr><td style="color: #64748b;"><strong>Email:</strong></td><td><a href="mailto:' . safe_html($contact_data['email'] ?? '') . '" style="color: #4f46e5; text-decoration: none;">' . safe_html($contact_data['email'] ?? 'N/A') . '</a></td></tr>
            <tr><td style="color: #64748b;"><strong>Subject:</strong></td><td style="color: #0f172a;">' . safe_html($contact_data['subject'] ?? 'N/A') . '</td></tr>
            <tr><td style="color: #64748b; vertical-align: top;"><strong>Message:</strong></td><td style="color: #334155; line-height: 1.5;">' . nl2br(safe_html($contact_data['message'] ?? 'N/A')) . '</td></tr>
        </table>
    </div>';

    $body = buildProfessionalEmailTemplate([
        'title'        => 'Contact Form Submission',
        'subtitle'     => 'New message from ' . safe_html($contact_data['name'] ?? 'Visitor'),
        'badge'        => 'CONTACT FORM',
        'badge_color'  => '#3b82f6',
        'content_html' => $content,
        'button_text'  => 'View in Admin',
        'button_url'   => (defined('SITE_URL') ? SITE_URL : 'https://cloudaspire.in') . '/admin/'
    ]);

    return sendEmail($admin_email, $subject, $body, '', $async);
}

function sendReportToAdmin(string $report_type, string $report_data) {
    global $conn;

    $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = 'mail_admin_email'");
    $stmt->execute();
    $admin_email = $stmt->fetchColumn() ?: '';

    $subject = 'CloudASPIRE Report: ' . ucfirst($report_type) . ' - ' . date('Y-m-d');
    
    $content = '
    <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 20px; margin: 15px 0;">
        ' . $report_data . '
    </div>';

    $body = buildProfessionalEmailTemplate([
        'title'        => ucfirst($report_type) . ' Report',
        'subtitle'     => 'Generated on ' . date('Y-m-d H:i:s'),
        'badge'        => 'SYSTEM REPORT',
        'badge_color'  => '#6366f1',
        'content_html' => $content,
        'footer_note'  => 'This is an automated system report generated by the administrative panel.'
    ]);

    return sendEmail($admin_email, $subject, $body);
}

// ========== ADDITIONAL EMAIL FUNCTIONS ==========

/**
 * Send welcome email to new user
 */
function sendWelcomeEmail(string $email, string $name, string $password = '') {
    global $conn;

    $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = 'site_name'");
    $stmt->execute();
    $site_name = $stmt->fetchColumn() ?: 'CloudASPIRE';

    $subject = "Welcome to {$site_name} - Account Created";
    
    $content = '
    <h2 style="color: #0f172a; margin-top: 0; font-size: 18px;">Hello, ' . safe_html($name) . '!</h2>
    <p>Your account has been successfully created and configured. You now have complete access to your account dashboard and services.</p>
    <div style="background: #f1f5f9; border-left: 4px solid #4f46e5; padding: 14px 18px; border-radius: 6px; margin: 20px 0;">
        <p style="margin: 0; font-size: 14px; color: #475569;">
            <strong>Quick Tip:</strong> If you ever forget your password, you can easily request a reset link anytime from the login screen.
        </p>
    </div>';

    $body = buildProfessionalEmailTemplate([
        'title'        => 'Welcome to ' . $site_name,
        'subtitle'     => 'Your account is ready to go',
        'badge'        => 'WELCOME ABOARD',
        'badge_color'  => '#4f46e5',
        'content_html' => $content,
        'button_text'  => 'Access Your Account',
        'button_url'   => (defined('SITE_URL') ? SITE_URL : 'https://cloudaspire.in') . '/login'
    ]);

    return sendEmail($email, $subject, $body);
}

/**
 * Send thank you email after lead submission (to user)
 */
function sendLeadThankYouEmail(string $email, string $name, string $service = '', bool $async = true) {
    global $conn;

    $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = 'site_name'");
    $stmt->execute();
    $site_name = $stmt->fetchColumn() ?: 'Cloud Aspire';

    $subject = "Thank You for Contacting {$site_name}";
    
    $content = '
    <div style="text-align: left;">
        <h2 style="color: #0f172a; margin-top: 0; font-size: 20px; font-weight: 700;">Hello, ' . safe_html($name) . '!</h2>
        <p style="color: #334155; line-height: 1.6; font-size: 15px;">
            Thank you for reaching out to us. We have successfully received your inquiry' . (!empty($service) ? ' regarding <strong>' . safe_html($service) . '</strong>' : '') . '.
        </p>
        
        <div style="background: linear-gradient(135deg, #f0fdf4 0%, #e0f2fe 100%); border: 1px solid #bbf7d0; border-radius: 12px; padding: 20px; margin: 24px 0;">
            <p style="margin: 0 0 8px 0; color: #166534; font-weight: 700; font-size: 15px;">What happens next?</p>
            <ul style="margin: 0; padding-left: 20px; color: #1e293b; font-size: 14px; line-height: 1.6;">
                <li>Our specialist team will review your requirements.</li>
                <li>We will prepare tailored solutions and answers for you.</li>
                <li>A representative will get in touch with you within <strong>24 hours</strong>.</li>
            </ul>
        </div>

        <p style="color: #64748b; font-size: 14px; margin-bottom: 24px;">
            If your request is urgent, you can reply directly to this email or reach us through our official portal.
        </p>

        <div style="border-top: 1px solid #e2e8f0; padding-top: 16px;">
            <p style="margin: 0; color: #334155; font-size: 14px;">Warm regards,</p>
            <p style="margin: 4px 0 0 0; color: #0f172a; font-weight: 700; font-size: 15px;">The ' . safe_html($site_name) . ' Team</p>
        </div>
    </div>';

    $body = buildProfessionalEmailTemplate([
        'title'        => 'Thank You for Reaching Out',
        'subtitle'     => 'We have received your message and are on it!',
        'badge'        => 'INQUIRY RECEIVED',
        'badge_color'  => '#10b981',
        'content_html' => $content,
        'button_text'  => 'Visit Our Website',
        'button_url'   => defined('SITE_URL') ? SITE_URL : 'https://cloudaspire.in'
    ]);

    return sendEmail($email, $subject, $body, '', $async);
}

/**
 * Send admin login notification
 */
function sendAdminLoginEmail(string $admin_name, string $admin_email, string $ip_address = '') {
    global $conn;

    $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = 'site_name'");
    $stmt->execute();
    $site_name = $stmt->fetchColumn() ?: 'CloudASPIRE';

    $subject = "[{$site_name}] Admin Security Alert: Login Detected";
    
    $content = '
    <p style="margin-top: 0;">An administrative login was just authenticated on your account:</p>
    <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 18px; margin: 20px 0;">
        <table width="100%" cellspacing="0" cellpadding="6" style="font-size: 14px;">
            <tr><td style="color: #64748b; width: 30%;"><strong>Admin User:</strong></td><td style="color: #0f172a; font-weight: 600;">' . safe_html($admin_name) . '</td></tr>
            <tr><td style="color: #64748b;"><strong>Account:</strong></td><td style="color: #0f172a;">' . safe_html($admin_email) . '</td></tr>
            <tr><td style="color: #64748b;"><strong>Time:</strong></td><td style="color: #0f172a;">' . date('Y-m-d H:i:s') . '</td></tr>
            <tr><td style="color: #64748b;"><strong>IP Address:</strong></td><td style="font-family: monospace; color: #4f46e5;">' . safe_html($ip_address ?: $_SERVER['REMOTE_ADDR'] ?? 'Unknown') . '</td></tr>
        </table>
    </div>
    <div style="background: #fffbeb; border-left: 4px solid #f59e0b; padding: 12px 16px; border-radius: 6px;">
        <p style="margin: 0; color: #b45309; font-size: 13px;">
            <strong>Security Notice:</strong> If this was not you, please immediately log in and change your password.
        </p>
    </div>';

    $body = buildProfessionalEmailTemplate([
        'title'        => 'Admin Login Alert',
        'subtitle'     => 'Security notification for ' . safe_html($admin_name),
        'badge'        => 'SECURITY ALERT',
        'badge_color'  => '#f59e0b',
        'content_html' => $content,
        'button_text'  => 'Review Security Settings',
        'button_url'   => (defined('SITE_URL') ? SITE_URL : 'https://cloudaspire.in') . '/admin/settings/security.php'
    ]);

    return sendEmail($admin_email, $subject, $body);
}

/**
 * Send admin logout notification
 */
function sendAdminLogoutEmail(string $admin_name, string $admin_email) {
    global $conn;

    $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = 'site_name'");
    $stmt->execute();
    $site_name = $stmt->fetchColumn() ?: 'CloudASPIRE';

    $subject = "[{$site_name}] Admin Logged Out";
    
    $content = '
    <p style="margin-top: 0;">Your administrator session has ended.</p>
    <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 18px; margin: 20px 0;">
        <p style="margin: 0 0 8px 0;"><strong>Admin User:</strong> ' . safe_html($admin_name) . '</p>
        <p style="margin: 0 0 8px 0;"><strong>Account:</strong> ' . safe_html($admin_email) . '</p>
        <p style="margin: 0;"><strong>Timestamp:</strong> ' . date('Y-m-d H:i:s') . '</p>
    </div>';

    $body = buildProfessionalEmailTemplate([
        'title'        => 'Admin Session Ended',
        'badge'        => 'LOGOUT',
        'badge_color'  => '#64748b',
        'content_html' => $content
    ]);

    return sendEmail($admin_email, $subject, $body);
}

/**
 * Send thank you email to reviewer after submitting a review
 */
function sendReviewThankYouEmail(string $email, string $name, int $star_rating = 5, bool $async = true) {
    global $conn;

    $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = 'site_name'");
    $stmt->execute();
    $site_name = $stmt->fetchColumn() ?: 'Cloud Aspire';

    $subject = "Thank You for Your Review, {$name}! - {$site_name}";
    
    $starsHtml = str_repeat('&#9733;', max(1, min(5, $star_rating))) . str_repeat('&#9734;', max(0, 5 - $star_rating));

    $content = '
    <div style="text-align: left;">
        <h2 style="color: #0f172a; margin-top: 0; font-size: 20px; font-weight: 700;">Hello, ' . safe_html($name) . '!</h2>
        <p style="color: #334155; line-height: 1.6; font-size: 15px;">
            Thank you so much for taking the time to share your feedback with <strong>' . safe_html($site_name) . '</strong>! Your review helps us continuously elevate our services and helps other businesses discover our solutions.
        </p>
        
        <div style="background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); border: 1px solid #fde68a; border-radius: 12px; padding: 22px; margin: 24px 0; text-align: center;">
            <p style="margin: 0 0 6px 0; color: #92400e; font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Your Submitted Rating</p>
            <div style="color: #f59e0b; font-size: 26px; letter-spacing: 4px; line-height: 1.2;">
                ' . $starsHtml . '
            </div>
            <p style="margin: 6px 0 0 0; color: #78350f; font-size: 14px; font-weight: 600;">' . $star_rating . ' out of 5 Stars</p>
        </div>

        <p style="color: #475569; font-size: 14px; line-height: 1.6;">
            We truly value your partnership and look forward to continuing to deliver impactful digital results for you.
        </p>

        <div style="border-top: 1px solid #e2e8f0; padding-top: 16px; margin-top: 24px;">
            <p style="margin: 0; color: #334155; font-size: 14px;">With warm regards,</p>
            <p style="margin: 4px 0 0 0; color: #0f172a; font-weight: 700; font-size: 15px;">The ' . safe_html($site_name) . ' Team</p>
        </div>
    </div>';

    $body = buildProfessionalEmailTemplate([
        'title'        => 'Thank You for Your Review',
        'subtitle'     => 'Your feedback means the world to us!',
        'badge'        => 'REVIEW RECEIVED',
        'badge_color'  => '#f59e0b',
        'content_html' => $content,
        'button_text'  => 'Visit Our Website',
        'button_url'   => defined('SITE_URL') ? SITE_URL : 'https://cloudaspire.in'
    ]);

    return sendEmail($email, $subject, $body, '', $async);
}

/**
 * Send approval notification email to reviewer when their review is published
 */
function sendReviewApprovedEmail(string $email, string $name, int $star_rating = 5, string $comment = '', bool $async = true) {
    global $conn;

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'No valid email provided'];
    }

    $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = 'site_name'");
    $stmt->execute();
    $site_name = $stmt->fetchColumn() ?: 'Cloud Aspire';

    $subject = "Your Review is Now Live on {$site_name}! ⭐";
    $starsHtml = str_repeat('&#9733;', max(1, min(5, $star_rating))) . str_repeat('&#9734;', max(0, 5 - $star_rating));

    $content = '
    <div style="text-align: left;">
        <h2 style="color: #0f172a; margin-top: 0; font-size: 20px; font-weight: 700;">Hello ' . safe_html($name) . ',</h2>
        <p style="color: #334155; line-height: 1.6; font-size: 15px;">
            Great news! Your review has been approved by our team and is now published live on the <strong>' . safe_html($site_name) . '</strong> website.
        </p>
        
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; margin: 20px 0;">
            <div style="color: #f59e0b; font-size: 22px; letter-spacing: 3px; margin-bottom: 8px;">
                ' . $starsHtml . '
            </div>
            ' . (!empty($comment) ? '<p style="color: #334155; font-style: italic; margin: 0; line-height: 1.6;">"' . nl2br(safe_html($comment)) . '"</p>' : '') . '
        </div>

        <p style="color: #475569; font-size: 14px; line-height: 1.6;">
            We sincerely appreciate your trust and endorsement. It motivates our team to keep delivering outstanding digital results!
        </p>

        <div style="border-top: 1px solid #e2e8f0; padding-top: 16px; margin-top: 24px;">
            <p style="margin: 0; color: #334155; font-size: 14px;">Best regards,</p>
            <p style="margin: 4px 0 0 0; color: #0f172a; font-weight: 700; font-size: 15px;">The ' . safe_html($site_name) . ' Team</p>
        </div>
    </div>';

    $body = buildProfessionalEmailTemplate([
        'title'        => 'Your Review is Published!',
        'subtitle'     => 'Thank you for sharing your experience',
        'badge'        => 'PUBLISHED',
        'badge_color'  => '#10b981',
        'content_html' => $content,
        'button_text'  => 'View Reviews on Website',
        'button_url'   => defined('SITE_URL') ? (SITE_URL . '/reviews') : 'https://cloudaspire.in/reviews'
    ]);

    return sendEmail($email, $subject, $body, '', $async);
}

/**
 * Send review notification to admin
 */
function sendReviewNotificationEmail(string $action, array $review_data, int $created_by_admin = 0, int $created_by_user = 0, bool $async = true) {
    global $conn;

    $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = 'mail_admin_email'");
    $stmt->execute();
    $admin_email = $stmt->fetchColumn() ?: '';

    if (empty($admin_email)) {
        $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = 'contact_email'");
        $stmt->execute();
        $admin_email = $stmt->fetchColumn() ?: 'cloudaspire2026@gmail.com';
    }

    $stmt2 = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = 'site_name'");
    $stmt2->execute();
    $site_name = $stmt2->fetchColumn() ?: 'CloudASPIRE';

    $action_text = ($action === 'create') ? 'New Review Posted' : 'Review Updated';
    $subject = "[{$site_name}] {$action_text}";

    $creator = 'Website User';
    if ($created_by_admin > 0) {
        $stmt = $conn->prepare("SELECT name FROM admin WHERE id = ?");
        $stmt->execute([$created_by_admin]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $creator = ($result ? $result['name'] : 'Admin') . ' (Admin Panel)';
    } elseif ($created_by_user > 0) {
        $creator = 'User ID: ' . (int)$created_by_user;
    }

    $ratingVal = (int)($review_data['star_rating'] ?? $review_data['rating'] ?? 5);
    $commentVal = $review_data['message'] ?? $review_data['comment'] ?? $review_data['review_text'] ?? 'N/A';

    $content = '
    <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 18px; margin: 20px 0;">
        <table width="100%" cellspacing="0" cellpadding="6" style="font-size: 14px;">
            <tr><td style="color: #64748b; width: 30%;"><strong>Reviewer:</strong></td><td style="color: #0f172a; font-weight: 600;">' . safe_html($review_data['name'] ?? 'N/A') . '</td></tr>
            ' . (!empty($review_data['email']) ? '<tr><td style="color: #64748b;"><strong>Email:</strong></td><td><a href="mailto:' . safe_html($review_data['email']) . '" style="color: #4f46e5; text-decoration: none;">' . safe_html($review_data['email']) . '</a></td></tr>' : '') . '
            <tr><td style="color: #64748b;"><strong>Rating:</strong></td><td style="color: #f59e0b; font-size: 16px;">' . str_repeat('&#9733;', $ratingVal) . ' <span style="font-size: 13px; color: #64748b;">(' . $ratingVal . '/5)</span></td></tr>
            ' . (!empty($review_data['company']) || !empty($review_data['designation']) ? '<tr><td style="color: #64748b;"><strong>Role/Company:</strong></td><td style="color: #0f172a;">' . safe_html(trim(($review_data['designation'] ?? '') . ' ' . ($review_data['company'] ?? ''))) . '</td></tr>' : '') . '
            <tr><td style="color: #64748b; vertical-align: top;"><strong>Comment:</strong></td><td style="color: #334155; line-height: 1.5;">' . nl2br(safe_html($commentVal)) . '</td></tr>
            <tr><td style="color: #64748b;"><strong>Source:</strong></td><td style="color: #0f172a;">' . safe_html($creator) . '</td></tr>
        </table>
    </div>';

    $body = buildProfessionalEmailTemplate([
        'title'        => $action_text,
        'subtitle'     => 'Customer review activity',
        'badge'        => 'REVIEWS',
        'badge_color'  => '#f59e0b',
        'content_html' => $content,
        'button_text'  => 'Manage Reviews',
        'button_url'   => (defined('SITE_URL') ? SITE_URL : 'https://cloudaspire.in') . '/admin/reviews.php'
    ]);

    return sendEmail($admin_email, $subject, $body, '', $async);
}

/**
 * Send project activity notification
 */
function sendProjectActivityEmail(string $action, string $project_name, int $admin_id) {
    global $conn;

    $admins = [];
    $stmt = $conn->query("SELECT email FROM admin WHERE is_active = 1");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if (!empty($row['email'])) {
            $admins[] = $row['email'];
        }
    }

    $stmt = $conn->prepare("SELECT name FROM admin WHERE id = ?");
    $stmt->execute([$admin_id]);
    $admin_name = $stmt->fetchColumn() ?: 'Unknown Admin';

    $stmt2 = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = 'site_name'");
    $stmt2->execute();
    $site_name = $stmt2->fetchColumn() ?: 'CloudASPIRE';

    $subject = "[{$site_name}] Project {$action}: {$project_name}";

    $content = '
    <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 18px; margin: 20px 0;">
        <table width="100%" cellspacing="0" cellpadding="6" style="font-size: 14px;">
            <tr><td style="color: #64748b; width: 30%;"><strong>Project:</strong></td><td style="color: #0f172a; font-weight: 600;">' . safe_html($project_name) . '</td></tr>
            <tr><td style="color: #64748b;"><strong>Action:</strong></td><td><span style="background: #e0e7ff; color: #3730a3; padding: 3px 10px; border-radius: 12px; font-size: 12px; font-weight: 700;">' . strtoupper($action) . '</span></td></tr>
            <tr><td style="color: #64748b;"><strong>Admin:</strong></td><td style="color: #0f172a;">' . safe_html($admin_name) . '</td></tr>
            <tr><td style="color: #64748b;"><strong>Timestamp:</strong></td><td style="color: #0f172a;">' . date('Y-m-d H:i:s') . '</td></tr>
        </table>
    </div>';

    $body = buildProfessionalEmailTemplate([
        'title'        => 'Project Activity',
        'subtitle'     => 'Changes made to project records',
        'badge'        => 'PROJECTS',
        'badge_color'  => '#4f46e5',
        'content_html' => $content,
        'button_text'  => 'View Projects',
        'button_url'   => (defined('SITE_URL') ? SITE_URL : 'https://cloudaspire.in') . '/admin/projects.php'
    ]);

    return sendEmail($admins, $subject, $body);
}

/**
 * Send site change notification
 */
function sendSiteChangeNotification(string $action, string $details, string $admin_name, string $ip_address = '') {
    global $conn;

    $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = 'mail_admin_email'");
    $stmt->execute();
    $admin_email = $stmt->fetchColumn() ?: '';

    $stmt2 = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = 'site_name'");
    $stmt2->execute();
    $site_name = $stmt2->fetchColumn() ?: 'CloudASPIRE';

    if (empty($admin_email)) {
        error_log("[Email Warning] No admin email configured for site change notification");
        return ['success' => false, 'message' => 'Admin email not configured'];
    }

    $subject = "[{$site_name}] Site Activity: " . ucwords(str_replace('_', ' ', $action));

    $content = '
    <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 18px; margin: 20px 0;">
        <table width="100%" cellspacing="0" cellpadding="6" style="font-size: 14px;">
            <tr><td style="color: #64748b; width: 30%;"><strong>Admin:</strong></td><td style="color: #0f172a; font-weight: 600;">' . safe_html($admin_name) . '</td></tr>
            <tr><td style="color: #64748b;"><strong>Action:</strong></td><td><span style="background: #e0e7ff; color: #3730a3; padding: 3px 10px; border-radius: 12px; font-size: 12px; font-weight: 700;">' . strtoupper(str_replace('_', ' ', $action)) . '</span></td></tr>
            <tr><td style="color: #64748b; vertical-align: top;"><strong>Details:</strong></td><td style="color: #334155; line-height: 1.5;">' . safe_html($details) . '</td></tr>
            <tr><td style="color: #64748b;"><strong>IP Address:</strong></td><td style="font-family: monospace; color: #4f46e5;">' . safe_html($ip_address ?: 'Unknown') . '</td></tr>
            <tr><td style="color: #64748b;"><strong>Time:</strong></td><td style="color: #0f172a;">' . date('Y-m-d H:i:s') . '</td></tr>
        </table>
    </div>
    <div style="background: #fffbeb; border-left: 4px solid #f59e0b; padding: 12px 16px; border-radius: 6px;">
        <p style="margin: 0; color: #b45309; font-size: 13px;">
            <strong>Action Required:</strong> If this modification was not authorized by you, please secure your account immediately.
        </p>
    </div>';

    $body = buildProfessionalEmailTemplate([
        'title'        => 'Site Activity Notification',
        'subtitle'     => 'System configuration change recorded',
        'badge'        => 'AUDIT LOG',
        'badge_color'  => '#f59e0b',
        'content_html' => $content,
        'button_text'  => 'Open Activity Log',
        'button_url'   => (defined('SITE_URL') ? SITE_URL : 'https://cloudaspire.in') . '/admin/reports.php'
    ]);

    return sendEmail($admin_email, $subject, $body);
}