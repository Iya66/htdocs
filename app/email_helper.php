<?php
/**
 * Email Helper - Centralized Transactional Email Engine
 *
 * Configured strictly for high-deliverability lead capture and essential system notifications:
 * 1. Admin Lead & Audit Notifications (Reply-To = visitor email)
 * 2. Visitor Thank-You Confirmations (Reply-To = authenticated domain noreply)
 * 3. Contact Form Notifications (Reply-To = visitor email)
 *
 * Non-essential automated emails (welcome, admin session alerts, review notices,
 * project/digest updates) are safely disabled as no-ops to protect sender reputation.
 *
 * @requires app/smtp_factory.php
 * @requires app/mail_queue.php
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/smtp_factory.php';
require_once __DIR__ . '/mail_queue.php';

// Backward compatibility alias for SMTP password decryption
if (!function_exists('decryptSMTPPassword')) {
    function decryptSMTPPassword(string $encoded): string {
        return decryptSmtpPassword($encoded);
    }
}

/**
 * Escape HTML output securely.
 */
function safe_html($string) {
    return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8', false);
}

/**
 * Log email errors to PHP error log and database audit log if available.
 */
function logEmailError(string $function, string $message) {
    error_log("[Email Error - $function] $message");
    global $conn;
    if (isset($conn)) {
        try {
            $conn->prepare("INSERT INTO admin_activities (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)")
                ->execute([$_SESSION['admin_id'] ?? 0, 'EMAIL_ERROR', "$function: $message", $_SERVER['REMOTE_ADDR'] ?? '']);
        } catch (Exception $e) {
            // Silently ignore activity log failures
        }
    }
}

/**
 * Dispatch an email either asynchronously via the mail queue (default, non-blocking)
 * or synchronously (direct connection).
 *
 * @param mixed       $to           Recipient email address(es)
 * @param string      $subject      Email subject line
 * @param string      $body         HTML message body
 * @param string      $altBody      Plain text alternative
 * @param bool        $async        True to queue and send in background, false for direct send
 * @param string|null $replyTo      Reply-To email address
 * @param string|null $replyToName  Reply-To display name
 * @return array{success: bool, message: string, queued?: bool}
 */
function sendEmail(mixed $to, string $subject, string $body, string $altBody = '', bool $async = false, ?string $replyTo = null, ?string $replyToName = null): array {
    global $conn;

    if ($async) {
        return enqueueEmail($conn, $to, $subject, $body, $altBody, true, $replyTo, $replyToName);
    }

    return sendEmailDirect($to, $subject, $body, $altBody, $replyTo, $replyToName);
}

/**
 * Enqueue an email explicitly for asynchronous background delivery.
 */
function queueEmail(mixed $to, string $subject, string $body, string $altBody = '', ?string $replyTo = null, ?string $replyToName = null): array {
    global $conn;
    return enqueueEmail($conn, $to, $subject, $body, $altBody, true, $replyTo, $replyToName);
}

/**
 * Direct synchronous email sending via PHPMailer.
 * Used for interactive SMTP diagnostics (Test SMTP / Send Test Email) where
 * immediate inline confirmation or debug output is required.
 */
