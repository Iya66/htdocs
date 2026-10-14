<?php
/**
 * Activity History - Admin Activity Tracking Dashboard
 */
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/security_notifier.php';


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

// Filter handling
$filter_action = $_GET['action'] ?? '';
$filter_user = $_GET['user'] ?? '';
$filter_ip = $_GET['ip'] ?? '';
$date_from = $_GET['from'] ?? '';
$date_to = $_GET['to'] ?? '';

// Pagination
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 25;
$offset = ($page - 1) * $per_page;

// Build query
$where = [];
$params = [];

if ($filter_action) {
    $where[] = "aa.action LIKE ?";
    $params[] = "%$filter_action%";
}

if ($filter_user) {
    $where[] = "a.name LIKE ?";
    $params[] = "%$filter_user%";
}

if ($filter_ip) {
    $where[] = "aa.ip_address LIKE ?";
    $params[] = "%$filter_ip%";
}

if ($date_from) {
    $where[] = "aa.created_at >= ?";
    $params[] = $date_from . ' 00:00:00';
}

if ($date_to) {
    $where[] = "aa.created_at <= ?";
    $params[] = $date_to . ' 23:59:59';
}

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Get total count
$countSql = "SELECT COUNT(*) FROM admin_activities aa LEFT JOIN admin a ON aa.user_id = a.id $whereClause";
$stmt = $conn->prepare($countSql);
$stmt->execute($params);
$total_activities = $stmt->fetchColumn();
$total_pages = ceil($total_activities / $per_page);

// Get activities
$sql = "SELECT aa.*, a.name as user_name, a.email as user_email
        FROM admin_activities aa
        LEFT JOIN admin a ON aa.user_id = a.id
        $whereClause
        ORDER BY aa.created_at DESC
        LIMIT $per_page OFFSET $offset";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get unique actions for filter dropdown
$stmt = $conn->query("SELECT DISTINCT action FROM admin_activities ORDER BY action");
$unique_actions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get unique users for filter dropdown
$stmt = $conn->query("SELECT DISTINCT a.id, a.name FROM admin_activities aa JOIN admin a ON aa.user_id = a.id ORDER BY a.name");
$unique_users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Security stats
$stats = [];
$stmt = $conn->query("SELECT COUNT(*) FROM admin_activities WHERE action LIKE '%LOGIN%' AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)");
$stats['logins_today'] = $stmt->fetchColumn();

$stmt = $conn->query("SELECT COUNT(*) FROM admin_activities WHERE action = 'LOGIN_FAILED' AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)");
$stats['failed_logins_today'] = $stmt->fetchColumn();

$stmt = $conn->query("SELECT COUNT(*) FROM admin_activities WHERE created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)");
$stats['activities_week'] = $stmt->fetchColumn();

$stmt = $conn->query("SELECT COUNT(*) FROM security_alerts WHERE resolved = 0");
$stats['unresolved_alerts'] = $stmt->fetchColumn();

$stmt = $conn->query("SELECT COUNT(*) FROM admin_sessions WHERE last_activity > DATE_SUB(NOW(), INTERVAL 30 MINUTE)");
$stats['active_sessions'] = $stmt->fetchColumn();

$csrf_token = generateCSRFToken();

require_once __DIR__ . '/../app/Views/admin/activity.php';
