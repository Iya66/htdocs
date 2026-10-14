<?php
/**
 * My Profile - Edit Logged In Admin Details
 */
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/email_helper.php';
require_once __DIR__ . '/../app/security_notifier.php';

$message = '';
$message_type = '';

$user_id = $_SESSION['admin_id'];
$stmt = $conn->prepare("SELECT * FROM admin WHERE id = ?");
$stmt->execute([$user_id]);
$current_user = $stmt->fetch();

if (!$current_user) {
    header('Location: logout.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid request. Please refresh and try again.';
        $message_type = 'error';
    } else {
        $name = trim($_POST['name'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $wa_number = trim($_POST['wa_number'] ?? '');
        $avatar = trim($_POST['avatar'] ?? '');
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (empty($name) || empty($username) || empty($email)) {
            $message = 'Name, Username, and Email are required!';
            $message_type = 'error';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = 'Invalid email format!';
            $message_type = 'error';
        } else {
            $check = $conn->prepare("SELECT id FROM admin WHERE email = ? AND id != ?");
            $check->execute([$email, $user_id]);
            if ($check->fetch()) {
                $message = 'Email already exists!';
                $message_type = 'error';
            } else {
                $check = $conn->prepare("SELECT id FROM admin WHERE username = ? AND id != ?");
                $check->execute([$username, $user_id]);
                if ($check->fetch()) {
                    $message = 'Username already exists!';
                    $message_type = 'error';
                } else {
                    if (!empty($new_password)) {
                        if (empty($current_password)) {
                            $message = 'Current password is required to change password!';
                            $message_type = 'error';
                        } elseif (!password_verify($current_password, $current_user['password'])) {
                            $message = 'Current password is incorrect!';
                            $message_type = 'error';
                        } elseif ($new_password !== $confirm_password) {
                            $message = 'New passwords do not match!';
                            $message_type = 'error';
                        } elseif ($errors = validatePasswordStrength($new_password)) {
                            $message = 'Password must contain: 8+ characters, uppercase, lowercase, number, and special character.';
                            $message_type = 'error';
                        } else {
                            $hashedPassword = password_hash($new_password, PASSWORD_BCRYPT);
                            $stmt = $conn->prepare("UPDATE admin SET name = ?, username = ?, email = ?, wa_number = ?, avatar = ?, password = ? WHERE id = ?");
                            $stmt->execute([$name, $username, $email, $wa_number, $avatar, $hashedPassword, $user_id]);
                            logAudit($conn, 'UPDATE_PROFILE_PASSWORD', "Profile updated with password change: $name", $_SESSION['admin_id']);
                            notifySecurity('PASSWORD_CHANGE', "Admin changed their password: $name ($email)", $_SESSION['admin_id']);
                            $message = 'Profile updated with new password!';
                            $message_type = 'success';
                        }
                    } else {
                        $stmt = $conn->prepare("UPDATE admin SET name = ?, username = ?, email = ?, wa_number = ?, avatar = ? WHERE id = ?");
                        $stmt->execute([$name, $username, $email, $wa_number, $avatar, $user_id]);
                        logAudit($conn, 'UPDATE_PROFILE', "Profile updated for: $name", $_SESSION['admin_id']);
                        notifySecurity('UPDATE_PROFILE', "Profile updated: $name ($email)", $_SESSION['admin_id']);
                        $message = 'Profile updated successfully!';
                        $message_type = 'success';
                    }

                    if ($message_type === 'success') {
                        $_SESSION['admin_name'] = $name;
                        $_SESSION['admin_username'] = $username;
                        $_SESSION['admin_email'] = $email;
                        $stmt = $conn->prepare("SELECT * FROM admin WHERE id = ?");
                        $stmt->execute([$user_id]);
                        $current_user = $stmt->fetch();
                    }
                }
            }
        }
    }
}

require_once __DIR__ . '/../app/Views/admin/accounts.php';
