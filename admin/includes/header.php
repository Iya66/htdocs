<?php
/**
 * Professional Admin Header — single source of truth
 * ---------------------------------------------------
 * Replaces the former header.php / header-pro.php pair, which had drifted
 * apart into two near-identical copies. admin/includes/header-pro.php is now
 * a shim that includes this file, so every admin view keeps working
 * unchanged.
 *
 * Inspired by Linear, Notion, and modern SaaS dashboards.
 */

if (!defined('ADMIN_HEADER_LOADED')) {
    define('ADMIN_HEADER_LOADED', true);
}

// ─── Route awareness ───────────────────────────────────────────────────────
// Everything below is derived from the URL so the header never needs the
// calling view to pass anything in. `admin/settings/general` and
// `admin/settings/index` resolve to two path segments.

$script_path = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$parts       = explode('/', trim($script_path, '/'));
$admin_index = array_search('admin', $parts);

// Depth of the *current* page below /admin, used to build relative URLs so
// the same header works from /admin and /admin/settings alike.
$current_depth = ($admin_index !== false) ? count($parts) - $admin_index - 2 : 0;
$base_path     = str_repeat('../', max(0, $current_depth));

// Route key: "settings/general", "leads", "index", "".
if ($admin_index !== false) {
    $relative_path = implode('/', array_slice($parts, $admin_index + 1));
} else {
    $relative_path = basename($script_path);
}
$current_route = preg_replace('/\.php$/', '', $relative_path);

// ─── Site settings ─────────────────────────────────────────────────────────
// app/config.php has already read every row of `settings` into $settings at
// bootstrap. Re-querying it here cost one extra round-trip on every page, so
// the cached array is used directly and only falls back to a query when this
// header is somehow rendered without the app bootstrap (e.g. a static test).

$site_name      = 'CloudASPIRE';
$site_logo      = '';
$theme_primary  = '#0F172A';
$theme_accent   = '#2563EB';

if (!isset($settings) || !is_array($settings)) {
    $settings = [];
    if (isset($conn) && $conn instanceof PDO) {
        try {
            $stmt = $conn->query("SELECT setting_key, setting_value FROM settings");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
        } catch (Throwable $e) {
            // A header must never take the page down over a settings read.
        }
    }
}

$site_name     = !empty($settings['site_name'])     ? $settings['site_name']     : $site_name;
$site_logo     = !empty($settings['site_logo'])     ? $settings['site_logo']     : '';
$theme_primary = !empty($settings['theme_primary']) ? $settings['theme_primary'] : $theme_primary;
$theme_accent  = !empty($settings['theme_accent'])  ? $settings['theme_accent']  : $theme_accent;

// The stored default was a purple that the palette no longer carries; the
// slate primary is the intended value, so treat it as "unset".
if ($theme_primary === '#5b6ef5') {
    $theme_primary = '#0F172A';
}

// ─── Admin identity ────────────────────────────────────────────────────────
require_once __DIR__ . '/../../app/profile_photo_helper.php';

$admin_name = $_SESSION['admin_name'] ?? 'Admin';
$admin_role = $_SESSION['admin_role'] ?? 'admin';
$admin_initial = strtoupper(substr(trim($admin_name), 0, 1)) ?: 'A';

if (!isset($_SESSION['admin_avatar']) && !empty($_SESSION['admin_id']) && isset($conn) && $conn instanceof PDO) {
    try {
        $avStmt = $conn->prepare("SELECT avatar FROM admin WHERE id = ? LIMIT 1");
        $avStmt->execute([$_SESSION['admin_id']]);
        $_SESSION['admin_avatar'] = (string)($avStmt->fetchColumn() ?: '');
    } catch (Throwable $e) {
        $_SESSION['admin_avatar'] = '';
    }
}
$admin_avatar_url = getAdminAvatarUrl($_SESSION['admin_avatar'] ?? '', $base_path);

// ─── Navigation ────────────────────────────────────────────────────────────
// One shared list for every admin page. The old "-pro" header silently
// omitted Map Controller and Contact Us, so those pages were only reachable
// from the sidebar on some screens and not others.

