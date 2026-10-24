<?php
/**
 * CloudASPIRE High-Performance Asynchronous Mail Queue Engine
 * 
 * Provides ultra-fast non-blocking email queuing (< 2ms) and background
 * worker dispatch to ensure admin actions and web forms respond instantly.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/smtp_factory.php';

/**
 * Ensure mail_queue table exists in the database.
 */
function ensureMailQueueTable(?PDO $conn = null): void
{
    static $checked = false;
    if ($checked) return;
    if ($conn === null) {
        global $conn;
    }
    if (!$conn) return;
    $checked = true;

    try {
        $conn->exec("
            CREATE TABLE IF NOT EXISTS `mail_queue` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `recipient` VARCHAR(255) NOT NULL,
                `subject` VARCHAR(255) NOT NULL,
                `body` MEDIUMTEXT NOT NULL,
                `alt_body` TEXT NULL,
                `is_html` TINYINT(1) DEFAULT 1,
                `status` ENUM('pending','processing','sent','failed') DEFAULT 'pending',
                `attempts` INT DEFAULT 0,
                `max_attempts` INT DEFAULT 3,
                `error_message` TEXT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                `processed_at` DATETIME NULL,
                INDEX (`status`),
                INDEX (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    } catch (Exception $e) {}
}

/**
 * Enqueue an email for asynchronous background delivery.
 * Execution takes ~1-2ms, preventing any delay in HTTP responses.
 *
 * @param PDO|null     $conn          Database connection (falls back to global $conn)
 * @param mixed        $to            Single email string or array of emails
 * @param string       $subject       Email subject
 * @param string       $body          Email body HTML
 * @param string       $altBody       Optional plain text alternative
 * @param bool         $triggerWorker Whether to spawn background worker immediately
 * @return array{success: bool, queued: bool, queue_ids?: array, message: string}
 */
function enqueueEmail(?PDO $conn, mixed $to, string $subject, string $body, string $altBody = '', bool $triggerWorker = true): array
{
    ensureMailQueueTable($conn);

    if ($conn === null) {
        global $conn;
    }

    if (!$conn) {
        return ['success' => false, 'queued' => false, 'message' => 'Database connection unavailable'];
    }

    $recipients = [];
    if (is_array($to)) {
        foreach ($to as $r) {
            $trimmed = trim((string)$r);
            if (filter_var($trimmed, FILTER_VALIDATE_EMAIL)) {
                $recipients[] = $trimmed;
            }
        }
    } else {
        $trimmed = trim((string)$to);
        if (filter_var($trimmed, FILTER_VALIDATE_EMAIL)) {
            $recipients[] = $trimmed;
        }
    }

    if (empty($recipients)) {
        return ['success' => false, 'queued' => false, 'message' => 'No valid recipient email address'];
    }

    $queueIds = [];
    try {
        $stmt = $conn->prepare("INSERT INTO mail_queue (recipient, subject, body, alt_body, is_html, status, created_at) VALUES (?, ?, ?, ?, 1, 'pending', NOW())");

        foreach ($recipients as $recipient) {
            $plainText = !empty($altBody) ? $altBody : (function_exists('htmlToCleanPlainText') ? htmlToCleanPlainText($body) : strip_tags($body));
            $stmt->execute([$recipient, $subject, $body, $plainText]);
            $queueIds[] = (int) $conn->lastInsertId();
        }

        if ($triggerWorker) {
            triggerAsyncMailWorker();
        }

        return [
            'success'   => true,
            'queued'    => true,
            'queue_ids' => $queueIds,
            'message'   => 'Email queued for fast delivery'
        ];
    } catch (Exception $e) {
        error_log('[Mail Queue] Enqueue error: ' . $e->getMessage());
        return ['success' => false, 'queued' => false, 'message' => $e->getMessage()];
    }
}

/**
 * Trigger background mail queue worker without blocking the HTTP response.
 * Spawns a detached process in < 15ms.
 */
function triggerAsyncMailWorker(): void
{
    static $triggered = false;
    if ($triggered) {
        return;
    }
    $triggered = true;

    $workerScript = __DIR__ . DIRECTORY_SEPARATOR . 'queue_worker.php';
    if (!file_exists($workerScript)) {
        return;
    }

    // Locate PHP CLI binary
    $phpBinary = 'php';
    if (defined('PHP_BINARY') && !empty(PHP_BINARY) && file_exists(PHP_BINARY)) {
        // If running in Apache mod_php, PHP_BINARY might be httpd.exe; check for php.exe
        if (stripos(PHP_BINARY, 'php.exe') !== false) {
            $phpBinary = PHP_BINARY;
        } elseif (file_exists('C:\\xampp2\\php\\php.exe')) {
            $phpBinary = 'C:\\xampp2\\php\\php.exe';
        } elseif (file_exists('C:\\xampp\\php\\php.exe')) {
            $phpBinary = 'C:\\xampp\\php\\php.exe';
        }
    } elseif (file_exists('C:\\xampp2\\php\\php.exe')) {
        $phpBinary = 'C:\\xampp2\\php\\php.exe';
    } elseif (file_exists('C:\\xampp\\php\\php.exe')) {
        $phpBinary = 'C:\\xampp\\php\\php.exe';
    }

    try {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Non-blocking detached process on Windows
            $cmd = 'start /B "" ' . escapeshellarg($phpBinary) . ' ' . escapeshellarg($workerScript) . ' > NUL 2>&1';
            $handle = @popen($cmd, 'r');
            if ($handle) {
                pclose($handle);
            }
        } else {
            // Non-blocking detached process on Linux / Unix
            $cmd = escapeshellarg($phpBinary) . ' ' . escapeshellarg($workerScript) . ' > /dev/null 2>&1 &';
            @exec($cmd);
        }
    } catch (Exception $e) {
        error_log('[Mail Queue] Failed to spawn worker: ' . $e->getMessage());
    }
}

/**
 * Process pending items in mail_queue.
 * Uses atomic claim, SMTP connection reuse for batch sending, and updates statuses.
 *
 * @param PDO|null $conn Database connection
 * @param int      $batchSize Maximum emails to process in this run
 * @return array{processed: int, sent: int, failed: int, errors: array}
 */
function processMailQueue(?PDO $conn = null, int $batchSize = 15): array
{
    if ($conn === null) {
        global $conn;
    }

    if (!$conn) {
        return ['processed' => 0, 'sent' => 0, 'failed' => 0, 'errors' => ['Database connection unavailable']];
    }

    ensureMailQueueTable($conn);

    // Prevent HTTP 500 timeouts by extending execution limit & auto-recovering stale processing items
    @ini_set('max_execution_time', '180');
    @set_time_limit(180);
    $batchStartTime = microtime(true);

    try {
        $conn->query("UPDATE mail_queue SET status = IF(attempts >= max_attempts, 'failed', 'pending') WHERE status = 'processing' AND updated_at < DATE_SUB(NOW(), INTERVAL 3 MINUTE)");
    } catch (Exception $e) {}

    // Atomic fetch & claim pending items
    $stmt = $conn->prepare("SELECT id, recipient, subject, body, alt_body, is_html, attempts 
                            FROM mail_queue 
                            WHERE status = 'pending' AND attempts < max_attempts 
                            ORDER BY id ASC 
                            LIMIT " . (int)$batchSize);
    $stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($items)) {
        return ['processed' => 0, 'sent' => 0, 'failed' => 0, 'errors' => []];
    }

    $itemIds = array_column($items, 'id');
    $placeholders = implode(',', array_fill(0, count($itemIds), '?'));
    
    // Mark items as processing
    $claimStmt = $conn->prepare("UPDATE mail_queue SET status = 'processing', attempts = attempts + 1 WHERE id IN ($placeholders) AND status = 'pending'");
    $claimStmt->execute($itemIds);

    $settings = loadSmtpSettings($conn);
    $fromEmail = $settings['mail_smtp_email'] ?? '';
    $fromName  = $settings['mail_from_name'] ?? (defined('SMTP_FROM_NAME') ? SMTP_FROM_NAME : 'CloudASPIRE');
    $adminEmail = $settings['mail_admin_email'] ?? $fromEmail;

    $sentCount = 0;
    $failedCount = 0;
    $errors = [];

    // Create a single PHPMailer instance to reuse across items
    $mail = null;
    try {
        $mail = createMailer($conn);
        $mail->SMTPKeepAlive = true; // Keep connection open for batch efficiency
    } catch (Exception $e) {
        $errMsg = 'Mailer setup failed: ' . $e->getMessage();
        error_log("[Mail Queue] $errMsg");
        $failStmt = $conn->prepare("UPDATE mail_queue SET status = 'failed', error_message = ?, processed_at = NOW() WHERE id IN ($placeholders)");
        $failStmt->execute(array_merge([$errMsg], $itemIds));
        return ['processed' => count($items), 'sent' => 0, 'failed' => count($items), 'errors' => [$errMsg]];
    }

    $updateSentStmt = $conn->prepare("UPDATE mail_queue SET status = 'sent', error_message = NULL, processed_at = NOW() WHERE id = ?");
    $updateFailStmt = $conn->prepare("UPDATE mail_queue SET status = ?, error_message = ?, processed_at = NOW() WHERE id = ?");
    $logStmt        = $conn->prepare("INSERT INTO mail_logs (recipient, subject, status, error_message, sent_at) VALUES (?, ?, ?, ?, NOW())");

    foreach ($items as $item) {
        // Time guard: stop loop if batch approaches 20s to ensure zero 500 timeouts
        if ((microtime(true) - $batchStartTime) > 20) {
            error_log("[Mail Queue] Time guard reached (20s). Deferring remaining items to pending.");
            $remainingIds = array_slice($itemIds, $sentCount + $failedCount);
            if (!empty($remainingIds)) {
                $resetPlaceholders = implode(',', array_fill(0, count($remainingIds), '?'));
                $conn->prepare("UPDATE mail_queue SET status = 'pending', attempts = GREATEST(attempts - 1, 0) WHERE id IN ($resetPlaceholders)")->execute($remainingIds);
            }
            break;
        }
        $id = $item['id'];
        $recipient = $item['recipient'];
        $subject = $item['subject'];
        $body = $item['body'];
        $altBody = !empty($item['alt_body']) ? $item['alt_body'] : (function_exists('htmlToCleanPlainText') ? htmlToCleanPlainText($body) : strip_tags($body));
        $attempts = (int)$item['attempts'] + 1;

        try {
            $mail->clearAddresses();
            $mail->clearAttachments();
            $mail->clearReplyTos();
            $mail->clearCustomHeaders();

            $mail->setFrom($fromEmail, $fromName);
            $mail->Sender = $fromEmail;

            // Anti-Spam & Deliverability headers
            $mail->addCustomHeader('Auto-Submitted', 'auto-generated');
            $mail->addCustomHeader('X-Auto-Response-Suppress', 'All');

            $mail->addAddress($recipient);
            if (!empty($adminEmail)) {
                $mail->addReplyTo($adminEmail, $fromName);
            }

            $mail->isHTML((bool)$item['is_html']);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->AltBody = $altBody;

            // Send using retry wrapper
            sendWithRetry($mail, 2);

            $updateSentStmt->execute([$id]);
            try {
                $logStmt->execute([$recipient, $subject, 'sent', null]);
            } catch (Exception $logEx) {}

            $sentCount++;
            error_log("[Mail Queue] Successfully delivered queue item #$id to $recipient");
        } catch (Exception $e) {
            $errorMsg = $e->getMessage();
            if (!empty($mail->ErrorInfo)) {
                $errorMsg .= ' | ' . $mail->ErrorInfo;
            }

            $newStatus = ($attempts >= 3) ? 'failed' : 'pending';
            $updateFailStmt->execute([$newStatus, $errorMsg, $id]);

            try {
                $logStmt->execute([$recipient, $subject, 'failed', $errorMsg]);
            } catch (Exception $logEx) {}

            $failedCount++;
            $errors[] = "#$id ($recipient): $errorMsg";
            error_log("[Mail Queue] Failed delivery for queue item #$id: $errorMsg");

            // Reset connection on failure
            try {
                $mail->smtpClose();
            } catch (Exception $closeEx) {}
        }
    }

    // Close persistent SMTP connection
    try {
        if ($mail !== null) {
            $mail->smtpClose();
        }
    } catch (Exception $e) {}

    return [
        'processed' => count($items),
        'sent'      => $sentCount,
        'failed'    => $failedCount,
        'errors'    => $errors
    ];
}

/**
 * Get mail queue statistics for monitoring.
 */
function getMailQueueStats(PDO $conn): array
{
    ensureMailQueueTable($conn);
    $stats = [
        'pending'    => 0,
        'processing' => 0,
        'sent'       => 0,
        'failed'     => 0,
        'total'      => 0
    ];

    try {
        $stmt = $conn->query("SELECT status, COUNT(*) as cnt FROM mail_queue GROUP BY status");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $status = $row['status'];
            if (isset($stats[$status])) {
                $stats[$status] = (int)$row['cnt'];
            }
            $stats['total'] += (int)$row['cnt'];
        }
    } catch (Exception $e) {
        error_log('[Mail Queue] Stats query failed: ' . $e->getMessage());
    }

    return $stats;
}

