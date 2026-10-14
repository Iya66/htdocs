<?php
/**
 * Admin Frequently Asked Questions (FAQ) Controller
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
        header('Location: faqs.php');
        exit;
    } elseif (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['flash_message'] = 'Invalid security token. Please refresh and try again.';
        $_SESSION['flash_type'] = 'error';
        header('Location: faqs.php');
        exit;
    } else {
        $action = $_POST['action'];
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

        if ($action === 'create' || $action === 'update') {
            $question = trim($_POST['question'] ?? '');
            $answer = trim($_POST['answer'] ?? '');
            $status = in_array($_POST['status'] ?? '', ['active', 'inactive']) ? $_POST['status'] : 'active';
            $sort_order = isset($_POST['sort_order']) ? (int)$_POST['sort_order'] : 0;

            if (empty($question) || empty($answer)) {
                $message = 'Question and Answer are required fields.';
                $message_type = 'error';
            } else {
                // Check duplicate question in faqs table
                $exclude_clause = ($action === 'update' && $id > 0) ? " AND id != ?" : "";
                $check_stmt = $conn->prepare("SELECT id FROM faqs WHERE question = ?" . $exclude_clause . " LIMIT 1");
                if ($action === 'update' && $id > 0) {
                    $check_stmt->execute([$question, $id]);
                } else {
                    $check_stmt->execute([$question]);
                }
                if ($check_stmt->fetch()) {
                    $message = 'A FAQ with this question already exists.';
                    $message_type = 'error';
                }
            }
        }

        if (empty($message)) {
            switch ($action) {
                case 'create':
                    $stmt = $conn->prepare("INSERT INTO faqs (question, answer, status, sort_order) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$question, $answer, $status, $sort_order]);
                    $new_id = (int)$conn->lastInsertId();
                    logAudit($conn, 'CREATE_FAQ', "Created FAQ: {$question} (ID: {$new_id})", $_SESSION['admin_id']);
                    notifySecurity('CREATE_FAQ', "New FAQ added: {$question}", $_SESSION['admin_id']);
                    $_SESSION['flash_message'] = 'FAQ added successfully!';
                    $_SESSION['flash_type'] = 'success';
                    header('Location: faqs.php');
                    exit;

                case 'update':
                    $stmt = $conn->prepare("UPDATE faqs SET question = ?, answer = ?, status = ?, sort_order = ? WHERE id = ?");
                    $stmt->execute([$question, $answer, $status, $sort_order, $id]);
                    logAudit($conn, 'UPDATE_FAQ', "Updated FAQ ID: {$id}", $_SESSION['admin_id']);
                    notifySecurity('UPDATE_FAQ', "FAQ updated (ID: {$id})", $_SESSION['admin_id']);
                    $_SESSION['flash_message'] = 'FAQ updated successfully!';
                    $_SESSION['flash_type'] = 'success';
                    header('Location: faqs.php');
                    exit;

                case 'delete':
                    $stmt = $conn->prepare("DELETE FROM faqs WHERE id = ?");
                    $stmt->execute([$id]);
                    logAudit($conn, 'DELETE_FAQ', "Deleted FAQ ID: {$id}", $_SESSION['admin_id']);
                    notifySecurity('DELETE_FAQ', "FAQ deleted (ID: {$id})", $_SESSION['admin_id']);
                    $_SESSION['flash_message'] = 'FAQ deleted successfully!';
                    $_SESSION['flash_type'] = 'success';
                    header('Location: faqs.php');
                    exit;

                case 'toggle':
                    $stmt = $conn->prepare("UPDATE faqs SET status = IF(status='active', 'inactive', 'active') WHERE id = ?");
                    $stmt->execute([$id]);
                    logAudit($conn, 'TOGGLE_FAQ', "Toggled status for FAQ ID: {$id}", $_SESSION['admin_id']);
                    notifySecurity('TOGGLE_FAQ', "FAQ ID {$id} status toggled", $_SESSION['admin_id']);
                    $_SESSION['flash_message'] = 'FAQ status updated!';
                    $_SESSION['flash_type'] = 'success';
                    header('Location: faqs.php');
                    exit;

                case 'seed_defaults':
                    $starter_faqs = [
                        ['q' => 'Why partner with Cloud Aspire?', 'a' => 'We treat your business as our own. By pairing data-driven analytics with creative excellence, we design campaigns that deliver measurable bottom-line growth, not vanity metrics.', 'order' => 1],
                        ['q' => 'How do you approach multi-channel digital growth?', 'a' => 'We begin with a deep market audit, identify high-intent conversion bottlenecks, and deploy a synchronized mix of SEO, Meta/Google Ads, content, and high-speed landing pages.', 'order' => 2],
                        ['q' => 'How quickly can we expect to see tangible results?', 'a' => 'Paid performance campaigns generate qualified inquiries within days of launch. SEO ranking momentum typically builds strong compounding returns within 60 to 90 days.', 'order' => 3],
                        ['q' => 'Do you offer custom tailored packages?', 'a' => 'Yes! We craft bespoke growth roadmaps aligned with your immediate goals, target CAC (Customer Acquisition Cost), and market trajectory.', 'order' => 4],
                        ['q' => 'How do you report on campaign performance?', 'a' => 'You receive 24/7 access to live analytics dashboards and concise executive summaries detailing spend, leads, conversions, and ROI.', 'order' => 5],
                    ];
                    $inserted = 0;
                    $stmt = $conn->prepare("INSERT INTO faqs (question, answer, status, sort_order) VALUES (?, ?, 'active', ?)");
                    foreach ($starter_faqs as $sf) {
                        $check = $conn->prepare("SELECT id FROM faqs WHERE question = ? LIMIT 1");
                        $check->execute([$sf['q']]);
                        if (!$check->fetch()) {
                            $stmt->execute([$sf['q'], $sf['a'], $sf['order']]);
                            $inserted++;
                        }
                    }
                    logAudit($conn, 'SEED_FAQS', "Populated {$inserted} starter FAQs", $_SESSION['admin_id']);
                    $_SESSION['flash_message'] = "Successfully loaded {$inserted} starter FAQs!";
                    $_SESSION['flash_type'] = 'success';
                    header('Location: faqs.php');
                    exit;

                case 'bulk_delete':
                    $ids = $_POST['item_ids'] ?? [];
                    if (!empty($ids) && is_array($ids)) {
                        $placeholders = implode(',', array_fill(0, count($ids), '?'));
                        $stmt = $conn->prepare("DELETE FROM faqs WHERE id IN ($placeholders)");
                        $stmt->execute($ids);
                        logAudit($conn, 'BULK_DELETE_FAQS', "Bulk deleted faqs items", $_SESSION['admin_id']);
                        $_SESSION['flash_message'] = "Selected FAQs deleted successfully.";
                        $_SESSION['flash_type'] = "success";
                    }
                    header('Location: faqs.php');
                    exit;

                case 'bulk_update_status':
                    $ids = $_POST['item_ids'] ?? [];
                    $status = in_array($_POST['bulk_status'] ?? '', ['active', 'inactive']) ? $_POST['bulk_status'] : 'active';
                    if (!empty($ids) && is_array($ids)) {
                        $placeholders = implode(',', array_fill(0, count($ids), '?'));
                        $params = array_merge([$status], $ids);
                        $stmt = $conn->prepare("UPDATE faqs SET status = ? WHERE id IN ($placeholders)");
                        $stmt->execute($params);
                        logAudit($conn, 'BULK_UPDATE_FAQS', "Bulk updated faqs status to $status", $_SESSION['admin_id']);
                        $_SESSION['flash_message'] = "Selected FAQs status updated.";
                        $_SESSION['flash_type'] = "success";
                    }
                    header('Location: faqs.php');
                    exit;
            }
        }
    }
}

// Fetch FAQs from faqs table
$faqs = $conn->query("SELECT * FROM faqs ORDER BY COALESCE(sort_order, 0) ASC, created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
$total_faqs = count($faqs);
$active_faqs = count(array_filter($faqs, fn($f) => ($f['status'] ?? '') === 'active'));
$inactive_faqs = $total_faqs - $active_faqs;

$csrf_token = generateCSRFToken();

require_once __DIR__ . '/../app/Views/admin/faqs.php';
