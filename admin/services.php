<?php
/**
 * Admin Services Management
 * Senior Full-Stack Implementation: Comprehensive Dynamic Services Management
 */
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/email_helper.php';
require_once __DIR__ . '/../app/security_notifier.php';

$message = $_SESSION['flash_message'] ?? '';
$message_type = $_SESSION['flash_type'] ?? '';
unset($_SESSION['flash_message'], $_SESSION['flash_type']);

$userRole = $_SESSION['admin_role'] ?? 'viewer';

/**
 * Secure File Upload Handler
 */
function handleServiceUpload($fileKey, $prefix = 'service_', &$errorMsg = '') {
    if (!isset($_FILES[$fileKey]) || $_FILES[$fileKey]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    $file = $_FILES[$fileKey];
    if ($file['size'] > 5 * 1024 * 1024) {
        $errorMsg = 'Uploaded file is too large. Maximum allowed size is 5MB.';
        return null;
    }
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
    if (!in_array($ext, $allowed_exts)) {
        $errorMsg = 'Invalid file format. Allowed formats: JPG, PNG, GIF, WEBP, SVG.';
        return null;
    }
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    $allowed_mimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];
    if (!in_array($mime, $allowed_mimes)) {
        $errorMsg = 'Invalid MIME type: ' . htmlspecialchars($mime);
        return null;
    }
    $upload_dir = dirname(__DIR__, 1) . '/uploads/services/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    $filename = $prefix . time() . '_' . uniqid() . '.' . $ext;
    $filepath = $upload_dir . $filename;
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return 'uploads/services/' . $filename;
    }
    $errorMsg = 'Failed to move uploaded file.';
    return null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (in_array($userRole, ['viewer'])) {
        if (!empty($_POST['ajax'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Access denied. You have view-only privileges.']);
            exit;
        }
        $_SESSION['flash_message'] = 'Access denied. You have view-only privileges.';
        $_SESSION['flash_type'] = 'error';
        header('Location: services.php');
        exit;
    } elseif (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        if (!empty($_POST['ajax'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid security token. Please refresh and try again.']);
            exit;
        }
        $_SESSION['flash_message'] = 'Invalid request. Please refresh and try again.';
        $_SESSION['flash_type'] = 'error';
        header('Location: services.php');
        exit;
    } else {
        $action = $_POST['action'];
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

        $service_name = trim($_POST['service_name'] ?? '');
        $raw_slug = trim($_POST['slug'] ?? '');
        if (empty($raw_slug) && !empty($service_name)) {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $service_name), '-'));
        } else {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $raw_slug), '-'));
        }

        $service_image = trim($_POST['image'] ?? '');
        $hero_bg_image = trim($_POST['hero_bg_image'] ?? '');
        $og_image = trim($_POST['og_image'] ?? '');

        if ($action === 'create' || $action === 'update') {
            $exclude_clause = ($action === 'update' && $id > 0) ? " AND id != ?" : "";

            if (!empty($service_name)) {
                $check_stmt = $conn->prepare("SELECT id FROM services WHERE service_name = ?" . $exclude_clause . " LIMIT 1");
                if ($action === 'update' && $id > 0) {
                    $check_stmt->execute([$service_name, $id]);
                } else {
                    $check_stmt->execute([$service_name]);
                }
                if ($check_stmt->fetch()) { 
                    $message = 'A service with this name already exists!'; 
                    $message_type = 'error'; 
                }
            } else {
                $message = 'Service title is required.';
                $message_type = 'error';
            }

            if (empty($message) && !empty($slug)) {
                $check_slug_stmt = $conn->prepare("SELECT id FROM services WHERE slug = ?" . $exclude_clause . " LIMIT 1");
                if ($action === 'update' && $id > 0) {
                    $check_slug_stmt->execute([$slug, $id]);
                } else {
                    $check_slug_stmt->execute([$slug]);
                }
                if ($check_slug_stmt->fetch()) { 
                    $message = 'This URL slug already exists! Please choose a unique slug.'; 
                    $message_type = 'error'; 
                }
            }

            // 1. Handle Primary Image Upload
            if (empty($message) && isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
                $uploadErr = '';
                $uploaded = handleServiceUpload('image_file', 'service_', $uploadErr);
                if ($uploaded) {
                    $service_image = $uploaded;
                } else {
                    $message = $uploadErr ?: 'Failed to upload primary service image.';
                    $message_type = 'error';
                }
            } elseif ($action === 'update' && empty($service_image) && !empty($_POST['existing_image'])) {
                $service_image = trim($_POST['existing_image']);
            }

            // 2. Handle Hero Background Image Upload
            if (empty($message) && isset($_FILES['hero_bg_file']) && $_FILES['hero_bg_file']['error'] === UPLOAD_ERR_OK) {
                $uploadErr = '';
                $uploaded = handleServiceUpload('hero_bg_file', 'service_hero_', $uploadErr);
                if ($uploaded) {
                    $hero_bg_image = $uploaded;
                } else {
                    $message = $uploadErr ?: 'Failed to upload hero background image.';
                    $message_type = 'error';
                }
            } elseif ($action === 'update' && empty($hero_bg_image) && !empty($_POST['existing_hero_bg_image'])) {
                $hero_bg_image = trim($_POST['existing_hero_bg_image']);
            }

            // 3. Handle Open Graph Image Upload
            if (empty($message) && isset($_FILES['og_image_file']) && $_FILES['og_image_file']['error'] === UPLOAD_ERR_OK) {
                $uploadErr = '';
                $uploaded = handleServiceUpload('og_image_file', 'service_og_', $uploadErr);
                if ($uploaded) {
                    $og_image = $uploaded;
                } else {
                    $message = $uploadErr ?: 'Failed to upload Open Graph image.';
                    $message_type = 'error';
                }
            } elseif ($action === 'update' && empty($og_image) && !empty($_POST['existing_og_image'])) {
                $og_image = trim($_POST['existing_og_image']);
            }
        }

        if (empty($message)) {
            // Process rich service metadata & fields
            $hero_heading = trim($_POST['hero_heading'] ?? '') ?: null;
            $hero_highlight = trim($_POST['hero_highlight'] ?? '') ?: null;
            $learn_more_text = trim($_POST['learn_more_text'] ?? '') ?: 'Learn More';
            $learn_more_url = trim($_POST['learn_more_url'] ?? '') ?: null;
            $explore_btn_text = trim($_POST['explore_btn_text'] ?? '') ?: 'Explore Service';
            $explore_btn_url = trim($_POST['explore_btn_url'] ?? '') ?: null;

            $sort_order = isset($_POST['sort_order']) && is_numeric($_POST['sort_order']) ? (int)$_POST['sort_order'] : 0;
            if ($action === 'create' && $sort_order <= 0) {
                $maxOrderStmt = $conn->query("SELECT MAX(sort_order) FROM services");
                $sort_order = ((int)$maxOrderStmt->fetchColumn()) + 1;
            }

            $seo_title = trim($_POST['seo_title'] ?? '') ?: null;
            $meta_description = trim($_POST['meta_description'] ?? '') ?: null;
            $meta_keywords = trim($_POST['meta_keywords'] ?? '') ?: null;

            // Features / Deliverables
            $raw_features = trim($_POST['features'] ?? '');
            if (!empty($raw_features)) {
                $decoded = json_decode($raw_features, true);
                if (is_array($decoded)) {
                    $features_json = json_encode($decoded, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                } else {
                    $feature_items = [];
                    $lines = array_filter(array_map('trim', explode("\n", $raw_features)));
                    foreach ($lines as $line) {
                        if (str_contains($line, ':')) {
                            [$ftitle, $fdesc] = explode(':', $line, 2);
                            $feature_items[] = ['title' => trim($ftitle), 'desc' => trim($fdesc)];
                        } else {
                            $feature_items[] = ['title' => trim($line), 'desc' => ''];
                        }
                    }
                    $features_json = json_encode($feature_items, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                }
            } else {
                $features_json = null;
            }

            // Benefits
            $raw_benefits = trim($_POST['benefits'] ?? '');
            if (!empty($raw_benefits)) {
                $decoded = json_decode($raw_benefits, true);
                if (is_array($decoded)) {
                    $benefits_json = json_encode($decoded, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                } else {
                    $lines = array_values(array_filter(array_map('trim', explode("\n", $raw_benefits))));
                    $benefits_json = json_encode($lines, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                }
            } else {
                $benefits_json = null;
            }

            // Process Steps (4-Step Workflow)
            $step_titles = $_POST['step_title'] ?? [];
            $step_descs = $_POST['step_desc'] ?? [];
            $process_steps_arr = [];
            if (!empty($step_titles) && is_array($step_titles)) {
                foreach ($step_titles as $idx => $st_title) {
                    $st_title = trim($st_title);
                    $st_desc = trim($step_descs[$idx] ?? '');
                    if (!empty($st_title)) {
                        $process_steps_arr[] = [
                            'step' => sprintf('%02d', $idx + 1),
                            'title' => $st_title,
                            'desc' => $st_desc
                        ];
                    }
                }
            }
            if (!empty($process_steps_arr)) {
                $process_steps_json = json_encode($process_steps_arr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            } elseif (!empty($_POST['process_steps_raw'])) {
                $process_steps_json = trim($_POST['process_steps_raw']);
            } else {
                $process_steps_json = null;
            }

            // Service FAQs
            $faq_questions = $_POST['faq_q'] ?? [];
            $faq_answers = $_POST['faq_a'] ?? [];
            $faqs_arr = [];
            if (!empty($faq_questions) && is_array($faq_questions)) {
                foreach ($faq_questions as $idx => $fq_q) {
                    $fq_q = trim($fq_q);
                    $fq_a = trim($faq_answers[$idx] ?? '');
                    if (!empty($fq_q) && !empty($fq_a)) {
                        $faqs_arr[] = [
                            'question' => $fq_q,
                            'answer' => $fq_a
                        ];
                    }
                }
            }
            if (!empty($faqs_arr)) {
                $faqs_json = json_encode($faqs_arr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            } elseif (!empty($_POST['faqs_raw'])) {
                $faqs_json = trim($_POST['faqs_raw']);
            } else {
                $faqs_json = null;
            }

            switch ($action) {
                case 'create':
                    $stmt = $conn->prepare("INSERT INTO services (
                        service_name, slug, short_description, full_description, 
                        hero_heading, hero_highlight, learn_more_text, learn_more_url, 
                        explore_btn_text, explore_btn_url, icon, image, hero_bg_image, 
                        price, status, sort_order, seo_title, meta_description, 
                        meta_keywords, og_image, features, benefits, process_steps, faqs
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $service_name, 
                        $slug, 
                        $_POST['short_description'] ?? '', 
                        $_POST['full_description'] ?? '', 
                        $hero_heading,
                        $hero_highlight,
                        $learn_more_text,
                        $learn_more_url,
                        $explore_btn_text,
                        $explore_btn_url,
                        $_POST['icon'] ?: null, 
                        $service_image ?: null, 
                        $hero_bg_image ?: null,
                        $_POST['price'] ?: null, 
                        $_POST['status'] ?? 'active',
                        $sort_order,
                        $seo_title,
                        $meta_description,
                        $meta_keywords,
                        $og_image ?: null,
                        $features_json,
                        $benefits_json,
                        $process_steps_json,
                        $faqs_json
                    ]);
                    $new_id = $conn->lastInsertId();
                    logAudit($conn, 'CREATE_SERVICE', "Created service: $service_name (ID: $new_id)", $_SESSION['admin_id']);
                    notifySecurity('CREATE_SERVICE', "Created service: $service_name", $_SESSION['admin_id']);
                    $_SESSION['flash_message'] = 'Service created successfully!';
                    $_SESSION['flash_type'] = 'success';
                    break;

                case 'update':
                    $stmt = $conn->prepare("UPDATE services SET 
                        service_name = ?, slug = ?, short_description = ?, full_description = ?, 
                        hero_heading = ?, hero_highlight = ?, learn_more_text = ?, learn_more_url = ?, 
                        explore_btn_text = ?, explore_btn_url = ?, icon = ?, image = ?, hero_bg_image = ?, 
                        price = ?, status = ?, sort_order = ?, seo_title = ?, meta_description = ?, 
                        meta_keywords = ?, og_image = ?, features = ?, benefits = ?, process_steps = ?, faqs = ? 
                        WHERE id = ?");
                    $stmt->execute([
                        $service_name,
                        $slug,
                        $_POST['short_description'] ?? '',
                        $_POST['full_description'] ?? '',
                        $hero_heading,
                        $hero_highlight,
                        $learn_more_text,
                        $learn_more_url,
                        $explore_btn_text,
                        $explore_btn_url,
                        $_POST['icon'] ?? null,
                        $service_image ?: null,
                        $hero_bg_image ?: null,
                        $_POST['price'] ?? null,
                        $_POST['status'] ?? 'active',
                        $sort_order,
                        $seo_title,
                        $meta_description,
                        $meta_keywords,
                        $og_image ?: null,
                        $features_json,
                        $benefits_json,
                        $process_steps_json,
                        $faqs_json,
                        $id
                    ]);
                    logAudit($conn, 'UPDATE_SERVICE', "Updated service ID: $id ($service_name)", $_SESSION['admin_id']);
                    notifySecurity('UPDATE_SERVICE', "Updated service: $service_name (ID: $id)", $_SESSION['admin_id']);
                    $_SESSION['flash_message'] = 'Service updated successfully!';
                    $_SESSION['flash_type'] = 'success';
                    break;

                case 'delete':
                    $del_stmt = $conn->prepare("SELECT service_name FROM services WHERE id = ?");
                    $del_stmt->execute([$id]);
                    $del_name = $del_stmt->fetchColumn() ?: "ID $id";

                    $stmt = $conn->prepare("DELETE FROM services WHERE id = ?");
                    $stmt->execute([$id]);
                    logAudit($conn, 'DELETE_SERVICE', "Deleted service: $del_name (ID: $id)", $_SESSION['admin_id']);
                    notifySecurity('DELETE_SERVICE', "Service deleted: $del_name (ID: $id)", $_SESSION['admin_id']);
                    $_SESSION['flash_message'] = "Service \"$del_name\" was permanently deleted.";
                    $_SESSION['flash_type'] = 'success';
                    break;

                case 'toggle':
                    $stmt = $conn->prepare("UPDATE services SET status = IF(status='active', 'inactive', 'active') WHERE id = ?");
                    $stmt->execute([$id]);
                    logAudit($conn, 'TOGGLE_SERVICE', "Toggled service ID: $id", $_SESSION['admin_id']);
                    notifySecurity('TOGGLE_SERVICE', "Service ID $id status toggled", $_SESSION['admin_id']);
                    $_SESSION['flash_message'] = 'Service status updated!';
                    $_SESSION['flash_type'] = 'success';
                    break;

                case 'reorder':
                    if (!empty($_POST['orders']) && is_array($_POST['orders'])) {
                        $updateStmt = $conn->prepare("UPDATE services SET sort_order = ? WHERE id = ?");
                        foreach ($_POST['orders'] as $svc_id => $order_val) {
                            $updateStmt->execute([(int)$order_val, (int)$svc_id]);
                        }
                        logAudit($conn, 'REORDER_SERVICES', "Updated service display orders", $_SESSION['admin_id']);
                        if (!empty($_POST['ajax'])) {
                            header('Content-Type: application/json');
                            echo json_encode(['success' => true, 'message' => 'Display orders updated successfully!']);
                            exit;
                        }
                        $_SESSION['flash_message'] = 'Service display order updated successfully!';
                        $_SESSION['flash_type'] = 'success';
                    } elseif ($id > 0 && isset($_POST['sort_order'])) {
                        $stmt = $conn->prepare("UPDATE services SET sort_order = ? WHERE id = ?");
                        $stmt->execute([(int)$_POST['sort_order'], $id]);
                        logAudit($conn, 'REORDER_SERVICES', "Updated service ID $id sort order", $_SESSION['admin_id']);
                        if (!empty($_POST['ajax'])) {
                            header('Content-Type: application/json');
                            echo json_encode(['success' => true, 'message' => 'Display order updated!']);
                            exit;
                        }
                        $_SESSION['flash_message'] = 'Service display order updated!';
                        $_SESSION['flash_type'] = 'success';
                    }
                    break;

                case 'bulk_delete':
                    $raw_ids = $_POST['ids'] ?? [];
                    $ids = array_filter(array_map('intval', (array)$raw_ids));
                    if (!empty($ids)) {
                        $placeholders = implode(',', array_fill(0, count($ids), '?'));
                        $stmt = $conn->prepare("DELETE FROM services WHERE id IN ($placeholders)");
                        $stmt->execute($ids);
                        $del_count = count($ids);
                        logAudit($conn, 'BULK_DELETE_SERVICES', "Deleted $del_count services in bulk (IDs: " . implode(',', $ids) . ")", $_SESSION['admin_id']);
                        notifySecurity('BULK_DELETE_SERVICES', "$del_count services deleted in bulk", $_SESSION['admin_id']);
                        $_SESSION['flash_message'] = "Successfully deleted $del_count selected service(s)!";
                        $_SESSION['flash_type'] = 'success';
                    } else {
                        $_SESSION['flash_message'] = 'No valid services selected for deletion.';
                        $_SESSION['flash_type'] = 'error';
                    }
                    break;

                case 'bulk_activate':
                    $raw_ids = $_POST['ids'] ?? [];
                    $ids = array_filter(array_map('intval', (array)$raw_ids));
                    if (!empty($ids)) {
                        $placeholders = implode(',', array_fill(0, count($ids), '?'));
                        $stmt = $conn->prepare("UPDATE services SET status = 'active' WHERE id IN ($placeholders)");
                        $stmt->execute($ids);
                        $act_count = count($ids);
                        logAudit($conn, 'BULK_ACTIVATE_SERVICES', "Activated $act_count services in bulk", $_SESSION['admin_id']);
                        $_SESSION['flash_message'] = "Successfully activated $act_count selected service(s)!";
                        $_SESSION['flash_type'] = 'success';
                    } else {
                        $_SESSION['flash_message'] = 'No valid services selected.';
                        $_SESSION['flash_type'] = 'error';
                    }
                    break;

                case 'bulk_deactivate':
                    $raw_ids = $_POST['ids'] ?? [];
                    $ids = array_filter(array_map('intval', (array)$raw_ids));
                    if (!empty($ids)) {
                        $placeholders = implode(',', array_fill(0, count($ids), '?'));
                        $stmt = $conn->prepare("UPDATE services SET status = 'inactive' WHERE id IN ($placeholders)");
                        $stmt->execute($ids);
                        $deact_count = count($ids);
                        logAudit($conn, 'BULK_DEACTIVATE_SERVICES', "Deactivated $deact_count services in bulk", $_SESSION['admin_id']);
                        $_SESSION['flash_message'] = "Successfully deactivated $deact_count selected service(s)!";
                        $_SESSION['flash_type'] = 'success';
                    } else {
                        $_SESSION['flash_message'] = 'No valid services selected.';
                        $_SESSION['flash_type'] = 'error';
                    }
                    break;
            }
        } else {
            $_SESSION['flash_message'] = $message;
            $_SESSION['flash_type'] = $message_type;
        }

        header('Location: services.php');
        exit;
    }
}

// Fetch all services ordered by sort_order ASC, id ASC
$services = $conn->query("SELECT * FROM services ORDER BY sort_order ASC, id ASC")->fetchAll(PDO::FETCH_ASSOC);
$csrf_token = generateCSRFToken();

$available_icons = [
    'fa-globe' => 'Globe', 'fa-cloud' => 'Cloud', 'fa-server' => 'Server', 'fa-shield-alt' => 'Shield',
    'fa-lock' => 'Lock', 'fa-bolt' => 'Bolt', 'fa-rocket' => 'Rocket', 'fa-chart-line' => 'Chart',
    'fa-database' => 'Database', 'fa-envelope' => 'Email', 'fa-mobile-alt' => 'Mobile', 'fa-laptop' => 'Laptop',
    'fa-laptop-code' => 'Code / Dev', 'fa-cog' => 'Settings', 'fa-paint-brush' => 'Design', 'fa-palette' => 'Palette',
    'fa-search' => 'Search', 'fa-magnifying-glass-chart' => 'SEO Analytics', 'fa-bullhorn' => 'Ads & Ads',
    'fa-share-nodes' => 'Social Media', 'fa-pen-nib' => 'Content & Copy', 'fa-check-circle' => 'Verified'
];

require_once __DIR__ . '/../app/Views/admin/services-pro.php';
