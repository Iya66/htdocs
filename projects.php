<?php
$pageTitle = 'Our Projects';
$pageDesc = 'Explore the success portfolio of the best digital marketing agency in Pudhucherry.';
require_once __DIR__ . '/app/config.php';

try {
    $stmt = $conn->query("SELECT * FROM projects WHERE status != 'cancelled' ORDER BY COALESCE(project_datetime, created_at) DESC");
    $projects = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
} catch (PDOException $e) {
    error_log("Error fetching projects: " . $e->getMessage());
    $projects = [];
}

function getProjectCategory(array|string $project): string {
    if (is_array($project)) {
        if (!empty($project['category'])) {
            $cat = strtolower(trim($project['category']));
            if (strpos($cat, 'seo') !== false || strpos($cat, 'search') !== false) return 'seo';
            if (strpos($cat, 'social') !== false || strpos($cat, 'smm') !== false || strpos($cat, 'media') !== false || strpos($cat, 'brand') !== false || strpos($cat, 'instagram') !== false) return 'social';
            if (strpos($cat, 'ad') !== false || strpos($cat, 'ppc') !== false || strpos($cat, 'google') !== false || strpos($cat, 'meta') !== false) return 'ads';
            if (strpos($cat, 'web') !== false || strpos($cat, 'dev') !== false || strpos($cat, 'design') !== false || strpos($cat, 'app') !== false) return 'web';
            return $cat;
        }
        $name = strtolower(($project['project_name'] ?? '') . ' ' . ($project['description'] ?? ''));
    } else {
        $name = strtolower((string)$project);
    }

    if (strpos($name, 'seo') !== false || strpos($name, 'search') !== false || strpos($name, 'ranking') !== false) {
        return 'seo';
    } elseif (strpos($name, 'social') !== false || strpos($name, 'instagram') !== false || strpos($name, 'facebook') !== false || strpos($name, 'brand') !== false || strpos($name, 'content') !== false || strpos($name, 'smm') !== false) {
        return 'social';
    } elseif (strpos($name, 'ads') !== false || strpos($name, 'ad ') !== false || strpos($name, 'google') !== false || strpos($name, 'meta') !== false || strpos($name, 'ppc') !== false || strpos($name, 'roas') !== false) {
        return 'ads';
    } else {
        return 'web';
    }
}

function getProjectIndustry(array|string $project): string {
    if (is_array($project)) {
        if (!empty($project['industry'])) {
            return trim($project['industry']);
        }
        $name = strtolower(($project['project_name'] ?? '') . ' ' . ($project['description'] ?? ''));
    } else {
        $name = strtolower((string)$project);
    }

    if (strpos($name, 'e-commerce') !== false || strpos($name, 'shop') !== false || strpos($name, 'store') !== false || strpos($name, 'retail') !== false || strpos($name, 'fashion') !== false) {
        return 'E-Commerce';
    } elseif (strpos($name, 'restaurant') !== false || strpos($name, 'food') !== false || strpos($name, 'cafe') !== false || strpos($name, 'bistro') !== false) {
        return 'Food & Beverage';
    } elseif (strpos($name, 'estate') !== false || strpos($name, 'property') !== false || strpos($name, 'living') !== false || strpos($name, 'villa') !== false) {
        return 'Real Estate';
    } elseif (strpos($name, 'saas') !== false || strpos($name, 'software') !== false || strpos($name, 'tech') !== false || strpos($name, 'cloud') !== false) {
        return 'Technology';
    } elseif (strpos($name, 'clinic') !== false || strpos($name, 'hospital') !== false || strpos($name, 'health') !== false || strpos($name, 'medical') !== false || strpos($name, 'care') !== false) {
        return 'Healthcare';
    } else {
        return 'Business Services';
    }
}

require_once __DIR__ . '/app/Views/frontend/projects.php';
