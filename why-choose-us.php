<?php
require_once __DIR__ . '/app/config.php';
$pageTitle = 'Why Choose Us';
$pageDesc = 'Discover why businesses choose ' . $site_name . ' for their digital marketing needs.';

$why_items = [];
try {
    $stmt = $conn->query("SELECT * FROM why_choose_us WHERE status = 'active' ORDER BY COALESCE(sort_order, 0) ASC, created_at ASC");
    $why_items = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
} catch (PDOException $e) {
    error_log("Error fetching why_choose_us: " . $e->getMessage());
    $why_items = [];
}

$faqs = [];
try {
    $stmt = $conn->query("SELECT * FROM faqs WHERE status = 'active' ORDER BY COALESCE(sort_order, 0) ASC, created_at ASC");
    $faqs = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
} catch (PDOException $e) {
    error_log("Error fetching faqs: " . $e->getMessage());
    $faqs = [];
}

// Fetch database counts for stats
$db_projects = 0;
$db_leads = 0;
$db_services = 0;
$satisfaction_percentage = 100;

try {
    $db_projects = (int)$conn->query("SELECT COUNT(*) FROM projects")->fetchColumn();
    $db_leads = (int)$conn->query("SELECT COUNT(*) FROM leads")->fetchColumn();
    $db_services = (int)$conn->query("SELECT COUNT(*) FROM services WHERE status = 'active'")->fetchColumn();

    // Calculate client satisfaction from reviews table
    $db_avg_rating = $conn->query("SELECT AVG(star_rating) FROM reviews WHERE status = 'approved' OR status IS NULL")->fetchColumn();
    $satisfaction_percentage = ($db_avg_rating !== null && $db_avg_rating > 0) ? round(($db_avg_rating / 5) * 100) : 100;
} catch (Exception $e) {
    error_log("Error calculating metrics: " . $e->getMessage());
}

$stat_projects = $db_projects;
$stat_clients = $db_leads;
$stat_services = $db_services;
$stat_satisfaction = $satisfaction_percentage . '%';

require_once __DIR__ . '/app/Views/frontend/why-choose-us.php';
