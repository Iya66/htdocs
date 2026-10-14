<?php
/**
 * General Settings - Separate Save Buttons for Each Section
 */
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../../app/config.php';
require_once __DIR__ . '/../../app/email_helper.php';
require_once __DIR__ . '/../../app/security_notifier.php';

// Admin-only access
$stmt = $conn->prepare("SELECT role FROM admin WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$cu = $stmt->fetch();
if (!$cu || $cu['role'] !== 'admin') {
    $_SESSION['error'] = 'Access denied. Admin privileges required.';
    header('Location: ../dashboard.php');
    exit;
}

$csrf_token = generateCSRFToken();

// Helper function to get setting
function getSetting(PDO $conn, string $key, $default = '') {
    global $settings;
    return $settings[$key] ?? $default;
}

// Helper function for section saves with CSRF
function saveSection(PDO $conn, string $section, callable $callback) {
    global $csrf_token;

    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        return ['success' => false, 'message' => 'Invalid security token. Please refresh.'];
    }

    try {
        $callback();
        return ['success' => true, 'message' => ucfirst($section) . ' saved successfully!'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

// ============================================
// 1. SAVE SITE NAME
// ============================================
if (isset($_POST['save_site_name'])) {
    $result = saveSection($conn, 'Site Name', function() {
        global $conn;
        $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        $stmt->execute(['site_name', trim($_POST['site_name'])]);
        logAudit($conn, 'UPDATE_SITE_NAME', 'Updated site name', $_SESSION['admin_id']);
        notifySecurity('UPDATE_SITE_NAME', 'Site name updated', $_SESSION['admin_id']);
    });
    $_SESSION['site_name_msg'] = $result['message'];
    $_SESSION['site_name_type'] = $result['success'] ? 'success' : 'error';
    header('Location: general.php');
    exit;
}

// ============================================
// 2. SAVE THEME COLORS
// ============================================
if (isset($_POST['save_theme'])) {
    $result = saveSection($conn, 'Theme Colors', function() {
        global $conn;
        $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        $stmt->execute(['theme_primary', trim($_POST['theme_primary'])]);
        $stmt->execute(['theme_accent', trim($_POST['theme_accent'])]);
        logAudit($conn, 'UPDATE_THEME', 'Updated theme colors', $_SESSION['admin_id']);
    });
    $_SESSION['theme_msg'] = $result['message'];
    $_SESSION['theme_type'] = $result['success'] ? 'success' : 'error';
    header('Location: general.php');
    exit;
}

// ============================================
// 3. SAVE LOGO
// ============================================
if (isset($_POST['save_logo'])) {
    $result = saveSection($conn, 'Logo', function() {
        global $conn;
        if (isset($_FILES['site_logo']) && $_FILES['site_logo']['error'] === UPLOAD_ERR_OK) {
            handleLogoUpload($conn, 'site_logo');
        }
        logAudit($conn, 'UPDATE_LOGO', 'Updated site logo', $_SESSION['admin_id']);
    });
    $_SESSION['logo_msg'] = $result['message'];
    $_SESSION['logo_type'] = $result['success'] ? 'success' : 'error';
    header('Location: general.php');
    exit;
}

// ============================================
// 4. SAVE FAVICON
// ============================================
if (isset($_POST['save_favicon'])) {
    $result = saveSection($conn, 'Favicon', function() {
        global $conn;
        if (isset($_FILES['site_favicon']) && $_FILES['site_favicon']['error'] === UPLOAD_ERR_OK) {
            handleLogoUpload($conn, 'site_favicon');
        }
        logAudit($conn, 'UPDATE_FAVICON', 'Updated site favicon', $_SESSION['admin_id']);
    });
    $_SESSION['favicon_msg'] = $result['message'];
    $_SESSION['favicon_type'] = $result['success'] ? 'success' : 'error';
    header('Location: general.php');
    exit;
}

// ============================================
// 5. SAVE FILES (TXT, XML, HTML)
// ============================================
if (isset($_POST['save_files'])) {
    $result = saveSection($conn, 'Files', function() {
        global $conn;
        $file_fields = ['upload_txt', 'upload_xml', 'upload_html'];
        foreach ($file_fields as $field) {
            if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
                handleFileUpload($conn, $field);
            }
        }
        logAudit($conn, 'UPDATE_FILES', 'Updated file uploads', $_SESSION['admin_id']);
    });
    $_SESSION['files_msg'] = $result['message'];
    $_SESSION['files_type'] = $result['success'] ? 'success' : 'error';
    header('Location: general.php');
    exit;
}

// ============================================
// 6. DELETE FILE
// ============================================
if (isset($_POST['delete_file'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['delete_msg'] = 'Invalid request.';
        $_SESSION['delete_type'] = 'error';
    } else {
        $allowed_keys = ['site_logo', 'site_favicon', 'upload_txt', 'upload_xml', 'upload_html'];
        $file_key = $_POST['file_key'] ?? '';

        if (in_array($file_key, $allowed_keys)) {
            $file_path = getSetting($conn, $file_key);
            $full_path = realpath(__DIR__ . '/../../uploads/');

            if ($file_path && file_exists(__DIR__ . '/../../' . $file_path)) {
                $resolved_path = realpath(__DIR__ . '/../../' . $file_path);
                if ($resolved_path && strpos($resolved_path, $full_path) === 0) {
                    unlink($resolved_path);
                }
            }

            $stmt = $conn->prepare("DELETE FROM settings WHERE setting_key = ?");
            $stmt->execute([$file_key]);
            logAudit($conn, 'DELETE_FILE', 'Deleted file: ' . $file_key, $_SESSION['admin_id']);

            $_SESSION['delete_msg'] = 'File deleted successfully!';
            $_SESSION['delete_type'] = 'success';
        } else {
            $_SESSION['delete_msg'] = 'Invalid file key.';
            $_SESSION['delete_type'] = 'error';
        }
    }
    header('Location: general.php');
    exit;
}

// ============================================
// UPLOAD HANDLERS
// ============================================
function handleLogoUpload(PDO $conn, string $key) {
    if (!isset($_FILES[$key]) || $_FILES[$key]['error'] !== UPLOAD_ERR_OK) {
        return false;
    }

    $file = $_FILES[$key];

    if ($file['size'] > 2 * 1024 * 1024) {
        throw new Exception('Image too large. Maximum size is 2MB');
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed_ext = ['png', 'jpg', 'jpeg', 'gif', 'svg', 'ico'];
    if (!in_array($ext, $allowed_ext)) {
        throw new Exception('Only PNG, JPG, GIF, SVG, ICO files are allowed');
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $allowed_mime = ['image/png', 'image/jpeg', 'image/gif', 'image/svg+xml', 'image/x-icon'];
    if (!in_array($mime, $allowed_mime)) {
        throw new Exception('Invalid image type');
    }

    $upload_dir = __DIR__ . '/../../uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0750, true);
    }

    $filename = $key . '_' . time() . '.' . $ext;
    $filepath = $upload_dir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        throw new Exception('Failed to upload file');
    }

    $stmt = $conn->prepare("INSERT INTO uploads (file_name, file_path, file_type, file_size) VALUES (?, ?, ?, ?)");
    $stmt->execute([$filename, 'uploads/' . $filename, $key, $file['size']]);

    $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    $stmt->execute([$key, 'uploads/' . $filename]);

    return true;
}

function handleFileUpload(PDO $conn, string $key) {
    if (!isset($_FILES[$key]) || $_FILES[$key]['error'] !== UPLOAD_ERR_OK) {
        return false;
    }

    $file = $_FILES[$key];

    if ($file['size'] > 5 * 1024 * 1024) {
        throw new Exception('File too large. Maximum size is 5MB');
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $isHtmlKey = ($key === 'upload_html');
    $allowed_ext = $isHtmlKey ? ['html', 'htm'] : ['txt', 'xml'];
    if (!in_array($ext, $allowed_ext, true)) {
        throw new Exception($isHtmlKey ? 'Only HTML files are allowed' : 'Only TXT and XML files are allowed');
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $allowed_mime = $isHtmlKey ? ['text/html', 'text/plain'] : ['text/plain', 'application/xml', 'text/xml'];
    if (!in_array($mime, $allowed_mime, true)) {
        throw new Exception('Invalid file type');
    }

    // Sanitize HTML verification files against active script injection
    if ($isHtmlKey) {
        $content = file_get_contents($file['tmp_name']);
        if (preg_match('/<script|onload|onerror|onclick|javascript:|iframe|object|embed/i', $content)) {
            throw new Exception('HTML verification file must not contain active scripts or embedded objects.');
        }
    }

    $upload_dir = __DIR__ . '/../../uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0750, true);
    }

    $filename = $key . '_' . time() . '.' . $ext;
    $filepath = $upload_dir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        throw new Exception('Failed to upload file');
    }

    $stmt = $conn->prepare("INSERT INTO uploads (file_name, file_path, file_type, file_size) VALUES (?, ?, ?, ?)");
    $stmt->execute([$filename, 'uploads/' . $filename, $key, $file['size']]);

    $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    $stmt->execute([$key, 'uploads/' . $filename]);

    return true;
}

// ============================================
// GET ALL SETTINGS
// ============================================
$site_name = getSetting($conn, 'site_name', 'CloudASPIRE');
$site_logo = getSetting($conn, 'site_logo', '');
$favicon = getSetting($conn, 'site_favicon', '');
$upload_txt = getSetting($conn, 'upload_txt', '');
$upload_xml = getSetting($conn, 'upload_xml', '');
$upload_html = getSetting($conn, 'upload_html', '');
$theme_primary = getSetting($conn, 'theme_primary', '#6366f1');
$theme_accent = getSetting($conn, 'theme_accent', '#8b5cf6');

// Get all uploaded files
$stmt = $conn->query("SELECT * FROM uploads ORDER BY uploaded_at DESC");
$all_uploads = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ============================================
// RETRIEVE SESSION MESSAGES
// ============================================
$site_name_msg = $_SESSION['site_name_msg'] ?? '';
$site_name_type = $_SESSION['site_name_type'] ?? '';
$theme_msg = $_SESSION['theme_msg'] ?? '';
$theme_type = $_SESSION['theme_type'] ?? '';
$logo_msg = $_SESSION['logo_msg'] ?? '';
$logo_type = $_SESSION['logo_type'] ?? '';
$favicon_msg = $_SESSION['favicon_msg'] ?? '';
$favicon_type = $_SESSION['favicon_type'] ?? '';
$files_msg = $_SESSION['files_msg'] ?? '';
$files_type = $_SESSION['files_type'] ?? '';
$delete_msg = $_SESSION['delete_msg'] ?? '';
$delete_type = $_SESSION['delete_type'] ?? '';

// Clear session messages
unset($_SESSION['site_name_msg'], $_SESSION['site_name_type']);
unset($_SESSION['theme_msg'], $_SESSION['theme_type']);
unset($_SESSION['logo_msg'], $_SESSION['logo_type']);
unset($_SESSION['favicon_msg'], $_SESSION['favicon_type']);
unset($_SESSION['files_msg'], $_SESSION['files_type']);
unset($_SESSION['delete_msg'], $_SESSION['delete_type']);

require_once __DIR__ . '/../../app/Views/admin/settings/general.php';
