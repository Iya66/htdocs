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
        $nav_services = $conn->query("SELECT service_name, slug FROM services WHERE status = 'active' ORDER BY created_at ASC LIMIT 8")->fetchAll(PDO::FETCH_ASSOC);
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
<html lang="en">
<head>
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
        :root {
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

        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: var(--font-main);
            color: var(--text-dark);
            background-color: #ffffff;
            line-height: 1.6;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        a {
            text-decoration: none;
            color: inherit;
            transition: all 0.25s ease;
        }

        img {
            max-width: 100%;
            height: auto;
            display: inline-block;
        }

        /* Top Bar */
        .cdm-top-bar {
            background-color: #03228f;
            color: #ffffff;
            padding: 9px 0;
            font-size: 13px;
            font-weight: 500;
            border-bottom: 1px solid rgba(255, 255, 255, 0.12);
        }

        .cdm-top-bar .container {
            max-width: 1240px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .cdm-top-info {
            display: flex;
            align-items: center;
            gap: 25px;
        }

        .cdm-top-info a {
            color: #ffffff;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .cdm-top-info a i {
            color: var(--secondary-color);
            font-size: 14px;
        }

        .cdm-top-info a:hover {
            color: var(--secondary-color);
        }

        .cdm-top-social {
            display: flex;
            align-items: center;
            gap: 10px;
            list-style: none;
        }

        .cdm-top-social a {
            width: 28px;
            height: 28px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.15);
            color: #ffffff;
            font-size: 12px;
            transition: all 0.3s ease;
        }

        .cdm-top-social a:hover {
            background: var(--secondary-color);
            color: #ffffff;
            transform: translateY(-2px);
        }

        /* Header Navbar */
        .cdm-header {
            position: sticky;
            top: 0;
            left: 0;
            right: 0;
            background: #ffffff;
            z-index: 1000;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
            transition: all 0.3s ease;
        }

        .cdm-header.scrolled {
            box-shadow: 0 8px 30px rgba(3, 34, 143, 0.12);
        }

        .cdm-header .container {
            max-width: 1240px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 85px;
        }

        .cdm-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 800;
            font-size: 24px;
            color: var(--primary-color);
        }

        .cdm-logo img {
            max-height: 52px;
            width: auto;
        }

        .cdm-logo span.accent {
            color: var(--secondary-color);
        }

        /* Desktop Nav */
        .cdm-nav {
            display: flex;
            align-items: center;
            gap: 30px;
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
            transform: translateY(12px);
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            list-style: none;
            border-top: 3px solid var(--secondary-color);
            z-index: 1010;
        }

        .cdm-nav > li:hover .cdm-dropdown {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
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

        /* Mobile Hamburger */
        .cdm-menu-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 24px;
            color: var(--primary-color);
            cursor: pointer;
            padding: 8px;
        }

        /* Mobile Drawer */
        .cdm-mobile-drawer {
            position: fixed;
            top: 0;
            right: -320px;
            width: 300px;
            height: 100vh;
            background: #ffffff;
            box-shadow: -10px 0 30px rgba(0, 0, 0, 0.2);
            z-index: 9999;
            transition: right 0.35s cubic-bezier(0.16, 1, 0.3, 1);
            padding: 30px 24px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        }

        .cdm-mobile-drawer.active {
            right: 0;
        }

        .cdm-drawer-overlay {
            position: fixed;
            inset: 0;
            background: rgba(3, 34, 143, 0.6);
            backdrop-filter: blur(4px);
            z-index: 9998;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .cdm-drawer-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .cdm-drawer-close {
            align-self: flex-end;
            background: none;
            border: none;
            font-size: 26px;
            color: #64748b;
            cursor: pointer;
            margin-bottom: 20px;
        }

        .cdm-drawer-nav {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .cdm-drawer-nav a {
            font-size: 16px;
            font-weight: 600;
            color: #1e293b;
            display: block;
            padding: 8px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .cdm-drawer-nav a:hover,
        .cdm-drawer-nav a.active {
            color: var(--primary-color);
        }

        @media (max-width: 992px) {
            .cdm-top-bar .cdm-top-social {
                display: none;
            }
            .cdm-nav {
                display: none;
            }
            .cdm-menu-toggle {
                display: block;
            }
            .cdm-btn-quote {
                padding: 8px 18px;
                font-size: 13px;
            }
        }

        @media (max-width: 600px) {
            .cdm-top-bar {
                font-size: 12px;
            }
            .cdm-top-info {
                gap: 15px;
            }
            .cdm-header .container {
                height: 72px;
            }
            .cdm-logo img {
                max-height: 40px;
            }
        }
    </style>
</head>
<body>

    <!-- TOP BAR -->
    <div class="cdm-top-bar">
        <div class="container">
            <div class="cdm-top-info">
                <a href="tel:<?= preg_replace('/[^0-9+]/', '', $contact_phone) ?>">
                    <i class="fas fa-phone-alt"></i>
                    <strong><?= htmlspecialchars($contact_phone) ?></strong>
                </a>
                <a href="mailto:<?= htmlspecialchars($contact_email) ?>">
                    <i class="fas fa-envelope"></i>
                    <span><?= htmlspecialchars($contact_email) ?></span>
                </a>
            </div>
            <ul class="cdm-top-social">
                <li><a href="<?= htmlspecialchars($facebook_url) ?>" target="_blank" rel="noopener" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a></li>
                <li><a href="<?= htmlspecialchars($twitter_url) ?>" target="_blank" rel="noopener" aria-label="Twitter"><i class="fab fa-twitter"></i></a></li>
                <li><a href="<?= htmlspecialchars($instagram_url) ?>" target="_blank" rel="noopener" aria-label="Instagram"><i class="fab fa-instagram"></i></a></li>
                <li><a href="<?= htmlspecialchars($linkedin_url) ?>" target="_blank" rel="noopener" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a></li>
                <li><a href="<?= htmlspecialchars($maps_url) ?>" target="_blank" rel="noopener" aria-label="Google Maps"><i class="fas fa-map-marker-alt"></i></a></li>
            </ul>
        </div>
    </div>

    <!-- MAIN NAVBAR -->
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
                    <a href="<?= $base_url ?>/services" class="<?= $cp == 'services.php' ? 'active' : '' ?>">
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

            <div style="display: flex; align-items: center; gap: 15px;">
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

    <!-- MOBILE DRAWER -->
    <div class="cdm-drawer-overlay" id="cdmDrawerOverlay"></div>
    <div class="cdm-mobile-drawer" id="cdmMobileDrawer">
        <button class="cdm-drawer-close" id="cdmDrawerClose">&times;</button>
        <div style="margin-bottom: 20px;">
            <a href="<?= $base_url ?>/" class="cdm-logo">
                <?php if ($site_logo): ?>
                <img src="<?= $base_url ?>/<?= htmlspecialchars(ltrim($site_logo, '/')) ?>" alt="<?= htmlspecialchars($site_name) ?>" style="max-height: 42px;">
                <?php else: ?>
                <span><?= htmlspecialchars($site_name) ?></span>
                <?php endif; ?>
            </a>
        </div>
        <ul class="cdm-drawer-nav">
            <li><a href="<?= $base_url ?>/" class="<?= in_array($cp, ['index.php','home.php']) ? 'active' : '' ?>">Home</a></li>
            <li><a href="<?= $base_url ?>/why-choose-us">About Us</a></li>
            <li>
                <a href="<?= $base_url ?>/services">Services</a>
                <?php if (!empty($nav_services)): ?>
                <ul style="list-style:none; padding-left:14px; margin-top:4px; display:flex; flex-direction:column; gap:6px;">
                    <?php foreach ($nav_services as $ns): ?>
                    <li>
                        <a href="<?= $base_url ?>/service/<?= htmlspecialchars($ns['slug']) ?>" style="font-size:13.5px; font-weight:500; color:#475569; padding:4px 0; border-bottom:none;">
                            <i class="fas fa-angle-right" style="color:#fb9a27; font-size:11px; margin-right:6px;"></i>
                            <?= htmlspecialchars($ns['service_name']) ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </li>
            <li><a href="<?= $base_url ?>/projects">Portfolio</a></li>
            <li><a href="<?= $base_url ?>/blog" class="<?= in_array($cp, ['blog.php', 'blog-detail.php']) ? 'active' : '' ?>">Blog</a></li>
            <li><a href="<?= $base_url ?>/reviews">Reviews & Ratings</a></li>
            <li><a href="<?= $base_url ?>/testimonials">Testimonials</a></li>
            <li><a href="<?= $base_url ?>/contact">Contact Us</a></li>
        </ul>
        <div style="margin-top: 30px;">
            <button type="button" class="cdm-btn-quote" style="width: 100%; justify-content: center;" onclick="openContactPopup()">
                <span>Get Free Quote</span>
                <i class="fas fa-arrow-right"></i>
            </button>
        </div>
    </div>