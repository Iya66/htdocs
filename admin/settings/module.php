<?php
/**
 * Module Settings - CRUD Operations & Google Login Integration
 */
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../../app/config.php';
require_once __DIR__ . '/../../app/email_helper.php';
require_once __DIR__ . '/../../app/security_notifier.php';

// Admin role check
$stmt = $conn->prepare("SELECT role FROM admin WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$current_user = $stmt->fetch();

if (!$current_user || $current_user['role'] !== 'admin') {
    $_SESSION['error'] = 'Access denied. Admin privileges required.';
    header('Location: ../dashboard.php');
    exit;
}

$message = '';
$message_type = '';
$csrf_token = generateCSRFToken();

// CRUD Operations for Modules
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid security token. Please refresh and try again.';
        $message_type = 'error';
    } else {
    if (isset($_POST['save_module'])) {
        $module_id = isset($_POST['module_id']) && !empty($_POST['module_id']) ? (int)$_POST['module_id'] : null;
        $module_name = trim($_POST['module_name'] ?? '');
        $module_key = trim($_POST['module_key'] ?? '');
        $module_description = trim($_POST['module_description'] ?? '');
        $module_type = $_POST['module_type'] ?? 'feature';

        if (empty($module_name) || empty($module_key)) {
            $message = 'Module name and key are required!';
            $message_type = 'error';
        } else {
            // Check for duplicate
            if ($module_id) {
                $check = $conn->prepare("SELECT id FROM modules WHERE module_key = ? AND id != ?");
                $check->execute([$module_key, $module_id]);
            } else {
                $check = $conn->prepare("SELECT id FROM modules WHERE module_key = ?");
                $check->execute([$module_key]);
            }

            if ($check->fetch()) {
                $message = 'Module key already exists!';
                $message_type = 'error';
            } else {
                if ($module_id) {
                    $stmt = $conn->prepare("UPDATE modules SET module_name = ?, module_key = ?, description = ?, module_type = ? WHERE id = ?");
                    $stmt->execute([$module_name, $module_key, $module_description, $module_type, $module_id]);
                    logAudit($conn, 'UPDATE_MODULE', 'Updated module: ' . $module_key, $_SESSION['admin_id']);
                    notifySecurity('UPDATE_MODULE', 'Updated module: ' . $module_key, $_SESSION['admin_id']);
                    $message = 'Module updated successfully!';
                } else {
                    $stmt = $conn->prepare("INSERT INTO modules (module_name, module_key, description, module_type) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$module_name, $module_key, $module_description, $module_type]);
                    logAudit($conn, 'CREATE_MODULE', 'Created module: ' . $module_key, $_SESSION['admin_id']);
                    notifySecurity('CREATE_MODULE', 'Created module: ' . $module_key, $_SESSION['admin_id']);
                    $message = 'Module created successfully!';
                }
                $message_type = 'success';
            }
        }
    }

    // Toggle Module Status
    if (isset($_POST['toggle_module'])) {
        $module_id = (int)$_POST['module_id'];
        $stmt = $conn->prepare("UPDATE modules SET is_active = IF(is_active = 1, 0, 1) WHERE id = ?");
        $stmt->execute([$module_id]);
        logAudit($conn, 'TOGGLE_MODULE', 'Toggled module ID: ' . $module_id, $_SESSION['admin_id']);
        notifySecurity('TOGGLE_MODULE', 'Module ID ' . $module_id . ' status toggled', $_SESSION['admin_id']);
        $message = 'Module status updated!';
        $message_type = 'success';
    }

    // Delete Module
    if (isset($_POST['delete_module'])) {
        $module_id = (int)$_POST['module_id'];
        $stmt = $conn->prepare("DELETE FROM modules WHERE id = ?");
        $stmt->execute([$module_id]);
        logAudit($conn, 'DELETE_MODULE', 'Deleted module ID: ' . $module_id, $_SESSION['admin_id']);
        notifySecurity('DELETE_MODULE', 'Module ID ' . $module_id . ' deleted', $_SESSION['admin_id']);
        $message = 'Module deleted successfully!';
        $message_type = 'success';
    }

    // Save Google Login Settings
    if (isset($_POST['save_google_login'])) {
        $google_client_key = trim($_POST['google_client_key'] ?? '');
        $google_secret_key = trim($_POST['google_secret_key'] ?? '');
        $google_redirect_uri = trim($_POST['google_redirect_uri'] ?? '');
        $google_login_enabled = isset($_POST['google_login_enabled']) ? '1' : '0';
        $allowed_domains = trim($_POST['allowed_domains'] ?? '');

        if (empty($google_client_key) || empty($google_secret_key)) {
            $message = 'Client Key and Secret Key are required!';
            $message_type = 'error';
        } else {
            $settings = [
                'google_client_key' => $google_client_key,
                'google_secret_key' => encryptSensitive($google_secret_key),
                'google_redirect_uri' => $google_redirect_uri,
                'google_login_enabled' => $google_login_enabled,
                'google_allowed_domains' => $allowed_domains
            ];

            foreach ($settings as $key => $value) {
                $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
                $stmt->execute([$key, $value]);
            }

            logAudit($conn, 'UPDATE_GOOGLE_LOGIN', 'Updated Google Login settings', $_SESSION['admin_id']);
            notifySecurity('UPDATE_GOOGLE_LOGIN', 'Google Login settings updated', $_SESSION['admin_id']);
            $message = 'Google Login settings saved successfully!';
            $message_type = 'success';
        }
    }

    // Add Registered Email
    if (isset($_POST['add_email'])) {
        $email = trim($_POST['email'] ?? '');
        $email_name = trim($_POST['email_name'] ?? '');
        $email_role = trim($_POST['email_role'] ?? 'user');

        if (empty($email)) {
            $message = 'Email is required!';
            $message_type = 'error';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = 'Invalid email format!';
            $message_type = 'error';
        } else {
            // Check for duplicate
            $check = $conn->prepare("SELECT id FROM registered_emails WHERE email = ?");
            $check->execute([$email]);

            if ($check->fetch()) {
                $message = 'Email already registered!';
                $message_type = 'error';
            } else {
                $stmt = $conn->prepare("INSERT INTO registered_emails (email, name, role) VALUES (?, ?, ?)");
                $stmt->execute([$email, $email_name, $email_role]);
                logAudit($conn, 'ADD_REGISTERED_EMAIL', 'Added email: ' . $email, $_SESSION['admin_id']);
                notifySecurity('ADD_REGISTERED_EMAIL', 'Registered new email: ' . $email . ' (' . $email_role . ')', $_SESSION['admin_id']);
                $message = 'Email registered successfully!';
                $message_type = 'success';
            }
        }
    }

    // Update Registered Email
    if (isset($_POST['update_email'])) {
        $email_id = (int)$_POST['email_id'];
        $email = trim($_POST['email'] ?? '');
        $email_name = trim($_POST['email_name'] ?? '');
        $email_role = trim($_POST['email_role'] ?? 'user');

        $stmt = $conn->prepare("UPDATE registered_emails SET email = ?, name = ?, role = ? WHERE id = ?");
        $stmt->execute([$email, $email_name, $email_role, $email_id]);
        logAudit($conn, 'UPDATE_REGISTERED_EMAIL', 'Updated email ID: ' . $email_id, $_SESSION['admin_id']);
        notifySecurity('UPDATE_REGISTERED_EMAIL', 'Updated email ID: ' . $email_id, $_SESSION['admin_id']);
        $message = 'Email updated successfully!';
        $message_type = 'success';
    }

    // Delete Registered Email
    if (isset($_POST['delete_email'])) {
        $email_id = (int)$_POST['email_id'];
        $stmt = $conn->prepare("DELETE FROM registered_emails WHERE id = ?");
        $stmt->execute([$email_id]);
        logAudit($conn, 'DELETE_REGISTERED_EMAIL', 'Deleted email ID: ' . $email_id, $_SESSION['admin_id']);
        notifySecurity('DELETE_REGISTERED_EMAIL', 'Removed registered email ID: ' . $email_id, $_SESSION['admin_id']);
        $message = 'Email removed successfully!';
        $message_type = 'success';
    }

    // Toggle Email Status
    if (isset($_POST['toggle_email_status'])) {
        $email_id = (int)$_POST['email_id'];
        $stmt = $conn->prepare("UPDATE registered_emails SET is_active = IF(is_active = 1, 0, 1) WHERE id = ?");
        $stmt->execute([$email_id]);
        logAudit($conn, 'TOGGLE_REGISTERED_EMAIL', 'Toggled email ID: ' . $email_id, $_SESSION['admin_id']);
        notifySecurity('TOGGLE_REGISTERED_EMAIL', 'Toggled email status ID: ' . $email_id, $_SESSION['admin_id']);
        $message = 'Email status updated!';
        $message_type = 'success';
    }
    }
} 

