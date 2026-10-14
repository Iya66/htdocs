<?php
/**
 * CloudASPIRE Background Mail Queue Worker
 * 
 * Invoked asynchronously by enqueueEmail() or cron to process pending emails
 * without blocking web requests or user interactions.
 */

// Prevent multiple overlapping worker processes via lock file
$lockFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'cloudaspire_mail_queue.lock';
$lockHandle = @fopen($lockFile, 'c+');

if (!$lockHandle || !flock($lockHandle, LOCK_EX | LOCK_NB)) {
    // Another worker is currently executing — safely exit
    exit(0);
}

// Allow background process to complete even if caller disconnects
ignore_user_abort(true);
set_time_limit(120);

// Load core dependencies
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/smtp_factory.php';
require_once __DIR__ . '/mail_queue.php';

global $conn;

if (!$conn) {
    flock($lockHandle, LOCK_UN);
    fclose($lockHandle);
    exit(1);
}

$startTime = microtime(true);
$maxRunSeconds = 45;
$totalProcessed = 0;
$totalSent = 0;
$totalFailed = 0;

try {
    // Process queue in batches until empty or timeout reached
    while ((microtime(true) - $startTime) < $maxRunSeconds) {
        $result = processMailQueue($conn, 10);
        
        $totalProcessed += $result['processed'];
        $totalSent += $result['sent'];
        $totalFailed += $result['failed'];

        // If no more pending items were found, break out
        if ($result['processed'] === 0) {
            break;
        }

        // Brief pause between batches to prevent hammering SMTP server
        usleep(100000); // 100ms
    }

    if ($totalProcessed > 0) {
        $duration = round((microtime(true) - $startTime), 2);
        error_log("[Queue Worker] Finished run: Processed=$totalProcessed, Sent=$totalSent, Failed=$totalFailed in {$duration}s");
    }
} catch (Exception $e) {
    error_log("[Queue Worker] Error during processing: " . $e->getMessage());
} finally {
    flock($lockHandle, LOCK_UN);
    fclose($lockHandle);
    @unlink($lockFile);
}
