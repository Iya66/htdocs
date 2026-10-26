<?php
/**
 * Admin Reports - Activity Reports & Email Notifications
 */
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../app/config.php';

// Role check (Admin or Manager only)
$stmt = $conn->prepare("SELECT role FROM admin WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$cu = $stmt->fetch();
if (!$cu || !in_array($cu['role'], ['admin', 'manager'])) {
    $_SESSION['error'] = 'Access denied. Privileged role required.';
    header('Location: dashboard.php');
    exit;
}

$message = '';
$message_type = '';

// Handle email report request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_report_email'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid request.';
        $message_type = 'error';
    } else {
        $message = 'Email report delivery is disabled by policy to maintain inbox deliverability. You can review all activity logs directly on this page.';
        $message_type = 'info';
    }
}

// Fetch audit logs with admin info
$audit_logs = $conn->query("SELECT al.*, a.name as admin_name, a.username as admin_username, a.email as admin_email
    FROM admin_activities al
    LEFT JOIN admin a ON al.user_id = a.id
    ORDER BY al.created_at DESC LIMIT 200")->fetchAll(PDO::FETCH_ASSOC);

// Get unique actions
$unique_actions = $conn->query("SELECT DISTINCT action FROM admin_activities ORDER BY action")->fetchAll(PDO::FETCH_ASSOC);

// Stats
$today_count = $conn->query("SELECT COUNT(*) FROM admin_activities WHERE DATE(created_at) = CURDATE()")->fetchColumn();
$week_count = $conn->query("SELECT COUNT(*) FROM admin_activities WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetchColumn();
$month_count = $conn->query("SELECT COUNT(*) FROM admin_activities WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetchColumn();
$total_count = $conn->query("SELECT COUNT(*) FROM admin_activities")->fetchColumn();

$csrf_token = generateCSRFToken();

require_once __DIR__ . '/../app/Views/admin/reports.php';
