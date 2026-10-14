<?php
/**
 * Admin Testimonials Management
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

        $avatar_image = trim($_POST['image'] ?? '');

        // Handle Avatar File Upload
        if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['image_file'];
            if ($file['size'] > 5 * 1024 * 1024) {
                $message = 'Avatar image file too large. Maximum size is 5MB.';
                $message_type = 'error';
            } else {
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                if (!in_array($ext, $allowed_exts)) {
                    $message = 'Invalid image format. Only JPG, PNG, GIF, and WEBP are allowed.';
                    $message_type = 'error';
                } else {
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime = finfo_file($finfo, $file['tmp_name']);
                    finfo_close($finfo);
                    $allowed_mimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                    if (!in_array($mime, $allowed_mimes)) {
                        $message = 'Invalid image MIME type: ' . htmlspecialchars($mime);
                        $message_type = 'error';
                    } else {
                        $upload_dir = dirname(__DIR__, 1) . '/uploads/testimonials/';
                        if (!is_dir($upload_dir)) {
                            mkdir($upload_dir, 0755, true);
                        }
                        $filename = 'testimonial_' . time() . '_' . uniqid() . '.' . $ext;
                        $filepath = $upload_dir . $filename;
                        if (move_uploaded_file($file['tmp_name'], $filepath)) {
                            $avatar_image = 'uploads/testimonials/' . $filename;
                        } else {
                            $message = 'Failed to upload testimonial avatar.';
                            $message_type = 'error';
                        }
                    }
                }
            }
        } elseif ($action === 'update' && empty($avatar_image) && !empty($_POST['existing_image'])) {
            $avatar_image = trim($_POST['existing_image']);
        }

        if (empty($message)) {
            switch ($action) {
                case 'create':
                    $stmt = $conn->prepare("INSERT INTO testimonials (name, designation, message, image, status) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$_POST['name'], $_POST['designation'] ?? '', $_POST['message'], $avatar_image ?: null, $_POST['status'] ?? 'active']);
                    logAudit($conn, 'CREATE_TESTIMONIAL', "Created testimonial for: " . $_POST['name'], $_SESSION['admin_id']);
                    notifySecurity('CREATE_TESTIMONIAL', "Created testimonial for: " . $_POST['name'], $_SESSION['admin_id']);
                    $message = 'Testimonial created successfully!'; $message_type = 'success';
                    break;
                case 'update':
                    $stmt = $conn->prepare("UPDATE testimonials SET name = ?, designation = ?, message = ?, image = ?, status = ? WHERE id = ?");
                    $stmt->execute([$_POST['name'], $_POST['designation'] ?? '', $_POST['message'], $avatar_image ?: null, $_POST['status'] ?? 'active', $id]);
                    logAudit($conn, 'UPDATE_TESTIMONIAL', "Updated testimonial ID: $id", $_SESSION['admin_id']);
                    notifySecurity('UPDATE_TESTIMONIAL', "Testimonial ID $id updated", $_SESSION['admin_id']);
                    $message = 'Testimonial updated successfully!'; $message_type = 'success';
                    break;
                case 'delete':
                    $stmt = $conn->prepare("DELETE FROM testimonials WHERE id = ?");
                    $stmt->execute([$id]);
                    logAudit($conn, 'DELETE_TESTIMONIAL', "Deleted testimonial ID: $id", $_SESSION['admin_id']);
                    notifySecurity('DELETE_TESTIMONIAL', "Testimonial ID $id deleted", $_SESSION['admin_id']);
                    $message = 'Testimonial deleted successfully!'; $message_type = 'success';
                    break;
                case 'toggle':
                    $stmt = $conn->prepare("UPDATE testimonials SET status = IF(status='active', 'inactive', 'active') WHERE id = ?");
                    $stmt->execute([$id]);
                    logAudit($conn, 'TOGGLE_TESTIMONIAL', "Toggled testimonial ID: $id", $_SESSION['admin_id']);
                    $message = 'Testimonial status updated!'; $message_type = 'success';
                    break;
                case 'bulk_delete':
                    $ids = $_POST['bulk_ids'] ?? [];
                    if (!empty($ids) && is_array($ids)) {
                        $placeholders = implode(',', array_fill(0, count($ids), '?'));
                        $intIds = array_map('intval', $ids);
                        $stmt = $conn->prepare("DELETE FROM testimonials WHERE id IN ($placeholders)");
                        $stmt->execute($intIds);
                        $count = $stmt->rowCount();
                        logAudit($conn, 'BULK_DELETE_TESTIMONIALS', "Bulk deleted $count testimonials: " . implode(',', $intIds), $_SESSION['admin_id']);
                        notifySecurity('BULK_DELETE_TESTIMONIALS', "$count testimonials bulk deleted", $_SESSION['admin_id']);
                        $message = $count . ' testimonial(s) deleted successfully!';
                        $message_type = 'success';
                    }
                    break;
                case 'bulk_update_status':
                    $ids = $_POST['bulk_ids'] ?? [];
                    $status = in_array($_POST['status'] ?? '', ['active', 'inactive']) ? $_POST['status'] : '';
                    if (!empty($ids) && is_array($ids) && !empty($status)) {
                        $placeholders = implode(',', array_fill(0, count($ids), '?'));
                        $intIds = array_map('intval', $ids);
                        $params = array_merge([$status], $intIds);
                        $stmt = $conn->prepare("UPDATE testimonials SET status = ? WHERE id IN ($placeholders)");
                        $stmt->execute($params);
                        $count = $stmt->rowCount();
                        logAudit($conn, 'BULK_UPDATE_TESTIMONIALS', "Bulk updated $count testimonials to status: $status", $_SESSION['admin_id']);
                        notifySecurity('BULK_UPDATE_TESTIMONIALS', "$count testimonials updated to $status", $_SESSION['admin_id']);
                        $message = $count . ' testimonial(s) updated to ' . ucfirst($status) . '!';
                        $message_type = 'success';
                    }
                    break;
            }
        }
    }
}

$testimonials = $conn->query("SELECT * FROM testimonials ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
$csrf_token = generateCSRFToken();

// Discover available avatars for quick selection
$sample_avatars = [];
$avatar_dirs = [
    dirname(__DIR__, 1) . '/assets/images/testimonials' => 'assets/images/testimonials/',
    dirname(__DIR__, 1) . '/uploads/testimonials' => 'uploads/testimonials/'
];
foreach ($avatar_dirs as $dir => $prefix) {
    if (is_dir($dir)) {
        $files = scandir($dir);
        foreach ($files as $f) {
            if ($f !== '.' && $f !== '..' && preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $f)) {
                $sample_avatars[] = $prefix . $f;
            }
        }
    }
}

require_once __DIR__ . '/../app/Views/admin/testimonials.php';
