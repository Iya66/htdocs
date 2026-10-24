<?php
/**
 * Admin Homepage & Dynamic Content Management Controller
 * Allows full admin control over Homepage Hero, About Us, CTA Banner, Innovative Strategies, and Client Showcase.
 */
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../../app/config.php';
require_once __DIR__ . '/../../app/email_helper.php';
require_once __DIR__ . '/../../app/security_notifier.php';

// Admin-only access
$stmt = $conn->prepare("SELECT role FROM admin WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$cu = $stmt->fetch();
if (!$cu || $cu['role'] !== 'admin') {
    $_SESSION['error'] = 'Access denied. Admin privileges required.';
    header('Location: ../dashboard.php');
    exit;
}

$csrf_token = generateCSRFToken();

function getSettingVal(PDO $conn, string $key, $default = '') {
    global $settings;
    return $settings[$key] ?? $default;
}

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $_SESSION['flash_msg'] = 'Invalid security token. Please refresh.';
        $_SESSION['flash_type'] = 'error';
        header('Location: homepage.php');
        exit;
    }

    $action = $_POST['action'] ?? '';

    // 1. SAVE ABOUT SECTION
    if ($action === 'save_about') {
        $keys = [
            'about_title', 'about_highlight', 'about_p1', 'about_p2', 'about_p3',
            'about_metric1_title', 'about_metric1_sub', 'about_metric2_title', 'about_metric2_sub',
            'about_exp_title', 'about_exp_sub'
        ];
        $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        foreach ($keys as $k) {
            $stmt->execute([$k, trim($_POST[$k] ?? '')]);
        }
        logAudit($conn, 'UPDATE_HOMEPAGE_ABOUT', 'Updated About Us section content', $_SESSION['admin_id']);
        $_SESSION['flash_msg'] = 'About Us content updated successfully!';
        $_SESSION['flash_type'] = 'success';
        header('Location: homepage.php#about');
        exit;
    }

    // 2. SAVE CTA BANNER
    if ($action === 'save_cta') {
        $keys = ['cta_title', 'cta_subtitle', 'cta_btn_text'];
        $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        foreach ($keys as $k) {
            $stmt->execute([$k, trim($_POST[$k] ?? '')]);
        }
        logAudit($conn, 'UPDATE_CTA_BANNER', 'Updated CTA Banner content', $_SESSION['admin_id']);
        $_SESSION['flash_msg'] = 'CTA Banner content updated successfully!';
        $_SESSION['flash_type'] = 'success';
        header('Location: homepage.php#cta');
        exit;
    }

    // 3. SAVE INNOVATIVE STRATEGIES
    if ($action === 'save_innovative') {
        $keys = ['innovative_title', 'innovative_highlight', 'innovative_desc', 'innovative_b1', 'innovative_b2', 'innovative_b3', 'innovative_footer'];
        $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        foreach ($keys as $k) {
            $stmt->execute([$k, trim($_POST[$k] ?? '')]);
        }
        logAudit($conn, 'UPDATE_INNOVATIVE_STRATEGIES', 'Updated Innovative Strategies section', $_SESSION['admin_id']);
        $_SESSION['flash_msg'] = 'Innovative Strategies section updated successfully!';
        $_SESSION['flash_type'] = 'success';
        header('Location: homepage.php#innovative');
        exit;
    }

    // 4. SAVE CLIENTS SECTION
    if ($action === 'save_clients') {
        $keys = ['clients_heading', 'clients_list'];
        $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        foreach ($keys as $k) {
            $stmt->execute([$k, trim($_POST[$k] ?? '')]);
        }
        logAudit($conn, 'UPDATE_CLIENTS_SHOWCASE', 'Updated Clients Showcase list', $_SESSION['admin_id']);
        $_SESSION['flash_msg'] = 'Clients Showcase updated successfully!';
        $_SESSION['flash_type'] = 'success';
        header('Location: homepage.php#clients');
        exit;
    }
}

