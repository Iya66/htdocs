<?php
/**
 * Email History & Outbound Delivery Logs
 * Advanced SaaS Email Monitoring Dashboard
 */
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/email_helper.php';
require_once __DIR__ . '/../app/mail_queue.php';
require_once __DIR__ . '/../app/security_notifier.php';

// Role check (Admin or Manager only)
$stmt = $conn->prepare("SELECT role FROM admin WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$cu = $stmt->fetch();
if (!$cu || !in_array($cu['role'], ['admin', 'manager'])) {
    $_SESSION['error'] = 'Access denied. Privileged role required.';
    header('Location: dashboard.php');
    exit;
}

$message = '';
$message_type = '';
$csrf_token = generateCSRFToken();

// Handle Export CSV
if (isset($_GET['action']) && $_GET['action'] === 'export_csv') {
    if (!verifyCSRFToken($_GET['csrf_token'] ?? '')) {
        die('Invalid security token.');
    }

    $filename = 'email_sent_history_' . date('Ymd_His') . '.csv';
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['Log ID', 'Recipient', 'Subject', 'Status', 'Error Details', 'Sent Timestamp']);

    $stmt = $conn->query("SELECT id, recipient, subject, status, error_message, sent_at FROM mail_logs ORDER BY sent_at DESC");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, [
            $row['id'],
            $row['recipient'],
            $row['subject'],
            strtoupper($row['status']),
            $row['error_message'] ?: 'None',
            $row['sent_at']
        ]);
    }
    fclose($output);
    exit;
}

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid request or expired session. Please refresh and try again.';
        $message_type = 'error';
    } else {
        $act = $_POST['action'];

        // 1. Delete Log Entry
        if ($act === 'delete_log') {
            $logId = (int)($_POST['log_id'] ?? 0);
            if ($logId > 0) {
                $stmt = $conn->prepare("DELETE FROM mail_logs WHERE id = ?");
                $stmt->execute([$logId]);
                logAudit($conn, 'DELETE_MAIL_LOG', "Deleted mail log ID: $logId", $_SESSION['admin_id']);
                $message = "Email log entry #$logId has been deleted.";
                $message_type = 'success';
            }
        }

        // 2. Clear Old Logs (> 30 days)
        elseif ($act === 'clear_old_logs') {
            $stmt = $conn->prepare("DELETE FROM mail_logs WHERE sent_at < DATE_SUB(NOW(), INTERVAL 30 DAY)");
            $stmt->execute();
            $deleted = $stmt->rowCount();
            logAudit($conn, 'PURGE_MAIL_LOGS', "Purged $deleted old mail logs (> 30 days)", $_SESSION['admin_id']);
            $message = "Cleaned up $deleted email logs older than 30 days.";
            $message_type = 'success';
        }

        // 3. Resend Email
        elseif ($act === 'resend_email') {
            $logId = (int)($_POST['log_id'] ?? 0);
            $stmt = $conn->prepare("SELECT * FROM mail_logs WHERE id = ?");
            $stmt->execute([$logId]);
            $log = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($log) {
                // Find matching body from mail_queue
                $stmtQ = $conn->prepare("SELECT body, alt_body FROM mail_queue WHERE recipient = ? AND subject = ? ORDER BY id DESC LIMIT 1");
                $stmtQ->execute([$log['recipient'], $log['subject']]);
                $qMatch = $stmtQ->fetch(PDO::FETCH_ASSOC);

                $bodyToSend = $qMatch['body'] ?? "<p>Resent notification: " . htmlspecialchars($log['subject']) . "</p>";
                $res = sendEmail($log['recipient'], $log['subject'], $bodyToSend, $qMatch['alt_body'] ?? '', false);

                if (!empty($res['success'])) {
                    logAudit($conn, 'RESEND_EMAIL', "Resent email to: " . $log['recipient'] . " (Subject: " . $log['subject'] . ")", $_SESSION['admin_id']);
                    $message = "Email successfully resent to " . htmlspecialchars($log['recipient']) . "!";
                    $message_type = 'success';
                } else {
                    $message = "Failed to resend email: " . ($res['message'] ?? 'SMTP error');
                    $message_type = 'error';
                }
            } else {
                $message = "Email log not found.";
                $message_type = 'error';
            }
        }

        // 4. Trigger Mail Queue Worker
        elseif ($act === 'process_queue') {
            $workerRes = processMailQueue($conn, 25);
            $processedCount = $workerRes['processed'] ?? 0;
            $sentCount = $workerRes['sent'] ?? 0;
            $failedCount = $workerRes['failed'] ?? 0;

            logAudit($conn, 'PROCESS_MAIL_QUEUE', "Manually ran mail queue worker: $processedCount processed, $sentCount sent, $failedCount failed", $_SESSION['admin_id']);
            $message = "Mail queue processed: $sentCount sent, $failedCount failed ($processedCount total).";
            $message_type = $failedCount === 0 ? 'success' : 'warning';
        }

        // 5. Retry Failed Queue Emails
        elseif ($act === 'retry_failed_queue') {
            $retryCount = retryFailedEmails($conn);
            logAudit($conn, 'RETRY_FAILED_EMAILS', "Reset $retryCount failed queue emails to pending", $_SESSION['admin_id']);
            $message = "Reset $retryCount failed email(s) for immediate retry!";
            $message_type = 'success';
        }
    }
}

