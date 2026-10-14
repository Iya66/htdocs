<?php
/**
 * Admin Why Choose Us Controller
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
        header('Location: why-choose-us.php');
        exit;
    } elseif (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['flash_message'] = 'Invalid security token. Please refresh and try again.';
        $_SESSION['flash_type'] = 'error';
        header('Location: why-choose-us.php');
        exit;
    } else {
        $action = $_POST['action'];
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

        if ($action === 'create' || $action === 'update') {
            $question = trim($_POST['question'] ?? '');
            $answer = trim($_POST['answer'] ?? '');
            $icon = trim($_POST['icon'] ?? 'fa-star');
            $status = in_array($_POST['status'] ?? '', ['active', 'inactive']) ? $_POST['status'] : 'active';
            $sort_order = isset($_POST['sort_order']) ? (int)$_POST['sort_order'] : 0;

            if (empty($question)) {
                $message = 'Title / Feature Heading is required.';
                $message_type = 'error';
            } else {
                $exclude_clause = ($action === 'update' && $id > 0) ? " AND id != ?" : "";
                $check_stmt = $conn->prepare("SELECT id FROM why_choose_us WHERE question = ?" . $exclude_clause . " LIMIT 1");
                if ($action === 'update' && $id > 0) {
                    $check_stmt->execute([$question, $id]);
                } else {
                    $check_stmt->execute([$question]);
                }
                if ($check_stmt->fetch()) {
                    $message = 'An item with this title already exists.';
                    $message_type = 'error';
                }
            }
        }

        if (empty($message)) {
            switch ($action) {
                case 'create':
                    $stmt = $conn->prepare("INSERT INTO why_choose_us (question, answer, icon, status, sort_order) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$question, $answer, $icon, $status, $sort_order]);
                    $new_id = (int)$conn->lastInsertId();
                    logAudit($conn, 'CREATE_WHY_US', "Created Why Choose Us item: {$question} (ID: {$new_id})", $_SESSION['admin_id']);
                    notifySecurity('CREATE_WHY_US', "New Why Choose Us item: {$question}", $_SESSION['admin_id']);
                    $_SESSION['flash_message'] = 'Feature item added successfully!';
                    $_SESSION['flash_type'] = 'success';
                    header('Location: why-choose-us.php');
                    exit;

                case 'update':
                    $stmt = $conn->prepare("UPDATE why_choose_us SET question = ?, answer = ?, icon = ?, status = ?, sort_order = ? WHERE id = ?");
                    $stmt->execute([$question, $answer, $icon, $status, $sort_order, $id]);
                    logAudit($conn, 'UPDATE_WHY_US', "Updated Why Choose Us ID: {$id}", $_SESSION['admin_id']);
                    notifySecurity('UPDATE_WHY_US', "Why Choose Us item updated (ID: {$id})", $_SESSION['admin_id']);
                    $_SESSION['flash_message'] = 'Feature item updated successfully!';
                    $_SESSION['flash_type'] = 'success';
                    header('Location: why-choose-us.php');
                    exit;

                case 'delete':
                    $stmt = $conn->prepare("DELETE FROM why_choose_us WHERE id = ?");
                    $stmt->execute([$id]);
                    logAudit($conn, 'DELETE_WHY_US', "Deleted Why Choose Us ID: {$id}", $_SESSION['admin_id']);
                    notifySecurity('DELETE_WHY_US', "Why Choose Us item deleted (ID: {$id})", $_SESSION['admin_id']);
                    $_SESSION['flash_message'] = 'Feature item deleted successfully!';
                    $_SESSION['flash_type'] = 'success';
                    header('Location: why-choose-us.php');
                    exit;

                case 'toggle':
                    $stmt = $conn->prepare("UPDATE why_choose_us SET status = IF(status='active', 'inactive', 'active') WHERE id = ?");
                    $stmt->execute([$id]);
                    logAudit($conn, 'TOGGLE_WHY_US', "Toggled status for Why Choose Us ID: {$id}", $_SESSION['admin_id']);
                    notifySecurity('TOGGLE_WHY_US', "Why Choose Us item ID {$id} status toggled", $_SESSION['admin_id']);
                    $_SESSION['flash_message'] = 'Status updated!';
                    $_SESSION['flash_type'] = 'success';
                    header('Location: why-choose-us.php');
                    exit;

                case 'seed_defaults':
                    $defaults = [
                        ['icon' => 'fa-trophy', 'q' => 'Proven Track Record', 'a' => '500+ successful digital initiatives with measurable ROI across e-commerce, healthcare, and SaaS.', 'order' => 1],
                        ['icon' => 'fa-users-gear', 'q' => 'Certified Specialists', 'a' => 'Dedicated specialists in performance marketing, technical SEO, and conversion-focused web development.', 'order' => 2],
                        ['icon' => 'fa-chart-line', 'q' => 'Data-Driven Growth', 'a' => 'Every campaign decision is backed by live analytics and deep audience behavior modeling.', 'order' => 3],
                        ['icon' => 'fa-headset', 'q' => 'Dedicated Support', 'a' => 'Direct access to account leads and transparent bi-weekly strategy alignment calls.', 'order' => 4],
                        ['icon' => 'fa-bolt', 'q' => 'Rapid Execution', 'a' => 'Fast turnaround without sacrificing code quality, security, or creative finesse.', 'order' => 5],
                        ['icon' => 'fa-shield-halved', 'q' => 'Transparent Analytics', 'a' => 'Live real-time performance dashboards so you always know your exact cost per acquisition.', 'order' => 6],
                    ];
                    $inserted = 0;
                    $stmt = $conn->prepare("INSERT INTO why_choose_us (question, answer, icon, status, sort_order) VALUES (?, ?, ?, 'active', ?)");
                    foreach ($defaults as $d) {
                        $check = $conn->prepare("SELECT id FROM why_choose_us WHERE question = ? LIMIT 1");
                        $check->execute([$d['q']]);
                        if (!$check->fetch()) {
                            $stmt->execute([$d['q'], $d['a'], $d['icon'], $d['order']]);
                            $inserted++;
                        }
                    }
                    logAudit($conn, 'SEED_WHY_US', "Populated {$inserted} starter Why Choose Us items", $_SESSION['admin_id']);
                    $_SESSION['flash_message'] = "Successfully loaded {$inserted} starter items!";
                    $_SESSION['flash_type'] = 'success';
                    header('Location: why-choose-us.php');
                    exit;
                    
                case 'bulk_delete':
                    $ids = $_POST['item_ids'] ?? [];
                    if (!empty($ids) && is_array($ids)) {
                        $placeholders = implode(',', array_fill(0, count($ids), '?'));
                        $stmt = $conn->prepare("DELETE FROM why_choose_us WHERE id IN ($placeholders)");
                        $stmt->execute($ids);
                        logAudit($conn, 'BULK_DELETE_WHY_US', "Bulk deleted why-choose-us items", $_SESSION['admin_id']);
                        $_SESSION['flash_message'] = "Selected items deleted successfully.";
                        $_SESSION['flash_type'] = "success";
                    }
                    header('Location: why-choose-us.php');
                    exit;

                case 'bulk_update_status':
                    $ids = $_POST['item_ids'] ?? [];
                    $status = in_array($_POST['bulk_status'] ?? '', ['active', 'inactive']) ? $_POST['bulk_status'] : 'active';
                    if (!empty($ids) && is_array($ids)) {
                        $placeholders = implode(',', array_fill(0, count($ids), '?'));
                        $params = array_merge([$status], $ids);
                        $stmt = $conn->prepare("UPDATE why_choose_us SET status = ? WHERE id IN ($placeholders)");
                        $stmt->execute($params);
                        logAudit($conn, 'BULK_UPDATE_WHY_US', "Bulk updated why-choose-us status to $status", $_SESSION['admin_id']);
                        $_SESSION['flash_message'] = "Selected items status updated.";
                        $_SESSION['flash_type'] = "success";
                    }
                    header('Location: why-choose-us.php');
                    exit;
            }
        }
    }
}

$items = $conn->query("SELECT * FROM why_choose_us ORDER BY COALESCE(sort_order, 0) ASC, created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
$total_items = count($items);
$active_items = count(array_filter($items, fn($item) => ($item['status'] ?? '') === 'active'));
$inactive_items = $total_items - $active_items;

$csrf_token = generateCSRFToken();

$available_icons = [
    'fa-trophy' => 'Trophy',
    'fa-shield-alt' => 'Shield',
    'fa-bolt' => 'Bolt',
    'fa-headset' => 'Support',
    'fa-chart-line' => 'Growth Chart',
    'fa-globe' => 'Globe',
    'fa-clock' => 'Clock',
    'fa-lock' => 'Security Lock',
    'fa-rocket' => 'Rocket Speed',
    'fa-star' => 'Star Rating',
    'fa-check-circle' => 'Checkmark',
    'fa-award' => 'Award Badge',
    'fa-sync-alt' => 'Sync / Agile',
    'fa-server' => 'Server Tech',
    'fa-cloud' => 'Cloud',
    'fa-database' => 'Data / DB',
    'fa-tools' => 'Tools & Tech',
    'fa-certificate' => 'Certificate',
    'fa-user-shield' => 'Protection',
    'fa-users-gear' => 'Specialists'
];

require_once __DIR__ . '/../app/Views/admin/why-choose-us.php';
