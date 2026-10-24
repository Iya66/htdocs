<?php
/**
 * Cloud Aspire Admin Portal — Profile Photo API Endpoint
 * 
 * Handles authenticated AJAX requests for uploading, previewing, and removing
 * administrator profile photos with CSRF protection, fileinfo validation, and
 * granular activity audit logging.
 */

require_once __DIR__ . '/../../app/config.php';
require_once __DIR__ . '/../../app/profile_photo_helper.php';

header('Content-Type: application/json; charset=utf-8');

// 1. Authentication check
if (empty($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized. Please log in to manage your profile photo.'
    ]);
    exit;
}

$currentAdminId = (int)$_SESSION['admin_id'];
$currentAdminRole = $_SESSION['admin_role'] ?? 'viewer';

// 2. Determine target admin
$requestedTargetId = isset($_POST['admin_id']) ? (int)$_POST['admin_id'] : $currentAdminId;
if ($requestedTargetId !== $currentAdminId && $currentAdminRole !== 'admin') {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Access denied. You cannot modify another administrator’s photo.'
    ]);
    exit;
}

$targetAdminId = $requestedTargetId;

// Read action from POST, GET, REQUEST, or JSON body
$action = trim((string)($_POST['action'] ?? ($_GET['action'] ?? ($_REQUEST['action'] ?? ''))));

if ($action === '' && str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'application/json')) {
    $rawInput = file_get_contents('php://input');
    $jsonData = json_decode($rawInput, true);
    if (is_array($jsonData)) {
        $action = trim((string)($jsonData['action'] ?? ''));
        if (empty($_POST['csrf_token']) && !empty($jsonData['csrf_token'])) {
            $_POST['csrf_token'] = $jsonData['csrf_token'];
        }
        if (empty($_POST['admin_id']) && !empty($jsonData['admin_id'])) {
            $_POST['admin_id'] = $jsonData['admin_id'];
        }
    }
}

// Ensure action is provided
if ($action === '') {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'No action specified in request. Supported actions are: upload, remove, get.'
    ]);
    exit;
}

// 3. For write operations, require POST and CSRF validation
$isWriteAction = in_array($action, ['upload', 'remove', 'upload_avatar', 'remove_avatar', 'upload_profile_photo', 'remove_profile_photo'], true);
if ($isWriteAction) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed. Write actions require POST.']);
        exit;
    }

    $token = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if (!function_exists('verifyCSRFToken') || !verifyCSRFToken($token)) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid or expired security token. Please refresh the page.'
        ]);
        exit;
    }
}

// 4. Action handling
switch ($action) {
    case 'upload':
    case 'upload_avatar':
    case 'upload_profile_photo':
        $uploadedFile = $_FILES['avatar'] ?? ($_FILES['avatar_file'] ?? ($_FILES['photo'] ?? ($_FILES['file'] ?? null)));
        if (!$uploadedFile || !isset($uploadedFile['tmp_name']) || empty($uploadedFile['tmp_name'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'No image file was received. Please select a valid photo to upload.'
            ]);
            exit;
        }

        $errorMsg = null;
        $savedPath = uploadAdminProfilePhoto($conn, $targetAdminId, $uploadedFile, $errorMsg);

        if (!$savedPath) {
            http_response_code(422);
            echo json_encode([
                'success' => false,
                'message' => $errorMsg ?: 'Failed to upload profile photo.'
            ]);
            exit;
        }

        // Determine public URL relative to admin folder
        $avatarUrl = getAdminAvatarUrl($savedPath, '../');

        echo json_encode([
            'success'     => true,
            'avatar_path' => $savedPath,
            'avatar_url'  => $avatarUrl,
            'message'     => 'Profile photo uploaded successfully!'
        ]);
        exit;

    case 'remove':
    case 'remove_avatar':
    case 'remove_profile_photo':
        $errorMsg = null;
        $removed = removeAdminProfilePhoto($conn, $targetAdminId, $errorMsg);

        if (!$removed) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => $errorMsg ?: 'Failed to remove profile photo.'
            ]);
            exit;
        }

        $initial = strtoupper(substr($_SESSION['admin_name'] ?? 'Admin', 0, 1)) ?: 'A';

        echo json_encode([
            'success'         => true,
            'default_initial' => $initial,
            'message'         => 'Profile photo removed successfully.'
        ]);
        exit;

    case 'get':
    case 'get_avatar':
    case 'get_profile_photo':
        $stmt = $conn->prepare("SELECT id, name, username, avatar FROM admin WHERE id = ? LIMIT 1");
        $stmt->execute([$targetAdminId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Admin not found.']);
            exit;
        }

        $url = getAdminAvatarUrl($row['avatar'] ?? '', '../');

        echo json_encode([
            'success'    => true,
            'avatar'     => $row['avatar'] ?? '',
            'avatar_url' => $url,
            'initial'    => strtoupper(substr($row['name'] ?: $row['username'] ?: 'A', 0, 1))
        ]);
        exit;

    default:
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => "Unknown action: '$action'. Supported actions are: upload, remove, get."
        ]);
        exit;
}
