<?php
/**
 * Lead Capture API Endpoint
 * Handles submissions from CherriDM Popup Modal, Service Sidebars, and Footer Audit Form.
 *
 * Deliverability & Anti-Abuse Hardening:
 * - CR/LF injection stripping across all headers and fields.
 * - Honeypot detection (website_hp, hp_field) silently dropping bot traffic.
 * - Cloudflare Turnstile token validation support.
 * - IP rate limiting (max 3 submissions per IP per hour) via rate_limits table.
 * - Recipient thank-you rate limiting (max 1 autoresponder per email per hour) to prevent spoof relay.
 * - Strict email validation including DNS MX/A record verification.
 */
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../app/config.php';
global $conn;
@include_once __DIR__ . '/../app/email_helper.php';
@include_once __DIR__ . '/../app/security_notifier.php';
@include_once __DIR__ . '/../app/whatsapp_helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

// 1. Honeypot bot trap: If bot fills hidden honeypot fields, return fake success immediately
$honeypotFields = ['website_hp', 'hp_field', 'comment_body', 'url_confirm'];
foreach ($honeypotFields as $hp) {
    if (!empty($_POST[$hp])) {
        // Silently exit with normal success message so spam bot thinks it succeeded
        echo json_encode([
            'status'  => 'success',
            'message' => 'Thank you! Your request has been received successfully.'
        ]);
        exit;
    }
}

// 2. Client IP Resolution
$clientIp = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
    $clientIp = trim($_SERVER['HTTP_CF_CONNECTING_IP']);
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $parts = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
    $clientIp = trim($parts[0]);
}
$clientIp = preg_replace('/[^0-9a-fA-F:.]/', '', substr($clientIp, 0, 45));

// 3. Database Rate Limiting (Max 3 submissions per IP per hour)
if (isset($conn)) {
    try {
        $ipType = 'lead_capture_ip';
        $limitStmt = $conn->prepare("SELECT count, updated_at FROM rate_limits WHERE identifier = ? AND type = ? LIMIT 1");
        $limitStmt->execute([$clientIp, $ipType]);
        $rateRow = $limitStmt->fetch(PDO::FETCH_ASSOC);

        if ($rateRow) {
            $lastUpdated = strtotime($rateRow['updated_at']);
            if ((time() - $lastUpdated) < 3600) {
                if ((int)$rateRow['count'] >= 3) {
                    echo json_encode([
                        'status'  => 'error',
                        'message' => 'You have submitted multiple requests recently. Please wait a short while or contact our team directly.'
                    ]);
                    exit;
                }
                $updStmt = $conn->prepare("UPDATE rate_limits SET count = count + 1, updated_at = NOW() WHERE identifier = ? AND type = ?");
                $updStmt->execute([$clientIp, $ipType]);
            } else {
                $updStmt = $conn->prepare("UPDATE rate_limits SET count = 1, updated_at = NOW() WHERE identifier = ? AND type = ?");
                $updStmt->execute([$clientIp, $ipType]);
            }
        } else {
            $insStmt = $conn->prepare("INSERT INTO rate_limits (identifier, type, count, updated_at) VALUES (?, ?, 1, NOW())");
            $insStmt->execute([$clientIp, $ipType]);
        }
    } catch (Exception $rateEx) {
        // Silently continue if rate_limits table query fails
        error_log("[Lead Capture Rate Limit Error] " . $rateEx->getMessage());
    }
}

// 4. Sanitize and strip CR/LF to prevent email header injection
$cleanHeaderString = function(string $str): string {
    return trim(str_replace(["\r", "\n", "%0a", "%0d"], '', strip_tags($str)));
};

$name    = $cleanHeaderString($_POST['name'] ?? '');
$phone   = $cleanHeaderString($_POST['mobile'] ?? ($_POST['phone'] ?? ''));
$email   = $cleanHeaderString($_POST['email'] ?? '');
$service = $cleanHeaderString($_POST['services'] ?? ($_POST['service'] ?? ''));
$website = $cleanHeaderString($_POST['website'] ?? '');
$source  = $cleanHeaderString($_POST['source'] ?? '');
$message = trim(strip_tags($_POST['message'] ?? ''));

