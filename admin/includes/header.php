<?php
/**
 * Professional Admin Header
 * Inspired by Linear, Notion, modern SaaS dashboards
 */

$current_page = basename($_SERVER['PHP_SELF']);
$script_path = $_SERVER['SCRIPT_NAME'];
$parts = explode('/', trim($script_path, '/'));
$admin_index = array_search('admin', $parts);
$current_depth = count($parts) - $admin_index - 2;
$base_path = str_repeat('../', max(0, $current_depth));

// Fetch site settings
$site_name = 'CloudASPIRE';
$site_logo = '';
$theme_primary = '#5b6ef5';
$theme_accent = '#7c5cfc';
$font_family = 'Inter';

if (isset($conn)) {
 $stmt = $conn->prepare("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('site_name', 'site_logo', 'theme_primary', 'theme_accent', 'theme_font')");
 $stmt->execute();
 $settings = [];
 while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
 $settings[$row['setting_key']] = $row['setting_value'];
 }
 $site_name = $settings['site_name'] ?? 'CloudASPIRE';
 $site_logo = $settings['site_logo'] ?? '';
 $theme_primary = $settings['theme_primary'] ?? '#5b6ef5';
 $theme_accent = $settings['theme_accent'] ?? '#7c5cfc';
 $font_family = $settings['theme_font'] ?? 'Inter';
}

// Get admin info
$admin_name = $_SESSION['admin_name'] ?? 'Admin';
$admin_role = $_SESSION['admin_role'] ?? 'admin';

// Navigation structure
$nav_items = [
	['url' => 'dashboard', 'icon' => 'fa-chart-line', 'label' => 'Dashboard'],
	['url' => 'leads', 'icon' => 'fa-users', 'label' => 'Leads'],
	['url' => 'services', 'icon' => 'fa-cube', 'label' => 'Services'],
	['url' => 'reviews', 'icon' => 'fa-star', 'label' => 'Reviews'],
	['url' => 'projects', 'icon' => 'fa-folder', 'label' => 'Projects'],
	['url' => 'testimonials', 'icon' => 'fa-quote-left', 'label' => 'Testimonials'],
	['url' => 'files', 'icon' => 'fa-images', 'label' => 'Media & Files'],
	['url' => 'staff', 'icon' => 'fa-user-shield', 'label' => 'Staff'],
	['url' => 'activity', 'icon' => 'fa-clock', 'label' => 'Activity'],
	['url' => 'mail_logs', 'icon' => 'fa-envelope-open-text', 'label' => 'Email History'],
	['url' => 'reports', 'icon' => 'fa-chart-bar', 'label' => 'Reports'],
];

$content_items = [
	['url' => 'blogs', 'icon' => 'fa-newspaper', 'label' => 'Blog Posts'],
	['url' => 'map', 'icon' => 'fa-map-location-dot', 'label' => 'Map Controller'],
	['url' => 'contact-us', 'icon' => 'fa-envelope', 'label' => 'Contact Us'],
	['url' => 'pages', 'icon' => 'fa-file-alt', 'label' => 'Pages & SEO'],
	['url' => 'why-choose-us', 'icon' => 'fa-award', 'label' => 'Why Choose Us'],
	['url' => 'faqs', 'icon' => 'fa-question-circle', 'label' => 'FAQs'],
	['url' => 'terms-and-conditions', 'icon' => 'fa-file-contract', 'label' => 'Terms'],
	['url' => 'privacy-policy', 'icon' => 'fa-shield-alt', 'label' => 'Privacy'],
	['url' => 'refund-policy', 'icon' => 'fa-undo', 'label' => 'Refund Policy'],
];

$settings_items = [
	['url' => 'settings/general', 'icon' => 'fa-sliders-h', 'label' => 'Site Settings'],
	['url' => 'settings/mailsettings', 'icon' => 'fa-envelope', 'label' => 'Mail Settings'],
	['url' => 'settings/integration', 'icon' => 'fa-plug', 'label' => 'SEO & Integrations'],
	['url' => 'settings/module', 'icon' => 'fa-cubes', 'label' => 'Modules'],
	['url' => 'settings/index', 'icon' => 'fa-user-cog', 'label' => 'Admin Profile'],
];

