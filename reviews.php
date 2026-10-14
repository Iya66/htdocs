<?php
require_once __DIR__ . '/app/config.php';
require_once __DIR__ . '/app/email_helper.php';
require_once __DIR__ . '/app/security_notifier.php';
require_once __DIR__ . '/app/whatsapp_helper.php';

$pageTitle = 'Client Reviews';
$pageDesc = 'Read what our clients say about working with ' . $site_name . '.';

$msg_status = '';
$msg_text = '';
$client_ip = getClientIP();
$is_ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') 
    || isset($_POST['ajax']) 
    || (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'));

// Handle Review Submission
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_review') {
    // 1. Rate Limiting: max 5 reviews per IP per hour
    $is_rate_limited = false;
    try {
        if (isset($conn)) {
            $stmt = $conn->prepare("SELECT count FROM rate_limits WHERE identifier = ? AND type = 'review_form' AND updated_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)");
            $stmt->execute([$client_ip]);
            $rateCount = (int)$stmt->fetchColumn();
            if ($rateCount >= 5) {
                $is_rate_limited = true;
                $msg_status = 'error';
                $msg_text = 'You have submitted several reviews recently. Please wait a little while before posting again.';
            }
        }
    } catch (Exception $e) {
        error_log('[Reviews] Rate limit check failed: ' . $e->getMessage());
    }

    if ($is_rate_limited) {
        // Handled above
    } elseif (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $msg_status = 'error';
        $msg_text = 'Invalid security token or session expired. Please refresh the page and try again.';
    } else {
        $name = sanitize($_POST['name'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $star_rating = (int)($_POST['star_rating'] ?? 5);
        $message = sanitize($_POST['message'] ?? '');
        $designation = sanitize($_POST['designation'] ?? '');
        $company = sanitize($_POST['company'] ?? '');

        // Bound star rating between 1 and 5
        if ($star_rating < 1 || $star_rating > 5) {
            $star_rating = 5;
        }

        if (empty($name) || empty($email) || empty($message)) {
            $msg_status = 'error';
            $msg_text = 'Please fill in all required fields (Name, Email, and Review Message).';
        } elseif (!validateEmail($email)) {
            $msg_status = 'error';
            $msg_text = 'Please provide a valid email address so we can send your confirmation note.';
        } else {
            try {
                $stmt = $conn->prepare("
                    INSERT INTO reviews (name, email, star_rating, rating, message, review_text, designation, company, status, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
                ");
                $stmt->execute([
                    $name,
                    $email,
                    $star_rating,
                    (string)$star_rating,
                    $message,
                    $message,
                    $designation ?: null,
                    $company ?: null
                ]);
                $review_id = (int)$conn->lastInsertId();

                // Track Rate Limit
                try {
                    $conn->prepare("
                        INSERT INTO rate_limits (identifier, type, count, updated_at) 
                        VALUES (?, 'review_form', 1, NOW()) 
                        ON DUPLICATE KEY UPDATE count = count + 1, updated_at = NOW()
                    ")->execute([$client_ip]);
                } catch (Exception $re) {}

                // Send Thank You Email to Reviewer
                try {
                    sendReviewThankYouEmail($email, $name, $star_rating);
                } catch (Throwable $emEx) {
                    error_log('[Reviews] Thank you email failed: ' . $emEx->getMessage());
                }

                // Send Notification Email to Admin
                try {
                    $review_data = [
                        'id' => $review_id,
                        'name' => $name,
                        'email' => $email,
                        'star_rating' => $star_rating,
                        'rating' => $star_rating,
                        'message' => $message,
                        'designation' => $designation,
                        'company' => $company
                    ];
                    sendReviewNotificationEmail('create', $review_data);
                    
                    // Send WhatsApp Review Alert to Admin
                    sendReviewWhatsAppAlert($review_data);
                } catch (Throwable $emAdminEx) {
                    error_log('[Reviews] Admin notification failed: ' . $emAdminEx->getMessage());
                }

                // Security & Audit logging
                logAudit($conn, 'CREATE_REVIEW', "User submitted review (pending): $name ($email, $star_rating stars)", null);
                notifySecurity('CREATE_REVIEW', "Review submitted by client (pending): $name ($email)", null);

                $msg_status = 'success';
                $msg_text = 'Thank you so much for your review, ' . htmlspecialchars($name) . '! Your review has been submitted and will be published after moderation. A confirmation note has also been sent to ' . htmlspecialchars($email) . '.';

                if ($is_ajax) {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => true,
                        'message' => $msg_text,
                        'review' => [
                            'id' => $review_id,
                            'name' => htmlspecialchars($name),
                            'email' => htmlspecialchars($email),
                            'star_rating' => $star_rating,
                            'message' => htmlspecialchars($message),
                            'designation' => htmlspecialchars($designation),
                            'company' => htmlspecialchars($company),
                            'date' => date('M Y')
                        ]
                    ]);
                    exit;
                }

                $_POST = [];
            } catch (PDOException $e) {
                $msg_status = 'error';
                $msg_text = 'Unable to post your review at this time. Please try again shortly.';
                error_log('[Reviews] Submission error: ' . $e->getMessage());
            }
        }
    }

    if ($is_ajax && $msg_status === 'error') {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => $msg_text
        ]);
        exit;
    }
}

// Fetch all active/approved reviews
$reviews = [];
try {
    $stmt = $conn->query("SELECT * FROM reviews WHERE status = 'approved' OR status IS NULL ORDER BY created_at DESC");
    $reviews = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
} catch (PDOException $e) {
    error_log("Error fetching reviews: " . $e->getMessage());
    $reviews = [];
}

// Compute live metrics dynamically from database tables (Zero hardcoding)
$review_count = 0;
$db_avg_rating = 0.0;
$positive_count = 0;
$satisfaction_percentage = 0;
$retention_percentage = 0;

try {
    // 1. Live Reviews Table Aggregation (Average Rating, Satisfaction Rate, Positive Feedback)
    $statRow = $conn->query("
        SELECT 
            COUNT(*) as total_reviews,
            AVG(star_rating) as avg_rating,
            SUM(CASE WHEN star_rating >= 4 THEN 1 ELSE 0 END) as positive_reviews
        FROM reviews 
        WHERE status = 'approved' OR status IS NULL
    ")->fetch(PDO::FETCH_ASSOC);

    if ($statRow) {
        $review_count = (int)($statRow['total_reviews'] ?? 0);
        if ($statRow['avg_rating'] !== null && (float)$statRow['avg_rating'] > 0) {
            $db_avg_rating = (float)$statRow['avg_rating'];
            $satisfaction_percentage = round(($db_avg_rating / 5) * 100);
        }
        $positive_count = (int)($statRow['positive_reviews'] ?? 0);
        if ($review_count > 0) {
            $retention_percentage = round(($positive_count / $review_count) * 100);
        }
    }
} catch (Exception $e) {
    error_log("Error calculating review stats from DB: " . $e->getMessage());
}

// 2. Live Happy Clients directly from the `leads` table in Database (matching home.php & why-choose-us.php)
$db_leads = 0;
try {
    $db_leads = (int)$conn->query("SELECT COUNT(*) FROM leads")->fetchColumn();
} catch (Exception $e) {
    error_log("Error fetching leads count from DB: " . $e->getMessage());
}

$stat_clients = $db_leads;
$avg_rating_display = $db_avg_rating > 0 ? number_format($db_avg_rating, 1) : '0.0';
$stat_satisfaction = $satisfaction_percentage . '%';
$stat_retention = $retention_percentage . '%';
$csrf_token = generateCSRFToken();

require_once __DIR__ . '/app/Views/frontend/reviews.php';
