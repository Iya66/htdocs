<?php
$pageTitle = 'Contact Us';
$pageDesc = 'Get in touch with Cloud Aspire for a free consultation.';
require_once __DIR__ . '/app/config.php';
require_once __DIR__ . '/app/email_helper.php';
require_once __DIR__ . '/app/security_notifier.php';
require_once __DIR__ . '/app/whatsapp_helper.php';

$msg_status = '';
$msg_text = '';

// Rate limiting: max 5 submissions per IP per hour
$client_ip = getClientIP();
$is_rate_limited = false;
try {
    if (isset($conn)) {
        $stmt = $conn->prepare("SELECT count FROM rate_limits WHERE identifier = ? AND type = 'contact_form' AND updated_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)");
        $stmt->execute([$client_ip]);
        $rateCount = (int)$stmt->fetchColumn();
        if ($rateCount >= 5) {
            $is_rate_limited = true;
            $msg_status = 'error';
            $msg_text = 'You have submitted multiple inquiries recently. Please wait a little while before submitting again.';
        }
    }
} catch (Exception $e) {
    error_log('[Contact] Rate limit check failed: ' . $e->getMessage());
}

// Fetch active services in real-time from MySQL database
$active_services = [];
try {
    if (isset($conn)) {
        $active_services = $conn->query("SELECT id, service_name, slug, price FROM services WHERE status = 'active' ORDER BY created_at ASC")->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    error_log('[Contact] Fetch services failed: ' . $e->getMessage());
}

$selected_service = '';
if (!empty($_POST['service'])) {
    $selected_service = sanitize($_POST['service']);
} elseif (!empty($_GET['service'])) {
    $selected_service = sanitize($_GET['service']);
} elseif (!empty($_GET['plan'])) {
    $selected_service = sanitize($_GET['plan']);
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    if ($is_rate_limited) {
        // $msg_status and $msg_text already set
    } elseif (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $msg_status = 'error';
        $msg_text = 'Invalid request or session expired. Please refresh the page and try again.';
    } else {
        $name = sanitize($_POST['name'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');
        $service = sanitize($_POST['service'] ?? '');
        $message = sanitize($_POST['message'] ?? '');

        if (empty($name) || empty($email) || empty($message)) {
            $msg_status = 'error';
            $msg_text = 'Please fill out all required fields (Name, Email, and Message).';
        } elseif (!validateEmail($email)) {
            $msg_status = 'error';
            $msg_text = 'Please provide a valid email address.';
        } else {
            try {
                $stmt = $conn->prepare("INSERT INTO leads (name, phone, email, service, message, status) VALUES (?, ?, ?, ?, ?, 'new')");
                $stmt->execute([$name, $phone, $email, $service ?: null, $message ?: null]);

                // Track rate limit
                try {
                    $conn->prepare("INSERT INTO rate_limits (identifier, type, count, updated_at) VALUES (?, 'contact_form', 1, NOW()) ON DUPLICATE KEY UPDATE count = count + 1, updated_at = NOW()")->execute([$client_ip]);
                } catch (Exception $re) {}

                logAudit($conn, 'CREATE_LEAD', "Lead submitted via contact form: $name ($email)", null);
                notifySecurity('CREATE_LEAD', "Lead submitted via contact form: $name ($email)", null);

                $lead_data = [
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone,
                    'service' => $service,
                    'message' => $message
                ];
                sendLeadNotification($lead_data);
                sendLeadThankYouEmail($email, $name, $service);

                // Send WhatsApp Notifications (Admin alert & Client Thank-You)
                try {
                    sendLeadWhatsAppAlert($lead_data);
                    if (!empty($phone)) {
                        sendLeadWhatsAppThankYou($phone, $name, $service);
                    }
                } catch (Exception $we) {
                    error_log("WhatsApp notification error: " . $we->getMessage());
                }

                $msg_status = 'success';
                $msg_text = 'Thank you! Your message has been sent successfully. We will contact you shortly.';
                
                // Clear post variables so inputs reset
                $_POST = [];
            } catch (PDOException $e) {
                $msg_status = 'error';
                $msg_text = 'Something went wrong. Please try again later.';
                error_log("Contact form error: " . $e->getMessage());
            }
        }
    }
}

require_once __DIR__ . '/app/Views/frontend/contact.php';