if (!function_exists('isNavActive')) {
    function isNavActive($url) {
        $script_path = $_SERVER['SCRIPT_NAME'] ?? '';
        $parts = explode('/', trim($script_path, '/'));
        $admin_index = array_search('admin', $parts);
        if ($admin_index === false) {
            return false;
        }
        $relative_path = implode('/', array_slice($parts, $admin_index + 1));
        $relative_clean = preg_replace('/\.php$/', '', $relative_path);
        $url_clean = preg_replace('/\.php$/', '', $url);
        return $relative_clean === $url_clean || ($url_clean === 'dashboard' && ($relative_clean === 'dashboard' || $relative_clean === 'index' || $relative_clean === ''));
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
 <meta charset="UTF-8">
 <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
 <title>Admin Panel - <?= htmlspecialchars($site_name) ?></title>
 <meta name="robots" content="noindex, nofollow">

 <!-- Preconnect -->
 <link rel="preconnect" href="https://fonts.googleapis.com">
 <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
 <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>

 <!-- Inter Font -->
 <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

 <!-- Font Awesome -->
 <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

 <!-- Professional Theme -->
 <link rel="stylesheet" href="<?= $base_path ?>css/theme-professional.css?v=3.0">
 <link rel="stylesheet" href="<?= $base_path ?>css/theme-saas.css?v=1.0">

 <!-- Custom Theme Colors -->
 <style>
 :root {
 --primary: <?= ($theme_primary === '#5b6ef5' || empty($theme_primary)) ? '#0F172A' : htmlspecialchars($theme_primary) ?>;
 --accent: <?= htmlspecialchars($theme_accent ?: '#2563EB') ?>;
 --font-sans: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', system-ui, sans-serif;
 }
 </style>

 <!-- Critical CSS -->
 <style>
 body {
 opacity: 0;
 transition: opacity 0.2s ease-in;
 }
 body.loaded {
 opacity: 1;
 }
 </style>
</head>

<body class="loaded">
 <!-- Sidebar Overlay (Mobile) -->
 <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()" aria-hidden="true"></div>

 <!-- SIDEBAR -->
 <aside class="sidebar" id="sidebar">
 <div class="sidebar-header">
 <a href="<?= $base_path ?>dashboard" class="sidebar-logo">
 <?php if ($site_logo): ?>
 <img src="<?= $base_path . '../' . htmlspecialchars($site_logo) ?>" alt="<?= htmlspecialchars($site_name) ?>">
 <?php else: ?>
 <i class="fas fa-layer-group" style="color: var(--primary)"></i>
 <?php endif; ?>
 <span><?= htmlspecialchars($site_name) ?></span>
 </a>
 <button class="icon-btn md:hidden" onclick="toggleSidebar()" aria-label="Close menu" style="margin-left: auto;">
 <i class="fas fa-times"></i>
 </button>
 </div>

 <nav class="sidebar-nav">
 <div class="nav-section">
 <div class="nav-label">Overview</div>
 <?php foreach ($nav_items as $item): ?>
 <div class="nav-item">
 <a href="<?= $base_path . $item['url'] ?>" class="nav-link <?= isNavActive($item['url']) ? 'active' : '' ?>">
 <i class="fas <?= $item['icon'] ?>"></i>
 <span><?= $item['label'] ?></span>
 </a>
 </div>
 <?php endforeach; ?>
 </div>

 <div class="nav-section">
 <div class="nav-label">Content</div>
 <?php foreach ($content_items as $item): ?>
 <div class="nav-item">
 <a href="<?= $base_path . $item['url'] ?>" class="nav-link <?= isNavActive($item['url']) ? 'active' : '' ?>">
 <i class="fas <?= $item['icon'] ?>"></i>
 <span><?= $item['label'] ?></span>
 </a>
 </div>
 <?php endforeach; ?>
 </div>

 <div class="nav-section">
 <div class="nav-label">Settings</div>
 <?php foreach ($settings_items as $item): ?>
 <div class="nav-item">
 <a href="<?= $base_path . $item['url'] ?>" class="nav-link <?= isNavActive($item['url']) ? 'active' : '' ?>">
 <i class="fas <?= $item['icon'] ?>"></i>
 <span><?= $item['label'] ?></span>
 </a>
 </div>
 <?php endforeach; ?>
 </div>
 </nav>

 <div class="sidebar-footer">
 <a href="<?= $base_path ?>logout" class="nav-link" style="color: var(--danger);">
 <i class="fas fa-sign-out-alt"></i>
 <span>Logout</span>
 </a>
 </div>
 </aside>

 <!-- MAIN CONTENT -->
 <div class="main-content">
 <header class="topbar">
 <div class="topbar-left">
 <button class="icon-btn md:hidden" onclick="toggleSidebar()" aria-label="Toggle menu" id="menuToggle">
 <i class="fas fa-bars"></i>
 </button>

 <nav class="breadcrumb">
 <a href="<?= $base_path ?>dashboard.php">Home</a>
 <i class="fas fa-chevron-right" style="font-size: 8px; opacity: 0.5;"></i>
 <span style="color: var(--text-primary);"><?= $page_title ?? 'Dashboard' ?></span>
 </nav>
 </div>

 <div class="topbar-right">
					<div class="search-box">
						<i class="fas fa-search" aria-hidden="true"></i>
						<input type="text" placeholder="Search..." id="searchInput" aria-label="Search">
						<kbd class="search-kbd">Ctrl K</kbd>
					</div>

 <button class="icon-btn" onclick="toggleTheme()" aria-label="Toggle theme" id="themeToggle">
 <i class="fas fa-moon theme-icon-moon"></i>
 <i class="fas fa-sun theme-icon-sun" style="display: none;"></i>
 </button>

 <button class="icon-btn" onclick="toggleNotifications()" aria-label="Notifications" id="notifBtn">
 <i class="fas fa-bell"></i>
 <span class="badge" id="notifCount">0</span>
 </button>

 <div class="profile-btn" onclick="window.location='<?= $base_path ?>accounts.php'" role="button" tabindex="0">
 <div class="profile-avatar">
 <?= strtoupper(substr($admin_name, 0, 1)) ?>
 </div>
 <div class="profile-info">
 <div class="profile-name"><?= htmlspecialchars($admin_name) ?></div>
 <div class="profile-role"><?= ucfirst(htmlspecialchars($admin_role)) ?></div>
 </div>
 </div>
 </div>
 </header>

 <main class="page-content">
