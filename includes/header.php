<?php
/**
 * Site Header - CherriDM Theme
 * 100% Connected to Database Settings & Dynamic Services
 */

// Global settings fallbacks from DB
$site_name = !empty($settings['site_name']) ? $settings['site_name'] : 'Cloud Aspire';
$site_favicon = !empty($settings['site_favicon']) ? $settings['site_favicon'] : '';
$site_logo = !empty($settings['site_logo']) ? $settings['site_logo'] : '';
$contact_email = !empty($settings['contact_email']) ? $settings['contact_email'] : 'cloudaspire2025@gmail.com';
$contact_phone = !empty($settings['contact_phone']) ? $settings['contact_phone'] : '8098830937';
$contact_whatsapp = !empty($settings['contact_whatsapp']) ? $settings['contact_whatsapp'] : '8098830937';
$contact_hours = !empty($settings['contact_hours']) ? $settings['contact_hours'] : 'Mon - Sat: 9:00 AM - 7:00 PM';
$contact_address = !empty($settings['contact_address']) ? $settings['contact_address'] : 'No. 53, Auvai Nagar, Puducherry, 605008';

// Social URLs from DB
$facebook_url = !empty($settings['social_facebook']) ? $settings['social_facebook'] : (!empty($settings['social_facebook_url']) ? $settings['social_facebook_url'] : 'https://facebook.com');
$twitter_url = !empty($settings['social_twitter']) ? $settings['social_twitter'] : (!empty($settings['social_twitter_url']) ? $settings['social_twitter_url'] : 'https://twitter.com');
$instagram_url = !empty($settings['social_instagram']) ? $settings['social_instagram'] : (!empty($settings['social_instagram_url']) ? $settings['social_instagram_url'] : 'https://www.instagram.com/cloudaspire.in/');
$linkedin_url = !empty($settings['social_linkedin']) ? $settings['social_linkedin'] : (!empty($settings['social_linkedin_url']) ? $settings['social_linkedin_url'] : 'https://www.linkedin.com/in/cloud-aspire-3819b43a3');
$maps_url = !empty($settings['maps_url']) ? $settings['maps_url'] : 'https://maps.google.com/?q=No+53+Auvai+Nagar+Puducherry';

// Active services for navbar dropdown (from DB)
$nav_services = [];
if (isset($conn)) {
    try {
        $nav_services = $conn->query("SELECT service_name, slug FROM services WHERE status = 'active' ORDER BY sort_order ASC, id ASC LIMIT 25")->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $nav_services = [];
    }
}

$page_settings = [];
$is_page_active = [];

if (isset($conn)) {
    try {
        $stmtPages = $conn->query("SELECT page_slug, is_active FROM pages");
        if ($stmtPages) {
            while ($pRow = $stmtPages->fetch(PDO::FETCH_ASSOC)) {
                $is_page_active[$pRow['page_slug']] = (int) $pRow['is_active'];
            }
        }

        $current_script = basename($_SERVER['SCRIPT_NAME'] ?? '');
        if (!empty($current_script)) {
            $stmtCurr = $conn->prepare("SELECT * FROM pages WHERE page_slug = ? LIMIT 1");
            $stmtCurr->execute([$current_script]);
            $page_settings = $stmtCurr->fetch(PDO::FETCH_ASSOC) ?: [];
        }
    } catch (Exception $e) {
        error_log("[Header] Page settings fetch error: " . $e->getMessage());
    }
}

if (!function_exists('isActivePage')) {
    function isActivePage($slug) {
        global $is_page_active;
        return isset($is_page_active[$slug]) ? $is_page_active[$slug] === 1 : true;
    }
}

if (!function_exists('displaySiteName')) {
    function displaySiteName() {
        global $site_name;
        echo htmlspecialchars($site_name);
    }
}

