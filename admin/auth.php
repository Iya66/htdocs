<?php
if (session_status() === PHP_SESSION_NONE) {
 session_start();
}

// Determine correct login path based on directory depth
$script_path = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$parts = explode('/', trim($script_path, '/'));
$admin_index = array_search('admin', $parts);
$depth = ($admin_index !== false) ? count($parts) - $admin_index - 2 : 0;
$admin_prefix = str_repeat('../', max(0, $depth));
$login_path = $admin_prefix . 'login';

// Check if logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ' . $login_path);
    exit;
}

// Check for session timeout (30 minutes idle)
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
    // Unset and destroy session so login.php doesn't redirect back to dashboard
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    header('Location: ' . $login_path . '?timeout=1');
    exit;
}

// Update last activity timestamp
$_SESSION['last_activity'] = time();
?>