// Retrieve values for View
$about_title = getSettingVal($conn, 'about_title', 'Best Digital Marketing Company in Pondicherry');
$about_highlight = getSettingVal($conn, 'about_highlight', 'Best Digital Marketing');
$about_p1 = getSettingVal($conn, 'about_p1', 'Cloud Aspire is an expert digital marketing and custom web architecture firm based in Pondicherry, India. With proven expertise across high-growth industries, our top-rated team empowers businesses with tailored strategies that drive measurable, compounding revenue.');
$about_p2 = getSettingVal($conn, 'about_p2', 'Specializing in SEO services to boost organic visibility, PPC campaigns for Targeted Advertising, Social Media Marketing for audience engagement, Google Ads for High-Converting Leads, and Content Marketing to captivate and convert, we excel in creating growth-centric solutions.');
$about_p3 = getSettingVal($conn, 'about_p3', 'Explore the power of digital excellence with Cloud Aspire — Pondicherry\'s leading digital partner. Let\'s build your brand\'s future together!');
$about_metric1_title = getSettingVal($conn, 'about_metric1_title', 'Drive More Leads');
$about_metric1_sub = getSettingVal($conn, 'about_metric1_sub', 'Qualified High-Intent Inquiries');
$about_metric2_title = getSettingVal($conn, 'about_metric2_title', 'Boost More Sales');
$about_metric2_sub = getSettingVal($conn, 'about_metric2_sub', 'Maximized ROI & Conversions');
$about_exp_title = getSettingVal($conn, 'about_exp_title', '5+ Years of Industry Expertise');
$about_exp_sub = getSettingVal($conn, 'about_exp_sub', 'Pondicherry’s Most Trusted Digital Firm');

$cta_title = getSettingVal($conn, 'cta_title', 'Ready to Get Started? Let\'s Discuss your projects.');
$cta_subtitle = getSettingVal($conn, 'cta_subtitle', 'Pondicherry\'s Trusted Experts!');
$cta_btn_text = getSettingVal($conn, 'cta_btn_text', 'Call Us Now');

$innovative_title = getSettingVal($conn, 'innovative_title', 'Innovative Strategies for Real Results');
$innovative_highlight = getSettingVal($conn, 'innovative_highlight', 'Real Results');
$innovative_desc = getSettingVal($conn, 'innovative_desc', 'At Cloud Aspire, we don\'t just follow the trends — we engineer them. Our team of specialists is dedicated to creating innovative digital marketing strategies that yield real, tangible results.');
$innovative_b1 = getSettingVal($conn, 'innovative_b1', 'High-converting audience remarketing & retargeting');
$innovative_b2 = getSettingVal($conn, 'innovative_b2', 'Search-intent content optimization & semantic clustering');
$innovative_b3 = getSettingVal($conn, 'innovative_b3', 'Omni-channel social media engagement & community building');
$innovative_footer = getSettingVal($conn, 'innovative_footer', 'Our innovative approach is all about delivering Growth and Long-Term Business success.');

$clients_heading = getSettingVal($conn, 'clients_heading', 'Our Client\'s');
$clients_list = getSettingVal($conn, 'clients_list', 'Advanced GroHair & GloSkin, Grand Serenaa Hotel & Resorts, Jeno Maran Builders Pvt. Ltd., Kalaialayam, Aishwarya Grand, RV Boating, Aurowellness Castle, Yas Travels, SVM Sweets, Economic Construction, Appachi Chettinad, Presidency School, MML Groups, Green City Promoters, Aakruthi Interiors, Cloud 96 Technology');

$flash_msg = $_SESSION['flash_msg'] ?? '';
$flash_type = $_SESSION['flash_type'] ?? '';
unset($_SESSION['flash_msg'], $_SESSION['flash_type']);

require_once __DIR__ . '/../../app/Views/admin/settings/homepage.php';
