<?php
/**
 * Admin Map Controller Page
 * Dedicated interface for controlling website Google Maps settings, coordinates, and preview.
 */
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/controller/MapController.php';

$csrf_token = generateCSRFToken();
$mapCtrl = new MapController($conn);
$userRole = $_SESSION['admin_role'] ?? 'viewer';

$msg = '';
$type = '';

// Handle Form Submissions
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    if (isset($_POST['save_map_settings'])) {
        $location = trim($_POST['map_location'] ?? '');
        $raw_iframe = trim($_POST['map_raw_iframe'] ?? '');
        $zoom = (int)($_POST['map_zoom'] ?? 14);
        $mapType = trim($_POST['map_type'] ?? 'm');
        $token = $_POST['csrf_token'] ?? '';

        // If raw iframe is provided and not empty, prioritize it; otherwise use location
        $input = !empty($raw_iframe) ? $raw_iframe : $location;
        $result = $mapCtrl->save($input, $token, $zoom, $mapType);

        $_SESSION['msg'] = $result['message'];
        $_SESSION['type'] = $result['success'] ? 'success' : 'error';
        header('Location: map.php');
        exit;
    }

    if (isset($_POST['reset_map_default'])) {
        $token = $_POST['csrf_token'] ?? '';
        $result = $mapCtrl->resetDefault($token);

        $_SESSION['msg'] = $result['message'];
        $_SESSION['type'] = $result['success'] ? 'success' : 'error';
        header('Location: map.php');
        exit;
    }
}

// Retrieve message from session
$msg = $_SESSION['msg'] ?? '';
$type = $_SESSION['type'] ?? '';
unset($_SESSION['msg'], $_SESSION['type']);

// Get current map configuration
$mapData = $mapCtrl->get();

// Render view
require_once __DIR__ . '/../app/Views/admin/map.php';
