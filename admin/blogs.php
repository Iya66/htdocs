<?php
/**
 * Admin Blog Posts Management Controller
 * Full CRUD, Image Uploads, Media Library Picker, SEO & Slug Management
 */
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/email_helper.php';
require_once __DIR__ . '/../app/security_notifier.php';

$message = $_SESSION['flash_message'] ?? '';
$message_type = $_SESSION['flash_type'] ?? '';
unset($_SESSION['flash_message'], $_SESSION['flash_type']);

$userRole = $_SESSION['admin_role'] ?? 'viewer';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (in_array($userRole, ['viewer'])) {
        $_SESSION['flash_message'] = 'Access denied. You have view-only privileges.';
        $_SESSION['flash_type'] = 'error';
        header('Location: blogs.php');
        exit;
    } elseif (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['flash_message'] = 'Invalid request. Please refresh and try again.';
        $_SESSION['flash_type'] = 'error';
        header('Location: blogs.php');
        exit;
    } else {
        $action = $_POST['action'];
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

        $title = trim($_POST['title'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $category = trim($_POST['category'] ?? 'Digital Marketing');
        $author = trim($_POST['author'] ?? 'Cloud Aspire Team');
        $excerpt = trim($_POST['excerpt'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $published_date = !empty($_POST['published_date']) ? $_POST['published_date'] : date('Y-m-d');
        $status = in_array($_POST['status'] ?? '', ['published', 'draft']) ? $_POST['status'] : 'published';

        $seo_title = trim($_POST['seo_title'] ?? '') ?: null;
        $meta_description = trim($_POST['meta_description'] ?? '') ?: null;
        $meta_keywords = trim($_POST['meta_keywords'] ?? '') ?: null;

        $blog_image = trim($_POST['image'] ?? '');

        // Auto-generate slug if empty
        if (empty($slug) && !empty($title)) {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));
        }

        if ($action === 'create' || $action === 'update') {
            $exclude_clause = ($action === 'update' && $id > 0) ? " AND id != ?" : "";

            if (!empty($slug)) {
                $check_slug_stmt = $conn->prepare("SELECT id FROM blogs WHERE slug = ?" . $exclude_clause . " LIMIT 1");
                if ($action === 'update' && $id > 0) {
                    $check_slug_stmt->execute([$slug, $id]);
                } else {
                    $check_slug_stmt->execute([$slug]);
                }
                if ($check_slug_stmt->fetch()) {
                    $message = 'An article with this URL slug already exists! Please enter a unique slug.';
                    $message_type = 'error';
                }
            }

            // Handle Image Upload
            if (empty($message) && isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['image_file'];
                if ($file['size'] > 5 * 1024 * 1024) {
                    $message = 'Featured image file too large. Maximum size is 5MB.';
                    $message_type = 'error';
                } else {
                    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                    $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    if (!in_array($ext, $allowed_exts)) {
                        $message = 'Invalid image format. Only JPG, PNG, GIF, and WEBP are allowed.';
                        $message_type = 'error';
                    } else {
                        $upload_dir = dirname(__DIR__, 1) . '/uploads/blogs/';
                        if (!is_dir($upload_dir)) {
                            mkdir($upload_dir, 0755, true);
                        }
                        $filename = 'blog_' . time() . '_' . uniqid() . '.' . $ext;
                        $filepath = $upload_dir . $filename;
                        if (move_uploaded_file($file['tmp_name'], $filepath)) {
                            $blog_image = 'uploads/blogs/' . $filename;
                        } else {
                            $message = 'Failed to upload featured image file.';
                            $message_type = 'error';
                        }
                    }
                }
            } elseif ($action === 'update' && empty($blog_image) && !empty($_POST['existing_image'])) {
                $blog_image = trim($_POST['existing_image']);
            }
        }

        if (empty($message)) {
            switch ($action) {
                case 'create':
                    $stmt = $conn->prepare("
                        INSERT INTO blogs (title, slug, category, author, image, excerpt, content, published_date, status, seo_title, meta_description, meta_keywords)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $title,
                        $slug,
                        $category,
                        $author,
                        $blog_image ?: null,
                        $excerpt,
                        $content,
                        $published_date,
                        $status,
                        $seo_title,
                        $meta_description,
                        $meta_keywords
                    ]);
                    logAudit($conn, 'CREATE_BLOG', "Created blog article: $title", $_SESSION['admin_id']);
                    notifySecurity('CREATE_BLOG', "New blog post created: $title", $_SESSION['admin_id']);
                    $_SESSION['flash_message'] = 'Blog article published successfully!';
                    $_SESSION['flash_type'] = 'success';
                    break;

                case 'update':
                    $stmt = $conn->prepare("
                        UPDATE blogs 
                        SET title = ?, slug = ?, category = ?, author = ?, image = ?, excerpt = ?, content = ?, published_date = ?, status = ?, seo_title = ?, meta_description = ?, meta_keywords = ?
                        WHERE id = ?
                    ");
                    $stmt->execute([
                        $title,
                        $slug,
                        $category,
                        $author,
                        $blog_image ?: null,
                        $excerpt,
                        $content,
                        $published_date,
                        $status,
                        $seo_title,
                        $meta_description,
                        $meta_keywords,
                        $id
                    ]);
                    logAudit($conn, 'UPDATE_BLOG', "Updated blog article ID: $id", $_SESSION['admin_id']);
                    notifySecurity('UPDATE_BLOG', "Blog article updated: $title (ID: $id)", $_SESSION['admin_id']);
                    $_SESSION['flash_message'] = 'Blog article updated successfully!';
                    $_SESSION['flash_type'] = 'success';
                    break;

                case 'delete':
                    $stmt = $conn->prepare("DELETE FROM blogs WHERE id = ?");
                    $stmt->execute([$id]);
                    logAudit($conn, 'DELETE_BLOG', "Deleted blog ID: $id", $_SESSION['admin_id']);
                    notifySecurity('DELETE_BLOG', "Blog article deleted (ID: $id)", $_SESSION['admin_id']);
                    $_SESSION['flash_message'] = 'Blog article deleted successfully!';
                    $_SESSION['flash_type'] = 'success';
                    break;

                case 'toggle':
                    $stmt = $conn->prepare("UPDATE blogs SET status = IF(status='published', 'draft', 'published') WHERE id = ?");
                    $stmt->execute([$id]);
                    logAudit($conn, 'TOGGLE_BLOG', "Toggled status of blog ID: $id", $_SESSION['admin_id']);
                    $_SESSION['flash_message'] = 'Blog status updated!';
                    $_SESSION['flash_type'] = 'success';
                    break;
            }
        } else {
            $_SESSION['flash_message'] = $message;
            $_SESSION['flash_type'] = $message_type;
        }

        header('Location: blogs.php');
        exit;
    }
}

// Fetch all blogs
$blogs = [];
if (isset($conn)) {
    try {
        $blogs = $conn->query("SELECT * FROM blogs ORDER BY published_date DESC, id DESC")->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $blogs = [];
    }
}

$csrf_token = generateCSRFToken();

require_once __DIR__ . '/../app/Views/admin/blogs-pro.php';