function sendEmailDirect(mixed $to, string $subject, string $body, string $altBody = '', ?string $replyTo = null, ?string $replyToName = null): array {
    global $conn;

    $recipientStr = is_array($to) ? implode(', ', $to) : $to;

    try {
        if (empty($to) || (is_array($to) && count(array_filter($to)) === 0)) {
            $msg = 'No recipient email address provided';
            logEmailError('sendEmailDirect', $msg);
            try {
                $stmtLog = $conn->prepare("INSERT INTO mail_logs (recipient, subject, status, error_message, sent_at) VALUES (?, ?, 'failed', ?, NOW())");
                $stmtLog->execute([$recipientStr, $subject, $msg]);
            } catch (Exception $logEx) {}
            return ['success' => false, 'message' => $msg];
        }

        $settings = loadSmtpSettings($conn);
        $smtp_email = $settings['mail_smtp_email'] ?? '';
        $from_name  = $settings['mail_from_name'] ?? (defined('SMTP_FROM_NAME') ? SMTP_FROM_NAME : 'Cloud Aspire');

        // Create mailer via centralized factory
        $mail = createMailer($conn);

        // Envelope Sender & From alignment for SPF/DMARC
        $mail->setFrom($smtp_email, $from_name);
        $mail->Sender = $smtp_email;

        // Recipients
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

        // Reply-To header alignment
        if (!empty($replyTo) && filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
            $mail->addReplyTo($replyTo, !empty($replyToName) ? $replyToName : $replyTo);
        } elseif (!empty($smtp_email)) {
            $mail->addReplyTo($smtp_email, $from_name);
        }

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = !empty($altBody) ? $altBody : htmlToCleanPlainText($body);

        // Send with automatic retry (2 attempts)
        sendWithRetry($mail, 2);

        error_log("[Email Success] Sent direct to: $recipientStr");

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
 * Build a modern, clean HTML email layout optimized for inbox deliverability.
 *
 * Rules:
 * - Table-based responsive container (600px width).
 * - PNG logo only (no SVG images).
 * - No spam trigger words or emojis in headers or badges.
 * - Clean inline styling compatible with Gmail, Apple Mail, and Outlook.
 */
function buildProfessionalEmailTemplate(array $options): string {
    global $conn;

    $siteName     = defined('SITE_NAME') ? SITE_NAME : 'Cloud Aspire';
    $siteUrl      = defined('SITE_URL') ? rtrim(SITE_URL, '/') : 'https://cloudaspiresolutions.in';
    $siteLogo     = '';
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

    $title       = $options['title'] ?? $siteName;
    $subtitle    = $options['subtitle'] ?? '';
    $badge       = $options['badge'] ?? '';
    $badgeColor  = $options['badge_color'] ?? '#312e81';
    $contentHtml = $options['content_html'] ?? '';
    $buttonText  = $options['button_text'] ?? '';
    $buttonUrl   = $options['button_url'] ?? '';
    $footerNote  = $options['footer_note'] ?? '';

    // Render PNG logo only. If SVG is configured, fall back to clean text brand to prevent spam triggers
    $logoHtml = '';
    if (!empty($siteLogo) && !str_ends_with(strtolower($siteLogo), '.svg')) {
        $logoUrl = (str_starts_with($siteLogo, 'http://') || str_starts_with($siteLogo, 'https://'))
            ? $siteLogo
            : $siteUrl . '/' . ltrim($siteLogo, '/');
        $logoHtml = '<img src="' . safe_html($logoUrl) . '" alt="' . safe_html($siteName) . '" style="max-height: 44px; max-width: 180px; height: auto; width: auto; object-fit: contain; vertical-align: middle; display: inline-block; margin-bottom: 6px;">';
    } else {
        $logoHtml = '<div style="font-size: 22px; font-weight: 800; color: #0f172a; letter-spacing: -0.3px; margin-bottom: 4px;">' . safe_html($siteName) . '</div>';
    }

    $buttonHtml = '';
    if (!empty($buttonText) && !empty($buttonUrl)) {
        $buttonHtml = '
        <div style="text-align: center; margin: 28px 0 12px 0;">
            <a href="' . safe_html($buttonUrl) . '" target="_blank" style="background-color: #312e81; color: #ffffff; padding: 12px 28px; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 14px; display: inline-block;">' . safe_html($buttonText) . '</a>
        </div>';
    }

    $badgeHtml = '';
    if (!empty($badge)) {
        $badgeHtml = '
        <div style="margin-bottom: 12px;">
            <span style="background-color: #f1f5f9; color: ' . safe_html($badgeColor) . '; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; padding: 4px 10px; border-radius: 4px; display: inline-block; border: 1px solid #e2e8f0;">' . safe_html($badge) . '</span>
        </div>';
    }

    $year = date('Y');

    return '<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>' . safe_html($title) . '</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f8fafc; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Helvetica, Arial, sans-serif; color: #1e293b; -webkit-font-smoothing: antialiased; line-height: 1.6;">
<table width="100%" border="0" cellpadding="0" cellspacing="0" style="background-color: #f8fafc; padding: 30px 15px;">
    <tr>
        <td align="center">
            <table width="100%" border="0" cellpadding="0" cellspacing="0" style="max-width: 600px; background-color: #ffffff; border-radius: 10px; overflow: hidden; border: 1px solid #e2e8f0;">
                <tr>
                    <td style="padding: 28px 36px 20px 36px; border-bottom: 1px solid #f1f5f9; text-align: left;">
                        ' . $logoHtml . '
                    </td>
                </tr>
                <tr>
                    <td style="padding: 32px 36px 24px 36px;">
                        ' . $badgeHtml . '
                        <h1 style="margin: 0 0 8px 0; font-size: 20px; font-weight: 700; color: #0f172a; line-height: 1.3;">' . safe_html($title) . '</h1>
                        ' . (!empty($subtitle) ? '<p style="margin: 0 0 20px 0; font-size: 14px; color: #64748b; line-height: 1.5;">' . safe_html($subtitle) . '</p>' : '<div style="margin-bottom: 20px;"></div>') . '
                        ' . $contentHtml . '
                        ' . $buttonHtml . '
                    </td>
                </tr>
                <tr>
                    <td style="padding: 20px 36px 24px 36px; background-color: #f8fafc; border-top: 1px solid #e2e8f0; font-size: 12px; color: #64748b; line-height: 1.6;">
                        ' . (!empty($footerNote) ? '<p style="margin: 0 0 8px 0; color: #64748b;">' . safe_html($footerNote) . '</p>' : '') . '
                        <p style="margin: 0 0 4px 0;">This email was sent by <strong>' . safe_html($siteName) . '</strong>.</p>
                        <p style="margin: 0;">For inquiries, contact us at <a href="mailto:' . safe_html($contactEmail) . '" style="color: #312e81; text-decoration: none;">' . safe_html($contactEmail) . '</a> or visit <a href="' . safe_html($siteUrl) . '" style="color: #312e81; text-decoration: none;">' . safe_html($siteUrl) . '</a>.</p>
                        <p style="margin: 8px 0 0 0; color: #94a3b8; font-size: 11px;">&copy; ' . $year . ' ' . safe_html($siteName) . '. All rights reserved.</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>';
}

// =========================================================================
// ACTIVE LEAD & AUDIT EMAIL FUNCTIONS (RETAINED)
// =========================================================================

/**
 * 1. Admin Notification for SEO Audit Requests.
 *
 * Subject: New SEO Audit Request - {Name}
 * Reply-To: Visitor's validated email address.
 */
function sendAuditRequestNotification(array $audit_data, bool $async = true): array {
    global $conn;

    $admin_email = '';
    if (isset($conn)) {
        try {
            $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = 'mail_admin_email'");
            $stmt->execute();
            $admin_email = $stmt->fetchColumn() ?: '';
            if (empty($admin_email)) {
                $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = 'contact_email'");
                $stmt->execute();
                $admin_email = $stmt->fetchColumn() ?: '';
            }
        } catch (Exception $e) {}
    }
    if (empty($admin_email)) {
        $admin_email = 'premkumar40187@gmail.com';
    }

    $name    = trim($audit_data['name'] ?? 'Prospective Client');
    $email   = trim($audit_data['email'] ?? '');
    $phone   = trim($audit_data['phone'] ?? '');
    $website = trim($audit_data['website'] ?? '');
    $leadId  = (int)($audit_data['lead_id'] ?? 0);
    $refId   = $leadId > 0 ? ('#AUD-' . str_pad((string)$leadId, 5, '0', STR_PAD_LEFT)) : ('#AUD-' . date('ymd') . rand(100, 999));

    // Deliverability: Clean subject, no emojis, no spam-trigger phrases
    $subject = 'New SEO Audit Request - ' . $name;

    $websiteText = !empty($website) ? safe_html($website) : 'Not provided';
    $emailText   = !empty($email) ? safe_html($email) : 'Not provided';
    $phoneText   = !empty($phone) ? safe_html($phone) : 'Not provided';
    $submittedAt = date('Y-m-d H:i:s T');
    $siteUrl     = defined('SITE_URL') ? rtrim(SITE_URL, '/') : 'https://cloudaspiresolutions.in';

    $content = '
    <div style="margin-bottom: 20px;">
        <p style="margin: 0 0 14px 0; font-size: 14px; color: #334155;">
            A new SEO and website performance audit request has been submitted through the website.
        </p>
        <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 18px; margin: 16px 0;">
            <table width="100%" cellspacing="0" cellpadding="6" style="font-size: 14px; border-collapse: collapse;">
                <tr><td style="color: #64748b; width: 30%;"><strong>Reference:</strong></td><td style="color: #0f172a; font-family: monospace; font-weight: 700;">' . safe_html($refId) . '</td></tr>
                <tr><td style="color: #64748b;"><strong>Contact Name:</strong></td><td style="color: #0f172a; font-weight: 600;">' . safe_html($name) . '</td></tr>
                <tr><td style="color: #64748b;"><strong>Email:</strong></td><td><a href="mailto:' . safe_html($email) . '" style="color: #312e81; text-decoration: none;">' . $emailText . '</a></td></tr>
                <tr><td style="color: #64748b;"><strong>Phone:</strong></td><td style="color: #0f172a;">' . $phoneText . '</td></tr>
                <tr><td style="color: #64748b;"><strong>Website:</strong></td><td style="color: #0f172a; font-family: monospace;">' . $websiteText . '</td></tr>
                <tr><td style="color: #64748b;"><strong>Submitted:</strong></td><td style="color: #64748b; font-size: 13px;">' . $submittedAt . '</td></tr>
            </table>
        </div>
        <p style="font-size: 13px; color: #64748b; margin: 0;">You can click Reply directly to respond to this prospective client.</p>
    </div>';

    $body = buildProfessionalEmailTemplate([
        'title'        => 'New SEO Audit Request',
        'subtitle'     => 'Inquiry details for ' . safe_html($name),
        'badge'        => 'AUDIT INQUIRY',
        'badge_color'  => '#059669',
        'content_html' => $content,
        'button_text'  => 'Open Admin Panel',
        'button_url'   => $siteUrl . '/admin/leads.php'
    ]);

    // Admin lead notification: Reply-To = visitor's email
    return sendEmail($admin_email, $subject, $body, '', $async, $email ?: null, $name ?: null);
}

/**
 * 2. Visitor Thank-You Email for SEO Audit Requests.
 *
 * Subject: We received your SEO audit request - {Site Name}
 * Reply-To: Authenticated From address (noreply@cloudaspiresolutions.in).
 */
function sendAuditThankYouEmail(string $email, string $name, ?string $website = null, ?int $lead_id = null, bool $async = true): array {
    global $conn;

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Invalid visitor email'];
    }

    $siteName     = 'Cloud Aspire';
    $sitePhone    = '8098830937';
    $contactEmail = 'cloudaspire2025@gmail.com';
    $fromEmail    = 'noreply@cloudaspiresolutions.in';
    $siteUrl      = defined('SITE_URL') ? rtrim(SITE_URL, '/') : 'https://cloudaspiresolutions.in';

    if (isset($conn)) {
        try {
            $stmt = $conn->query("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('site_name', 'contact_phone', 'contact_email', 'mail_smtp_email')");
            $s = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            if (!empty($s['site_name'])) $siteName = $s['site_name'];
            if (!empty($s['contact_phone'])) $sitePhone = $s['contact_phone'];
            if (!empty($s['contact_email'])) $contactEmail = $s['contact_email'];
            if (!empty($s['mail_smtp_email'])) $fromEmail = $s['mail_smtp_email'];
        } catch (Exception $e) {}
    }

    $refId = ($lead_id && $lead_id > 0)
        ? ('#AUD-' . str_pad((string)$lead_id, 5, '0', STR_PAD_LEFT))
        : ('#AUD-' . date('ymd') . rand(100, 999));

    // Deliverability: Clean subject line without spam keywords
    $subject = 'We received your SEO audit request - ' . $siteName;

    $websiteSection = '';
    if (!empty($website)) {
        $websiteSection = '
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px 16px; margin: 16px 0;">
            <span style="color: #64748b; font-size: 11px; text-transform: uppercase; font-weight: 700;">Analyzed Website:</span><br>
            <strong style="color: #0f172a; font-size: 14px;">' . safe_html($website) . '</strong>
        </div>';
    }

    $content = '
    <div>
        <p style="margin-top: 0; font-size: 15px; color: #1e293b;">Hello ' . safe_html($name) . ',</p>
        <p style="color: #334155; line-height: 1.6; font-size: 14px;">
            Thank you for requesting an SEO and performance audit from ' . safe_html($siteName) . '. We have received your details (Reference: <strong style="font-family: monospace;">' . safe_html($refId) . '</strong>).
        </p>

        ' . $websiteSection . '

        <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 18px; margin: 20px 0;">
            <p style="margin: 0 0 10px 0; color: #0f172a; font-weight: 700; font-size: 14px;">What we evaluate:</p>
            <ul style="margin: 0; padding-left: 20px; color: #334155; font-size: 13px; line-height: 1.7;">
                <li><strong>Technical SEO:</strong> Crawlability, indexation status, and Core Web Vitals health.</li>
                <li><strong>On-Page Structure:</strong> Metadata, content hierarchy, and internal linking.</li>
                <li><strong>Search Visibility:</strong> Keyword opportunities and competitor positioning.</li>
            </ul>
        </div>

        <p style="color: #334155; font-size: 14px; line-height: 1.6;">
            Our team will review your website metrics and get in touch with recommendations.
        </p>

        <p style="color: #64748b; font-size: 13px; margin-top: 24px;">
            If you have questions in the meantime, reach us at <a href="mailto:' . safe_html($contactEmail) . '" style="color: #312e81;">' . safe_html($contactEmail) . '</a> or call ' . safe_html($sitePhone) . '.
        </p>
    </div>';

    $body = buildProfessionalEmailTemplate([
        'title'        => 'Audit Request Received',
        'subtitle'     => 'We are processing your website review',
        'badge'        => 'CONFIRMATION',
        'badge_color'  => '#059669',
        'content_html' => $content,
        'button_text'  => 'Visit Our Website',
        'button_url'   => $siteUrl
    ]);

    // Visitor confirmation: Reply-To = authenticated domain from address
    return sendEmail($email, $subject, $body, '', $async, $fromEmail, $siteName);
}

/**
 * 3. Admin Notification for Standard Lead Inquiries.
 *
 * Subject: New Lead Received - {Name}
 * Reply-To: Visitor's validated email address.
 */
function sendLeadNotification(array $lead_data, bool $async = true): array {
    global $conn;

    $admin_email = '';
    if (isset($conn)) {
        try {
            $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = 'mail_admin_email'");
            $stmt->execute();
            $admin_email = $stmt->fetchColumn() ?: '';
            if (empty($admin_email)) {
                $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = 'contact_email'");
                $stmt->execute();
                $admin_email = $stmt->fetchColumn() ?: '';
            }
        } catch (Exception $e) {}
    }
    if (empty($admin_email)) {
        $admin_email = 'premkumar40187@gmail.com';
    }

    $name    = trim($lead_data['name'] ?? 'Visitor');
    $email   = trim($lead_data['email'] ?? '');
    $phone   = trim($lead_data['phone'] ?? '');
    $service = trim($lead_data['service'] ?? 'General Inquiry');
    $message = trim($lead_data['message'] ?? '');
    $siteUrl = defined('SITE_URL') ? rtrim(SITE_URL, '/') : 'https://cloudaspiresolutions.in';

    $subject = 'New Lead Received - ' . $name;

    $content = '
    <div style="margin-bottom: 20px;">
        <p style="margin: 0 0 14px 0; font-size: 14px; color: #334155;">A new lead inquiry has been received through the website:</p>
        <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 18px; margin: 16px 0;">
            <table width="100%" cellspacing="0" cellpadding="6" style="font-size: 14px; border-collapse: collapse;">
                <tr><td style="color: #64748b; width: 30%;"><strong>Name:</strong></td><td style="color: #0f172a; font-weight: 600;">' . safe_html($name) . '</td></tr>
                <tr><td style="color: #64748b;"><strong>Email:</strong></td><td><a href="mailto:' . safe_html($email) . '" style="color: #312e81; text-decoration: none;">' . safe_html($email ?: 'N/A') . '</a></td></tr>
                <tr><td style="color: #64748b;"><strong>Phone:</strong></td><td style="color: #0f172a;">' . safe_html($phone ?: 'N/A') . '</td></tr>
                <tr><td style="color: #64748b;"><strong>Service:</strong></td><td><span style="background: #e2e8f0; color: #1e293b; padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight: 600;">' . safe_html($service) . '</span></td></tr>
                <tr><td style="color: #64748b; vertical-align: top;"><strong>Message:</strong></td><td style="color: #334155; line-height: 1.5;">' . nl2br(safe_html($message ?: 'None')) . '</td></tr>
            </table>
        </div>
        <p style="color: #64748b; font-size: 13px; margin: 0;">Received on ' . date('Y-m-d H:i:s') . '</p>
    </div>';

    $body = buildProfessionalEmailTemplate([
        'title'        => 'New Lead Submission',
        'subtitle'     => 'Inquiry received via website form',
        'badge'        => 'NEW LEAD',
        'badge_color'  => '#059669',
        'content_html' => $content,
        'button_text'  => 'Open Admin Panel',
        'button_url'   => $siteUrl . '/admin/leads.php'
    ]);

    // Admin lead notification: Reply-To = visitor's email
    return sendEmail($admin_email, $subject, $body, '', $async, $email ?: null, $name ?: null);
}

/**
 * 4. Visitor Thank-You Email for Standard Inquiries.
 *
 * Subject: Thank you for contacting {Site Name}
 * Reply-To: Authenticated From address (noreply@cloudaspiresolutions.in).
 */
function sendLeadThankYouEmail(string $email, string $name, string $service = '', bool $async = true): array {
    global $conn;

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Invalid recipient email'];
    }

    $siteName  = 'Cloud Aspire';
    $fromEmail = 'noreply@cloudaspiresolutions.in';
    $siteUrl   = defined('SITE_URL') ? rtrim(SITE_URL, '/') : 'https://cloudaspiresolutions.in';

    if (isset($conn)) {
        try {
            $stmt = $conn->query("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('site_name', 'mail_smtp_email')");
            $s = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            if (!empty($s['site_name'])) $siteName = $s['site_name'];
            if (!empty($s['mail_smtp_email'])) $fromEmail = $s['mail_smtp_email'];
        } catch (Exception $e) {}
    }

    $subject = 'Thank you for contacting ' . $siteName;

    $content = '
    <div>
        <p style="margin-top: 0; font-size: 15px; color: #1e293b;">Hello ' . safe_html($name) . ',</p>
        <p style="color: #334155; line-height: 1.6; font-size: 14px;">
            Thank you for contacting ' . safe_html($siteName) . '. We have received your inquiry' . (!empty($service) ? ' regarding <strong>' . safe_html($service) . '</strong>' : '') . '.
        </p>

        <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 18px; margin: 20px 0;">
            <p style="margin: 0 0 8px 0; color: #0f172a; font-weight: 700; font-size: 14px;">Next steps:</p>
            <ul style="margin: 0; padding-left: 20px; color: #334155; font-size: 13px; line-height: 1.7;">
                <li>Our team will review your requirements.</li>
                <li>We will prepare the relevant details and options.</li>
                <li>A representative will reach out to you within one business day.</li>
            </ul>
        </div>

        <p style="color: #64748b; font-size: 13px; margin-top: 20px;">
            With regards,<br>
            <strong>The ' . safe_html($siteName) . ' Team</strong>
        </p>
    </div>';

    $body = buildProfessionalEmailTemplate([
        'title'        => 'Inquiry Received',
        'subtitle'     => 'Thank you for reaching out to us',
        'badge'        => 'RECEIVED',
        'badge_color'  => '#059669',
        'content_html' => $content,
        'button_text'  => 'Visit Our Website',
        'button_url'   => $siteUrl
    ]);

    // Visitor confirmation: Reply-To = authenticated from email
    return sendEmail($email, $subject, $body, '', $async, $fromEmail, $siteName);
}

