<?php
/**
 * Individual Service Detail Controller
 * Senior-level dynamic page controller backed 100% by database & admin panel
 * URL Patterns: /service/{slug} | /services/{slug} | /service-detail.php?slug={slug}
 */

require_once __DIR__ . '/app/config.php';

// Extract slug from GET parameter or fallback to REQUEST_URI
$slug = trim($_GET['slug'] ?? '');

if (empty($slug) && !empty($_SERVER['REQUEST_URI'])) {
    $uri_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (preg_match('#^/(?:services?)/([a-zA-Z0-9_-]+)/?$#', $uri_path, $matches)) {
        $slug = trim($matches[1]);
    }
}

if (empty($slug)) {
    header('Location: ' . $base_url . '/services');
    exit;
}

// Fetch active service by slug
$service = null;
try {
    if (isset($conn)) {
        $stmt = $conn->prepare("SELECT * FROM services WHERE slug = ? AND status = 'active' LIMIT 1");
        $stmt->execute([$slug]);
        $service = $stmt->fetch(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    error_log("[Service Detail] Fetch service error: " . $e->getMessage());
}

// Handle 404 if service does not exist or is inactive
if (!$service) {
    http_response_code(404);
    $pageTitle = 'Service Not Found - ' . $site_name;
    $pageDesc = 'The requested service could not be found or has been relocated.';
    if (file_exists(__DIR__ . '/404.php')) {
        include __DIR__ . '/404.php';
    } else {
        echo "<h1>404 - Service Not Found</h1><p><a href='/services'>Return to all services</a></p>";
    }
    exit;
}

// 1. Parse Deliverables & Features (Supports JSON or newline-separated text)
$features = [];
if (!empty($service['features'])) {
    $decoded = json_decode($service['features'], true);
    if (is_array($decoded)) {
        $features = $decoded;
    } else {
        $lines = array_filter(array_map('trim', explode("\n", $service['features'])));
        foreach ($lines as $line) {
            $features[] = ['title' => $line, 'desc' => ''];
        }
    }
}

// 2. Parse Key Benefits (Supports JSON or newline-separated text)
$benefits = [];
if (!empty($service['benefits'])) {
    $decoded = json_decode($service['benefits'], true);
    if (is_array($decoded)) {
        $benefits = $decoded;
    } else {
        $benefits = array_filter(array_map('trim', explode("\n", $service['benefits'])));
    }
}

// 3. Parse Strategic 4-Step Process
$process_steps = [];
if (!empty($service['process_steps'])) {
    $decoded = json_decode($service['process_steps'], true);
    if (is_array($decoded)) {
        $process_steps = $decoded;
    }
}

// 4. Parse Service-Specific FAQs
$service_faqs = [];
if (!empty($service['faqs'])) {
    $decoded = json_decode($service['faqs'], true);
    if (is_array($decoded)) {
        $service_faqs = $decoded;
    }
}

// 5. Query All Other Active Services for Quick Sidebar Navigation
$other_services = [];
try {
    if (isset($conn)) {
        $stmtOther = $conn->query("SELECT id, service_name, slug, icon, short_description, price, image FROM services WHERE status = 'active' ORDER BY created_at ASC");
        $other_services = $stmtOther->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    $other_services = [];
}

// 6. Setup Senior-Level SEO Metadata
$pageTitle = !empty($service['seo_title']) ? $service['seo_title'] : $service['service_name'] . ' - ' . $site_name;
$pageDesc = !empty($service['meta_description']) ? $service['meta_description'] : ($service['short_description'] ?? 'Expert ' . $service['service_name'] . ' services in Pondicherry by Cloud Aspire.');
$metaKeywords = !empty($service['meta_keywords']) ? $service['meta_keywords'] : strtolower($service['service_name']) . ', digital marketing pondicherry, cloud aspire';
$canonicalUrl = $base_url . '/service/' . urlencode($service['slug']);

// Render View Layer
require_once __DIR__ . '/app/Views/frontend/service-detail.php';