// 5. Cloudflare Turnstile Verification (if present and configured)
$turnstileToken = $_POST['cf-turnstile-response'] ?? '';
if (!empty($turnstileToken) && isset($conn)) {
    try {
        $st = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = 'cf_turnstile_secret'");
        $st->execute();
        $cfSecret = $st->fetchColumn() ?: '';
        if (!empty($cfSecret)) {
            $verifyData = http_build_query([
                'secret'   => $cfSecret,
                'response' => $turnstileToken,
                'remoteip' => $clientIp
            ]);
            $opts = [
                'http' => [
                    'method'  => 'POST',
                    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                    'content' => $verifyData,
                    'timeout' => 5
                ]
            ];
            $ctx = stream_context_create($opts);
            $cfRes = @file_get_contents('https://challenges.cloudflare.com/turnstile/v0/siteverify', false, $ctx);
            if ($cfRes) {
                $cfJson = json_decode($cfRes, true);
                if (empty($cfJson['success'])) {
                    echo json_encode(['status' => 'error', 'message' => 'Security verification failed. Please refresh and try again.']);
                    exit;
                }
            }
        }
    } catch (Exception $cfEx) {}
}

// 6. Determine if this is an audit request
$isAudit = ($source === 'footer_audit' || stripos($service, 'audit') !== false || !empty($website));
if ($isAudit && (empty($service) || $service === 'General Inquiry')) {
    $service = 'SEO & Performance Audit';
} elseif (empty($service)) {
    $service = 'General Inquiry';
}

if (!empty($website)) {
    $message = ($message ? $message . "\n" : "") . "Website: " . $website;
}

// 7. Validate inputs
if (empty($name) || mb_strlen($name) < 2) {
    echo json_encode(['status' => 'error', 'message' => 'Please provide your full name (at least 2 characters).']);
    exit;
}

if (!empty($email)) {
    $email = strtolower($email);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['status' => 'error', 'message' => 'Please provide a valid email address.']);
        exit;
    }

    // Domain MX / DNS validation check
    $emailDomain = substr(strrchr($email, '@'), 1);
    if (function_exists('checkdnsrr') && !empty($emailDomain)) {
        // Skip check for localhost/intranet testing
        if ($emailDomain !== 'localhost' && str_contains($emailDomain, '.')) {
            $hasMx = @checkdnsrr($emailDomain, 'MX');
            $hasA  = @checkdnsrr($emailDomain, 'A');
            if (!$hasMx && !$hasA) {
                echo json_encode(['status' => 'error', 'message' => 'Please provide an email address with an active mail server domain.']);
                exit;
            }
        }
    }
} else {
    $email = null;
}

if (empty($phone) && empty($email)) {
    echo json_encode(['status' => 'error', 'message' => 'Please provide your phone number or email address so we can reach you.']);
    exit;
}

$clean_phone = preg_replace('/[^0-9+]/', '', $phone);
if (!empty($phone) && strlen(preg_replace('/[^0-9]/', '', $clean_phone)) < 7) {
    echo json_encode(['status' => 'error', 'message' => 'Please provide a valid phone number with at least 7 digits.']);
    exit;
}