$admin_nav = [
    'Overview' => [
        ['url' => 'dashboard',            'icon' => 'fa-chart-line',         'label' => 'Dashboard'],
        ['url' => 'leads',                'icon' => 'fa-users',              'label' => 'Leads'],
        ['url' => 'services',             'icon' => 'fa-cube',               'label' => 'Services'],
        ['url' => 'reviews',              'icon' => 'fa-star',               'label' => 'Reviews'],
        ['url' => 'projects',             'icon' => 'fa-folder',             'label' => 'Projects'],
        ['url' => 'testimonials',         'icon' => 'fa-quote-left',         'label' => 'Testimonials'],
        ['url' => 'files',                'icon' => 'fa-images',             'label' => 'Media & Files'],
        ['url' => 'staff',                'icon' => 'fa-user-shield',        'label' => 'Staff'],
        ['url' => 'activity',             'icon' => 'fa-clock',              'label' => 'Activity'],
        ['url' => 'mail_logs',            'icon' => 'fa-envelope-open-text', 'label' => 'Email History'],
        ['url' => 'reports',              'icon' => 'fa-chart-bar',          'label' => 'Reports'],
        ['url' => 'backup',               'icon' => 'fa-database',           'label' => 'Backup & Restore'],
    ],
    'Content' => [
        ['url' => 'blogs',                'icon' => 'fa-newspaper',          'label' => 'Blog Posts'],
        ['url' => 'map',                  'icon' => 'fa-map-location-dot',   'label' => 'Map Controller'],
        ['url' => 'contact-us',           'icon' => 'fa-envelope',           'label' => 'Contact Us'],
        ['url' => 'pages',                'icon' => 'fa-file-alt',           'label' => 'Pages & SEO'],
        ['url' => 'why-choose-us',        'icon' => 'fa-award',              'label' => 'Why Choose Us'],
        ['url' => 'faqs',                 'icon' => 'fa-question-circle',    'label' => 'FAQs'],
        ['url' => 'terms-and-conditions', 'icon' => 'fa-file-contract',      'label' => 'Terms'],
        ['url' => 'privacy-policy',       'icon' => 'fa-shield-alt',         'label' => 'Privacy'],
        ['url' => 'refund-policy',        'icon' => 'fa-undo',               'label' => 'Refund Policy'],
    ],
    'Settings' => [
        ['url' => 'settings/general',      'icon' => 'fa-sliders-h',  'label' => 'Site Settings'],
        ['url' => 'settings/homepage',     'icon' => 'fa-home',       'label' => 'Homepage & Content'],
        ['url' => 'settings/mailsettings', 'icon' => 'fa-envelope',   'label' => 'Mail Settings'],
        ['url' => 'settings/integration',  'icon' => 'fa-plug',       'label' => 'SEO & Integrations'],
        ['url' => 'settings/module',       'icon' => 'fa-cubes',      'label' => 'Modules'],
        ['url' => 'settings/index',        'icon' => 'fa-user-cog',   'label' => 'Admin Profile'],
    ],
];

/**
 * True when the current route matches a nav entry. Also keeps the dashboard
 * item lit for /admin and /admin/index.php, which are the same screen.
 */
if (!function_exists('adminNavIsActive')) {
    function adminNavIsActive(string $url): bool
    {
        $route = $GLOBALS['current_route'] ?? '';
        $url   = preg_replace('/\.php$/', '', $url);

        if ($url === 'dashboard') {
            return in_array($route, ['dashboard', 'index', ''], true);
        }

        return $route === $url;
    }
}

/**
 * Human title for the current route.
 *
 * The old header printed `$page_title ?? 'Dashboard'`, but most views assign
 * $page_title *after* including the header, so nearly every page was titled
 * "Dashboard". Views may still override by setting $page_title before the
 * include; otherwise it is derived from the route here.
 */
