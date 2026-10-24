<?php
require_once __DIR__ . '/../../app/config.php';
header('Content-Type: application/json');

/**
 * Format a timestamp as a relative "… ago" string.
 *
 * Declared before first use and wrapped in a function_exists guard, because
 * this endpoint can be included more than once in a process and an unguarded
 * redeclaration (or a call before declaration) is a fatal error.
 */
if (!function_exists('timeAgo')) {
    function timeAgo(string $datetime): string
    {
        $time = strtotime($datetime);
        $diff = time() - $time;

        if ($diff < 5)    return 'Just now';
        if ($diff < 60)   return $diff . ' secs ago';
        if ($diff < 3600) return round($diff / 60) . ' mins ago';
        if ($diff < 86400) return round($diff / 3600) . ' hours ago';

        return date('M d, Y', $time);
    }
}

if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if (!isset($_SESSION['read_notifications'])) {
    $_SESSION['read_notifications'] = [];
}

$action = $_GET['action'] ?? 'get';

try {
    // 1. Fetch recent leads
    $stmt = $conn->prepare("SELECT id, name, service, created_at FROM leads ORDER BY created_at DESC LIMIT 10");
    $stmt->execute();
    $leads = $stmt->fetchAll();
    
    $feed = [];
    foreach ($leads as $l) {
        $feed[] = [
            'id' => 'lead_' . $l['id'],
            'title' => 'New Lead Received',
            'details' => $l['name'] . ' requested ' . ($l['service'] ?: 'General Inquiry'),
            'time' => $l['created_at'],
            'icon' => 'fa-user-group',
            'color' => 'var(--primary)',
            'link' => 'leads.php'
        ];
    }

    // 2. Fetch recent reviews
    $stmt = $conn->prepare("SELECT id, name, star_rating, created_at FROM reviews ORDER BY created_at DESC LIMIT 10");
    $stmt->execute();
    $reviews = $stmt->fetchAll();
    foreach ($reviews as $r) {
        $feed[] = [
            'id' => 'review_' . $r['id'],
            'title' => 'New Review Submitted',
            'details' => $r['name'] . ' rated ' . $r['star_rating'] . ' stars',
            'time' => $r['created_at'],
            'icon' => 'fa-star',
            'color' => '#fbbf24',
            'link' => 'reviews.php'
        ];
    }

    // 3. Fetch recent admin activities
    // admin_activities.user_id references the `admin` table — the same join
    // used by dashboard.php, activity.php and staff.php. Joining `accounts`
    // here left every activity notification attributed to "System".
    $stmt = $conn->prepare("SELECT a.id, a.action, a.details, a.created_at, acc.name as admin_name FROM admin_activities a LEFT JOIN admin acc ON a.user_id = acc.id ORDER BY a.created_at DESC LIMIT 10");
    $stmt->execute();
    $activities = $stmt->fetchAll();
    foreach ($activities as $act) {
        $feed[] = [
            'id' => 'activity_' . $act['id'],
            'title' => 'System Activity',
            'details' => ($act['admin_name'] ?: 'System') . ': ' . $act['details'],
            'time' => $act['created_at'],
            'icon' => 'fa-clock-rotate-left',
            'color' => '#10b981',
            'link' => 'reports.php'
        ];
    }

    // Sort combined feed by time descending
    usort($feed, function($a, $b) {
        return strcmp($b['time'], $a['time']);
    });

    // Limit to top 15 notifications
    $feed = array_slice($feed, 0, 15);

    // Handle Actions
    if ($action === 'read_all') {
        foreach ($feed as $item) {
            if (!in_array($item['id'], $_SESSION['read_notifications'])) {
                $_SESSION['read_notifications'][] = $item['id'];
            }
        }
    } elseif ($action === 'read_one') {
        $notif_id = $_GET['id'] ?? '';
        if ($notif_id && !in_array($notif_id, $_SESSION['read_notifications'])) {
            $_SESSION['read_notifications'][] = $notif_id;
        }
    }

    // Recalculate read state & unread count
    $unread_count = 0;
    foreach ($feed as &$item) {
        $item['is_read'] = in_array($item['id'], $_SESSION['read_notifications']);
        if (!$item['is_read']) {
            $unread_count++;
        }
        $item['time_ago'] = timeAgo($item['time']);
    }

    echo json_encode([
        'notifications' => $feed,
        'unread_count' => $unread_count
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
