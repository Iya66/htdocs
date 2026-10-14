<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/email_helper.php';

// Send logout notification before destroying session
if (isset($_SESSION['admin_id']) && isset($_SESSION['admin_email'])) {
    $admin_id = $_SESSION['admin_id'];
    $admin_email = $_SESSION['admin_email'];
    $admin_name = $_SESSION['admin_name'] ?? 'Admin';

    if (isset($conn)) {
        logAudit($conn, 'LOGOUT', 'Admin logged out: ' . $admin_email, $admin_id);
    }

    sendAdminLogoutEmail($admin_name, $admin_email);
}

// Invalidate session cookie and clear all session data
$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();

header('Location: login.php');
exit;
?>
