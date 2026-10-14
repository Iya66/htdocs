<?php
/**
 * Staff Management - Full CRUD with CSRF Protection & Audit
 */
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/email_helper.php';
require_once __DIR__ . '/../app/security_notifier.php';

$stmt = $conn->prepare("SELECT role FROM admin WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$current_user = $stmt->fetch();

if (!$current_user || $current_user['role'] !== 'admin') {
    $_SESSION['error'] = 'Access denied. Admin privileges required.';
    header('Location: dashboard.php');
    exit;
}

$message = '';
$message_type = '';
$csrf_token = generateCSRFToken();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid request. Please refresh and try again.';
        $message_type = 'error';
    } else {
        if (isset($_POST['create_staff'])) {
            $name = trim($_POST['name'] ?? '');
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $permissions_json = json_encode($_POST['permissions'] ?? []);
            $is_active = isset($_POST['is_active']) ? 1 : 0;

            if (empty($name) || empty($username) || empty($email) || empty($password)) {
                $message = 'All fields are required!';
                $message_type = 'error';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $message = 'Invalid email format!';
                $message_type = 'error';
            } else {
                $check = $conn->prepare("SELECT id FROM admin WHERE email = ?");
                $check->execute([$email]);
                if ($check->fetch()) {
                    $message = 'Email already exists!';
                    $message_type = 'error';
                } else {
                    $check = $conn->prepare("SELECT id FROM admin WHERE username = ?");
                    $check->execute([$username]);
                    if ($check->fetch()) {
                        $message = 'Username already exists!';
                        $message_type = 'error';
                    } else {
                        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                        $stmt = $conn->prepare("INSERT INTO admin (name, username, email, password, role, permissions, is_active) VALUES (?, ?, ?, ?, 'staff', ?, ?)");
                        $stmt->execute([$name, $username, $email, $hashedPassword, $permissions_json, $is_active]);

                        $conn->prepare("INSERT INTO admin_activities (user_id, action, details, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)")
                            ->execute([$_SESSION['admin_id'], 'CREATE_STAFF', "Created staff: $email", $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? '']);
                        notifySecurity('CREATE_STAFF', "Created staff: $email", $_SESSION['admin_id']);
                        sendWelcomeEmail($email, $name, $password);

                        $message = 'Staff member created successfully! Welcome email sent.';
                        $message_type = 'success';
                    }
                }
            }
        }

        if (isset($_POST['update_staff'])) {
            $staff_id = (int)$_POST['staff_id'];
            $name = trim($_POST['name'] ?? '');
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $permissions_json = json_encode($_POST['permissions'] ?? []);
            $is_active = isset($_POST['is_active']) ? 1 : 0;

            if (empty($name) || empty($username) || empty($email)) {
                $message = 'Name, Username, and Email are required!';
                $message_type = 'error';
            } else {
                $check = $conn->prepare("SELECT id FROM admin WHERE email = ? AND id != ?");
                $check->execute([$email, $staff_id]);
                if ($check->fetch()) {
                    $message = 'Email already exists!';
                    $message_type = 'error';
                } else {
                    $stmt = $conn->prepare("UPDATE admin SET name = ?, username = ?, email = ?, permissions = ?, is_active = ? WHERE id = ? AND role != 'admin'");
                    $stmt->execute([$name, $username, $email, $permissions_json, $is_active, $staff_id]);

                    $conn->prepare("INSERT INTO admin_activities (user_id, action, details, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)")
                        ->execute([$_SESSION['admin_id'], 'UPDATE_STAFF', "Updated staff ID: $staff_id", $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? '']);
                    notifySecurity('UPDATE_STAFF', "Updated staff ID: $staff_id", $_SESSION['admin_id']);

                    $message = 'Staff member updated successfully!';
                    $message_type = 'success';
                }
            }
        }

        if (isset($_POST['delete_staff'])) {
            $staff_id = (int)$_POST['staff_id'];
            if ($staff_id == $_SESSION['admin_id']) {
                $message = 'You cannot delete your own account!';
                $message_type = 'error';
            } else {
                $stmt = $conn->prepare("DELETE FROM admin WHERE id = ? AND role != 'admin'");
                $stmt->execute([$staff_id]);

                $conn->prepare("INSERT INTO admin_activities (user_id, action, details, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)")
                    ->execute([$_SESSION['admin_id'], 'DELETE_STAFF', "Deleted staff ID: $staff_id", $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? '']);
                notifySecurity('DELETE_STAFF', "Deleted staff ID: $staff_id", $_SESSION['admin_id']);

                $message = 'Staff member deleted successfully!';
                $message_type = 'success';
            }
        }

        if (isset($_POST['toggle_status'])) {
            $staff_id = (int)$_POST['staff_id'];
            if ($staff_id == $_SESSION['admin_id']) {
                $message = 'You cannot disable your own account!';
                $message_type = 'error';
            } else {
                $stmt = $conn->prepare("UPDATE admin SET is_active = IF(is_active = 1, 0, 1) WHERE id = ? AND role != 'admin'");
                $stmt->execute([$staff_id]);

                $conn->prepare("INSERT INTO admin_activities (user_id, action, details, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)")
                    ->execute([$_SESSION['admin_id'], 'TOGGLE_STAFF', "Toggled staff ID: $staff_id", $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? '']);
                notifySecurity('TOGGLE_STAFF', "Toggled staff ID: $staff_id", $_SESSION['admin_id']);

                $message = 'Staff status updated!';
                $message_type = 'success';
            }
        }

        if (isset($_POST['change_password'])) {
            $staff_id = (int)$_POST['staff_id'];
            $new_password = $_POST['new_password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';

            if (empty($new_password) || empty($confirm_password)) {
                $message = 'Both password fields are required!';
                $message_type = 'error';
            } elseif ($new_password !== $confirm_password) {
                $message = 'Passwords do not match!';
                $message_type = 'error';
            } elseif ($errors = validatePasswordStrength($new_password)) {
                $message = 'Password must contain: 8+ characters, uppercase, lowercase, number, and special character.';
                $message_type = 'error';
            } else {
                $hashedPassword = password_hash($new_password, PASSWORD_BCRYPT);
                $stmt = $conn->prepare("UPDATE admin SET password = ? WHERE id = ?");
                $stmt->execute([$hashedPassword, $staff_id]);

                $conn->prepare("INSERT INTO admin_activities (user_id, action, details, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)")
                    ->execute([$_SESSION['admin_id'], 'PASSWORD_CHANGE', "Password changed for staff ID: $staff_id", $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? '']);
                notifySecurity('PASSWORD_CHANGE', "Password changed for staff ID: $staff_id", $_SESSION['admin_id']);

                $message = 'Password changed successfully!';
                $message_type = 'success';
            }
        }
    }
}

$staff_list = $conn->query("SELECT * FROM admin WHERE role != 'admin' ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

$edit_staff = null;
if (isset($_GET['edit'])) {
    $stmt = $conn->prepare("SELECT * FROM admin WHERE id = ? AND role != 'admin'");
    $stmt->execute([(int)$_GET['edit']]);
    $edit_staff = $stmt->fetch();
}

$password_staff = null;
if (isset($_GET['password'])) {
    $stmt = $conn->prepare("SELECT id, name, email FROM admin WHERE id = ?");
    $stmt->execute([(int)$_GET['password']]);
    $password_staff = $stmt->fetch();
}

$admin_activities = $conn->query("SELECT al.*, a.name as admin_name FROM admin_activities al LEFT JOIN admin a ON al.user_id = a.id ORDER BY al.created_at DESC LIMIT 50")->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../app/Views/admin/staff.php';
