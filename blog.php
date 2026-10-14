<?php
/**
 * Root Blog Listing Controller
 * CherriDM Format - 100% Dynamically Controlled via MySQL & Admin Panel
 * URL: /blog | /blogs | /blog.php
 */

require_once __DIR__ . '/app/config.php';

$site_name = !empty($settings['site_name']) ? $settings['site_name'] : 'Cloud Aspire';
$pageTitle = 'Our Blog - Best Digital Marketing Strategies & Growth Tips | ' . $site_name;
$pageDesc = 'Explore expert digital marketing tips, SEO strategies, Google & Meta Ads benchmarks, and growth insights from ' . $site_name . '’s official blog.';
$metaKeywords = 'digital marketing blog pondicherry, seo blog, google ads tips, social media growth strategies';

$selected_category = trim($_GET['category'] ?? '');
$search_query = trim($_GET['q'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 9;
$offset = ($page - 1) * $per_page;

$blogs = [];
$total_blogs = 0;
$categories = [];

if (isset($conn)) {
    try {
        // Fetch categories with active post count
        $catStmt = $conn->query("
            SELECT category, COUNT(*) as count 
            FROM blogs 
            WHERE status = 'published' AND category IS NOT NULL AND category != ''
            GROUP BY category 
            ORDER BY count DESC
        ");
        $categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);

        // Build dynamic query
        $where_clauses = ["status = 'published'"];
        $params = [];

        if (!empty($selected_category)) {
            $where_clauses[] = "category = ?";
            $params[] = $selected_category;
        }

        if (!empty($search_query)) {
            $where_clauses[] = "(title LIKE ? OR excerpt LIKE ? OR content LIKE ?)";
            $params[] = "%$search_query%";
            $params[] = "%$search_query%";
            $params[] = "%$search_query%";
        }

        $where_sql = implode(' AND ', $where_clauses);

        // Count total
        $countStmt = $conn->prepare("SELECT COUNT(*) FROM blogs WHERE $where_sql");
        $countStmt->execute($params);
        $total_blogs = (int)$countStmt->fetchColumn();

        // Fetch paginated blogs
        $sql = "SELECT id, title, slug, category, author, image, excerpt, published_date, views_count 
                FROM blogs 
                WHERE $where_sql 
                ORDER BY published_date DESC, id DESC 
                LIMIT $per_page OFFSET $offset";
        $blogStmt = $conn->prepare($sql);
        $blogStmt->execute($params);
        $blogs = $blogStmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (Exception $e) {
        error_log("[Blog Controller] Query error: " . $e->getMessage());
        $blogs = [];
        $total_blogs = 0;
    }
}

$total_pages = ceil($total_blogs / $per_page);

// Render Frontend View
require_once __DIR__ . '/app/Views/frontend/blog.php';
