<?php
/**
 * Admin Media & Files API
 * Supports querying, AJAX uploads, and media selection for services, testimonials, etc.
 */
require_once __DIR__ . '/../../app/config.php';
require_once __DIR__ . '/../../app/email_helper.php';
require_once __DIR__ . '/../../app/security_notifier.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit;
}

$userRole = $_SESSION['admin_role'] ?? 'viewer';
$action = $_REQUEST['action'] ?? 'list';

// Helper function to format file sizes
function formatFileSize($bytes) {
    if ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 1) . ' KB';
    }
    return $bytes . ' B';
}

// Determine base URL for media assets
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$script_name = $_SERVER['SCRIPT_NAME'] ?? '';
$base_dir = rtrim(dirname(dirname(dirname($script_name))), '/\\');
$site_root_url = $protocol . $host . ($base_dir ? $base_dir : '');

try {
    switch ($action) {
        case 'list':
            $folder = trim($_GET['folder'] ?? 'all');
            $search = trim($_GET['search'] ?? '');
            $limit  = isset($_GET['limit']) ? min(100, max(1, (int)$_GET['limit'])) : 60;
            $offset = isset($_GET['offset']) ? max(0, (int)$_GET['offset']) : 0;

            $params = [];
            $where = [];

            if ($folder !== 'all' && !empty($folder)) {
                $where[] = "folder = ?";
                $params[] = $folder;
            }

            if (!empty($search)) {
                $where[] = "(file_name LIKE ? OR file_title LIKE ?)";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }

            $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

            // Total count
            $countStmt = $conn->prepare("SELECT COUNT(*) FROM uploads $whereClause");
            $countStmt->execute($params);
            $totalCount = (int)$countStmt->fetchColumn();

            // Fetch records
            $query = "SELECT * FROM uploads $whereClause ORDER BY uploaded_at DESC LIMIT $limit OFFSET $offset";
            $stmt = $conn->prepare($query);
            $stmt->execute($params);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $files = [];
            foreach ($rows as $row) {
                $cleanPath = ltrim($row['file_path'], '/');
                $fullUrl = (str_starts_with($row['file_path'], 'http://') || str_starts_with($row['file_path'], 'https://'))
                    ? $row['file_path']
                    : $site_root_url . '/' . $cleanPath;

                $files[] = [
                    'id'             => (int)$row['id'],
                    'file_name'      => $row['file_name'],
                    'file_title'     => $row['file_title'] ?? pathinfo($row['file_name'], PATHINFO_FILENAME),
                    'file_path'      => $row['file_path'],
                    'relative_path'  => $cleanPath,
                    'full_url'       => $fullUrl,
                    'file_type'      => $row['file_type'],
                    'file_size'      => (int)$row['file_size'],
                    'formatted_size' => formatFileSize((int)$row['file_size']),
                    'folder'         => $row['folder'] ?? 'general',
                    'uploaded_at'    => $row['uploaded_at'],
                    'uploaded_date'  => date('M d, Y', strtotime($row['uploaded_at']))
                ];
            }

            echo json_encode([
                'success' => true,
                'total'   => $totalCount,
                'limit'   => $limit,
                'offset'  => $offset,
                'files'   => $files
            ]);
            break;

        case 'upload':
            if (in_array($userRole, ['viewer'])) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Permission denied. Viewers cannot upload files.']);
                exit;
            }

            $csrfToken = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_REQUEST['csrf_token'] ?? '';
            if (!verifyCSRFToken($csrfToken)) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Invalid or expired CSRF security token.']);
                exit;
            }

            if (!isset($_FILES['file']) && !isset($_FILES['image_file'])) {
                echo json_encode(['success' => false, 'error' => 'No file uploaded.']);
                exit;
            }

            $fileObj = $_FILES['file'] ?? $_FILES['image_file'];

            if ($fileObj['error'] !== UPLOAD_ERR_OK) {
                echo json_encode(['success' => false, 'error' => 'Upload error code: ' . $fileObj['error']]);
                exit;
            }

            // Size validation (Max 10MB)
            if ($fileObj['size'] > 10 * 1024 * 1024) {
                echo json_encode(['success' => false, 'error' => 'File size exceeds maximum 10MB limit.']);
                exit;
            }

            // Extension validation
            $ext = strtolower(pathinfo($fileObj['name'], PATHINFO_EXTENSION));
            $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
            if (!in_array($ext, $allowed_exts)) {
                echo json_encode(['success' => false, 'error' => 'Invalid file extension. Allowed: JPG, PNG, GIF, WEBP, SVG.']);
                exit;
            }

            // MIME Type validation
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $fileObj['tmp_name']);
            finfo_close($finfo);

            $allowed_mimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml', 'image/svg'];
            if (!in_array($mime, $allowed_mimes)) {
                echo json_encode(['success' => false, 'error' => 'Invalid image MIME type: ' . htmlspecialchars($mime)]);
                exit;
            }

            // Sanitize SVG against embedded scripts / event handlers
            if ($ext === 'svg') {
                $svgContent = file_get_contents($fileObj['tmp_name']);
                if (preg_match('/<script|onload|onerror|onclick|javascript:|data:text\/html/i', $svgContent)) {
                    echo json_encode(['success' => false, 'error' => 'SVG file contains prohibited scripts or active content.']);
                    exit;
                }
            }

            // Category / Folder selection
            $targetFolder = trim($_POST['folder'] ?? 'general');
            if (!in_array($targetFolder, ['services', 'testimonials', 'projects', 'general', 'banners', 'logos'])) {
                $targetFolder = 'general';
            }

            $uploadDir = dirname(__DIR__, 2) . '/uploads/' . $targetFolder . '/';
            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0755, true);
            }

            $prefix = match($targetFolder) {
                'services' => 'service_',
                'testimonials' => 'testimonial_',
                'projects' => 'project_',
                default => 'media_'
            };

            $safeOriginalName = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($fileObj['name'], PATHINFO_FILENAME));
            $newFileName = $prefix . time() . '_' . substr(uniqid(), -6) . '.' . $ext;
            $destination = $uploadDir . $newFileName;

            if (move_uploaded_file($fileObj['tmp_name'], $destination)) {
                $relativePath = 'uploads/' . $targetFolder . '/' . $newFileName;
                $fileTitle = trim($_POST['title'] ?? '') ?: ucwords(str_replace('_', ' ', $safeOriginalName));

                $insertStmt = $conn->prepare("INSERT INTO uploads (file_name, file_path, file_type, file_size, folder, file_title, uploaded_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $insertStmt->execute([
                    $newFileName,
                    $relativePath,
                    $mime,
                    $fileObj['size'],
                    $targetFolder,
                    $fileTitle,
                    $_SESSION['admin_id'] ?? null
                ]);
                $newId = (int)$conn->lastInsertId();

                logAudit($conn, 'UPLOAD_FILE', "Uploaded file: $newFileName to folder '$targetFolder'", $_SESSION['admin_id'] ?? null);

                $fullUrl = $site_root_url . '/' . $relativePath;

                echo json_encode([
                    'success' => true,
                    'message' => 'Image uploaded successfully!',
                    'file'    => [
                        'id'             => $newId,
                        'file_name'      => $newFileName,
                        'file_title'     => $fileTitle,
                        'file_path'      => $relativePath,
                        'relative_path'  => $relativePath,
                        'full_url'       => $fullUrl,
                        'file_type'      => $mime,
                        'file_size'      => $fileObj['size'],
                        'formatted_size' => formatFileSize($fileObj['size']),
                        'folder'         => $targetFolder,
                        'uploaded_at'    => date('Y-m-d H:i:s'),
                        'uploaded_date'  => date('M d, Y')
                    ]
                ]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Failed to write uploaded file to destination directory.']);
            }
            break;

        case 'delete':
            if (in_array($userRole, ['viewer'])) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Permission denied.']);
                exit;
            }

            $csrfToken = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_REQUEST['csrf_token'] ?? '';
            if (!verifyCSRFToken($csrfToken)) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Invalid or expired CSRF security token.']);
                exit;
            }

            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) {
                echo json_encode(['success' => false, 'error' => 'Invalid file ID.']);
                exit;
            }

            $stmt = $conn->prepare("SELECT * FROM uploads WHERE id = ?");
            $stmt->execute([$id]);
            $file = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$file) {
                echo json_encode(['success' => false, 'error' => 'File not found in database.']);
                exit;
            }

            // Delete physical file safely
            $relPath = ltrim($file['file_path'], '/\\');
            $fullPath = dirname(__DIR__, 2) . '/' . $relPath;
            $realRoot = realpath(dirname(__DIR__, 2));
            $realTarget = realpath($fullPath);

            if ($realTarget && str_starts_with($realTarget, $realRoot) && is_file($realTarget)) {
                @unlink($realTarget);
            }

            $delStmt = $conn->prepare("DELETE FROM uploads WHERE id = ?");
            $delStmt->execute([$id]);

            logAudit($conn, 'DELETE_FILE', "Deleted file: " . $file['file_name'], $_SESSION['admin_id'] ?? null);

            echo json_encode(['success' => true, 'message' => 'File deleted successfully.']);
            break;

        default:
            echo json_encode(['success' => false, 'error' => 'Invalid action specified.']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
}
