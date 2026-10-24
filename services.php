<?php
$pageTitle = 'Our Services';
$pageDesc = 'Best digital marketing agency in Pudhucherry offering result-driven SEO, Google Ads, Meta Ads, Social Media, Web Development and Content Marketing services.';
require_once __DIR__ . '/app/config.php';

$services = [];
try {
    if (isset($conn)) {
        $services = $conn->query("SELECT * FROM services WHERE status = 'active' ORDER BY sort_order ASC, id ASC")->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    error_log("[Services] Fetch services failed: " . $e->getMessage());
    $services = [];
}

require_once __DIR__ . '/app/Views/frontend/services.php';

