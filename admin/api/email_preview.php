<?php
/**
 * Admin API - Fetch Email Content and Logs Preview for Modal View
 */
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../../app/config.php';

header('Content-Type: application/json; charset=utf-8');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$type = $_GET['type'] ?? 'log'; // 'log' or 'queue'

if ($id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid email ID']);
    exit;
}

try {
    if ($type === 'queue') {
        $stmt = $conn->prepare("SELECT * FROM mail_queue WHERE id = ?");
        $stmt->execute([$id]);
        $email = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$email) {
            echo json_encode(['success' => false, 'error' => 'Queued email not found']);
            exit;
        }

        echo json_encode([
            'success' => true,
            'data' => [
                'id' => (int)$email['id'],
                'recipient' => htmlspecialchars($email['recipient']),
                'subject' => htmlspecialchars($email['subject']),
                'status' => $email['status'],
                'attempts' => (int)$email['attempts'],
                'max_attempts' => (int)$email['max_attempts'],
                'error_message' => $email['error_message'],
                'created_at' => $email['created_at'],
                'processed_at' => $email['processed_at'],
                'body_html' => $email['body'],
                'alt_body' => $email['alt_body']
            ]
        ]);
        exit;
    }

    // Default: Fetch from mail_logs and attempt to find corresponding body in mail_queue
    $stmt = $conn->prepare("SELECT * FROM mail_logs WHERE id = ?");
    $stmt->execute([$id]);
    $log = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$log) {
        echo json_encode(['success' => false, 'error' => 'Email log not found']);
        exit;
    }

    // Lookup corresponding message body from mail_queue
    $stmtQ = $conn->prepare("
        SELECT id, body, alt_body, attempts, created_at, processed_at 
        FROM mail_queue 
        WHERE recipient = ? AND subject = ?
        ORDER BY ABS(TIMESTAMPDIFF(SECOND, created_at, ?)) ASC 
        LIMIT 1
    ");
    $stmtQ->execute([$log['recipient'], $log['subject'], $log['sent_at']]);
    $queueMatch = $stmtQ->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => [
            'id' => (int)$log['id'],
            'recipient' => htmlspecialchars($log['recipient']),
            'subject' => htmlspecialchars($log['subject']),
            'status' => $log['status'],
            'error_message' => $log['error_message'],
            'sent_at' => $log['sent_at'],
            'body_html' => $queueMatch['body'] ?? null,
            'alt_body' => $queueMatch['alt_body'] ?? null,
            'queue_id' => $queueMatch ? (int)$queueMatch['id'] : null
        ]
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
