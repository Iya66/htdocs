<?php
/**
 * Admin API Entrypoint Router
 * Routes incoming AJAX / API requests to ApiController methods.
 */
require_once __DIR__ . '/../../app/config.php';
require_once __DIR__ . '/../controller/ApiController.php';

header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$controller = new ApiController($conn);

if (!$controller->authorize()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access. Please log in.']);
    exit;
}

$action = trim($_REQUEST['action'] ?? '');

switch ($action) {
    case 'preview_map':
        $location = trim($_POST['location'] ?? ($_GET['location'] ?? ''));
        echo json_encode($controller->previewMap($location));
        break;

    case 'save_map':
        $location = trim($_POST['location'] ?? '');
        $token = $_POST['csrf_token'] ?? '';
        echo json_encode($controller->saveMap($location, $token));
        break;

    case 'save_contact_settings':
        $token = $_POST['csrf_token'] ?? '';
        echo json_encode($controller->saveContactSettings($_POST, $token));
        break;

    case 'get_stats':
        echo json_encode($controller->getLiveStats());
        break;

    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "Unknown API action: '$action'"]);
        break;
}
