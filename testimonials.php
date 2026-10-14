<?php
require_once __DIR__ . '/app/config.php';
$pageTitle = 'Testimonials';
$pageDesc = 'Watch video testimonials and success stories from ' . $site_name . ' clients.';

$testimonials = [];
try {
    $stmt = $conn->query("SELECT * FROM testimonials WHERE status = 'active' ORDER BY created_at DESC");
    $testimonials = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
} catch (PDOException $e) {
    error_log("Error fetching testimonials from database: " . $e->getMessage());
    $testimonials = [];
}

// Fetch dynamic database counts & metrics directly from MySQL tables (Zero hardcoding)
$db_leads = 0;
$db_projects = 0;
$stat_satisfaction = '0%';
$stat_growth = '0%';

try {
    // 1. Enterprise & Growth Partners (from `leads` table)
    $db_leads = (int)$conn->query("SELECT COUNT(*) FROM leads")->fetchColumn();
    
    // 2. Successful Projects (from `projects` table)
    $db_projects = (int)$conn->query("SELECT COUNT(*) FROM projects")->fetchColumn();

    // 3. Client Satisfaction Rate (from `reviews` table)
    $db_avg_rating = $conn->query("SELECT AVG(star_rating) FROM reviews WHERE status = 'approved' OR status IS NULL")->fetchColumn();
    if ($db_avg_rating !== null && (float)$db_avg_rating > 0) {
        $stat_satisfaction = round(((float)$db_avg_rating / 5) * 100) . '%';
    }

    // 4. Average Client Growth (dynamically calculated from `projects.results` in database)
    $pResults = $conn->query("SELECT results FROM projects WHERE results IS NOT NULL AND results != ''")->fetchAll(PDO::FETCH_COLUMN);
    $growth_rates = [];
    foreach ($pResults as $res) {
        if (preg_match('/(\d+)\s*%/', $res, $matches)) {
            $growth_rates[] = (int)$matches[1];
        } elseif (preg_match('/([\d\.]+)\s*x/i', $res, $matches)) {
            $growth_rates[] = (int)(round(((float)$matches[1] - 1) * 100));
        }
    }
    if (!empty($growth_rates)) {
        $stat_growth = round(array_sum($growth_rates) / count($growth_rates)) . '%';
    }
} catch (Exception $e) {
    error_log("Error calculating testimonial stats: " . $e->getMessage());
}

$stat_partners = $db_leads > 0 ? $db_leads . '+' : '0+';
$stat_projects = $db_projects > 0 ? $db_projects . '+' : '0+';

require_once __DIR__ . '/app/Views/frontend/testimonials.php';

