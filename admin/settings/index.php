<?php
/**
 * Admin Settings - Profile
 */
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../../app/config.php';
require_once __DIR__ . '/../../app/email_helper.php';
require_once __DIR__ . '/../../app/security_notifier.php';

// Admin-only access
$stmt = $conn->prepare("SELECT role FROM admin WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$cu = $stmt->fetch();
if (!$cu || $cu['role'] !== 'admin') {
    $_SESSION['error'] = 'Access denied. Admin privileges required.';
    header('Location: ../dashboard.php');
    exit;
}

$message = '';
$message_type = '';
$csrf_token = generateCSRFToken();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid request. Please refresh and try again.';
        $message_type = 'error';
    } elseif (isset($_POST['update_profile'])) {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        if (empty($username) || empty($email)) {
            $message = 'Username and email are required!';
            $message_type = 'error';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = 'Invalid email format!';
            $message_type = 'error';
        } else {
            $stmt = $conn->prepare("UPDATE admin SET username = ?, email = ? WHERE id = ?");
            $stmt->execute([$username, $email, $_SESSION['admin_id']]);
            $_SESSION['admin_name'] = $username;
            $_SESSION['admin_email'] = $email;
            logAudit($conn, 'UPDATE_PROFILE', 'Updated profile', $_SESSION['admin_id']);
            notifySecurity('UPDATE_PROFILE', 'Profile updated', $_SESSION['admin_id']);
            $message = 'Profile updated successfully!';
            $message_type = 'success';
        }
    } elseif (isset($_POST['change_password'])) {
        $stmt = $conn->prepare("SELECT password FROM admin WHERE id = ?");
        $stmt->execute([$_SESSION['admin_id']]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!password_verify($_POST['current_password'], $admin['password'])) {
            $message = 'Current password is incorrect!';
            $message_type = 'error';
        } elseif ($_POST['new_password'] !== $_POST['confirm_password']) {
            $message = 'Passwords do not match!';
            $message_type = 'error';
        } elseif ($errors = validatePasswordStrength($_POST['new_password'])) {
            $message = 'Password must contain: 8+ characters, uppercase, lowercase, number, and special character.';
            $message_type = 'error';
        } else {
            $hash = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE admin SET password = ? WHERE id = ?");
            $stmt->execute([$hash, $_SESSION['admin_id']]);
            logAudit($conn, 'PASSWORD_CHANGE', 'Password changed', $_SESSION['admin_id']);
            notifySecurity('PASSWORD_CHANGE', 'Admin password changed', $_SESSION['admin_id']);
            $message = 'Password changed successfully!';
            $message_type = 'success';
        }
    }
}

$stmt = $conn->prepare("SELECT * FROM admin WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../../app/Views/admin/settings/index.php';
