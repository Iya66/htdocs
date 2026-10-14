<?php
/**
 * Single Blog Detail Controller
 * URL: /blog/{slug} | /blog-detail.php?slug={slug}
 */

require_once __DIR__ . '/app/config.php';

$slug = trim($_GET['slug'] ?? '');

if (empty($slug) && !empty($_SERVER['REQUEST_URI'])) {
    $uri_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (preg_match('#^/(?:blogs?)/([a-zA-Z0-9_-]+)/?$#', $uri_path, $matches)) {
        $slug = trim($matches[1]);
    }
}

if (empty($slug)) {
    header('Location: ' . $base_url . '/blog');
    exit;
}

$blog = null;
$recent_blogs = [];
$categories = [];

if (isset($conn)) {
    try {
        // Fetch article
        $stmt = $conn->prepare("SELECT * FROM blogs WHERE slug = ? AND status = 'published' LIMIT 1");
        $stmt->execute([$slug]);
        $blog = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($blog) {
            // Increment view count asynchronously
            $upStmt = $conn->prepare("UPDATE blogs SET views_count = views_count + 1 WHERE id = ?");
            $upStmt->execute([$blog['id']]);

            // Fetch recent posts
            $recStmt = $conn->prepare("SELECT id, title, slug, image, published_date, category FROM blogs WHERE id != ? AND status = 'published' ORDER BY published_date DESC, id DESC LIMIT 5");
            $recStmt->execute([$blog['id']]);
            $recent_blogs = $recStmt->fetchAll(PDO::FETCH_ASSOC);

            // Fetch categories with counts
            $catStmt = $conn->query("SELECT category, COUNT(*) as count FROM blogs WHERE status = 'published' AND category IS NOT NULL GROUP BY category ORDER BY count DESC LIMIT 6");
            $categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (Exception $e) {
        error_log("[Blog Detail] Error: " . $e->getMessage());
    }
}

if (!$blog) {
    http_response_code(404);
    $pageTitle = 'Blog Post Not Found - ' . $site_name;
    $pageDesc = 'The requested blog article could not be found or has been moved.';
    if (file_exists(__DIR__ . '/404.php')) {
        include __DIR__ . '/404.php';
    } else {
        echo "<h1>404 - Article Not Found</h1><p><a href='/blog'>Return to Blog</a></p>";
    }
    exit;
}

// SEO Metadata
$pageTitle = !empty($blog['seo_title']) ? $blog['seo_title'] : $blog['title'] . ' | ' . $site_name;
$pageDesc = !empty($blog['meta_description']) ? $blog['meta_description'] : ($blog['excerpt'] ?? substr(strip_tags($blog['content']), 0, 160));
$metaKeywords = !empty($blog['meta_keywords']) ? $blog['meta_keywords'] : strtolower($blog['category']) . ', digital marketing blog, cloud aspire';
$canonicalUrl = $base_url . '/blog/' . urlencode($blog['slug']);

// Render View
require_once __DIR__ . '/app/Views/frontend/blog-detail.php';
