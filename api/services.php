<?php
/**
 * API Endpoint: Fetch Active Services in Real-time
 * URL: /api/services.php
 */
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/../app/config.php';

try {
    $stmt = $conn->query("SELECT id, service_name, slug, short_description, price, icon, image, status FROM services WHERE status = 'active' ORDER BY created_at ASC");
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'count' => count($services),
        'data' => $services
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
} catch (Exception $e) {
    error_log('[API Services Error] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Unable to fetch services at this time.'
    ], JSON_UNESCAPED_UNICODE);
}