// -------------------------------------------------------------
// Filters & Search
// -------------------------------------------------------------
$filter_status = $_GET['status'] ?? 'all';
$filter_search = trim($_GET['search'] ?? '');
$filter_from   = trim($_GET['from'] ?? '');
$filter_to     = trim($_GET['to'] ?? '');

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

$where = [];
$params = [];

if ($filter_status !== 'all' && in_array($filter_status, ['sent', 'failed', 'pending'])) {
    $where[] = "status = ?";
    $params[] = $filter_status;
}

if (!empty($filter_search)) {
    $where[] = "(recipient LIKE ? OR subject LIKE ? OR error_message LIKE ?)";
    $searchWildcard = "%$filter_search%";
    $params[] = $searchWildcard;
    $params[] = $searchWildcard;
    $params[] = $searchWildcard;
}

if (!empty($filter_from)) {
    $where[] = "sent_at >= ?";
    $params[] = $filter_from . ' 00:00:00';
}

if (!empty($filter_to)) {
    $where[] = "sent_at <= ?";
    $params[] = $filter_to . ' 23:59:59';
}

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Count total matching logs
$countStmt = $conn->prepare("SELECT COUNT(*) FROM mail_logs $whereClause");
$countStmt->execute($params);
$total_logs = (int)$countStmt->fetchColumn();
$total_pages = ceil($total_logs / $per_page);

// Fetch paginated logs
$logQuery = "SELECT * FROM mail_logs $whereClause ORDER BY sent_at DESC LIMIT $per_page OFFSET $offset";
$stmt = $conn->prepare($logQuery);
$stmt->execute($params);
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// -------------------------------------------------------------
// System KPI Statistics
// -------------------------------------------------------------
$stats = [
    'total_sent' => 0,
    'total_failed' => 0,
    'sent_today' => 0,
    'sent_week' => 0,
    'success_rate' => '100%',
    'queue_pending' => 0,
    'queue_failed' => 0
];

try {
    $stats['total_sent'] = (int)$conn->query("SELECT COUNT(*) FROM mail_logs WHERE status = 'sent'")->fetchColumn();
    $stats['total_failed'] = (int)$conn->query("SELECT COUNT(*) FROM mail_logs WHERE status = 'failed'")->fetchColumn();
    $stats['sent_today'] = (int)$conn->query("SELECT COUNT(*) FROM mail_logs WHERE status = 'sent' AND DATE(sent_at) = CURDATE()")->fetchColumn();
    $stats['sent_week'] = (int)$conn->query("SELECT COUNT(*) FROM mail_logs WHERE status = 'sent' AND sent_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetchColumn();

    $grandTotal = $stats['total_sent'] + $stats['total_failed'];
    if ($grandTotal > 0) {
        $stats['success_rate'] = round(($stats['total_sent'] / $grandTotal) * 100, 1) . '%';
    }

    $qStats = getMailQueueStats($conn);
    $stats['queue_pending'] = $qStats['pending'] ?? 0;
    $stats['queue_failed'] = $qStats['failed'] ?? 0;
} catch (Exception $se) {}

// Load View
require_once __DIR__ . '/../app/Views/admin/mail_logs.php';