/**
 * 5. Admin Notification for Contact Form Submissions.
 *
 * Subject: New Contact Form Submission - {Name}
 * Reply-To: Visitor's validated email address.
 */
function sendContactNotification(array $contact_data, bool $async = true): array {
    global $conn;

    $admin_email = '';
    if (isset($conn)) {
        try {
            $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = 'mail_admin_email'");
            $stmt->execute();
            $admin_email = $stmt->fetchColumn() ?: '';
            if (empty($admin_email)) {
                $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = 'contact_email'");
                $stmt->execute();
                $admin_email = $stmt->fetchColumn() ?: '';
            }
        } catch (Exception $e) {}
    }
    if (empty($admin_email)) {
        $admin_email = 'premkumar40187@gmail.com';
    }

    $name    = trim($contact_data['name'] ?? 'Visitor');
    $email   = trim($contact_data['email'] ?? '');
    $subjectLine = trim($contact_data['subject'] ?? 'Website Inquiry');
    $message = trim($contact_data['message'] ?? '');
    $siteUrl = defined('SITE_URL') ? rtrim(SITE_URL, '/') : 'https://cloudaspiresolutions.in';

    $subject = 'New Contact Form Submission - ' . $name;

    $content = '
    <div style="margin-bottom: 20px;">
        <p style="margin: 0 0 14px 0; font-size: 14px; color: #334155;">A new message has been received from the website contact form:</p>
        <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 18px; margin: 16px 0;">
            <table width="100%" cellspacing="0" cellpadding="6" style="font-size: 14px; border-collapse: collapse;">
                <tr><td style="color: #64748b; width: 30%;"><strong>Name:</strong></td><td style="color: #0f172a; font-weight: 600;">' . safe_html($name) . '</td></tr>
                <tr><td style="color: #64748b;"><strong>Email:</strong></td><td><a href="mailto:' . safe_html($email) . '" style="color: #312e81; text-decoration: none;">' . safe_html($email ?: 'N/A') . '</a></td></tr>
                <tr><td style="color: #64748b;"><strong>Subject:</strong></td><td style="color: #0f172a;">' . safe_html($subjectLine) . '</td></tr>
                <tr><td style="color: #64748b; vertical-align: top;"><strong>Message:</strong></td><td style="color: #334155; line-height: 1.5;">' . nl2br(safe_html($message ?: 'None')) . '</td></tr>
            </table>
        </div>
    </div>';

    $body = buildProfessionalEmailTemplate([
        'title'        => 'Contact Form Message',
        'subtitle'     => 'Message from ' . safe_html($name),
        'badge'        => 'CONTACT FORM',
        'badge_color'  => '#312e81',
        'content_html' => $content,
        'button_text'  => 'Open Admin Panel',
        'button_url'   => $siteUrl . '/admin/'
    ]);

    // Admin notification: Reply-To = visitor's email
    return sendEmail($admin_email, $subject, $body, '', $async, $email ?: null, $name ?: null);
}

