<?php
/**
 * API Endpoint: Fetch Active Testimonials in Real-time via Database
 * URL: /api/testimonials.php
 */
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/../app/config.php';

$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$base_url = $protocol . '://' . $host;

try {
    $stmt = $conn->query("SELECT id, name, designation, message, image, status, created_at FROM testimonials WHERE status = 'active' ORDER BY created_at DESC");
    $testimonials = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

    // Format full image URLs if needed
    foreach ($testimonials as &$t) {
        if (!empty($t['image']) && !str_starts_with($t['image'], 'http://') && !str_starts_with($t['image'], 'https://')) {
            $t['image_url'] = $base_url . '/' . ltrim($t['image'], '/');
        } else {
            $t['image_url'] = $t['image'] ?? null;
        }
    }
    unset($t);

    echo json_encode([
        'success' => true,
        'count' => count($testimonials),
        'data' => $testimonials
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
} catch (Exception $e) {
    error_log('[API Testimonials Error] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Unable to fetch testimonials at this time.'
    ], JSON_UNESCAPED_UNICODE);
}
