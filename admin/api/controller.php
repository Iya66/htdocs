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

    case 'upload_profile_photo':
    case 'upload_avatar':
        $token = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        if (!$controller->verifyCSRF($token)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'CSRF verification failed or session expired.']);
            exit;
        }
        require_once __DIR__ . '/../../app/profile_photo_helper.php';
        $adminId = (int)($_SESSION['admin_id'] ?? 0);
        $fileKey = null;
        foreach (['avatar', 'avatar_file', 'photo', 'file'] as $k) {
            if (isset($_FILES[$k]) && is_array($_FILES[$k]) && !empty($_FILES[$k]['tmp_name'])) {
                $fileKey = $k;
                break;
            }
        }
        if (!$fileKey) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'No image file uploaded or invalid upload parameter.']);
            exit;
        }
        $errorMsg = null;
        $savedPath = uploadAdminProfilePhoto($conn, $adminId, $_FILES[$fileKey], $errorMsg);
        if (!$savedPath) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $errorMsg ?: 'Failed to upload photo.']);
            exit;
        }
        $photoUrl = getAdminAvatarUrl($savedPath, '/');
        echo json_encode([
            'success' => true,
            'message' => 'Profile photo updated successfully.',
            'avatar_path' => $savedPath,
            'avatar_url' => $photoUrl
        ]);
        break;

    case 'remove_profile_photo':
    case 'remove_avatar':
        $token = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        if (!$controller->verifyCSRF($token)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'CSRF verification failed or session expired.']);
            exit;
        }
        require_once __DIR__ . '/../../app/profile_photo_helper.php';
        $adminId = (int)($_SESSION['admin_id'] ?? 0);
        $errorMsg = null;
        if (!removeAdminProfilePhoto($conn, $adminId, $errorMsg)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $errorMsg ?: 'Failed to remove photo.']);
            exit;
        }
        echo json_encode([
            'success' => true,
            'message' => 'Profile photo removed successfully.',
            'avatar_url' => null
        ]);
        break;

    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "Unknown API action: '$action'"]);
        break;
}