if (!function_exists('adminPageTitle')) {
    function adminPageTitle(): string
    {
        // Explicit override always wins.
        if (isset($GLOBALS['page_title']) && is_string($GLOBALS['page_title']) && $GLOBALS['page_title'] !== '') {
            return $GLOBALS['page_title'];
        }

        $route = $GLOBALS['current_route'] ?? '';
        $key   = $route === '' ? 'dashboard' : $route;

        static $titles = null;
        if ($titles === null) {
            $titles = [
                'dashboard'            => 'Dashboard',
                'leads'                => 'Leads Management',
                'services'             => 'Services',
                'reviews'              => 'Reviews',
                'projects'             => 'Projects',
                'testimonials'         => 'Testimonials',
                'files'                => 'Media & Files Manager',
                'staff'                => 'Staff Management',
                'activity'             => 'Activity Log',
                'mail_logs'            => 'Email History',
                'reports'              => 'Reports',
                'backup'               => 'Backup & Restore',
                'accounts'             => 'My Account',
                'blogs'                => 'Blog Posts',
                'map'                  => 'Map Controller',
                'contact-us'           => 'Contact Us',
                'pages'                => 'Pages & SEO',
                'why-choose-us'        => 'Why Choose Us',
                'faqs'                 => 'FAQs',
                'terms-and-conditions' => 'Terms & Conditions',
                'privacy-policy'       => 'Privacy Policy',
                'refund-policy'        => 'Refund Policy',
                'settings/general'      => 'Site Settings',
                'settings/mailsettings' => 'Mail Settings',
                'settings/integration'  => 'SEO & Integrations',
                'settings/module'       => 'Modules',
                'settings/index'        => 'Admin Profile',
            ];
        }

        return $titles[$key] ?? 'Admin Panel';
    }
}

$page_title = adminPageTitle();

// Safe colour values for the inline :root block. A malformed setting must not
// be able to break out of the <style> element.
$safe_primary = preg_match('/^#[0-9a-fA-F]{3,8}$/', (string)$theme_primary) ? $theme_primary : '#0F172A';
$safe_accent  = preg_match('/^#[0-9a-fA-F]{3,8}$/', (string)$theme_accent)  ? $theme_accent  : '#2563EB';
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
 <meta charset="UTF-8">
 <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
 <title><?= htmlspecialchars($page_title) ?> — Admin Panel</title>
 <meta name="robots" content="noindex, nofollow">

 <!-- Preconnect -->
 <link rel="preconnect" href="https://fonts.googleapis.com">
 <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
 <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>

 <!-- Inter Font -->
 <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

 <!-- Font Awesome -->
 <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

 <!-- Professional Theme (single stylesheet) -->
 <link rel="stylesheet" href="<?= $base_path ?>css/theme-professional.css?v=4.0">

 <!-- Custom Theme Colours -->
 <style>
 :root {
 --primary: <?= htmlspecialchars($safe_primary, ENT_QUOTES, 'UTF-8') ?>;
 --accent: <?= htmlspecialchars($safe_accent, ENT_QUOTES, 'UTF-8') ?>;
 }
 </style>

 <!--
   Apply the saved theme before first paint. The old header relied on a
   script at the bottom of the page, which flashed the light theme on every
   load and never ran at all on views without the footer.
 -->
 <script>
  (function () {
   try {
    var saved = localStorage.getItem('theme');
    if (saved !== 'light' && saved !== 'dark') {
     saved = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }
    document.documentElement.setAttribute('data-theme', saved);
   } catch (e) {
    /* private mode / storage disabled — keep the light default */
   }
  })();
 </script>
</head>

