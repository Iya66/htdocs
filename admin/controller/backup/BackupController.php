<?php
/**
 * Backup Controller - Handles all backup operations
 * OTP verification, backup generation, download, delete, restore
 */

require_once __DIR__ . '/../../auth.php';
require_once __DIR__ . '/../../../app/config.php';
require_once __DIR__ . '/../../../app/email_helper.php';
require_once __DIR__ . '/../../../app/security_notifier.php';
require_once __DIR__ . '/../../model/backup/BackupModel.php';

// Ensure admin role
$stmt = $conn->prepare("SELECT role, email, name FROM admin WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$admin = $stmt->fetch();
if (!$admin || $admin['role'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

$backupModel = new BackupModel($conn);
$action = $_POST['action'] ?? $_GET['action'] ?? '';

header('Content-Type: application/json');

switch ($action) {

    // ─── SEND OTP ────────────────────────────────────────────
    case 'send_otp':
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Invalid security token']);
            exit;
        }

        // Rate limit: max 5 OTP requests per 10 minutes
        $rateKey = 'backup_otp_' . $_SESSION['admin_id'];
        if (!checkRateLimit($rateKey, 5, 600)) {
            echo json_encode(['success' => false, 'message' => 'Too many OTP requests. Please wait.']);
            exit;
        }

        $otp = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        $_SESSION['backup_otp'] = password_hash($otp, PASSWORD_BCRYPT);
        $_SESSION['backup_otp_expires'] = time() + 300; // 5 min
        $_SESSION['backup_otp_attempts'] = 0;
        $_SESSION['backup_otp_used'] = false;
        $_SESSION['backup_otp_sent_at'] = time();

        // Send OTP email
        $siteName = 'CloudASPIRE';
        $stmtS = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = 'mail_from_name'");
        $stmtS->execute();
        $sn = $stmtS->fetchColumn();
        if ($sn) $siteName = $sn;

        $body = '
        <div style="font-family:Arial,sans-serif;max-width:500px;margin:0 auto;">
            <div style="background:linear-gradient(135deg,#6366f1,#8b5cf6);padding:30px;text-align:center;border-radius:16px 16px 0 0;">
                <h1 style="color:#fff;margin:0;font-size:22px;">' . htmlspecialchars($siteName) . '</h1>
                <p style="color:rgba(255,255,255,0.8);margin:8px 0 0;">Backup Verification</p>
            </div>
            <div style="padding:30px;background:#fff;border:1px solid #eee;">
                <p>Hello <strong>' . htmlspecialchars($admin['name']) . '</strong>,</p>
                <p>Your backup verification code is:</p>
                <div style="text-align:center;margin:24px 0;">
                    <span style="font-size:36px;font-weight:700;letter-spacing:8px;color:#6366f1;background:#f0f0ff;padding:16px 32px;border-radius:12px;display:inline-block;">' . $otp . '</span>
                </div>
                <p style="color:#666;font-size:13px;">This code expires in <strong>5 minutes</strong>. Do not share it.</p>
                <p style="color:#999;font-size:12px;">IP: ' . ($_SERVER['REMOTE_ADDR'] ?? 'Unknown') . ' | Time: ' . date('Y-m-d H:i:s') . '</p>
            </div>
        </div>';

        $result = sendEmail($admin['email'], "[$siteName] Backup Verification Code: $otp", $body);

        if ($result['success'] || (defined('SITE_URL') && str_contains(SITE_URL, 'localhost'))) {
            logAudit($conn, 'BACKUP_OTP_SENT', 'Backup OTP sent to ' . $admin['email'], $_SESSION['admin_id']);
            echo json_encode(['success' => true, 'message' => 'OTP sent to your email!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to send OTP: ' . $result['message']]);
        }
        break;

    // ─── VERIFY OTP ──────────────────────────────────────────
    case 'verify_otp':
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Invalid security token']);
            exit;
        }

        $otp = trim($_POST['otp'] ?? '');

        if (empty($_SESSION['backup_otp'])) {
            echo json_encode(['success' => false, 'message' => 'No OTP requested. Please send a new one.']);
            exit;
        }
        if (!empty($_SESSION['backup_otp_used'])) {
            echo json_encode(['success' => false, 'message' => 'OTP already used. Request a new one.']);
            exit;
        }
        if (time() > ($_SESSION['backup_otp_expires'] ?? 0)) {
            unset($_SESSION['backup_otp']);
            echo json_encode(['success' => false, 'message' => 'OTP expired. Please request a new one.']);
            exit;
        }

        $_SESSION['backup_otp_attempts'] = ($_SESSION['backup_otp_attempts'] ?? 0) + 1;
        if ($_SESSION['backup_otp_attempts'] > 3) {
            unset($_SESSION['backup_otp']);
            echo json_encode(['success' => false, 'message' => 'Too many attempts. Request a new OTP.']);
            exit;
        }

        if (!password_verify($otp, $_SESSION['backup_otp'])) {
            $remaining = 3 - $_SESSION['backup_otp_attempts'];
            echo json_encode(['success' => false, 'message' => "Invalid OTP. $remaining attempts remaining."]);
            exit;
        }

        // OTP verified
        $_SESSION['backup_otp_used'] = true;
        $_SESSION['backup_verified'] = true;
        $_SESSION['backup_verified_at'] = time();
        logAudit($conn, 'BACKUP_OTP_VERIFIED', 'Backup OTP verified successfully', $_SESSION['admin_id']);
        echo json_encode(['success' => true, 'message' => 'OTP verified successfully']);
        break;

    // ─── GENERATE BACKUP ─────────────────────────────────────
    case 'generate':
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Invalid security token']);
            exit;
        }
        if (empty($_SESSION['backup_verified']) || (time() - ($_SESSION['backup_verified_at'] ?? 0)) > 600) {
            echo json_encode(['success' => false, 'message' => 'OTP verification required']);
            exit;
        }

        $type = $_POST['backup_type'] ?? 'full';
        $customName = preg_replace('/[^a-zA-Z0-9_-]/', '', trim($_POST['custom_name'] ?? 'backup'));
        if (empty($customName)) $customName = 'backup';

        $timestamp = date('Y_m_d_H_i');
        $filename = "{$customName}_{$timestamp}";

        set_time_limit(300);
        ini_set('memory_limit', '512M');

        try {
            $result = $backupModel->generateBackup($type, $filename);
            if ($result['success']) {
                // Invalidate OTP session
                unset($_SESSION['backup_verified'], $_SESSION['backup_otp']);
                logAudit($conn, 'BACKUP_GENERATED', "Backup created: {$result['filename']} ({$type})", $_SESSION['admin_id']);
            }
            echo json_encode($result);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Backup failed: ' . $e->getMessage()]);
        }
        break;

    // ─── DOWNLOAD ────────────────────────────────────────────
    case 'download':
        header('Content-Type: application/octet-stream');
        $file = basename($_GET['file'] ?? '');
        if (empty($file)) { http_response_code(400); exit('Invalid file'); }

        $path = $backupModel->getBackupDir() . '/' . $file;
        $realBackupDir = realpath($backupModel->getBackupDir());
        $realPath = realpath($path);

        if (!$realPath || !$realBackupDir || strpos($realPath, $realBackupDir) !== 0 || !file_exists($realPath)) {
            http_response_code(404);
            exit('File not found');
        }

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $file . '"');
        header('Content-Length: ' . filesize($realPath));
        header('Cache-Control: must-revalidate');
        readfile($realPath);
        exit;

    // ─── DELETE ───────────────────────────────────────────────
    case 'delete':
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Invalid security token']);
            exit;
        }

        $file = basename($_POST['file'] ?? '');
        $result = $backupModel->deleteBackup($file);
        if ($result['success']) {
            logAudit($conn, 'BACKUP_DELETED', "Deleted backup: $file", $_SESSION['admin_id']);
        }
        echo json_encode($result);
        break;

    // ─── LIST BACKUPS ────────────────────────────────────────
    case 'list':
        echo json_encode(['success' => true, 'backups' => $backupModel->listBackups()]);
        break;

    // ─── RESTORE ─────────────────────────────────────────────
    case 'restore':
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Invalid security token']);
            exit;
        }
        if (empty($_SESSION['backup_verified']) || (time() - ($_SESSION['backup_verified_at'] ?? 0)) > 600) {
            echo json_encode(['success' => false, 'message' => 'OTP verification required']);
            exit;
        }

        $file = basename($_POST['file'] ?? '');
        set_time_limit(300);
        ini_set('memory_limit', '512M');

        try {
            $result = $backupModel->restoreBackup($file);
            if ($result['success']) {
                unset($_SESSION['backup_verified'], $_SESSION['backup_otp']);
                logAudit($conn, 'BACKUP_RESTORED', "Restored backup: $file", $_SESSION['admin_id']);
            }
            echo json_encode($result);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Restore failed: ' . $e->getMessage()]);
        }
        break;

    // ─── UPLOAD & RESTORE ────────────────────────────────────
    case 'upload_restore':
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Invalid security token']);
            exit;
        }
        if (empty($_SESSION['backup_verified']) || (time() - ($_SESSION['backup_verified_at'] ?? 0)) > 600) {
            echo json_encode(['success' => false, 'message' => 'OTP verification required']);
            exit;
        }
        if (!isset($_FILES['backup_file']) || $_FILES['backup_file']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error']);
            exit;
        }

        $file = $_FILES['backup_file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['zip', 'sql'])) {
            echo json_encode(['success' => false, 'message' => 'Only .zip and .sql files allowed']);
            exit;
        }
        if ($file['size'] > 500 * 1024 * 1024) {
            echo json_encode(['success' => false, 'message' => 'File too large (max 500MB)']);
            exit;
        }

        $destName = 'uploaded_' . date('Y_m_d_H_i') . '.' . $ext;
        $destPath = $backupModel->getBackupDir() . '/' . $destName;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            echo json_encode(['success' => false, 'message' => 'Failed to save uploaded file']);
            exit;
        }

        set_time_limit(300);
        try {
            $result = $backupModel->restoreBackup($destName);
            if ($result['success']) {
                unset($_SESSION['backup_verified'], $_SESSION['backup_otp']);
                logAudit($conn, 'BACKUP_UPLOAD_RESTORE', "Uploaded & restored: $destName", $_SESSION['admin_id']);
            }
            echo json_encode($result);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Restore failed: ' . $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
