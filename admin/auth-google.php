<?php
require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/security_notifier.php';

// Check if Google Login module is enabled
$stmt = $conn->prepare("SELECT * FROM modules WHERE module_key = 'google_login' AND is_active = 1");
$stmt->execute();
$googleModule = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$googleModule) {
    $_SESSION['error'] = 'Google Login is not enabled.';
    header('Location: login.php');
    exit;
}

// Fetch Google OAuth credentials from settings table
$googleSettings = [];
$stmt = $conn->prepare("SELECT setting_key, setting_value FROM settings WHERE setting_key LIKE 'google_%'");
$stmt->execute();
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $googleSettings[$row['setting_key']] = $row['setting_value'];
}

$client_id = trim($googleSettings['google_client_key'] ?? '');
$client_secret = decryptSensitive($googleSettings['google_secret_key'] ?? '');
$redirect_uri = trim($googleSettings['google_redirect_uri'] ?? '');
if (empty($redirect_uri)) {
    $redirect_uri = (defined('SITE_URL') ? SITE_URL : 'http://localhost/CloudASPIRE') . '/admin/auth-google.php';
}

if (empty($client_id) || empty($client_secret)) {
    $_SESSION['error'] = 'Google OAuth is not properly configured.';
    header('Location: login.php');
    exit;
}

// Handle the callback
if (isset($_GET['code'])) {
    // Validate CSRF state parameter
    $state = $_GET['state'] ?? '';
    $savedState = $_SESSION['oauth_state'] ?? '';
    if (empty($state) || empty($savedState) || !hash_equals($savedState, $state)) {
        $_SESSION['error'] = 'Invalid OAuth security state. Please try logging in again.';
        unset($_SESSION['oauth_state']);
        header('Location: login.php');
        exit;
    }
    unset($_SESSION['oauth_state']);

    $token_url = 'https://oauth2.googleapis.com/token';
    $token_data = [
        'code' => $_GET['code'],
        'client_id' => $client_id,
        'client_secret' => $client_secret,
        'redirect_uri' => $redirect_uri,
        'grant_type' => 'authorization_code'
    ];

    $ch = curl_init($token_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($token_data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code !== 200) {
        $_SESSION['error'] = 'Failed to authenticate with Google. Please try again.';
        error_log('[Google OAuth] Token exchange failed: HTTP ' . $http_code . ' - ' . $response);
        header('Location: login.php');
        exit;
    }

    $tokens = json_decode($response, true);
    if (!isset($tokens['access_token'])) {
        $_SESSION['error'] = 'Invalid response from Google.';
        header('Location: login.php');
        exit;
    }

    // Get user info via cURL
    $user_info_url = 'https://www.googleapis.com/oauth2/v2/userinfo?access_token=' . urlencode($tokens['access_token']);
    $chUser = curl_init($user_info_url);
    curl_setopt($chUser, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($chUser, CURLOPT_TIMEOUT, 15);
    curl_setopt($chUser, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($chUser, CURLOPT_SSL_VERIFYHOST, 2);
    $user_response = curl_exec($chUser);
    $user_http_code = curl_getinfo($chUser, CURLINFO_HTTP_CODE);
    curl_close($chUser);

    if ($user_http_code !== 200 || !$user_response) {
        $_SESSION['error'] = 'Failed to get user info from Google.';
        header('Location: login.php');
        exit;
    }

    $user_info = json_decode($user_response, true);

    if (!isset($user_info['email'])) {
        $_SESSION['error'] = 'Could not retrieve email from Google.';
        header('Location: login.php');
        exit;
    }

    $email = filter_var($user_info['email'], FILTER_SANITIZE_EMAIL);
    $name = htmlspecialchars($user_info['name'] ?? '', ENT_QUOTES, 'UTF-8');
    $google_id = $user_info['id'] ?? '';

    // Check if email is registered and active in registered_emails table or accounts table
    $stmt = $conn->prepare("SELECT * FROM registered_emails WHERE email = ? AND is_active = 1");
    $stmt->execute([$email]);
    $registeredUser = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$registeredUser) {
        // Fallback: check accounts table
        $stmt = $conn->prepare("SELECT * FROM accounts WHERE email = ? AND is_active = 1");
        $stmt->execute([$email]);
        $registeredUser = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    if (!$registeredUser) {
        $_SESSION['error'] = 'Your email is not registered or is deactivated. Please contact an administrator.';
        header('Location: login.php');
        exit;
    }

 // Get admin account by email
 $stmt = $conn->prepare("SELECT * FROM admin WHERE email = ?");
 $stmt->execute([$email]);
 $account = $stmt->fetch(PDO::FETCH_ASSOC);

 if (!$account) {
 // Auto-create account with a strong random password
 $random_password = bin2hex(random_bytes(16));
 $hashed_password = password_hash($random_password, PASSWORD_BCRYPT);
 $stmt = $conn->prepare("INSERT INTO admin (name, email, password, role, is_active, created_at) VALUES (?, ?, ?, ?, 1, NOW())");
 $stmt->execute([$name, $email, $hashed_password, $registeredUser['role']]);
 $account_id = $conn->lastInsertId();

 // Fetch the newly created account
 $stmt = $conn->prepare("SELECT * FROM admin WHERE id = ?");
 $stmt->execute([$account_id]);
 $account = $stmt->fetch(PDO::FETCH_ASSOC);

 notifySecurity('GOOGLE_AUTO_CREATE', "Google OAuth auto-created admin account for: $email", 0);
 }

    // Prevent session fixation
    session_regenerate_id(true);

    // Set session
    $_SESSION['admin_id'] = $account['id'];
    $_SESSION['admin_name'] = $account['name'];
    $_SESSION['admin_username'] = $account['username'] ?? $email;
    $_SESSION['admin_email'] = $account['email'];
    $_SESSION['admin_role'] = $account['role'];
    $_SESSION['login_type'] = 'google';
    $_SESSION['login_welcome'] = true;
    $_SESSION['login_time'] = time();
    $_SESSION['login_ip'] = $_SERVER['REMOTE_ADDR'] ?? '';

 // Update last login
 $stmt = $conn->prepare("UPDATE admin SET last_login = NOW(), google_id = ? WHERE id = ?");
 $stmt->execute([$google_id, $account['id']]);

    notifyLogin(true, $account['id'], $account['name'], $account['email']);

    global $securityNotifier;
    if (isset($securityNotifier)) {
        $securityNotifier->createSession($account['id']);
    }

 header('Location: dashboard.php');
 exit;

} elseif (isset($_GET['error'])) {
 $_SESSION['error'] = 'Google authorization failed: ' . htmlspecialchars($_GET['error']);
 header('Location: login.php');
 exit;
} else {
 // Show error if no code parameter
 $_SESSION['error'] = 'Invalid Google login request.';
 header('Location: login.php');
 exit;
}
