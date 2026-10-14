<?php
/**
 * Admin Files & Media Manager Controller
 * Manage uploaded images and assets for Services, Testimonials, Projects, etc.
 */
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/email_helper.php';
require_once __DIR__ . '/../app/security_notifier.php';

$message = $_SESSION['flash_message'] ?? '';
$message_type = $_SESSION['flash_type'] ?? '';
unset($_SESSION['flash_message'], $_SESSION['flash_type']);

$userRole = $_SESSION['admin_role'] ?? 'viewer';

// Helper function to format file sizes
function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, $precision) . ' ' . $units[$pow];
}

// Function to synchronize existing files on disk with the database
function syncDiskFilesToDatabase(PDO $conn, $adminId = null) {
    $baseDir = dirname(__DIR__, 1);
    $syncDirs = [
        'uploads/services' => 'services',
        'uploads/testimonials' => 'testimonials',
        'uploads/projects' => 'projects',
        'uploads/general' => 'general',
        'uploads' => 'general',
        'assets/images/testimonials' => 'testimonials',
        'assets/images/projects' => 'projects'
    ];

    $syncedCount = 0;
    $existingPaths = $conn->query("SELECT file_path FROM uploads")->fetchAll(PDO::FETCH_COLUMN);
    $existingMap = array_flip($existingPaths);

    foreach ($syncDirs as $relDir => $folderCategory) {
        $fullDir = $baseDir . '/' . $relDir;
        if (!is_dir($fullDir)) continue;

        $items = scandir($fullDir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..' || $item === '.htaccess' || $item === 'index.html' || $item === 'index.php') continue;
            $filePath = $fullDir . '/' . $item;
            if (!is_file($filePath)) continue;

            $ext = strtolower(pathinfo($item, PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'])) continue;

            $relativePath = $relDir . '/' . $item;
            if (!isset($existingMap[$relativePath]) && !isset($existingMap['/' . $relativePath])) {
                $fsize = filesize($filePath) ?: 0;
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $filePath) ?: 'image/jpeg';
                finfo_close($finfo);

                $title = ucwords(str_replace(['_', '-'], ' ', pathinfo($item, PATHINFO_FILENAME)));

                $stmt = $conn->prepare("INSERT INTO uploads (file_name, file_path, file_type, file_size, folder, file_title, uploaded_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$item, $relativePath, $mime, $fsize, $folderCategory, $title, $adminId]);
                $existingMap[$relativePath] = true;
                $syncedCount++;
            }
        }
    }
    return $syncedCount;
}

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (in_array($userRole, ['viewer'])) {
        $_SESSION['flash_message'] = 'Access denied. You have view-only privileges.';
        $_SESSION['flash_type'] = 'error';
        header('Location: files.php');
        exit;
    } elseif (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['flash_message'] = 'Invalid security token. Please refresh and try again.';
        $_SESSION['flash_type'] = 'error';
        header('Location: files.php');
        exit;
    } else {
        $action = $_POST['action'];

        switch ($action) {
            case 'upload':
                $targetFolder = trim($_POST['folder'] ?? 'general');
                if (!in_array($targetFolder, ['services', 'testimonials', 'projects', 'general', 'banners', 'logos'])) {
                    $targetFolder = 'general';
                }

                $uploadDir = dirname(__DIR__, 1) . '/uploads/' . $targetFolder . '/';
                if (!is_dir($uploadDir)) {
                    @mkdir($uploadDir, 0755, true);
                }

                $uploadedSuccess = 0;
                $uploadErrors = [];

                // Check if multiple files or single file
                $filesArray = [];
                if (isset($_FILES['files'])) {
                    if (is_array($_FILES['files']['name'])) {
                        $count = count($_FILES['files']['name']);
                        for ($i = 0; $i < $count; $i++) {
                            if ($_FILES['files']['error'][$i] === UPLOAD_ERR_OK) {
                                $filesArray[] = [
                                    'name' => $_FILES['files']['name'][$i],
                                    'type' => $_FILES['files']['type'][$i],
                                    'tmp_name' => $_FILES['files']['tmp_name'][$i],
                                    'error' => $_FILES['files']['error'][$i],
                                    'size' => $_FILES['files']['size'][$i]
                                ];
                            }
                        }
                    }
                } elseif (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
                    $filesArray[] = $_FILES['file'];
                }

                if (empty($filesArray)) {
                    $_SESSION['flash_message'] = 'Please select at least one valid image file to upload.';
                    $_SESSION['flash_type'] = 'error';
                } else {
                    $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
                    $allowed_mimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml', 'image/svg'];

                    foreach ($filesArray as $file) {
                        if ($file['size'] > 10 * 1024 * 1024) {
                            $uploadErrors[] = htmlspecialchars($file['name']) . ': File exceeds 10MB limit.';
                            continue;
                        }

                        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                        if (!in_array($ext, $allowed_exts)) {
                            $uploadErrors[] = htmlspecialchars($file['name']) . ': Invalid format (' . $ext . ').';
                            continue;
                        }

                        $finfo = finfo_open(FILEINFO_MIME_TYPE);
                        $mime = finfo_file($finfo, $file['tmp_name']);
                        finfo_close($finfo);

                        if (!in_array($mime, $allowed_mimes)) {
                            $uploadErrors[] = htmlspecialchars($file['name']) . ': Invalid MIME type (' . $mime . ').';
                            continue;
                        }

                        $prefix = match($targetFolder) {
                            'services' => 'service_',
                            'testimonials' => 'testimonial_',
                            'projects' => 'project_',
                            default => 'media_'
                        };

                        $cleanName = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($file['name'], PATHINFO_FILENAME));
                        $newFilename = $prefix . time() . '_' . substr(uniqid(), -6) . '.' . $ext;
                        $destPath = $uploadDir . $newFilename;

                        if (move_uploaded_file($file['tmp_name'], $destPath)) {
                            $relativePath = 'uploads/' . $targetFolder . '/' . $newFilename;
                            $title = ucwords(str_replace(['_', '-'], ' ', $cleanName));

                            $stmt = $conn->prepare("INSERT INTO uploads (file_name, file_path, file_type, file_size, folder, file_title, uploaded_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
                            $stmt->execute([$newFilename, $relativePath, $mime, $file['size'], $targetFolder, $title, $_SESSION['admin_id'] ?? null]);
                            $uploadedSuccess++;
                        } else {
                            $uploadErrors[] = htmlspecialchars($file['name']) . ': Failed to save file.';
                        }
                    }

                    if ($uploadedSuccess > 0) {
                        logAudit($conn, 'UPLOAD_FILES', "Uploaded $uploadedSuccess file(s) into folder '$targetFolder'", $_SESSION['admin_id'] ?? null);
                        notifySecurity('UPLOAD_FILES', "$uploadedSuccess file(s) uploaded to $targetFolder folder", $_SESSION['admin_id'] ?? null);
                        $_SESSION['flash_message'] = "Successfully uploaded $uploadedSuccess file(s)!";
                        $_SESSION['flash_type'] = 'success';
                    }

                    if (!empty($uploadErrors)) {
                        $_SESSION['flash_message'] = ($uploadedSuccess > 0 ? "Uploaded $uploadedSuccess file(s). " : "") . "Errors: " . implode(' ', $uploadErrors);
                        $_SESSION['flash_type'] = $uploadedSuccess > 0 ? 'warning' : 'error';
                    }
                }
                break;

            case 'delete':
                $id = (int)($_POST['id'] ?? 0);
                if ($id > 0) {
                    $stmt = $conn->prepare("SELECT * FROM uploads WHERE id = ?");
                    $stmt->execute([$id]);
                    $file = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($file) {
                        $relPath = ltrim($file['file_path'], '/\\');
                        $fullPath = dirname(__DIR__, 1) . '/' . $relPath;
                        $realRoot = realpath(dirname(__DIR__, 1));
                        $realTarget = realpath($fullPath);

                        if ($realTarget && str_starts_with($realTarget, $realRoot) && is_file($realTarget)) {
                            @unlink($realTarget);
                        }

                        $delStmt = $conn->prepare("DELETE FROM uploads WHERE id = ?");
                        $delStmt->execute([$id]);

                        logAudit($conn, 'DELETE_FILE', "Deleted file: " . $file['file_name'], $_SESSION['admin_id'] ?? null);
                        $_SESSION['flash_message'] = 'File deleted successfully.';
                        $_SESSION['flash_type'] = 'success';
                    } else {
                        $_SESSION['flash_message'] = 'File not found.';
                        $_SESSION['flash_type'] = 'error';
                    }
                }
                break;

            case 'bulk_delete':
                $rawIds = $_POST['ids'] ?? [];
                $ids = array_filter(array_map('intval', (array)$rawIds));

                if (!empty($ids)) {
                    $placeholders = implode(',', array_fill(0, count($ids), '?'));
                    $stmt = $conn->prepare("SELECT * FROM uploads WHERE id IN ($placeholders)");
                    $stmt->execute($ids);
                    $files = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    $realRoot = realpath(dirname(__DIR__, 1));
                    $deletedCount = 0;

                    foreach ($files as $f) {
                        $relPath = ltrim($f['file_path'], '/\\');
                        $fullPath = dirname(__DIR__, 1) . '/' . $relPath;
                        $realTarget = realpath($fullPath);
                        if ($realTarget && str_starts_with($realTarget, $realRoot) && is_file($realTarget)) {
                            @unlink($realTarget);
                        }
                        $deletedCount++;
                    }

                    $delStmt = $conn->prepare("DELETE FROM uploads WHERE id IN ($placeholders)");
                    $delStmt->execute($ids);

                    logAudit($conn, 'BULK_DELETE_FILES', "Deleted $deletedCount files in bulk", $_SESSION['admin_id'] ?? null);
                    $_SESSION['flash_message'] = "Successfully deleted $deletedCount file(s).";
                    $_SESSION['flash_type'] = 'success';
                } else {
                    $_SESSION['flash_message'] = 'No files selected for deletion.';
                    $_SESSION['flash_type'] = 'error';
                }
                break;

            case 'bulk_update_folder':
                $rawIds = $_POST['ids'] ?? [];
                $ids = array_filter(array_map('intval', (array)$rawIds));
                $folder = trim($_POST['folder'] ?? '');

                if (!empty($ids) && in_array($folder, ['services', 'testimonials', 'projects', 'general', 'banners', 'logos'])) {
                    $placeholders = implode(',', array_fill(0, count($ids), '?'));
                    $params = array_merge([$folder], $ids);
                    
                    $stmt = $conn->prepare("UPDATE uploads SET folder = ? WHERE id IN ($placeholders)");
                    $stmt->execute($params);
                    $updatedCount = $stmt->rowCount();

                    logAudit($conn, 'BULK_UPDATE_FILES', "Updated folder to $folder for $updatedCount files", $_SESSION['admin_id'] ?? null);
                    $_SESSION['flash_message'] = "Successfully moved $updatedCount file(s) to $folder.";
                    $_SESSION['flash_type'] = 'success';
                } else {
                    $_SESSION['flash_message'] = 'Invalid selection or folder.';
                    $_SESSION['flash_type'] = 'error';
                }
                break;

            case 'update_info':
                $id = (int)($_POST['id'] ?? 0);
                $title = trim($_POST['file_title'] ?? '');
                $folder = trim($_POST['folder'] ?? 'general');

                if ($id > 0 && in_array($folder, ['services', 'testimonials', 'projects', 'general', 'banners', 'logos'])) {
                    $stmt = $conn->prepare("UPDATE uploads SET file_title = ?, folder = ? WHERE id = ?");
                    $stmt->execute([$title, $folder, $id]);
                    logAudit($conn, 'UPDATE_FILE_INFO', "Updated file info ID: $id", $_SESSION['admin_id'] ?? null);
                    $_SESSION['flash_message'] = 'File info updated successfully.';
                    $_SESSION['flash_type'] = 'success';
                }
                break;

            case 'sync':
                $synced = syncDiskFilesToDatabase($conn, $_SESSION['admin_id'] ?? null);
                $_SESSION['flash_message'] = "Sync completed! Discovered and indexed $synced new media file(s).";
                $_SESSION['flash_type'] = 'success';
                break;
        }

        header('Location: files.php');
        exit;
    }
}

// Auto-run disk sync if table has 0 records
$totalRowsCheck = (int)$conn->query("SELECT COUNT(*) FROM uploads")->fetchColumn();
if ($totalRowsCheck === 0) {
    syncDiskFilesToDatabase($conn, $_SESSION['admin_id'] ?? null);
}

// Storage & Category Statistics
$statsQuery = $conn->query("
    SELECT 
        COUNT(*) as total_files,
        COALESCE(SUM(file_size), 0) as total_size,
        SUM(CASE WHEN folder = 'services' THEN 1 ELSE 0 END) as services_count,
        SUM(CASE WHEN folder = 'testimonials' THEN 1 ELSE 0 END) as testimonials_count,
        SUM(CASE WHEN folder = 'projects' THEN 1 ELSE 0 END) as projects_count,
        SUM(CASE WHEN folder = 'general' THEN 1 ELSE 0 END) as general_count
    FROM uploads
")->fetch(PDO::FETCH_ASSOC);

$totalFiles = (int)($statsQuery['total_files'] ?? 0);
$totalSizeFormatted = formatBytes((int)($statsQuery['total_size'] ?? 0));
$servicesCount = (int)($statsQuery['services_count'] ?? 0);
$testimonialsCount = (int)($statsQuery['testimonials_count'] ?? 0);
$projectsCount = (int)($statsQuery['projects_count'] ?? 0);
$generalCount = (int)($statsQuery['general_count'] ?? 0);

// Filters and sorting
$selectedFolder = trim($_GET['folder'] ?? 'all');
$searchQuery = trim($_GET['search'] ?? '');
$sortOption = trim($_GET['sort'] ?? 'newest');

$where = [];
$params = [];

if ($selectedFolder !== 'all' && !empty($selectedFolder)) {
    $where[] = "folder = ?";
    $params[] = $selectedFolder;
}

if (!empty($searchQuery)) {
    $where[] = "(file_name LIKE ? OR file_title LIKE ?)";
    $params[] = "%$searchQuery%";
    $params[] = "%$searchQuery%";
}

$whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

$orderBy = match($sortOption) {
    'oldest' => 'uploaded_at ASC',
    'size_desc' => 'file_size DESC',
    'size_asc' => 'file_size ASC',
    'name' => 'file_name ASC',
    default => 'uploaded_at DESC'
};

$stmt = $conn->prepare("SELECT * FROM uploads $whereClause ORDER BY $orderBy");
$stmt->execute($params);
$files = $stmt->fetchAll(PDO::FETCH_ASSOC);

$csrf_token = generateCSRFToken();

require_once __DIR__ . '/../app/Views/admin/files.php';
