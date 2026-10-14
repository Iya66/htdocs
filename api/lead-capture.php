<?php
/**
 * Lead Capture API Endpoint
 * Handles submissions from CherriDM Popup Modal and Footer Audit Form
 */
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../app/config.php';
@include_once __DIR__ . '/../app/email_helper.php';
@include_once __DIR__ . '/../app/security_notifier.php';
@include_once __DIR__ . '/../app/whatsapp_helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

$name    = trim($_POST['name'] ?? '');
$phone   = trim($_POST['mobile'] ?? ($_POST['phone'] ?? ''));
$email   = trim($_POST['email'] ?? '');
$service = trim($_POST['services'] ?? ($_POST['service'] ?? 'General Inquiry'));
$message = trim($_POST['message'] ?? '');
$website = trim($_POST['website'] ?? '');

if (!empty($website)) {
    $message = ($message ? $message . "\n" : "") . "Website: " . $website;
}

if (empty($name)) {
    echo json_encode(['status' => 'error', 'message' => 'Please provide your name.']);
    exit;
}

if (empty($phone) && empty($email)) {
    echo json_encode(['status' => 'error', 'message' => 'Please provide your contact number or email address.']);
    exit;
}

// Normalize phone
$clean_phone = preg_replace('/[^0-9+]/', '', $phone);

try {
    if (isset($conn)) {
        $stmt = $conn->prepare("INSERT INTO leads (name, phone, email, service, message, status) VALUES (?, ?, ?, ?, ?, 'new')");
        $stmt->execute([
            $name,
            $clean_phone ?: null,
            $email ?: null,
            $service ?: null,
            $message ?: null
        ]);

        $lead_id = $conn->lastInsertId();

        // Optional notifications
        $lead_data = [
            'name'    => $name,
            'phone'   => $clean_phone,
            'email'   => $email,
            'service' => $service,
            'message' => $message
        ];

        if (function_exists('sendLeadNotification')) {
            @sendLeadNotification($lead_data);
        }
        if (!empty($email) && function_exists('sendLeadThankYouEmail')) {
            @sendLeadThankYouEmail($email, $name, $service);
        }
        if (!empty($clean_phone) && function_exists('sendLeadWhatsAppAlert')) {
            @sendLeadWhatsAppAlert($lead_data);
        }

        echo json_encode([
            'status' => 'success',
            'message' => 'Thank you, ' . htmlspecialchars($name) . '! Your request has been submitted successfully. Our team will get in touch shortly.'
        ]);
        exit;
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database connection unavailable.']);
        exit;
    }
} catch (Exception $e) {
    error_log("[Lead Capture] Error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'An error occurred while saving your details. Please try again.']);
    exit;
}