try {
    if (!isset($conn)) {
        echo json_encode(['status' => 'error', 'message' => 'Database connection unavailable. Please try again later.']);
        exit;
    }

    // 8. Anti-duplicate Guard: Check if identical submission occurred in the last 45 seconds
    if (!empty($clean_phone) || !empty($email)) {
        $dupStmt = $conn->prepare("
            SELECT id FROM leads 
            WHERE (
                (? != '' AND phone = ?) OR 
                (? != '' AND email = ?)
            ) 
            AND created_at >= DATE_SUB(NOW(), INTERVAL 45 SECOND) 
            ORDER BY id DESC LIMIT 1
        ");
        $dupStmt->execute([
            $clean_phone ?: '',
            $clean_phone ?: null,
            $email ?: '',
            $email ?: null
        ]);
        $existingLead = $dupStmt->fetch(PDO::FETCH_ASSOC);

        if ($existingLead) {
            $msg = $isAudit
                ? 'Thank you, ' . htmlspecialchars($name) . '! We have already received your SEO Audit request and our team is actively reviewing your website.'
                : 'Thank you, ' . htmlspecialchars($name) . '! Your request was already received and our team will get in touch shortly.';

            echo json_encode([
                'status'  => 'success',
                'message' => $msg
            ]);
            exit;
        }
    }

    // 9. Save lead record to database
    $stmt = $conn->prepare("INSERT INTO leads (name, phone, email, service, message, status) VALUES (?, ?, ?, ?, ?, 'new_lead')");
    $stmt->execute([
        $name,
        $clean_phone ?: null,
        $email ?: null,
        $service ?: null,
        $message ?: null
    ]);

    $lead_id = (int)$conn->lastInsertId();

    $lead_data = [
        'lead_id' => $lead_id,
        'name'    => $name,
        'phone'   => $clean_phone,
        'email'   => $email,
        'service' => $service,
        'website' => $website,
        'message' => $message,
        'source'  => $source
    ];

    // 10. Check thank-you email rate limit per recipient email (max 1 per email per hour)
    $allowThankYouEmail = true;
    if (!empty($email)) {
        try {
            $emailType = 'lead_thankyou_email';
            $tStmt = $conn->prepare("SELECT count, updated_at FROM rate_limits WHERE identifier = ? AND type = ? LIMIT 1");
            $tStmt->execute([$email, $emailType]);
            $tRow = $tStmt->fetch(PDO::FETCH_ASSOC);

            if ($tRow) {
                $lastT = strtotime($tRow['updated_at']);
                if ((time() - $lastT) < 3600) {
                    $allowThankYouEmail = false; // Suppress duplicate auto-responder within 1 hour
                } else {
                    $uStmt = $conn->prepare("UPDATE rate_limits SET count = 1, updated_at = NOW() WHERE identifier = ? AND type = ?");
                    $uStmt->execute([$email, $emailType]);
                }
            } else {
                $iStmt = $conn->prepare("INSERT INTO rate_limits (identifier, type, count, updated_at) VALUES (?, ?, 1, NOW())");
                $iStmt->execute([$email, $emailType]);
            }
        } catch (Exception $limitEx) {
            $allowThankYouEmail = true;
        }
    }

    // 11. Transactional Notification Dispatch Pipeline
    if ($isAudit) {
        if (function_exists('sendAuditRequestNotification')) {
            @sendAuditRequestNotification($lead_data);
        }

        if (!empty($email) && $allowThankYouEmail && function_exists('sendAuditThankYouEmail')) {
            @sendAuditThankYouEmail($email, $name, $website, $lead_id);
        }
    } else {
        if (function_exists('sendLeadNotification')) {
            @sendLeadNotification($lead_data);
        }
        if (!empty($email) && $allowThankYouEmail && function_exists('sendLeadThankYouEmail')) {
            @sendLeadThankYouEmail($email, $name, $service);
        }
    }

    // WhatsApp Notification Alert (if configured)
    if (!empty($clean_phone) && function_exists('sendLeadWhatsAppAlert')) {
        @sendLeadWhatsAppAlert($lead_data);
    }

    $successMsg = $isAudit
        ? 'Thank you, ' . htmlspecialchars($name) . '! Your SEO Audit request has been submitted successfully. Our team will review your website and reach out shortly.'
        : 'Thank you, ' . htmlspecialchars($name) . '! Your inquiry has been submitted successfully. Our team will get in touch shortly.';

    echo json_encode([
        'status'  => 'success',
        'message' => $successMsg
    ]);
    exit;

} catch (Exception $e) {
    error_log("[Lead Capture Error] " . $e->getMessage());
    echo json_encode([
        'status'  => 'error',
        'message' => 'An error occurred while saving your request. Please try again or reach out to us directly.'
    ]);
    exit;
}
