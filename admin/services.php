<?php
/**
 * Admin Services Management
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
        header('Location: services.php');
        exit;
    } elseif (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['flash_message'] = 'Invalid request. Please refresh and try again.';
        $_SESSION['flash_type'] = 'error';
        header('Location: services.php');
        exit;
    } else {
        $action = $_POST['action'];
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

        $service_name = trim($_POST['service_name'] ?? '');
        $slug = trim($_POST['slug'] ?? '');

        $service_image = trim($_POST['image'] ?? '');

        if ($action === 'create' || $action === 'update') {
            $exclude_clause = ($action === 'update' && $id > 0) ? " AND id != ?" : "";

            if (!empty($service_name)) {
                $check_stmt = $conn->prepare("SELECT id FROM services WHERE service_name = ?" . $exclude_clause . " LIMIT 1");
                if ($action === 'update' && $id > 0) {
                    $check_stmt->execute([$service_name, $id]);
                } else {
                    $check_stmt->execute([$service_name]);
                }
                if ($check_stmt->fetch()) { $message = 'This service name already exists!'; $message_type = 'error'; }
            }
            if (empty($message) && !empty($slug)) {
                $check_slug_stmt = $conn->prepare("SELECT id FROM services WHERE slug = ?" . $exclude_clause . " LIMIT 1");
                if ($action === 'update' && $id > 0) {
                    $check_slug_stmt->execute([$slug, $id]);
                } else {
                    $check_slug_stmt->execute([$slug]);
                }
                if ($check_slug_stmt->fetch()) { $message = 'This slug already exists!'; $message_type = 'error'; }
            }

            // Handle Image Upload
            if (empty($message) && isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['image_file'];
                if ($file['size'] > 5 * 1024 * 1024) {
                    $message = 'Service image file too large. Maximum size is 5MB.';
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
                            $upload_dir = dirname(__DIR__, 1) . '/uploads/services/';
                            if (!is_dir($upload_dir)) {
                                mkdir($upload_dir, 0755, true);
                            }
                            $filename = 'service_' . time() . '_' . uniqid() . '.' . $ext;
                            $filepath = $upload_dir . $filename;
                            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                                $service_image = 'uploads/services/' . $filename;
                            } else {
                                $message = 'Failed to upload service image file.';
                                $message_type = 'error';
                            }
                        }
                    }
                }
            } elseif ($action === 'update' && empty($service_image) && !empty($_POST['existing_image'])) {
                $service_image = trim($_POST['existing_image']);
            }
        }

        if (empty($message)) {
            // Process rich service metadata & fields
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
                    $stmt = $conn->prepare("INSERT INTO services (service_name, slug, short_description, full_description, icon, image, price, status, seo_title, meta_description, meta_keywords, features, benefits, process_steps, faqs) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $service_name, 
                        $slug, 
                        $_POST['short_description'] ?? '', 
                        $_POST['full_description'] ?? '', 
                        $_POST['icon'] ?: null, 
                        $service_image ?: null, 
                        $_POST['price'] ?: null, 
                        $_POST['status'] ?? 'active',
                        $seo_title,
                        $meta_description,
                        $meta_keywords,
                        $features_json,
                        $benefits_json,
                        $process_steps_json,
                        $faqs_json
                    ]);
                    logAudit($conn, 'CREATE_SERVICE', "Created service: $service_name", $_SESSION['admin_id']);
                    notifySecurity('CREATE_SERVICE', "Created service: $service_name", $_SESSION['admin_id']);
                    $_SESSION['flash_message'] = 'Service created successfully!';
                    $_SESSION['flash_type'] = 'success';
                    break;
                case 'update':
                    $stmt = $conn->prepare("UPDATE services SET service_name = ?, slug = ?, short_description = ?, full_description = ?, icon = ?, image = ?, price = ?, status = ?, seo_title = ?, meta_description = ?, meta_keywords = ?, features = ?, benefits = ?, process_steps = ?, faqs = ? WHERE id = ?");
                    $stmt->execute([
                        $service_name,
                        $slug,
                        $_POST['short_description'] ?? '',
                        $_POST['full_description'] ?? '',
                        $_POST['icon'] ?? null,
                        $service_image ?: null,
                        $_POST['price'] ?? null,
                        $_POST['status'] ?? 'active',
                        $seo_title,
                        $meta_description,
                        $meta_keywords,
                        $features_json,
                        $benefits_json,
                        $process_steps_json,
                        $faqs_json,
                        $id
                    ]);
                    logAudit($conn, 'UPDATE_SERVICE', "Updated service ID: $id", $_SESSION['admin_id']);
                    notifySecurity('UPDATE_SERVICE', "Updated service: $service_name (ID: $id)", $_SESSION['admin_id']);
                    $_SESSION['flash_message'] = 'Service updated successfully!';
                    $_SESSION['flash_type'] = 'success';
                    break;
                case 'delete':
                    $stmt = $conn->prepare("DELETE FROM services WHERE id = ?");
                    $stmt->execute([$id]);
                    logAudit($conn, 'DELETE_SERVICE', "Deleted service ID: $id", $_SESSION['admin_id']);
                    notifySecurity('DELETE_SERVICE', "Service deleted (ID: $id)", $_SESSION['admin_id']);
                    $_SESSION['flash_message'] = 'Service deleted successfully!';
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

$services = $conn->query("SELECT * FROM services ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
$csrf_token = generateCSRFToken();

$available_icons = [
    'fa-globe' => 'Globe', 'fa-cloud' => 'Cloud', 'fa-server' => 'Server', 'fa-shield-alt' => 'Shield',
    'fa-lock' => 'Lock', 'fa-bolt' => 'Bolt', 'fa-rocket' => 'Rocket', 'fa-chart-line' => 'Chart',
    'fa-database' => 'Database', 'fa-envelope' => 'Email', 'fa-mobile-alt' => 'Mobile', 'fa-laptop' => 'Laptop',
    'fa-cog' => 'Settings', 'fa-code' => 'Code', 'fa-paint-brush' => 'Design', 'fa-search' => 'SEO'
];

require_once __DIR__ . '/../app/Views/admin/services-pro.php';