// =========================================================================
// DISABLED EMAIL FUNCTIONS (SAFE NO-OPS TO PRESERVE SENDER REPUTATION)
// Kept for backward compatibility with caller signatures. Returns success.
// =========================================================================

/**
 * Disabled: Welcome Email
 */
function sendWelcomeEmail(string $email, string $name, string $password = ''): array {
    return ['success' => true, 'message' => 'Email notification disabled by configuration'];
}

/**
 * Disabled: Admin Login Alert
 */
function sendAdminLoginEmail(string $admin_name, string $admin_email, string $ip_address = ''): array {
    return ['success' => true, 'message' => 'Email notification disabled by configuration'];
}

/**
 * Disabled: Admin Logout Notice
 */
function sendAdminLogoutEmail(string $admin_name, string $admin_email): array {
    return ['success' => true, 'message' => 'Email notification disabled by configuration'];
}

/**
 * Disabled: Review Thank You
 */
function sendReviewThankYouEmail(string $email, string $name, int $star_rating = 5, bool $async = true): array {
    return ['success' => true, 'message' => 'Email notification disabled by configuration'];
}

/**
 * Disabled: Review Approved Notice
 */
function sendReviewApprovedEmail(string $email, string $name, int $star_rating = 5, string $comment = '', bool $async = true): array {
    return ['success' => true, 'message' => 'Email notification disabled by configuration'];
}

/**
 * Disabled: Review Notification to Admin
 */
function sendReviewNotificationEmail(string $action, array $review_data, int $created_by_admin = 0, int $created_by_user = 0, bool $async = true): array {
    return ['success' => true, 'message' => 'Email notification disabled by configuration'];
}

/**
 * Disabled: Project Activity Notice
 */
function sendProjectActivityEmail(string $action, string $project_name, int $admin_id): array {
    return ['success' => true, 'message' => 'Email notification disabled by configuration'];
}

/**
 * Disabled: Site Change Alert
 */
function sendSiteChangeNotification(string $action, string $details, string $admin_name, string $ip_address = ''): array {
    return ['success' => true, 'message' => 'Email notification disabled by configuration'];
}

/**
 * Disabled: Activity Digest Report
 */
function sendReportToAdmin(string $report_type, string $report_data): array {
    return ['success' => true, 'message' => 'Email notification disabled by configuration'];
}