// Get all modules
$modules = $conn->query("SELECT * FROM modules ORDER BY module_type, module_name")->fetchAll(PDO::FETCH_ASSOC);

// Get Google Login settings
$googleSettings = [];
$stmt = $conn->prepare("SELECT setting_key, setting_value FROM settings WHERE setting_key LIKE 'google_%'");
$stmt->execute();
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $googleSettings[$row['setting_key']] = $row['setting_value'];
}
$google_client_key = $googleSettings['google_client_key'] ?? '';
$google_secret_key = decryptSensitive($googleSettings['google_secret_key'] ?? '');
$google_redirect_uri = $googleSettings['google_redirect_uri'] ?? 'http://localhost/CloudASPIRE/admin/auth-google.php';
$google_login_enabled = $googleSettings['google_login_enabled'] ?? '0';
$google_allowed_domains = $googleSettings['google_allowed_domains'] ?? '';

// Get registered emails
$registered_emails = $conn->query("SELECT * FROM registered_emails ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

// Get editing module
$edit_module = null;
if (isset($_GET['edit_module'])) {
    $stmt = $conn->prepare("SELECT * FROM modules WHERE id = ?");
    $stmt->execute([(int)$_GET['edit_module']]);
    $edit_module = $stmt->fetch();
}

// Get editing email
$edit_email = null;
if (isset($_GET['edit_email'])) {
    $stmt = $conn->prepare("SELECT * FROM registered_emails WHERE id = ?");
    $stmt->execute([(int)$_GET['edit_email']]);
    $edit_email = $stmt->fetch();
}

require_once __DIR__ . '/../../app/Views/admin/settings/module.php';