<body>
 <!-- Sidebar Overlay (Mobile) -->
 <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()" aria-hidden="true"></div>

 <!-- SIDEBAR -->
 <aside class="sidebar" id="sidebar">
 <div class="sidebar-header">
 <a href="<?= $base_path ?>dashboard" class="sidebar-logo">
 <?php if ($site_logo): ?>
 <img src="<?= $base_path . '../' . htmlspecialchars($site_logo, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($site_name, ENT_QUOTES, 'UTF-8') ?>">
 <?php else: ?>
 <i class="fas fa-layer-group" style="color: var(--primary)"></i>
 <?php endif; ?>
 <span><?= htmlspecialchars($site_name, ENT_QUOTES, 'UTF-8') ?></span>
 </a>
 <button type="button" class="icon-btn sidebar-close" onclick="toggleSidebar()" aria-label="Close menu">
 <i class="fas fa-times"></i>
 </button>
 </div>

 <nav class="sidebar-nav" aria-label="Admin sections">
 <?php foreach ($admin_nav as $section_label => $section_items): ?>
 <div class="nav-section">
 <div class="nav-label"><?= htmlspecialchars($section_label, ENT_QUOTES, 'UTF-8') ?></div>
 <?php foreach ($section_items as $item): ?>
 <div class="nav-item">
 <a href="<?= $base_path . $item['url'] ?>"
    class="nav-link <?= adminNavIsActive($item['url']) ? 'active' : '' ?>"
    <?= adminNavIsActive($item['url']) ? 'aria-current="page"' : '' ?>>
 <i class="fas <?= htmlspecialchars($item['icon'], ENT_QUOTES, 'UTF-8') ?>"></i>
 <span><?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?></span>
 </a>
 </div>
 <?php endforeach; ?>
 </div>
 <?php endforeach; ?>
 </nav>

 <div class="sidebar-footer">
 <a href="<?= $base_path ?>logout" class="nav-link nav-link-logout">
 <i class="fas fa-sign-out-alt"></i>
 <span>Logout</span>
 </a>
 </div>
 </aside>

 <!-- MAIN CONTENT -->
 <div class="main-content">
 <header class="topbar">
 <div class="topbar-left">
 <button type="button" class="icon-btn topbar-menu" onclick="toggleSidebar()" aria-label="Toggle menu" id="menuToggle" aria-expanded="false" aria-controls="sidebar">
 <i class="fas fa-bars"></i>
 </button>

 <nav class="breadcrumb" aria-label="Breadcrumb">
 <a href="<?= $base_path ?>dashboard">Home</a>
 <i class="fas fa-chevron-right breadcrumb-sep" aria-hidden="true"></i>
 <span class="breadcrumb-current"><?= htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8') ?></span>
 </nav>
 </div>

 <div class="topbar-right">
 <div class="search-box" id="searchBox">
 <i class="fas fa-search" aria-hidden="true"></i>
 <input type="text" placeholder="Search pages & leads…" id="searchInput" aria-label="Search pages and leads" autocomplete="off" spellcheck="false">
 <kbd class="search-kbd">Ctrl K</kbd>
 <div class="dropdown-menu search-results" id="searchResults" role="listbox" aria-label="Search results"></div>
 </div>

 <button type="button" class="icon-btn topbar-search-toggle" onclick="toggleMobileSearch()" aria-label="Search">
 <i class="fas fa-search"></i>
 </button>

 <button type="button" class="icon-btn" onclick="toggleTheme()" aria-label="Toggle theme" id="themeToggle">
 <i class="fas fa-moon theme-icon-moon" aria-hidden="true"></i>
 <i class="fas fa-sun theme-icon-sun" aria-hidden="true"></i>
 </button>

 <div class="dropdown" id="notifDropdown">
 <button type="button" class="icon-btn" onclick="toggleNotifications(event)" aria-label="Notifications" id="notifBtn" aria-expanded="false" aria-haspopup="true">
 <i class="fas fa-bell" aria-hidden="true"></i>
 <span class="badge badge-notif" id="notifCount" hidden>0</span>
 </button>
 <div class="dropdown-menu notif-menu" id="notifMenu">
 <div class="notif-head">
 <span class="notif-title">Notifications</span>
 <button type="button" class="notif-mark-all" onclick="markAllNotificationsRead()">Mark all read</button>
 </div>
 <div class="notif-list" id="notifList">
 <div class="notif-empty">Loading…</div>
 </div>
 </div>
 </div>

 <a href="<?= $base_path ?>accounts.php" class="profile-btn" title="View & Edit Profile (<?= htmlspecialchars($admin_name, ENT_QUOTES, 'UTF-8') ?>)">
 <span class="profile-avatar" id="headerProfileAvatar" aria-hidden="true">
<?php if (!empty($admin_avatar_url)): ?>
 <img src="<?= htmlspecialchars($admin_avatar_url, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($admin_name, ENT_QUOTES, 'UTF-8') ?>" class="profile-avatar-img" onerror="this.onerror=null;this.parentElement.innerHTML='<span class=\'profile-avatar-initial\'><?= htmlspecialchars($admin_initial, ENT_QUOTES, 'UTF-8') ?></span>';">
<?php else: ?>
 <span class="profile-avatar-initial"><?= htmlspecialchars($admin_initial, ENT_QUOTES, 'UTF-8') ?></span>
<?php endif; ?>
 </span>
 <span class="profile-info">
 <span class="profile-name"><?= htmlspecialchars($admin_name, ENT_QUOTES, 'UTF-8') ?></span>
 <span class="profile-role"><?= htmlspecialchars(ucfirst((string)$admin_role), ENT_QUOTES, 'UTF-8') ?></span>
 </span>
 </a>
 </div>
 </header>

 <main class="page-content">
