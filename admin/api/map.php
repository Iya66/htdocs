<?php
/**
 * Admin Map API Endpoint
 * Dedicated router for MapController actions
 */
require_once __DIR__ . '/../../app/config.php';
require_once __DIR__ . '/../controller/MapController.php';

header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$mapCtrl = new MapController($conn);

if (!$mapCtrl->authorize()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access. Please log in to admin.']);
    exit;
}

$action = trim($_REQUEST['action'] ?? 'preview');
$zoom = (int)($_REQUEST['zoom'] ?? 14);
$mapType = in_array($_REQUEST['map_type'] ?? '', ['m', 'k', 'h', 'p']) ? $_REQUEST['map_type'] : 'm';

switch ($action) {
    case 'preview':
        $location = trim($_POST['location'] ?? ($_GET['location'] ?? ''));
        echo json_encode($mapCtrl->preview($location, $zoom, $mapType));
        break;

    case 'save':
        $location = trim($_POST['location'] ?? '');
        $token = $_POST['csrf_token'] ?? '';
        echo json_encode($mapCtrl->save($location, $token, $zoom, $mapType));
        break;

    case 'get':
        echo json_encode($mapCtrl->get());
        break;

    case 'reset':
        $token = $_POST['csrf_token'] ?? '';
        echo json_encode($mapCtrl->resetDefault($token));
        break;

    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "Invalid map action: '$action'"]);
        break;
}
