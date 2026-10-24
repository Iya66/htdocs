<?php
require_once __DIR__ . '/../../app/config.php';
require_once __DIR__ . '/../../app/lead_statuses.php';
header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$query = trim($_GET['q'] ?? '');

if ($query === '') {
    echo json_encode(['pages' => [], 'leads' => []]);
    exit;
}

try {
    // 1. Defined Pages
    $pages = [
        ['title' => 'Dashboard', 'url' => 'dashboard', 'icon' => 'fa-gauge', 'desc' => 'View site analytics and stats'],
        ['title' => 'Leads Management', 'url' => 'leads', 'icon' => 'fa-user-group', 'desc' => 'Manage incoming customer leads'],
        ['title' => 'Services Settings', 'url' => 'services', 'icon' => 'fa-briefcase', 'desc' => 'Add or edit offered services'],
        ['title' => 'Customer Reviews', 'url' => 'reviews', 'icon' => 'fa-star', 'desc' => 'Manage customer reviews'],
        ['title' => 'Testimonials', 'url' => 'testimonials', 'icon' => 'fa-quote-left', 'desc' => 'Manage client testimonials'],
        ['title' => 'Media & Files Manager', 'url' => 'files', 'icon' => 'fa-images', 'desc' => 'Upload and manage images for services and testimonials'],
        ['title' => 'Why Choose Us', 'url' => 'why-choose-us', 'icon' => 'fa-award', 'desc' => 'Configure key advantages and differentiators'],
        ['title' => 'Frequently Asked Questions (FAQ)', 'url' => 'faqs', 'icon' => 'fa-question-circle', 'desc' => 'Manage FAQ accordions and answers'],
        ['title' => 'Privacy Policy', 'url' => 'privacy-policy', 'icon' => 'fa-shield-halved', 'desc' => 'Edit website privacy policy'],
        ['title' => 'Contact Inquiries', 'url' => 'contact-us', 'icon' => 'fa-envelope', 'desc' => 'View and reply to messages'],
        ['title' => 'Project Gallery', 'url' => 'projects', 'icon' => 'fa-images', 'desc' => 'Configure project portfolio'],
        ['title' => 'User Accounts', 'url' => 'accounts', 'icon' => 'fa-user-gear', 'desc' => 'Manage administrator accounts'],
        ['title' => 'Staff Directory', 'url' => 'staff', 'icon' => 'fa-users', 'desc' => 'Manage team members'],
        ['title' => 'System Reports', 'url' => 'reports', 'icon' => 'fa-chart-line', 'desc' => 'Export statistics & audit log'],
        ['title' => 'General Settings', 'url' => 'settings/general', 'icon' => 'fa-sliders', 'desc' => 'Change site name, logo, & SEO'],
        ['title' => 'Modules Configuration', 'url' => 'settings/module', 'icon' => 'fa-puzzle-piece', 'desc' => 'Enable or disable features'],
        ['title' => 'Mail Integration', 'url' => 'settings/mailsettings', 'icon' => 'fa-paper-plane', 'desc' => 'Configure SMTP email settings'],
        ['title' => 'Integrations & API', 'url' => 'settings/integration', 'icon' => 'fa-link', 'desc' => 'Connect Google & external APIs'],
        ['title' => 'Database Backup', 'url' => 'backup', 'icon' => 'fa-database', 'desc' => 'Export and download SQL backups']
    ];

    // Filter pages
    $matched_pages = [];
    foreach ($pages as $page) {
        if (stripos($page['title'], $query) !== false || stripos($page['desc'], $query) !== false) {
            $matched_pages[] = $page;
        }
    }

    // 2. Search Leads in Database
    $escaped_query = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $query);
    $stmt = $conn->prepare("SELECT id, name, email, phone, service, status FROM leads WHERE name LIKE ? OR email LIKE ? OR phone LIKE ? OR service LIKE ? LIMIT 8");
    $stmt->execute(["%$escaped_query%", "%$escaped_query%", "%$escaped_query%", "%$escaped_query%"]);
    $matched_leads = $stmt->fetchAll();

    echo json_encode([
        'pages' => $matched_pages,
        'leads' => array_map(function($row) {
            return [
                'id' => (int)$row['id'],
                'name' => htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8'),
                'email' => htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8'),
                'phone' => htmlspecialchars($row['phone'], ENT_QUOTES, 'UTF-8'),
                'service' => htmlspecialchars($row['service'], ENT_QUOTES, 'UTF-8'),
                'status' => htmlspecialchars($row['status'], ENT_QUOTES, 'UTF-8'),
                // Never leak the raw enum key to the UI — legacy rows included.
                'status_label' => leadStatusLabel($row['status']),
            ];
        }, $matched_leads)
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
