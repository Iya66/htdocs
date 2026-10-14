<?php

/**
 * Admin Dashboard - Premium SaaS Edition
 */
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../app/config.php';

// Auto check backups
try {
    require_once __DIR__ . '/model/backup/BackupModel.php';
    $bkModel = new BackupModel($conn);
    $bkModel->checkAndTriggerSchedule();
} catch (Throwable $e) {
}

$leads_count = $conn->query("SELECT COUNT(*) FROM leads")->fetchColumn();
$services_count = $conn->query("SELECT COUNT(*) FROM services")->fetchColumn();
$reviews_count = $conn->query("SELECT COUNT(*) FROM reviews")->fetchColumn();
$why_us_count = $conn->query("SELECT COUNT(*) FROM why_choose_us")->fetchColumn();
$recent_leads = $conn->query("SELECT * FROM leads ORDER BY created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

// ─── Lead Status Breakdown ──────────────────────────────────────
$lead_status_counts = [
 'new' => (int)$conn->query("SELECT COUNT(*) FROM leads WHERE status='new'")->fetchColumn(),
 'contacted' => (int)$conn->query("SELECT COUNT(*) FROM leads WHERE status='contacted'")->fetchColumn(),
 'confirmed' => (int)$conn->query("SELECT COUNT(*) FROM leads WHERE status='confirmed'")->fetchColumn(),
 'lose' => (int)$conn->query("SELECT COUNT(*) FROM leads WHERE status='lose'")->fetchColumn(),
];

// ─── Recent Activity ────────────────────────────────────────────
$recent_activity = $conn->query("
 SELECT al.*, a.name as admin_name
 FROM admin_activities al
 LEFT JOIN admin a ON al.user_id = a.id
 ORDER BY al.created_at DESC
 LIMIT 8
")->fetchAll(PDO::FETCH_ASSOC);

// ─── Active Projects ────────────────────────────────────────────
$active_projects = $conn->query("
 SELECT * FROM projects
 WHERE status IN ('pending', 'running')
 ORDER BY created_at DESC
 LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// ─── Today's Activity Count ─────────────────────────────────────
$today_activity = (int)$conn->query("
 SELECT COUNT(*) FROM admin_activities
 WHERE DATE(created_at) = CURDATE()
")->fetchColumn();

// Real site data â€” no fake numbers
$new_leads = (int)$conn->query("SELECT COUNT(*) FROM leads WHERE status = 'new'")->fetchColumn();
$projects_count = (int)$conn->query("SELECT COUNT(*) FROM projects")->fetchColumn();
$uploads_count = (int)$conn->query("SELECT COUNT(*) FROM uploads")->fetchColumn();
$staff_count = (int)$conn->query("SELECT COUNT(*) FROM admin")->fetchColumn();

// Analytics calculations
$last_7_days = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $label = date('D', strtotime("-$i days"));
    $last_7_days[$date] = ['label' => $label, 'count' => 0];
}

try {
    $stmt = $conn->query("SELECT DATE(created_at) as date_val, COUNT(*) as count_val FROM leads WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) GROUP BY DATE(created_at)");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if (isset($last_7_days[$row['date_val']])) {
            $last_7_days[$row['date_val']]['count'] = (int)$row['count_val'];
        }
    }
} catch (Exception $e) {
}

$chart_dates = [];
$chart_counts = [];
foreach ($last_7_days as $day) {
    $chart_dates[] = $day['label'];
    $chart_counts[] = $day['count'];
}

$service_labels = [];
$service_counts = [];
try {
    $stmt = $conn->query("SELECT COALESCE(NULLIF(service, ''), 'General Inquiry') as service_name, COUNT(*) as count_val FROM leads GROUP BY service_name ORDER BY count_val DESC LIMIT 5");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $service_labels[] = $row['service_name'];
        $service_counts[] = (int)$row['count_val'];
    }
} catch (Exception $e) {
}

if (empty($service_labels)) {
    $service_labels = ['No Data'];
    $service_counts = [0];
}

// Use professional theme for dashboard
$page_title = 'Dashboard';
require_once __DIR__ . '/includes/header-pro.php';

// Dashboard content will be included here
include __DIR__ . '/../app/Views/admin/dashboard-pro.php';

require_once __DIR__ . '/includes/footer-pro.php';