// Base URL auto-detect
$protocol = 'http';
if (
    (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') ||
    (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https') ||
    (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && strtolower($_SERVER['HTTP_X_FORWARDED_SSL']) === 'on') ||
    (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
) {
    $protocol = 'https';
}
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$script_dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$subfolder = ($script_dir === '/' || $script_dir === '.') ? '' : rtrim($script_dir, '/');
if (preg_match('#^(.*?)/(admin|api|app)(?:/|$)#', $subfolder, $m)) {
    $subfolder = $m[1];
}
$base_url = $protocol . '://' . $host . $subfolder;

$display_title = $page_settings['seo_title'] ?? ($page_settings['page_title'] ?? (isset($pageTitle) ? $pageTitle : $site_name . ' - Best Digital Marketing Company in Pondicherry'));
$display_desc = $page_settings['meta_description'] ?? (isset($pageDesc) ? $pageDesc : 'Digital marketing agency in Pondicherry specializing in SEO, PPC, Google Ads, and Social Media Marketing.');
?>
<!DOCTYPE html>
<html lang="en" class="no-js">
<head>
    <script>document.documentElement.classList.remove('no-js');</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title><?= htmlspecialchars($display_title) ?></title>
    <meta name="description" content="<?= htmlspecialchars($display_desc) ?>">
    <?php if (!empty($page_settings['meta_keywords'])): ?>
    <meta name="keywords" content="<?= htmlspecialchars($page_settings['meta_keywords']) ?>">
    <?php endif; ?>
    <meta name="robots" content="<?= htmlspecialchars($page_settings['robots_meta'] ?? 'index, follow') ?>">
    <link rel="canonical" href="<?= htmlspecialchars($page_settings['canonical_url'] ?? $base_url . '/') ?>">

    <!-- Open Graph -->
    <meta property="og:title" content="<?= htmlspecialchars($display_title) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($display_desc) ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= htmlspecialchars($base_url . '/') ?>">

    <!-- Favicon -->
    <?php if ($site_favicon): ?>
    <link rel="icon" type="image/png" href="<?= $base_url ?>/<?= htmlspecialchars(ltrim($site_favicon, '/')) ?>">
    <?php else: ?>
    <link rel="icon" type="image/png" href="<?= $base_url ?>/uploads/site_favicon_1783239900.png">
    <?php endif; ?>

    <!-- Fonts & CSS -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?= $base_url ?>/assets/css/cherridm-animations.css?v=2.0">

    <!-- CherriDM Theme Core CSS -->
    <style>
        /* ═══════════════════════════════════════════════════════════════
           1. CSS VARIABLES AND DESIGN TOKENS
           ═══════════════════════════════════════════════════════════════ */
        :root {
            --cdm-header-height: 88px;
            --primary-color: #03228f;
            --primary-dark: #021a6e;
            --secondary-color: #fb9a27;
            --secondary-hover: #e08316;
            --text-dark: #1e293b;
            --text-muted: #64748b;
            --bg-light: #f8faff;
            --bg-ice: #e9eeff;
            --white: #ffffff;
            --border-color: #e2e8f0;
            --shadow-sm: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 10px 25px -3px rgba(3, 34, 143, 0.08);
            --shadow-lg: 0 20px 35px -4px rgba(3, 34, 143, 0.15);
            --font-main: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        /* ═══════════════════════════════════════════════════════════════
           2. GLOBAL RESET AND BASE STYLES
           ═══════════════════════════════════════════════════════════════ */
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html {
            width: 100%;
            max-width: 100%;
            overflow-x: hidden;
            scroll-padding-top: var(--cdm-header-height);
        }

        body {
            width: 100%;
            max-width: 100%;
            overflow-x: hidden;
            position: relative;
            font-family: var(--font-main);
            color: var(--text-dark);
            background-color: #ffffff;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }

        img, iframe, video, svg, canvas {
            max-width: 100% !important;
            height: auto;
            display: inline-block;
        }

        /* ═══════════════════════════════════════════════════════════════
           3. TYPOGRAPHY
           ═══════════════════════════════════════════════════════════════ */
        h1, h2, h3, h4, h5, h6, p, span, a, li {
            overflow-wrap: break-word;
            word-break: break-word;
        }

        a {
            text-decoration: none;
            color: inherit;
            transition: all 0.25s ease;
        }

        .text-secondary {
            color: var(--secondary-color, #fb9a27) !important;
        }

        .text-primary {
            color: var(--primary-color, #03228f) !important;
        }

        /* ═══════════════════════════════════════════════════════════════
           4. SHARED LAYOUT AND CONTAINERS
           ═══════════════════════════════════════════════════════════════ */
        /* Site-wide containment wrapper to 100% prevent horizontal scrolling on mobile */
        .cdm-site-wrapper {
            width: 100%;
            max-width: 100%;
            overflow-x: clip;
            overflow-y: visible;
            position: relative;
        }
        @supports not (overflow-x: clip) {
            .cdm-site-wrapper {
                overflow-x: hidden;
            }
        }

        /* Universal responsive container with consistent balanced margins */
        .container {
            width: 100%;
            max-width: 1240px;
            margin-left: auto;
            margin-right: auto;
            padding-left: 20px;
            padding-right: 20px;
            box-sizing: border-box;
        }

        /* Grid item containment - prevent grid blowout across mobile viewports */
        .cdm-about-grid > div,
        .cdm-partner-grid > div,
        .cdm-counter-grid > div,
        .cdm-counter-grid-comp > div,
        .cdm-services-grid > div,
        .cdm-flip-grid > div,
        .cdm-clients-grid > div,
        .cdm-clients-grid-comp > div,
        .cdm-why-grid > div,
        .cdm-footer-main-grid > div,
        .cdm-footer-top-grid > div {
            min-width: 0;
            max-width: 100%;
        }

        /* ═══════════════════════════════════════════════════════════════
           5. HEADER AND NAVIGATION
           ═══════════════════════════════════════════════════════════════ */
        /* Fixed Header Navbar */
        .cdm-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            width: 100%;
            height: var(--cdm-header-height);
            background: #ffffff;
            z-index: 1000;
            box-shadow: 0 2px 14px rgba(0, 0, 0, 0.05);
            transition: box-shadow 0.3s ease, background-color 0.3s ease;
        }

        .cdm-header.scrolled {
            box-shadow: 0 6px 24px rgba(3, 34, 143, 0.1);
        }

        /* In-flow spacer so page content never hides behind fixed header */
        .cdm-header-spacer {
            display: block;
            width: 100%;
            height: var(--cdm-header-height);
            flex-shrink: 0;
        }

        .cdm-header .container {
            max-width: 1240px;
            width: 100%;
            height: 100%;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-sizing: border-box;
        }

        .cdm-logo {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            flex-shrink: 0;
            text-decoration: none;
            line-height: 1;
        }

        .cdm-logo img {
            width: 180px;
            max-width: 195px;
            height: auto;
            max-height: 56px;
            object-fit: contain;
            display: block;
            transition: transform 0.25s ease;
        }

        .cdm-logo img:hover {
            transform: scale(1.02);
        }

        .cdm-logo span {
            font-weight: 800;
            font-size: 24px;
            color: var(--primary-color);
        }

        .cdm-logo span.accent {
            color: var(--secondary-color);
        }

        /* Desktop Nav */
        .cdm-nav {
            display: flex;
            align-items: center;
            gap: 26px;
            list-style: none;
        }

        .cdm-nav > li {
            position: relative;
        }

        .cdm-nav > li > a {
            font-size: 15px;
            font-weight: 600;
            color: #1e293b;
            padding: 10px 0;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            position: relative;
        }

        .cdm-nav > li > a:hover,
        .cdm-nav > li > a.active {
            color: var(--primary-color);
        }

        .cdm-nav > li > a::after {
            content: '';
            position: absolute;
            bottom: 2px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--secondary-color);
            transition: width 0.3s ease;
        }

        .cdm-nav > li:hover > a::after,
        .cdm-nav > li > a.active::after {
            width: 100%;
        }

        .cdm-nav > li > a i.fa-chevron-down {
            display: inline-block;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            will-change: transform;
        }

        .cdm-nav > li:hover > a i.fa-chevron-down {
            transform: rotate(180deg);
        }

        /* Dropdown */
        .cdm-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            background: #ffffff;
            min-width: 250px;
            border-radius: 8px;
            box-shadow: 0 12px 35px rgba(3, 34, 143, 0.15);
            padding: 12px 0;
            opacity: 0;
            visibility: hidden;
            transform: translate3d(0, 12px, 0);
            transition: opacity 0.3s cubic-bezier(0.16, 1, 0.3, 1), transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), visibility 0.3s ease;
            list-style: none;
            border-top: 3px solid var(--secondary-color);
            z-index: 1010;
            will-change: opacity, transform;
            pointer-events: none;
        }

        .cdm-nav > li:hover .cdm-dropdown {
            opacity: 1;
            visibility: visible;
            transform: translate3d(0, 0, 0);
            pointer-events: auto;
        }

        .cdm-dropdown li a {
            padding: 10px 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            font-weight: 500;
            color: #334155;
            transition: all 0.2s ease;
        }

        .cdm-dropdown li a:hover {
            background: #f1f5f9;
            color: var(--primary-color);
            padding-left: 24px;
        }

        .cdm-dropdown li a i {
            color: var(--secondary-color);
            font-size: 11px;
        }

        /* Mobile Hamburger */
        .cdm-menu-toggle {
            display: none;
            background: none;
            border: none;
            width: 42px;
            height: 42px;
            border-radius: 8px;
            font-size: 22px;
            color: var(--primary-color);
            cursor: pointer;
            padding: 0;
            align-items: center;
            justify-content: center;
            transition: background-color 0.2s ease, color 0.2s ease;
            flex-shrink: 0;
        }

        .cdm-menu-toggle:hover,
        .cdm-menu-toggle:focus-visible {
            background-color: #f1f5f9;
            color: var(--secondary-color);
            outline: none;
        }

        .cdm-header-actions {
            display: flex;
            align-items: center;
            gap: 15px;
            flex-shrink: 0;
        }

        /* Mobile Navigation Drawer Backdrop Overlay */
        .cdm-drawer-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.65);
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
            z-index: 99998;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.35s cubic-bezier(0.16, 1, 0.3, 1), visibility 0.35s ease;
            touch-action: none;
        }

        .cdm-drawer-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        /* Mobile Navigation Drawer */
        .cdm-mobile-drawer {
            position: fixed;
            top: 0;
            right: 0;
            width: min(320px, 84vw);
            max-width: 340px;
            height: 100vh;
            height: 100dvh;
            background: #ffffff;
            box-shadow: -10px 0 35px rgba(15, 23, 42, 0.16);
            z-index: 99999;
            transform: translateX(100%);
            transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            overscroll-behavior: contain;
            pointer-events: auto;
        }

        .cdm-mobile-drawer.active {
            transform: translateX(0);
        }

        /* Sticky Drawer Header: Logo + Close Button */
        .cdm-drawer-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 14px 18px;
            background: #ffffff;
            border-bottom: 1px solid #f1f5f9;
            flex-shrink: 0;
            z-index: 10;
        }

        .cdm-drawer-brand {
            display: inline-flex;
            align-items: center;
            text-decoration: none;
        }

        .cdm-drawer-logo {
            width: auto;
            max-width: 125px;
            max-height: 38px;
            height: auto;
            object-fit: contain;
            display: block;
        }

        .cdm-drawer-logo-text {
            font-size: 19px;
            font-weight: 800;
            color: var(--primary-color, #03228f);
        }

        .cdm-drawer-close {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            font-size: 16px;
            color: #64748b;
            cursor: pointer;
            transition: all 0.2s ease;
            flex-shrink: 0;
        }

        .cdm-drawer-close:hover,
        .cdm-drawer-close:focus-visible {
            background: #fee2e2;
            color: #ef4444;
            border-color: #fca5a5;
            outline: none;
        }

        /* Scrollable Drawer Body */
        .cdm-drawer-body {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            padding: 14px 18px 24px;
            -webkit-overflow-scrolling: touch;
            overscroll-behavior: contain;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 18px;
        }

        .cdm-drawer-nav {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            gap: 3px;
        }

        .cdm-drawer-item {
            display: block;
            position: relative;
        }

        .cdm-drawer-link {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            padding: 13px 14px;
            font-size: 15.5px;
            font-weight: 600;
            color: #1e293b;
            background: transparent;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            text-align: left;
            cursor: pointer;
            transition: background-color 0.2s ease, color 0.2s ease;
            font-family: inherit;
            border-bottom: 1px solid #f1f5f9;
        }

        .cdm-drawer-item:last-child .cdm-drawer-link {
            border-bottom: none;
        }

        .cdm-drawer-link:hover,
        .cdm-drawer-link:focus-visible,
        .cdm-drawer-link.active {
            background: #f8fafc;
            color: var(--primary-color, #03228f);
        }

        .cdm-drawer-toggle-btn {
            appearance: none;
            -webkit-appearance: none;
        }

        .cdm-drawer-arrow-wrapper {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            border-radius: 6px;
            background: #f1f5f9;
            margin-left: 8px;
            transition: background-color 0.2s ease;
        }

        .cdm-drawer-link:hover .cdm-drawer-arrow-wrapper,
        .cdm-drawer-toggle-btn.open .cdm-drawer-arrow-wrapper {
            background: rgba(251, 154, 39, 0.15);
        }

        .cdm-drawer-arrow {
            display: inline-block;
            font-size: 12px;
            color: #64748b;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), color 0.2s ease;
            will-change: transform;
        }

        .cdm-drawer-arrow.open,
        .cdm-drawer-toggle-btn.open .cdm-drawer-arrow {
            transform: rotate(180deg);
            color: var(--secondary-color, #fb9a27);
        }

        /* Collapsible Services Submenu */
        .cdm-drawer-submenu {
            list-style: none;
            margin: 0;
            padding: 0 8px;
            max-height: 0;
            overflow: hidden;
            opacity: 0;
            background: #f8fafc;
            border-radius: 8px;
            border-left: 3px solid var(--secondary-color, #fb9a27);
            transition: max-height 0.35s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.25s ease, padding 0.3s ease, margin 0.3s ease;
        }

        .cdm-drawer-submenu.open {
            max-height: 520px;
            opacity: 1;
            padding: 8px;
            margin: 6px 0 8px;
        }

        .cdm-drawer-subitem {
            display: block;
        }

        .cdm-drawer-sublink {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 10px;
            font-size: 13.5px;
            font-weight: 500;
            color: #475569;
            text-decoration: none;
            border-radius: 6px;
            transition: background-color 0.2s ease, color 0.2s ease, transform 0.2s ease;
        }

        .cdm-drawer-sublink i {
            font-size: 11px;
            color: var(--secondary-color, #fb9a27);
            transition: transform 0.2s ease;
            width: 12px;
            text-align: center;
        }

        .cdm-drawer-sublink:hover,
        .cdm-drawer-sublink:focus-visible,
        .cdm-drawer-sublink.active {
            background: #ffffff;
            color: var(--primary-color, #03228f);
            transform: translateX(4px);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .cdm-drawer-subitem-all {
            margin-top: 4px;
            padding-top: 4px;
            border-top: 1px dashed #e2e8f0;
        }

        .cdm-drawer-sublink-all {
            font-weight: 600;
            color: var(--primary-color, #03228f);
        }

        /* Drawer Footer CTA */
        .cdm-drawer-footer {
            margin-top: auto;
            padding-top: 12px;
            border-top: 1px solid #f1f5f9;
        }

        .cdm-drawer-cta {
            width: 100%;
            justify-content: center;
            padding: 12px 20px;
            font-size: 14.5px;
        }

        /* ═══════════════════════════════════════════════════════════════
           6. BUTTONS AND REUSABLE COMPONENTS
           ═══════════════════════════════════════════════════════════════ */
        /* Free Quote Button - CherriDM signature style */
        .cdm-btn-quote {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: var(--secondary-color);
            color: #ffffff;
            font-size: 14px;
            font-weight: 700;
            padding: 12px 28px;
            border-radius: 4px;
            border: 2px solid var(--secondary-color);
            cursor: pointer;
            text-transform: capitalize;
            box-shadow: 0 4px 15px rgba(251, 154, 39, 0.3);
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .cdm-btn-quote:hover {
            background: #ffffff;
            color: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(251, 154, 39, 0.4);
        }

        .cdm-btn-quote i {
            font-size: 13px;
            transition: transform 0.3s ease;
        }

        .cdm-btn-quote:hover i {
            transform: translateX(4px);
        }

        /* ═══════════════════════════════════════════════════════════════
           7. PAGE-SPECIFIC UTILITIES & DRAWER STATES
           ═══════════════════════════════════════════════════════════════ */
        /* Prevent background scroll when drawer is open */
        body.cdm-drawer-open {
            overflow: hidden !important;
            touch-action: none !important;
        }

        body.cdm-drawer-open .cdm-site-wrapper {
            pointer-events: none !important;
            user-select: none !important;
        }

        /* WhatsApp floating button suppression when drawer is open */
        body.cdm-drawer-open .cdm-float-wa,
        body.cdm-drawer-open .cdm-floating-actions {
            opacity: 0 !important;
            visibility: hidden !important;
            pointer-events: none !important;
            transform: scale(0.8) !important;
            transition: opacity 0.2s ease, visibility 0.2s ease, transform 0.2s ease;
        }

        /* ═══════════════════════════════════════════════════════════════
           8. RESPONSIVE MEDIA QUERIES
           ═══════════════════════════════════════════════════════════════ */
        @media (max-width: 1200px) and (min-width: 993px) {
            :root {
                --cdm-header-height: 84px;
            }
            .cdm-header .container {
                padding: 0 16px;
            }
            .cdm-logo img {
                width: 165px;
                max-height: 52px;
            }
            .cdm-nav {
                gap: 16px;
            }
            .cdm-nav > li > a {
                font-size: 14px;
            }
            .cdm-btn-quote {
                padding: 10px 20px;
                font-size: 13.5px;
            }
        }

        @media (max-width: 992px) {
            :root {
                --cdm-header-height: 74px;
            }
            .cdm-nav {
                display: none;
            }
            .cdm-menu-toggle {
                display: inline-flex;
            }
            .cdm-header .container {
                padding: 0 20px;
            }
            .cdm-logo img {
                width: 140px;
                max-width: 150px;
                max-height: 48px;
            }
            .cdm-btn-quote {
                padding: 8px 16px;
                font-size: 13px;
            }
            .cdm-header-actions {
                gap: 12px;
            }
        }

        @media (max-width: 650px) {
            :root {
                --cdm-header-height: 64px;
            }
            .cdm-header .container,
            .container {
                padding-left: 16px;
                padding-right: 16px;
            }
            .cdm-logo {
                display: inline-flex;
                align-items: center;
                flex-shrink: 0;
            }
            .cdm-logo img {
                width: 118px;
                max-width: 128px;
                height: auto;
                max-height: 42px;
                object-fit: contain;
                display: block;
            }
            .cdm-logo span {
                font-size: 19px;
            }
            .cdm-btn-quote {
                display: none !important; /* Mobile header displays logo + hamburger toggle cleanly */
            }
            .cdm-header-actions {
                gap: 0;
            }
            .cdm-menu-toggle {
                width: 40px;
                height: 40px;
                font-size: 20px;
            }
        }
    </style>
</head>
<body>

    <!-- MAIN FIXED NAVBAR -->
    <header class="cdm-header" id="cdmHeader">
        <div class="container">
            <a href="<?= $base_url ?>/" class="cdm-logo">
                <?php if ($site_logo): ?>
                <img src="<?= $base_url ?>/<?= htmlspecialchars(ltrim($site_logo, '/')) ?>" alt="<?= htmlspecialchars($site_name) ?>">
                <?php else: ?>
                <span><?= htmlspecialchars(explode(' ', $site_name)[0]) ?></span><span class="accent"><?= htmlspecialchars(substr($site_name, strlen(explode(' ', $site_name)[0]))) ?></span>
                <?php endif; ?>
            </a>

            <?php $cp = basename($_SERVER["PHP_SELF"]); ?>
            <ul class="cdm-nav">
                <li><a href="<?= $base_url ?>/" class="<?= in_array($cp, ['index.php','home.php']) ? 'active' : '' ?>">Home</a></li>
                <li><a href="<?= $base_url ?>/why-choose-us" class="<?= $cp == 'why-choose-us.php' ? 'active' : '' ?>">About</a></li>
                <li>
                    <a href="<?= $base_url ?>/services" class="<?= in_array($cp, ['services.php', 'service-detail.php']) ? 'active' : '' ?>">
                        Digital Marketing <i class="fas fa-chevron-down" style="font-size: 11px;"></i>
                    </a>
                    <ul class="cdm-dropdown">
                        <?php if (!empty($nav_services)): ?>
                            <?php foreach ($nav_services as $ns): ?>
                            <li>
                                <a href="<?= $base_url ?>/service/<?= htmlspecialchars($ns['slug']) ?>">
                                    <i class="fas fa-chevron-right"></i>
                                    <?= htmlspecialchars($ns['service_name']) ?>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li><a href="<?= $base_url ?>/service/seo-optimization"><i class="fas fa-chevron-right"></i> SEO Optimization</a></li>
                            <li><a href="<?= $base_url ?>/service/google-meta-ads"><i class="fas fa-chevron-right"></i> Google & Meta Ads</a></li>
                            <li><a href="<?= $base_url ?>/service/social-media-marketing"><i class="fas fa-chevron-right"></i> Social Media Marketing</a></li>
                            <li><a href="<?= $base_url ?>/service/website-app-development"><i class="fas fa-chevron-right"></i> Website & App Development</a></li>
                            <li><a href="<?= $base_url ?>/service/brand-identity-design"><i class="fas fa-chevron-right"></i> Brand Identity & Design</a></li>
                            <li><a href="<?= $base_url ?>/service/content-marketing"><i class="fas fa-chevron-right"></i> Content Marketing</a></li>
                        <?php endif; ?>
                    </ul>
                </li>
                <li><a href="<?= $base_url ?>/projects" class="<?= $cp == 'projects.php' ? 'active' : '' ?>">Portfolio</a></li>
                <li><a href="<?= $base_url ?>/blog" class="<?= in_array($cp, ['blog.php', 'blog-detail.php']) ? 'active' : '' ?>">Blog</a></li>
                <li><a href="<?= $base_url ?>/reviews" class="<?= in_array($cp, ['reviews.php', 'testimonials.php']) ? 'active' : '' ?>">Reviews</a></li>
                <li><a href="<?= $base_url ?>/contact" class="<?= $cp == 'contact.php' ? 'active' : '' ?>">Contact</a></li>
            </ul>

            <div class="cdm-header-actions">
                <button type="button" class="cdm-btn-quote" onclick="openContactPopup()">
                    <span>Free Quote</span>
                    <i class="fas fa-arrow-right"></i>
                </button>
                <button class="cdm-menu-toggle" id="cdmMenuToggle" aria-label="Toggle Navigation">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
    </header>

    <!-- IN-FLOW HEADER SPACER TO PREVENT PAGE CONTENT HIDING -->
    <div class="cdm-header-spacer" aria-hidden="true"></div>

    <!-- MOBILE DRAWER BACKDROP OVERLAY -->
    <div class="cdm-drawer-overlay" id="cdmDrawerOverlay" tabindex="-1" aria-hidden="true"></div>

    <!-- MOBILE NAVIGATION DRAWER -->
    <aside class="cdm-mobile-drawer" id="cdmMobileDrawer" role="dialog" aria-modal="true" aria-label="Mobile Navigation Menu">
        <!-- FIXED DRAWER HEADER: Prominent Logo + Aligned Close Button -->
        <div class="cdm-drawer-header">
            <a href="<?= $base_url ?>/" class="cdm-drawer-brand" aria-label="<?= htmlspecialchars($site_name) ?> Home">
                <?php if ($site_logo): ?>
                <img src="<?= $base_url ?>/<?= htmlspecialchars(ltrim($site_logo, '/')) ?>" alt="<?= htmlspecialchars($site_name) ?>" class="cdm-drawer-logo">
                <?php else: ?>
                <span class="cdm-drawer-logo-text"><?= htmlspecialchars($site_name) ?></span>
                <?php endif; ?>
            </a>
            <button type="button" class="cdm-drawer-close" id="cdmDrawerClose" aria-label="Close navigation menu">
                <i class="fas fa-times" aria-hidden="true"></i>
            </button>
        </div>

        <!-- SCROLLABLE DRAWER BODY -->
        <div class="cdm-drawer-body">
            <nav class="cdm-drawer-nav-wrap" aria-label="Mobile Main Navigation">
                <ul class="cdm-drawer-nav">
                    <!-- 1. Services (Collapsible with Chevron Dropdown Arrow) -->
                    <li class="cdm-drawer-item cdm-drawer-dropdown <?= in_array($cp, ['services.php', 'service-detail.php']) ? 'active' : '' ?>">
                        <button type="button" 
                                class="cdm-drawer-link cdm-drawer-toggle-btn <?= in_array($cp, ['services.php', 'service-detail.php']) ? 'active' : '' ?>" 
                                id="mobileServicesToggle" 
                                aria-expanded="false" 
                                aria-controls="mobileServicesSubmenu">
                            <span class="cdm-drawer-link-text">Services</span>
                            <span class="cdm-drawer-arrow-wrapper">
                                <i class="fas fa-chevron-down cdm-drawer-arrow" aria-hidden="true"></i>
                            </span>
                        </button>
                        <ul class="cdm-drawer-submenu" id="mobileServicesSubmenu" aria-label="Services Submenu">
                            <?php if (!empty($nav_services)): ?>
                                <?php foreach ($nav_services as $ns): ?>
                                <li class="cdm-drawer-subitem">
                                    <a href="<?= $base_url ?>/service/<?= htmlspecialchars($ns['slug']) ?>" class="cdm-drawer-sublink">
                                        <i class="fas fa-angle-right" aria-hidden="true"></i>
                                        <span><?= htmlspecialchars($ns['service_name']) ?></span>
                                    </a>
                                </li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li class="cdm-drawer-subitem"><a href="<?= $base_url ?>/service/seo-optimization" class="cdm-drawer-sublink"><i class="fas fa-angle-right"></i><span>SEO Optimization</span></a></li>
                                <li class="cdm-drawer-subitem"><a href="<?= $base_url ?>/service/google-meta-ads" class="cdm-drawer-sublink"><i class="fas fa-angle-right"></i><span>Google &amp; Meta Ads</span></a></li>
                                <li class="cdm-drawer-subitem"><a href="<?= $base_url ?>/service/social-media-marketing" class="cdm-drawer-sublink"><i class="fas fa-angle-right"></i><span>Social Media Marketing</span></a></li>
                                <li class="cdm-drawer-subitem"><a href="<?= $base_url ?>/service/website-app-development" class="cdm-drawer-sublink"><i class="fas fa-angle-right"></i><span>Website &amp; App Development</span></a></li>
                                <li class="cdm-drawer-subitem"><a href="<?= $base_url ?>/service/brand-identity-design" class="cdm-drawer-sublink"><i class="fas fa-angle-right"></i><span>Brand Identity &amp; Design</span></a></li>
                                <li class="cdm-drawer-subitem"><a href="<?= $base_url ?>/service/content-marketing" class="cdm-drawer-sublink"><i class="fas fa-angle-right"></i><span>Content Marketing &amp; Copywriting</span></a></li>
                            <?php endif; ?>
                        </ul>
                    </li>

                    <!-- 2. Portfolio -->
                    <li class="cdm-drawer-item">
                        <a href="<?= $base_url ?>/projects" class="cdm-drawer-link <?= $cp == 'projects.php' ? 'active' : '' ?>">
                            <span class="cdm-drawer-link-text">Portfolio</span>
                        </a>
                    </li>

                    <!-- 3. Blog -->
                    <li class="cdm-drawer-item">
                        <a href="<?= $base_url ?>/blog" class="cdm-drawer-link <?= in_array($cp, ['blog.php', 'blog-detail.php']) ? 'active' : '' ?>">
                            <span class="cdm-drawer-link-text">Blog</span>
                        </a>
                    </li>

                    <!-- 4. Reviews & Ratings -->
                    <li class="cdm-drawer-item">
                        <a href="<?= $base_url ?>/reviews" class="cdm-drawer-link <?= in_array($cp, ['reviews.php', 'testimonials.php']) ? 'active' : '' ?>">
                            <span class="cdm-drawer-link-text">Reviews &amp; Ratings</span>
                        </a>
                    </li>

                    <!-- 5. Contact Us -->
                    <li class="cdm-drawer-item">
                        <a href="<?= $base_url ?>/contact" class="cdm-drawer-link <?= $cp == 'contact.php' ? 'active' : '' ?>">
                            <span class="cdm-drawer-link-text">Contact Us</span>
                        </a>
                    </li>

                    <!-- 6. About Us -->
                    <li class="cdm-drawer-item">
                        <a href="<?= $base_url ?>/why-choose-us" class="cdm-drawer-link <?= $cp == 'why-choose-us.php' ? 'active' : '' ?>">
                            <span class="cdm-drawer-link-text">About Us</span>
                        </a>
                    </li>

                    <!-- 7. Privacy Policy -->
                    <li class="cdm-drawer-item">
                        <a href="<?= $base_url ?>/privacy-policy" class="cdm-drawer-link <?= $cp == 'privacy-policy.php' ? 'active' : '' ?>">
                            <span class="cdm-drawer-link-text">Privacy Policy</span>
                        </a>
                    </li>

                    <!-- 8. Terms & Conditions -->
                    <li class="cdm-drawer-item">
                        <a href="<?= $base_url ?>/terms" class="cdm-drawer-link <?= in_array($cp, ['terms.php', 'terms-and-conditions.php']) ? 'active' : '' ?>">
                            <span class="cdm-drawer-link-text">Terms &amp; Conditions</span>
                        </a>
                    </li>
                </ul>
            </nav>

            <!-- DRAWER FOOTER: CTA BUTTON -->
            <div class="cdm-drawer-footer">
                <button type="button" class="cdm-btn-quote cdm-drawer-cta" onclick="openContactPopup()">
                    <span>Get Free Quote</span>
                    <i class="fas fa-arrow-right" aria-hidden="true"></i>
                </button>
            </div>
        </div>
    </aside>

    <script>
    (function() {
        function initDrawer() {
            if (window.cdmDrawerInitDone) return;
            var toggle = document.getElementById('cdmMenuToggle');
            var drawer = document.getElementById('cdmMobileDrawer');
            var overlay = document.getElementById('cdmDrawerOverlay');
            var closeBtn = document.getElementById('cdmDrawerClose');
            var header = document.getElementById('cdmHeader');
            var servicesToggle = document.getElementById('mobileServicesToggle');
            var servicesSubmenu = document.getElementById('mobileServicesSubmenu');

            if (!toggle || !drawer || !overlay) return;
            window.cdmDrawerInitDone = true;

            function openDrawer(e) {
                if (e) {
                    if (e.preventDefault) e.preventDefault();
                    if (e.stopPropagation) e.stopPropagation();
                }
                drawer.classList.add('active');
                overlay.classList.add('active');
                document.body.classList.add('cdm-drawer-open');
                document.body.style.overflow = 'hidden';
                toggle.setAttribute('aria-expanded', 'true');
                if (closeBtn) {
                    setTimeout(function() { closeBtn.focus(); }, 100);
                }
            }

            function closeDrawer(e) {
                if (e && e.preventDefault) e.preventDefault();
                drawer.classList.remove('active');
                overlay.classList.remove('active');
                document.body.classList.remove('cdm-drawer-open');
                document.body.style.overflow = '';
                toggle.setAttribute('aria-expanded', 'false');
            }

            toggle.addEventListener('click', openDrawer);
            if (closeBtn) closeBtn.addEventListener('click', closeDrawer);
            if (overlay) {
                overlay.addEventListener('click', closeDrawer);
                overlay.addEventListener('touchmove', function(e) {
                    e.preventDefault();
                }, { passive: false });
            }

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && drawer.classList.contains('active')) {
                    closeDrawer();
                }
            });

            if (servicesToggle && servicesSubmenu) {
                servicesToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    var isOpen = servicesSubmenu.classList.toggle('open');
                    servicesToggle.classList.toggle('open', isOpen);
                    var arrow = servicesToggle.querySelector('.cdm-drawer-arrow');
                    if (arrow) arrow.classList.toggle('open', isOpen);
                    servicesToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                });
            }

            var navLinks = drawer.querySelectorAll('.cdm-drawer-nav a, .cdm-drawer-brand');
            navLinks.forEach(function(link) {
                link.addEventListener('click', function() {
                    closeDrawer();
                });
            });

            function updateHeaderScroll() {
                if (!header) return;
                if (window.scrollY > 20) {
                    header.classList.add('scrolled');
                } else {
                    header.classList.remove('scrolled');
                }
            }
            window.addEventListener('scroll', updateHeaderScroll, { passive: true });
            updateHeaderScroll();
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initDrawer);
        } else {
            initDrawer();
        }
    })();
    </script>

    <!-- Mobile-safe Page Wrapper to Clip Any Accidental Sub-pixel Overflow -->
    <div class="cdm-site-wrapper">