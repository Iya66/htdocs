<?php
/**
 * Admin Reviews Management - Full Moderation Workflow
 */
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/email_helper.php';
require_once __DIR__ . '/../app/security_notifier.php';

$message = '';
$message_type = '';
$userRole = $_SESSION['admin_role'] ?? 'viewer';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (in_array($userRole, ['viewer'])) {
        $message = 'Access denied. You have view-only privileges.';
        $message_type = 'error';
    } elseif (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid request. Please refresh and try again.';
        $message_type = 'error';
    } else {
        $action = $_POST['action'];
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

        switch ($action) {
            case 'create':
                $review_name = trim($_POST['name'] ?? '');
                $review_email = trim($_POST['email'] ?? '');
                $status = in_array($_POST['status'] ?? '', ['approved', 'pending', 'rejected']) ? $_POST['status'] : 'approved';
                $designation = trim($_POST['designation'] ?? '');
                $company = trim($_POST['company'] ?? '');
                $starRating = (int)$_POST['star_rating'];
                $reviewMsg = $_POST['message'] ?? '';

                $stmt = $conn->prepare("INSERT INTO reviews (name, email, star_rating, rating, message, review_text, designation, company, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$review_name, $review_email ?: null, $starRating, $starRating, $reviewMsg, $reviewMsg, $designation ?: null, $company ?: null, $status]);
                $newReviewId = (int)$conn->lastInsertId();

                logAudit($conn, 'CREATE_REVIEW', "Created review: " . $review_name . " ($status)", $_SESSION['admin_id']);
                notifySecurity('CREATE_REVIEW', "Created review: $review_name (" . $starRating . " stars, $status)", $_SESSION['admin_id']);

                if (!empty($review_email) && filter_var($review_email, FILTER_VALIDATE_EMAIL)) {
                    try {
                        sendReviewThankYouEmail($review_email, $review_name, $starRating);
                    } catch (Exception $e) {
                        error_log('[Admin Reviews] Thank you email failed: ' . $e->getMessage());
                    }
                }

                $message = 'Review created successfully!';
                $message_type = 'success';
                break;

            case 'update':
                $status = in_array($_POST['status'] ?? '', ['approved', 'pending', 'rejected']) ? $_POST['status'] : 'approved';
                $designation = trim($_POST['designation'] ?? '');
                $company = trim($_POST['company'] ?? '');

                $stmt = $conn->prepare("UPDATE reviews SET name = ?, email = ?, star_rating = ?, rating = ?, message = ?, review_text = ?, designation = ?, company = ?, status = ? WHERE id = ?");
                $stmt->execute([trim($_POST['name'] ?? ''), $_POST['email'] ?: null, (int)$_POST['star_rating'], (int)$_POST['star_rating'], $_POST['message'], $_POST['message'], $designation ?: null, $company ?: null, $status, $id]);
                logAudit($conn, 'UPDATE_REVIEW', "Updated review ID: $id ($status)", $_SESSION['admin_id']);
                notifySecurity('UPDATE_REVIEW', "Review ID $id updated (" . (int)$_POST['star_rating'] . " stars, $status)", $_SESSION['admin_id']);
                $message = 'Review updated successfully!';
                $message_type = 'success';
                break;

            case 'approve':
                $stmt = $conn->prepare("SELECT * FROM reviews WHERE id = ?");
                $stmt->execute([$id]);
                $rev = $stmt->fetch(PDO::FETCH_ASSOC);

                $stmt = $conn->prepare("UPDATE reviews SET status = 'approved' WHERE id = ?");
                $stmt->execute([$id]);
                logAudit($conn, 'APPROVE_REVIEW', "Approved review ID: $id", $_SESSION['admin_id']);

                if ($rev && !empty($rev['email']) && filter_var($rev['email'], FILTER_VALIDATE_EMAIL)) {
                    try {
                        sendReviewApprovedEmail($rev['email'], $rev['name'], (int)$rev['star_rating'], $rev['message'] ?? '');
                    } catch (Exception $ae) {
                        error_log('[Admin Reviews] Approval email failed: ' . $ae->getMessage());
                    }
                }

                $message = 'Review approved and published live!';
                $message_type = 'success';
                break;

            case 'reject':
                $stmt = $conn->prepare("UPDATE reviews SET status = 'rejected' WHERE id = ?");
                $stmt->execute([$id]);
                logAudit($conn, 'REJECT_REVIEW', "Rejected review ID: $id", $_SESSION['admin_id']);
                $message = 'Review rejected.';
                $message_type = 'success';
                break;

            case 'delete':
                $stmt = $conn->prepare("DELETE FROM reviews WHERE id = ?");
                $stmt->execute([$id]);
                logAudit($conn, 'DELETE_REVIEW', "Deleted review ID: $id", $_SESSION['admin_id']);
                notifySecurity('DELETE_REVIEW', "Review ID $id deleted", $_SESSION['admin_id']);
                $message = 'Review deleted successfully!';
                $message_type = 'success';
                break;

            case 'bulk_delete':
                $ids = $_POST['bulk_ids'] ?? [];
                if (!empty($ids) && is_array($ids)) {
                    $placeholders = implode(',', array_fill(0, count($ids), '?'));
                    $intIds = array_map('intval', $ids);
                    $stmt = $conn->prepare("DELETE FROM reviews WHERE id IN ($placeholders)");
                    $stmt->execute($intIds);
                    $count = $stmt->rowCount();
                    logAudit($conn, 'BULK_DELETE_REVIEWS', "Bulk deleted $count reviews: " . implode(',', $intIds), $_SESSION['admin_id']);
                    notifySecurity('BULK_DELETE_REVIEWS', "$count reviews bulk deleted", $_SESSION['admin_id']);
                    $message = $count . ' review(s) deleted successfully!';
                    $message_type = 'success';
                }
                break;

            case 'bulk_update_status':
                $ids = $_POST['bulk_ids'] ?? [];
                $status = in_array($_POST['status'] ?? '', ['approved', 'pending', 'rejected']) ? $_POST['status'] : '';
                if (!empty($ids) && is_array($ids) && !empty($status)) {
                    $placeholders = implode(',', array_fill(0, count($ids), '?'));
                    $intIds = array_map('intval', $ids);
                    $params = array_merge([$status], $intIds);
                    $stmt = $conn->prepare("UPDATE reviews SET status = ? WHERE id IN ($placeholders)");
                    $stmt->execute($params);
                    $count = $stmt->rowCount();
                    logAudit($conn, 'BULK_UPDATE_REVIEWS', "Bulk updated $count reviews to status: $status", $_SESSION['admin_id']);
                    notifySecurity('BULK_UPDATE_REVIEWS', "$count reviews updated to $status", $_SESSION['admin_id']);
                    $message = $count . ' review(s) updated to ' . ucfirst($status) . '!';
                    $message_type = 'success';
                }
                break;
        }
    }
}

$reviews = $conn->query("SELECT * FROM reviews ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
$csrf_token = generateCSRFToken();

require_once __DIR__ . '/../app/Views/admin/reviews.php';