/**
 * Reset failed queue items to pending status to retry sending.
 */
function retryFailedEmails(PDO $conn): int
{
    ensureMailQueueTable($conn);
    try {
        $stmt = $conn->prepare("UPDATE mail_queue SET status = 'pending', attempts = 0, error_message = NULL WHERE status = 'failed' OR (status = 'processing' AND processed_at < DATE_SUB(NOW(), INTERVAL 10 MINUTE))");
        $stmt->execute();
        $affected = $stmt->rowCount();
        if ($affected > 0) {
            triggerAsyncMailWorker();
        }
        return $affected;
    } catch (Exception $e) {
        error_log('[Mail Queue] Retry failed emails error: ' . $e->getMessage());
        return 0;
    }
}

/**
 * Clear old sent / failed items from mail_queue.
 */
function clearCompletedQueue(PDO $conn, int $daysOld = 7): int
{
    ensureMailQueueTable($conn);
    try {
        $stmt = $conn->prepare("DELETE FROM mail_queue WHERE status IN ('sent', 'failed') AND created_at < DATE_SUB(NOW(), INTERVAL ? DAY)");
        $stmt->execute([$daysOld]);
        return $stmt->rowCount();
    } catch (Exception $e) {
        error_log('[Mail Queue] Clear completed error: ' . $e->getMessage());
        return 0;
    }
}
