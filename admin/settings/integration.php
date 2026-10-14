<?php
/**
 * Integration Settings - CRUD Operations for SEO, Analytics, Social Media, and SEO Tools
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

$message = '';
$message_type = '';
$csrf_token = generateCSRFToken();

// Load all settings upfront so helper functions and generator actions can access them
$allSettings = [];
$stmt = $conn->prepare("SELECT setting_key, setting_value FROM settings");
$stmt->execute();
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $allSettings[$row['setting_key']] = $row['setting_value'];
}

if (!function_exists('getSettingValue')) {
    function getSettingValue(string $key, $default = '') {
        global $allSettings, $mailSettings;
        if (isset($allSettings[$key])) return $allSettings[$key];
        if (isset($mailSettings[$key])) return $mailSettings[$key];
        return $default;
    }
}

// Save Analytics Settings
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_analytics'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid request.';
        $message_type = 'error';
    } else {
        $settings = [
            'analytics_google_id' => trim($_POST['google_analytics_id'] ?? ''),
            'analytics_google_enabled' => isset($_POST['google_analytics_enabled']) ? '1' : '0',
            'analytics_tag_manager_id' => trim($_POST['tag_manager_id'] ?? ''),
            'analytics_tag_manager_enabled' => isset($_POST['tag_manager_enabled']) ? '1' : '0',
            'analytics_facebook_pixel_id' => trim($_POST['facebook_pixel_id'] ?? ''),
            'analytics_facebook_pixel_enabled' => isset($_POST['facebook_pixel_enabled']) ? '1' : '0',
        ];

        foreach ($settings as $key => $value) {
            $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
            $stmt->execute([$key, $value]);
        }
        logAudit($conn, 'UPDATE_ANALYTICS', 'Updated analytics settings', $_SESSION['admin_id']);
        notifySecurity('UPDATE_ANALYTICS', 'Updated analytics settings', $_SESSION['admin_id']);
        $message = 'Analytics settings saved successfully!';
        $message_type = 'success';
    }
}

// Save Social Media Links
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_social'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid request.';
        $message_type = 'error';
    } else {
        $socials = $_POST['social'] ?? [];
        foreach ($socials as $platform => $data) {
            $enabled = isset($data['enabled']) ? '1' : '0';
            $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
            $stmt->execute(['social_' . $platform . '_url', $data['url'] ?? '']);
            $stmt->execute(['social_' . $platform . '_enabled', $enabled]);
        }
        logAudit($conn, 'UPDATE_SOCIAL_MEDIA', 'Updated social media links', $_SESSION['admin_id']);
        notifySecurity('UPDATE_SOCIAL_MEDIA', 'Updated social media links', $_SESSION['admin_id']);
        $message = 'Social media links saved successfully!';
        $message_type = 'success';
    }
}

// Save SEO Settings
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_seo'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid request.';
        $message_type = 'error';
    } else {
        $seo_settings = [
            'seo_meta_title' => trim($_POST['meta_title'] ?? ''),
            'seo_meta_description' => trim($_POST['meta_description'] ?? ''),
            'seo_meta_keywords' => trim($_POST['meta_keywords'] ?? ''),
            'seo_meta_author' => trim($_POST['meta_author'] ?? ''),
            'seo_og_image' => trim($_POST['og_image'] ?? ''),
            'seo_canonical_url' => trim($_POST['canonical_url'] ?? ''),
            'seo_robots_index' => isset($_POST['robots_index']) ? '1' : '0',
            'seo_robots_follow' => isset($_POST['robots_follow']) ? '1' : '0',
        ];

        foreach ($seo_settings as $key => $value) {
            $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
            $stmt->execute([$key, $value]);
        }
        logAudit($conn, 'UPDATE_SEO', 'Updated SEO settings', $_SESSION['admin_id']);
        notifySecurity('UPDATE_SEO', 'Updated SEO settings', $_SESSION['admin_id']);
        $message = 'SEO settings saved successfully!';
        $message_type = 'success';
    }
}

// Save WhatsApp (Fast2SMS WABA) Settings
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_whatsapp'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid security token. Please refresh.';
        $message_type = 'error';
    } else {
        $whatsapp_settings = [
            'whatsapp_api_key' => trim($_POST['whatsapp_api_key'] ?? ''),
            'whatsapp_enabled' => isset($_POST['whatsapp_enabled']) ? '1' : '0',
            'whatsapp_admin_number' => trim($_POST['whatsapp_admin_number'] ?? ''),
            'whatsapp_sender_number' => trim($_POST['whatsapp_sender_number'] ?? ''),
            'whatsapp_phone_number_id' => trim($_POST['whatsapp_phone_number_id'] ?? ''),
            'whatsapp_waba_id' => trim($_POST['whatsapp_waba_id'] ?? ''),
            'whatsapp_lead_template_id' => trim($_POST['whatsapp_lead_template_id'] ?? ''),
            'whatsapp_welcome_template_id' => trim($_POST['whatsapp_welcome_template_id'] ?? ''),
            'whatsapp_review_template_id' => trim($_POST['whatsapp_review_template_id'] ?? ''),
        ];

        foreach ($whatsapp_settings as $key => $value) {
            $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
            $stmt->execute([$key, $value]);
            $allSettings[$key] = $value;
        }

        logAudit($conn, 'UPDATE_WHATSAPP_SETTINGS', 'Updated Fast2SMS WhatsApp API settings', $_SESSION['admin_id']);
        notifySecurity('UPDATE_WHATSAPP_SETTINGS', 'Updated Fast2SMS WhatsApp API settings', $_SESSION['admin_id']);
        $message = 'WhatsApp (Fast2SMS WABA) settings saved successfully!';
        $message_type = 'success';
    }
}

// Generate Sitemap
if (isset($_GET['action']) && $_GET['action'] === 'generate_sitemap') {
    header('Content-Type: application/xml; charset=utf-8');
    header('Content-Disposition: attachment; filename="sitemap.xml"');

    // Get all pages from database
    $pages = [];

    // Fetch leads pages/configurations
    $stmt = $conn->query("SELECT 'leads' as url, NOW() as lastmod FROM dual");
    $pages[] = ['loc' => 'leads.php', 'lastmod' => date('Y-m-d'), 'priority' => '0.8', 'changefreq' => 'weekly'];

    // Other main pages
    $main_pages = [
        ['loc' => 'index.php', 'priority' => '1.0', 'changefreq' => 'daily'],
        ['loc' => 'about.php', 'priority' => '0.7', 'changefreq' => 'monthly'],
        ['loc' => 'services.php', 'priority' => '0.9', 'changefreq' => 'weekly'],
        ['loc' => 'projects.php', 'priority' => '0.7', 'changefreq' => 'weekly'],
        ['loc' => 'contact.php', 'priority' => '0.9', 'changefreq' => 'monthly'],
    ];

    $base_url = getSettingValue('seo_canonical_url', 'http://localhost/CloudASPIRE');

    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

    foreach ($main_pages as $page) {
        echo '<url>' . "\n";
        echo '<loc>' . htmlspecialchars($base_url . '/' . $page['loc']) . '</loc>' . "\n";
        echo '<lastmod>' . date('Y-m-d') . '</lastmod>' . "\n";
        echo '<changefreq>' . $page['changefreq'] . '</changefreq>' . "\n";
        echo '<priority>' . $page['priority'] . '</priority>' . "\n";
        echo '</url>' . "\n";
    }

    echo '</urlset>';
    exit;
}

// Generate Robots.txt
if (isset($_GET['action']) && $_GET['action'] === 'generate_robots') {
    header('Content-Type: text/plain; charset=utf-8');
    header('Content-Disposition: attachment; filename="robots.txt"');

    $base_url = getSettingValue('seo_canonical_url', 'http://localhost/CloudASPIRE');
    $robots_index = getSettingValue('seo_robots_index', '1');
    $robots_follow = getSettingValue('seo_robots_follow', '1');

    $content = "# robots.txt generated by CloudASPIRE\n";
    $content .= "User-agent: *\n";

    if ($robots_index === '0') {
        $content .= "Disallow: /\n";
    } else {
        $content .= "Allow: /\n";
        $content .= "Disallow: /admin/\n";
        $content .= "Disallow: /app/\n";
        $content .= "Disallow: /vendor/\n";
    }

    $content .= "\n";
    $content .= "# Sitemap\n";
    $content .= "Sitemap: " . $base_url . "/sitemap.xml\n";

    echo $content;
    exit;
}

// Generate LLMs.txt
if (isset($_GET['action']) && $_GET['action'] === 'generate_llms') {
    header('Content-Type: text/plain; charset=utf-8');
    header('Content-Disposition: attachment; filename="llms.txt"');

    $base_url = getSettingValue('seo_canonical_url', 'http://localhost/CloudASPIRE');
    $meta_title = getSettingValue('seo_meta_title', 'CloudASPIRE');
    $meta_description = getSettingValue('seo_meta_description', '');

    $content = "# " . $meta_title . "\n\n";
    $content .= $meta_description . "\n\n";
    $content .= "## Website Structure\n\n";
    $content .= "### Main Pages\n";
    $content .= "- Home: " . $base_url . "/index.php\n";
    $content .= "- Services: " . $base_url . "/services.php\n";
    $content .= "- Projects: " . $base_url . "/projects.php\n";
    $content .= "- Contact: " . $base_url . "/contact.php\n\n";

    $content .= "### Admin Panel\n";
    $content .= "- Admin Login: " . $base_url . "/admin/login.php\n\n";

    $content .= "---\n";
    $content .= "Generated by CloudASPIRE on " . date('Y-m-d H:i:s') . "\n";

    echo $content;
    exit;
}

require_once __DIR__ . '/../../app/Views/admin/settings/integration.php';
