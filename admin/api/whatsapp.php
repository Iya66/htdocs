<?php
/**
 * Admin WhatsApp Management API (Fast2SMS WABA)
 * Supports live template fetching, credential testing, and test message dispatching
 */

require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../../app/config.php';
require_once __DIR__ . '/../../app/whatsapp_helper.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit;
}

$userRole = $_SESSION['admin_role'] ?? 'viewer';
if (in_array($userRole, ['viewer'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Permission denied. Admin privileges required.']);
    exit;
}

$action = $_REQUEST['action'] ?? '';
$csrfToken = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_GET['csrf_token'] ?? '';

// Require CSRF token for mutating actions
if (in_array($action, ['send_test', 'save_credentials']) && !verifyCSRFToken($csrfToken)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Invalid or expired security token. Please refresh.']);
    exit;
}

try {
    $service = getWhatsAppService();

    switch ($action) {
        case 'fetch_templates':
            $apiKey = trim($_REQUEST['api_key'] ?? '');
            $result = $service->getWabaAndTemplateDetails($apiKey ?: null);
            echo json_encode($result);
            break;

        case 'send_test':
            $to = trim($_POST['to_number'] ?? '');
            $messageId = trim($_POST['message_id'] ?? '');
            $phoneNumberId = trim($_POST['phone_number_id'] ?? '');
            $rawVars = trim($_POST['variables'] ?? '');
            $vars = array_filter(array_map('trim', explode(',', $rawVars)));

            if (empty($to)) {
                echo json_encode(['success' => false, 'error' => 'Please provide a recipient mobile number.']);
                exit;
            }
            if (empty($messageId)) {
                echo json_encode(['success' => false, 'error' => 'Please provide a Template Message ID.']);
                exit;
            }

            $result = $service->sendTestMessage($to, $messageId, $phoneNumberId ?: null, $vars);
            echo json_encode($result);
            break;

        default:
            echo json_encode(['success' => false, 'error' => 'Invalid action specified.']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
