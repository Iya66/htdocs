<?php
/**
 * Admin Index Router
 * Directs authenticated admins to the dashboard and unauthenticated visitors to login.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['admin_id'])) {
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
        header('Location: login.php?timeout=1');
        exit;
    }
    header('Location: dashboard.php');
    exit;
}

header('Location: login.php');
exit;
