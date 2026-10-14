<?php
/**
 * Admin Projects Management
 */
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/email_helper.php';
require_once __DIR__ . '/../app/security_notifier.php';

$message = '';
$message_type = '';
$userRole = $_SESSION['admin_role'] ?? 'viewer';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (in_array($userRole, ['viewer'])) {
        $message = 'Access denied. You have view-only privileges.';
        $message_type = 'error';
    } elseif (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid request. Please refresh and try again.';
        $message_type = 'error';
    } else {
        $action = $_POST['action'];
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

        $project_name = trim($_POST['project_name'] ?? '');
        $thumbnail = '';

        if ($action === 'create' || $action === 'update') {
            $exclude_clause = ($action === 'update' && $id > 0) ? " AND id != ?" : "";
            $check_sql = "SELECT id FROM projects WHERE project_name = ?" . $exclude_clause . " LIMIT 1";
            $check_stmt = $conn->prepare($check_sql);
            if ($action === 'update' && $id > 0) {
                $check_stmt->execute([$project_name, $id]);
            } else {
                $check_stmt->execute([$project_name]);
            }
            if ($check_stmt->fetch()) { $message = 'This project name already exists!'; $message_type = 'error'; }
        }

        if (empty($message)) {
            if ($action === 'create' || $action === 'update') {
                $thumbnail = ltrim(trim($_POST['thumbnail'] ?? ''), '/');

                // Handle Image Upload
                if (isset($_FILES['thumbnail_file']) && $_FILES['thumbnail_file']['error'] === UPLOAD_ERR_OK) {
                    $file = $_FILES['thumbnail_file'];
                    
                    // Size limit: 5MB
                    if ($file['size'] > 5 * 1024 * 1024) {
                        $message = 'Thumbnail image file too large. Maximum size is 5MB.';
                        $message_type = 'error';
                    } else {
                        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                        
                        if (!in_array($ext, $allowed_exts)) {
                            $message = 'Invalid file format. Only JPG, JPEG, PNG, GIF, and WEBP images are allowed.';
                            $message_type = 'error';
                        } else {
                            // Check MIME Type
                            $finfo = finfo_open(FILEINFO_MIME_TYPE);
                            $mime = finfo_file($finfo, $file['tmp_name']);
                            finfo_close($finfo);
                            
                            $allowed_mimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                            if (!in_array($mime, $allowed_mimes)) {
                                $message = 'Invalid image MIME type: ' . htmlspecialchars($mime);
                                $message_type = 'error';
                            } else {
                                // Ensure directory exists
                                $upload_dir = dirname(__DIR__, 1) . '/uploads/projects/';
                                if (!is_dir($upload_dir)) {
                                    mkdir($upload_dir, 0755, true);
                                }
                                
                                $filename = 'project_' . time() . '_' . uniqid() . '.' . $ext;
                                $filepath = $upload_dir . $filename;
                                
                                if (move_uploaded_file($file['tmp_name'], $filepath)) {
                                    $thumbnail = 'uploads/projects/' . $filename;
                                } else {
                                    $message = 'Failed to save uploaded image.';
                                    $message_type = 'error';
                                }
                            }
                        }
                    }
                }
            }
        }

        if (empty($message)) {
            $category = trim($_POST['category'] ?? 'web');
            $industry = trim($_POST['industry'] ?? 'Digital');
            $description = trim($_POST['description'] ?? '');
            $client_name = trim($_POST['client_name'] ?? '');
            $results = trim($_POST['results'] ?? '');

            switch ($action) {
                case 'create':
                    $stmt = $conn->prepare("INSERT INTO projects (project_name, category, industry, description, budget, project_datetime, status, project_link, thumbnail, client_name, results) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $project_name,
                        $category ?: 'web',
                        $industry ?: 'Digital',
                        $description ?: null,
                        $_POST['budget'],
                        $_POST['project_datetime'],
                        $_POST['status'] ?? 'pending',
                        $_POST['project_link'] ?: null,
                        $thumbnail ?: null,
                        $client_name ?: null,
                        $results ?: null
                    ]);
                    logAudit($conn, 'CREATE_PROJECT', "Created project: $project_name", $_SESSION['admin_id']);
                    notifySecurity('CREATE_PROJECT', "Created project: $project_name (Budget: ₹" . $_POST['budget'] . ")", $_SESSION['admin_id']);
                    $message = 'Project created successfully!'; $message_type = 'success';
                    break;
                case 'update':
                    $stmt = $conn->prepare("UPDATE projects SET project_name = ?, category = ?, industry = ?, description = ?, budget = ?, project_datetime = ?, status = ?, project_link = ?, thumbnail = ?, client_name = ?, results = ? WHERE id = ?");
                    $stmt->execute([
                        $project_name,
                        $category ?: 'web',
                        $industry ?: 'Digital',
                        $description ?: null,
                        $_POST['budget'],
                        $_POST['project_datetime'],
                        $_POST['status'],
                        $_POST['project_link'] ?: null,
                        $thumbnail ?: null,
                        $client_name ?: null,
                        $results ?: null,
                        $id
                    ]);
                    logAudit($conn, 'UPDATE_PROJECT', "Updated project ID: $id", $_SESSION['admin_id']);
                    notifySecurity('UPDATE_PROJECT', "Project updated: $project_name (ID: $id, Status: " . $_POST['status'] . ")", $_SESSION['admin_id']);
                    $message = 'Project updated successfully!'; $message_type = 'success';
                    break;
                case 'delete':
                    $stmt = $conn->prepare("DELETE FROM projects WHERE id = ?");
                    $stmt->execute([$id]);
                    logAudit($conn, 'DELETE_PROJECT', "Deleted project ID: $id", $_SESSION['admin_id']);
                    notifySecurity('DELETE_PROJECT', "Project deleted (ID: $id)", $_SESSION['admin_id']);
                    $message = 'Project deleted successfully!'; $message_type = 'success';
                    break;
                case 'bulk_delete':
                    $ids = $_POST['bulk_ids'] ?? [];
                    if (!empty($ids) && is_array($ids)) {
                        $placeholders = implode(',', array_fill(0, count($ids), '?'));
                        $intIds = array_map('intval', $ids);
                        $stmt = $conn->prepare("DELETE FROM projects WHERE id IN ($placeholders)");
                        $stmt->execute($intIds);
                        $count = $stmt->rowCount();
                        logAudit($conn, 'BULK_DELETE_PROJECTS', "Bulk deleted $count projects: " . implode(',', $intIds), $_SESSION['admin_id']);
                        notifySecurity('BULK_DELETE_PROJECTS', "$count projects bulk deleted", $_SESSION['admin_id']);
                        $message = $count . ' project(s) deleted successfully!';
                        $message_type = 'success';
                    }
                    break;
                case 'bulk_update_status':
                    $ids = $_POST['bulk_ids'] ?? [];
                    $status = in_array($_POST['status'] ?? '', ['pending', 'running', 'completed', 'cancelled']) ? $_POST['status'] : '';
                    if (!empty($ids) && is_array($ids) && !empty($status)) {
                        $placeholders = implode(',', array_fill(0, count($ids), '?'));
                        $intIds = array_map('intval', $ids);
                        $params = array_merge([$status], $intIds);
                        $stmt = $conn->prepare("UPDATE projects SET status = ? WHERE id IN ($placeholders)");
                        $stmt->execute($params);
                        $count = $stmt->rowCount();
                        logAudit($conn, 'BULK_UPDATE_PROJECTS', "Bulk updated $count projects to status: $status", $_SESSION['admin_id']);
                        notifySecurity('BULK_UPDATE_PROJECTS', "$count projects updated to $status", $_SESSION['admin_id']);
                        $message = $count . ' project(s) updated to ' . ucfirst($status) . '!';
                        $message_type = 'success';
                    }
                    break;
                case 'bulk_update_category':
                    $ids = $_POST['bulk_ids'] ?? [];
                    $category = trim($_POST['category'] ?? '');
                    if (!empty($ids) && is_array($ids) && !empty($category)) {
                        $placeholders = implode(',', array_fill(0, count($ids), '?'));
                        $intIds = array_map('intval', $ids);
                        $params = array_merge([$category], $intIds);
                        $stmt = $conn->prepare("UPDATE projects SET category = ? WHERE id IN ($placeholders)");
                        $stmt->execute($params);
                        $count = $stmt->rowCount();
                        logAudit($conn, 'BULK_UPDATE_PROJECTS_CATEGORY', "Bulk updated $count projects to category: $category", $_SESSION['admin_id']);
                        notifySecurity('BULK_UPDATE_PROJECTS_CATEGORY', "$count projects updated to category $category", $_SESSION['admin_id']);
                        $message = $count . ' project(s) updated to ' . strtoupper($category) . '!';
                        $message_type = 'success';
                    }
                    break;
            }
        }
    }
}

$projects = $conn->query("SELECT * FROM projects ORDER BY project_datetime DESC")->fetchAll(PDO::FETCH_ASSOC);
$csrf_token = generateCSRFToken();

require_once __DIR__ . '/../app/Views/admin/projects.php';
