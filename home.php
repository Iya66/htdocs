<?php
/**
 * Root Homepage Controller
 * Fetches dynamic data from database for homepage
 */
require_once __DIR__ . '/app/config.php';

// Site Settings
$site_name = !empty($settings['site_name']) ? $settings['site_name'] : 'Cloud Aspire';
$pageTitle = $site_name . ' - Best Digital Marketing Company in Pondicherry';

// Fetch active services from DB
$services = [];
try {
    $services = $conn->query("SELECT * FROM services WHERE status = 'active' ORDER BY created_at ASC")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("[Home] Services query failed: " . $e->getMessage());
}

// Fetch active FAQs
$faq_items = [];
try {
    $faq_items = $conn->query("SELECT * FROM faqs WHERE status = 'active' ORDER BY COALESCE(sort_order, 0) ASC, created_at ASC")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $faq_items = [];
}

// Fetch approved reviews
$reviews = [];
try {
    $reviews = $conn->query("SELECT * FROM reviews WHERE status = 'approved' ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $reviews = [];
}

// Fetch active testimonials
$testimonials = [];
try {
    $testimonials = $conn->query("SELECT * FROM testimonials WHERE status = 'active' ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $testimonials = [];
}

// Fetch Why Choose Us items from DB
$why_choose_us_items = [];
try {
    $why_choose_us_items = $conn->query("SELECT * FROM why_choose_us WHERE status = 'active' ORDER BY COALESCE(sort_order, 0) ASC, created_at ASC")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $why_choose_us_items = [];
}

// Fetch Projects from DB
$projects = [];
try {
    $projects = $conn->query("SELECT * FROM projects WHERE status IN ('completed', 'running') ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $projects = [];
}

// Stats from DB
$db_projects = 0;
$db_leads = 0;
$db_services = count($services);
try {
    $db_projects = (int)$conn->query("SELECT COUNT(*) FROM projects")->fetchColumn();
    $db_leads = (int)$conn->query("SELECT COUNT(*) FROM leads")->fetchColumn();
    $db_services = (int)$conn->query("SELECT COUNT(*) FROM services WHERE status = 'active'")->fetchColumn();
} catch (Exception $e) {}

// Calculate rating
$db_avg_rating = null;
try {
    $db_avg_rating = $conn->query("SELECT AVG(star_rating) FROM reviews WHERE status = 'approved' OR status IS NULL")->fetchColumn();
} catch (Exception $e) {}
$satisfaction_percentage = ($db_avg_rating !== null && $db_avg_rating > 0) ? round(($db_avg_rating / 5) * 100) : 100;

$stat_projects = $db_projects ?: 159;
$stat_clients = $db_leads ?: 156;
$stat_satisfaction = $satisfaction_percentage . '%';
$stat_services = $db_services ?: count($services);

require_once __DIR__ . '/app/Views/frontend/home.php';
