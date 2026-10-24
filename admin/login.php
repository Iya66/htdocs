<?php
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
    } else {
        header('Location: dashboard.php');
        exit;
    }
}

require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/email_helper.php';
require_once __DIR__ . '/../app/security_notifier.php';

$error = '';
$success = '';
$googleLoginEnabled = false;
$googleAuthUrl = '';

$stmt = $conn->prepare("SELECT * FROM modules WHERE module_key = 'google_login' AND is_active = 1");
$stmt->execute();
$googleModule = $stmt->fetch(PDO::FETCH_ASSOC);

if ($googleModule) {
 $settings = [];
 $stmt = $conn->prepare("SELECT setting_key, setting_value FROM settings WHERE setting_key LIKE 'google_%'");
 $stmt->execute();
 while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
 $settings[$row['setting_key']] = $row['setting_value'];
 }

    if (!empty($settings['google_client_key']) && !empty($settings['google_secret_key'])) {
        $googleLoginEnabled = true;
        $client_key = $settings['google_client_key'];
        $redirect_uri = $settings['google_redirect_uri'] ?? 'http://localhost/CloudASPIRE/admin/auth-google.php';
        if (empty($_SESSION['oauth_state'])) {
            $_SESSION['oauth_state'] = bin2hex(random_bytes(32));
        }
        $googleAuthUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
            'client_id' => $client_key,
            'redirect_uri' => $redirect_uri,
            'response_type' => 'code',
            'scope' => 'email profile',
            'state' => $_SESSION['oauth_state'],
            'access_type' => 'online',
            'prompt' => 'select_account'
        ]);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
 $action = $_POST['view_state'] ?? 'login';

 if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
 $error = 'Invalid request. Please refresh and try again.';
 $view_state = in_array($action, ['send_otp', 'verify_otp', 'reset_password']) ? ($action === 'send_otp' ? 'forgot' : $action) : 'login';
 } else {
 if ($action === 'login') {
 $rateLimitKey = 'login_' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
 if (!checkRateLimit($rateLimitKey, 5, 900)) {
 $error = 'Too many attempts. Please try again after 15 minutes.';
 notifyLogin(false, null, 'Unknown', $_POST['email'] ?? '', 'Rate limit exceeded');
 } else {
 $email = trim($_POST['email'] ?? '');
 $password = $_POST['password'] ?? '';

 if (empty($email) || empty($password)) {
 $error = 'Please fill in all fields.';
 } else {
 $stmt = $conn->prepare("SELECT id, name, username, email, password, role FROM admin WHERE email = ? AND is_active = 1 LIMIT 1");
 $stmt->execute([$email]);
 $admin = $stmt->fetch(PDO::FETCH_ASSOC);

 if ($admin && password_verify($password, $admin['password'])) {
 session_regenerate_id(true);
 unset($_SESSION['login_attempts']);

 $_SESSION['admin_id'] = $admin['id'];
 $_SESSION['admin_name'] = $admin['name'];
 $_SESSION['admin_username'] = $admin['username'];
 $_SESSION['admin_email'] = $admin['email'];
 $_SESSION['admin_role'] = $admin['role'];
 $_SESSION['login_type'] = 'manual';
 $_SESSION['login_welcome'] = true;
 $_SESSION['login_time'] = time();
 $_SESSION['login_ip'] = $_SERVER['REMOTE_ADDR'] ?? '';

 notifyLogin(true, $admin['id'], $admin['name'], $admin['email']);
 sendAdminLoginEmail($admin['name'], $admin['email'], $_SERVER['REMOTE_ADDR'] ?? '');

 global $securityNotifier;
 $securityNotifier->createSession($admin['id']);

 header('Location: dashboard.php');
 exit;
 } else {
 $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
 notifyLogin(false, null, 'Unknown', $email, 'Invalid credentials');

 if ($_SESSION['login_attempts'] >= 5) {
 $_SESSION['login_attempts'] = 0;
 $error = 'Too many failed attempts. Please try again after 15 minutes.';
 } else {
 $error = 'Invalid email or password.';
 }
 }
 }
 }
 $view_state = 'login';
 } elseif ($action === 'send_otp' || $action === 'resend_otp') {
 $email = trim($_POST['forgot_email'] ?? '') ?: ($_SESSION['reset_email'] ?? '');
 if (empty($email)) {
 $error = 'Please enter your email address.';
 $view_state = 'forgot';
 } else {
 $otpRateLimitKey = 'otp_request_' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . '_' . md5($email);
 if (!checkRateLimit($otpRateLimitKey, 3, 1800)) {
 $error = 'Too many OTP requests. Please try again after 30 minutes.';
 $view_state = 'forgot';
 } else {
 $stmt = $conn->prepare("SELECT id, name FROM admin WHERE email = ? AND is_active = 1 LIMIT 1");
 $stmt->execute([$email]);
 $admin = $stmt->fetch(PDO::FETCH_ASSOC);

 				if ($admin) {
					$otp = sprintf("%06d", random_int(100000, 999999));
					$_SESSION['reset_email'] = $email;
					$_SESSION['reset_otp_hash'] = password_hash($otp, PASSWORD_BCRYPT);
					$_SESSION['reset_otp_expires'] = time() + 600;
					$_SESSION['reset_otp_attempts'] = 0;

					$subject = "Password Reset Verification Code";
					$body = '
					<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e2e8f0; border-radius: 12px; background: #ffffff;">
						<h2 style="color: #6366f1; text-align: center; margin-bottom: 24px;">CloudASPIRE Password Reset</h2>
						<p>Hello <strong>' . htmlspecialchars($admin['name']) . '</strong>,</p>
						<p>You requested a password reset for your CloudASPIRE admin account. Use the verification code below to proceed:</p>
						<div style="text-align: center; margin: 32px 0;">
							<span style="font-size: 32px; font-weight: 700; letter-spacing: 6px; color: #4f46e5; background: #f5f3ff; padding: 12px 24px; border-radius: 8px; border: 1px dashed #c7d2fe;">' . $otp . '</span>
						</div>
						<p style="color: #64748b; font-size: 13px;">This verification code is valid for <strong>10 minutes</strong>. If you did not request this, you can safely ignore this email.</p>
						<hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 24px 0;">
						<p style="color: #94a3b8; font-size: 11px; text-align: center;">This is an automated security notification. Please do not reply.</p>
					</div>';

					$mail_res = sendEmail($email, $subject, $body);
					if ($mail_res['success'] || (defined('SITE_URL') && str_contains(SITE_URL, 'localhost'))) {
						$success = 'Verification code sent to your email!';
						$view_state = 'verify_otp';
					} else {
						$error = 'Failed to send verification email: ' . $mail_res['message'];
						$view_state = 'forgot';
					}
				} else {
					// Uniform message to prevent email enumeration
					$success = 'If an active account with that email exists, a verification code has been sent.';
					$view_state = 'forgot';
				}
			}
		}
	} elseif ($action === 'verify_otp') {
		if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
			$error = 'Invalid request. Please refresh and try again.';
			$view_state = 'verify_otp';
		} else {
			$entered_otp = trim($_POST['otp'] ?? '');
			if (empty($entered_otp)) {
				$error = 'Please enter the verification code.';
				$view_state = 'verify_otp';
			} elseif (!isset($_SESSION['reset_otp_hash'], $_SESSION['reset_otp_expires'], $_SESSION['reset_email'])) {
				$error = 'Session expired. Please request a new verification code.';
				$view_state = 'forgot';
			} elseif (time() > $_SESSION['reset_otp_expires']) {
				unset($_SESSION['reset_otp_hash'], $_SESSION['reset_otp_expires'], $_SESSION['reset_otp_attempts']);
				$error = 'Verification code has expired. Please request a new code.';
				$view_state = 'forgot';
			} else {
				$_SESSION['reset_otp_attempts'] = ($_SESSION['reset_otp_attempts'] ?? 0) + 1;
				if ($_SESSION['reset_otp_attempts'] > 4) {
					unset($_SESSION['reset_otp_hash'], $_SESSION['reset_otp_expires'], $_SESSION['reset_email'], $_SESSION['reset_otp_attempts']);
					$error = 'Too many failed attempts. For security, please request a new verification code.';
					$view_state = 'forgot';
				} elseif (!password_verify($entered_otp, $_SESSION['reset_otp_hash'])) {
					$remaining = 5 - $_SESSION['reset_otp_attempts'];
					$error = "Invalid verification code. $remaining attempts remaining.";
					$view_state = 'verify_otp';
				} else {
					unset($_SESSION['reset_otp_hash'], $_SESSION['reset_otp_expires'], $_SESSION['reset_otp_attempts']);
					$_SESSION['otp_verified'] = true;
					$_SESSION['otp_verified_at'] = time();
					$success = 'Code verified successfully! You can now set a new password.';
					$view_state = 'reset_password';
				}
			}
		}
	} elseif ($action === 'reset_password') {
 $new_password = $_POST['new_password'] ?? '';
 $confirm_password = $_POST['confirm_password'] ?? '';

 $otpExpired = (isset($_SESSION['otp_verified_at']) && time() - $_SESSION['otp_verified_at'] > 900);

 if (!isset($_SESSION['otp_verified']) || $_SESSION['otp_verified'] !== true || !isset($_SESSION['reset_email']) || $otpExpired) {
 $error = 'Session expired. Please restart password reset process.';
 $view_state = 'forgot';
 	unset($_SESSION['otp_verified'], $_SESSION['reset_email'], $_SESSION['reset_otp_hash'], $_SESSION['reset_otp_expires'], $_SESSION['otp_verified_at'], $_SESSION['reset_otp_attempts']);
 } elseif (empty($new_password) || empty($confirm_password)) {
 $error = 'Please fill in all password fields.';
 $view_state = 'reset_password';
 } elseif ($new_password !== $confirm_password) {
 $error = 'Passwords do not match.';
 $view_state = 'reset_password';
 } elseif ($errors = validatePasswordStrength($new_password)) {
 $error = 'Password must contain: 8+ characters, uppercase, lowercase, number, and special character.';
 $view_state = 'reset_password';
 } else {
 $email = $_SESSION['reset_email'];

 $hashedPassword = password_hash($new_password, PASSWORD_BCRYPT);
 $stmt = $conn->prepare("UPDATE admin SET password = ? WHERE email = ?");
 $stmt->execute([$hashedPassword, $email]);

 $stmtUser = $conn->prepare("SELECT id, name FROM admin WHERE email = ? LIMIT 1");
 $stmtUser->execute([$email]);
 $user = $stmtUser->fetch(PDO::FETCH_ASSOC);
 $userId = $user['id'] ?? null;
 $userName = $user['name'] ?? 'Admin';

 logAudit($conn, 'PASSWORD_RESET', "Password reset successfully via OTP for: $userName", $userId);
 notifySecurity('PASSWORD_RESET', "Admin password reset via OTP: $userName ($email)", $userId);

 unset($_SESSION['reset_otp_hash']);
 unset($_SESSION['reset_otp_expires']);
 unset($_SESSION['reset_otp_attempts']);
 unset($_SESSION['reset_email']);
 unset($_SESSION['otp_verified']);
 unset($_SESSION['otp_verified_at']);

 $success = 'Password reset successfully! You can now sign in.';
 $view_state = 'login';
 }
 }
 }
} else {
 $view_state = $_GET['state'] ?? 'login';
 if (!in_array($view_state, ['login', 'forgot', 'verify_otp', 'reset_password'])) {
 $view_state = 'login';
 }
}

if (isset($_SESSION['error'])) {
 $error = $_SESSION['error'];
 unset($_SESSION['error']);
}

$stmt = $conn->prepare("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('site_name', 'site_favicon', 'site_logo', 'theme_primary', 'theme_accent', 'theme_btn_radius', 'theme_card_radius', 'theme_bg_primary')");
$stmt->execute();
$settings = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
 $settings[$row['setting_key']] = $row['setting_value'];
}

$site_name = $settings['site_name'] ?? 'CloudASPIRE';
$site_favicon = $settings['site_favicon'] ?? '';
$site_logo = $settings['site_logo'] ?? '';
$theme_primary = $settings['theme_primary'] ?? '#6366f1';
$theme_accent = $settings['theme_accent'] ?? '#8b5cf6';
$theme_btn_radius = $settings['theme_btn_radius'] ?? '10px';
$theme_card_radius = $settings['theme_card_radius'] ?? '16px';
$theme_bg_primary = $settings['theme_bg_primary'] ?? '';

$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">

<head>
 <meta charset="UTF-8">
 <meta name="viewport" content="width=device-width, initial-scale=1.0">
 <title>Sign In - <?= htmlspecialchars($site_name) ?></title>
 <?php if ($site_favicon): ?>
 <link rel="icon" href="../<?= htmlspecialchars($site_favicon) ?>">
 <?php endif; ?>
 <link rel="preconnect" href="https://fonts.googleapis.com">
 <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
 <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
 <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

 <style>
 :root {
 --primary: <?= htmlspecialchars($theme_primary) ?>;
 --primary-soft: color-mix(in srgb, var(--primary) 10%, transparent);
 --primary-shadow: color-mix(in srgb, var(--primary) 30%, transparent);
 --accent: <?= htmlspecialchars($theme_accent) ?>;
 --btn-radius: <?= htmlspecialchars($theme_btn_radius) ?>;
 --card-radius: <?= htmlspecialchars($theme_card_radius) ?>;
 --bg-page: #ffffff;
 --bg-card: #ffffff;
 --text-main: #0f172a;
 --text-muted: #64748b;
 --border-color: #e2e8f0;
 --input-bg: #ffffff;
 --input-border: #cbd5e1;
 --input-focus: #f1f5f9;
 }

 [data-theme="dark"] {
 --bg-page: <?= $theme_bg_primary ? htmlspecialchars($theme_bg_primary) : '#0f172a' ?>;
 --bg-card: #1e293b;
 --text-main: #f8fafc;
 --text-muted: #94a3b8;
 --border-color: #334155;
 --input-bg: #0f172a;
 --input-border: #475569;
 --input-focus: #1e293b;
 }

 *,
 *::before,
 *::after {
 margin: 0;
 padding: 0;
 box-sizing: border-box;
 }

 body {
 font-family: 'Inter', system-ui, -apple-system, sans-serif;
 background-color: var(--bg-page);
 color: var(--text-main);
 min-height: 100vh;
 display: flex;
 align-items: center;
 justify-content: center;
 overflow-x: hidden;
 transition: background-color 0.4s ease;
 }

 .bg-mesh {
 position: fixed;
 top: 0;
 left: 0;
 width: 100vw;
 height: 100vh;
 z-index: -1;
 overflow: hidden;
 pointer-events: none;
 }

 .mesh-blob {
 position: absolute;
 filter: blur(90px);
 opacity: 0.4;
 border-radius: 50%;
 animation: drift 15s infinite alternate ease-in-out;
 }

 .mesh-1 {
 background: var(--primary);
 width: 40vw;
 height: 40vw;
 top: -10vw;
 left: -10vw;
 }

 .mesh-2 {
 background: var(--accent);
 width: 30vw;
 height: 30vw;
 bottom: -5vw;
 right: -5vw;
 animation-delay: -5s;
 }

 @keyframes drift {
 0% { transform: translate(0, 0) scale(1); }
 100% { transform: translate(5%, 10%) scale(1.1); }
 }

 .auth-wrapper {
 width: 100%;
 max-width: 440px;
 padding: 2rem;
 animation: fadeUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
 opacity: 0;
 transform: translateY(20px);
 }

 @keyframes fadeUp {
 to { opacity: 1; transform: translateY(0); }
 }

 .auth-card {
 background-color: var(--bg-card);
 border-radius: var(--card-radius);
 box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.1);
 border: 1px solid var(--border-color);
 padding: 3rem 2.5rem;
 position: relative;
 z-index: 10;
 }

 .auth-header {
 text-align: center;
 margin-bottom: 2.5rem;
 }

 .logo-container {
 margin-bottom: 1.5rem;
 display: inline-flex;
 justify-content: center;
 align-items: center;
 width: 64px;
 height: 64px;
 border-radius: var(--btn-radius);
 background: linear-gradient(135deg, var(--primary), var(--accent));
 color: #fff;
 font-size: 28px;
 box-shadow: 0 10px 25px var(--primary-shadow);
 }

 .logo-container img {
 max-width: 100%;
 max-height: 100%;
 border-radius: var(--btn-radius);
 object-fit: contain;
 }

 .auth-header h1 {
 font-size: 1.75rem;
 font-weight: 700;
 letter-spacing: -0.025em;
 margin-bottom: 0.5rem;
 }

 .auth-header p {
 color: var(--text-muted);
 font-size: 0.95rem;
 }

 .alert-error {
 background-color: rgba(239, 68, 68, 0.1);
 color: #dc2626;
 border: 1px solid rgba(239, 68, 68, 0.2);
 padding: 1rem;
 border-radius: var(--btn-radius);
 margin-bottom: 1.5rem;
 font-size: 0.9rem;
 display: flex;
 align-items: center;
 gap: 0.75rem;
 animation: shake 0.4s ease-in-out;
 }

 [data-theme="dark"] .alert-error { color: #f87171; }

 .alert-success {
 background-color: rgba(16, 185, 129, 0.1);
 color: #059669;
 border: 1px solid rgba(16, 185, 129, 0.2);
 padding: 1rem;
 border-radius: var(--btn-radius);
 margin-bottom: 1.5rem;
 font-size: 0.9rem;
 display: flex;
 align-items: center;
 gap: 0.75rem;
 }

 [data-theme="dark"] .alert-success { color: #34d399; }

 @keyframes shake {
 0%, 100% { transform: translateX(0); }
 25% { transform: translateX(-4px); }
 75% { transform: translateX(4px); }
 }

 .form-group { margin-bottom: 1.25rem; }

 .form-label {
 display: block;
 font-size: 0.875rem;
 font-weight: 600;
 margin-bottom: 0.5rem;
 color: var(--text-main);
 }

 .input-group { position: relative; }

 .form-control {
 width: 100%;
 padding: 0.875rem 1rem 0.875rem 2.75rem;
 background-color: var(--input-bg);
 border: 1px solid var(--input-border);
 border-radius: var(--btn-radius);
 color: var(--text-main);
 font-size: 0.95rem;
 font-family: inherit;
 transition: all 0.2s ease;
 }

 .form-control:hover { border-color: var(--text-muted); }

 .form-control:focus {
 outline: none;
 border-color: var(--primary);
 box-shadow: 0 0 0 4px var(--primary-soft);
 background-color: var(--input-focus);
 }

 .input-icon {
 position: absolute;
 left: 1rem;
 top: 50%;
 transform: translateY(-50%);
 color: var(--text-muted);
 transition: color 0.2s ease;
 pointer-events: none;
 }

 .form-control:focus + .input-icon { color: var(--primary); }

 .password-toggle {
 position: absolute;
 right: 1rem;
 top: 50%;
 transform: translateY(-50%);
 background: none;
 border: none;
 color: var(--text-muted);
 cursor: pointer;
 padding: 0.25rem;
 font-size: 0.95rem;
 transition: color 0.2s ease;
 z-index: 2;
 display: flex;
 align-items: center;
 justify-content: center;
 }

 .password-toggle:hover { color: var(--primary); }

 .btn-submit {
 width: 100%;
 padding: 0.875rem;
 background-color: var(--primary);
 color: #ffffff;
 border: none;
 border-radius: var(--btn-radius);
 font-size: 1rem;
 font-weight: 600;
 cursor: pointer;
 transition: all 0.2s ease;
 margin-top: 1rem;
 display: flex;
 justify-content: center;
 align-items: center;
 gap: 0.5rem;
 }

 .btn-submit:hover {
 filter: brightness(1.1);
 transform: translateY(-1px);
 box-shadow: 0 8px 20px var(--primary-shadow);
 }

 .btn-submit:active { transform: translateY(0); }

 .divider {
 display: flex;
 align-items: center;
 text-align: center;
 margin: 2rem 0;
 color: var(--text-muted);
 font-size: 0.875rem;
 font-weight: 500;
 }

 .divider::before, .divider::after {
 content: '';
 flex: 1;
 border-bottom: 1px solid var(--border-color);
 }

 .divider span { padding: 0 1rem; }

 .btn-social {
 width: 100%;
 padding: 0.875rem;
 background-color: transparent;
 color: var(--text-main);
 border: 1px solid var(--input-border);
 border-radius: var(--btn-radius);
 font-size: 0.95rem;
 font-weight: 600;
 cursor: pointer;
 transition: all 0.2s ease;
 display: flex;
 justify-content: center;
 align-items: center;
 gap: 0.75rem;
 text-decoration: none;
 }

 .btn-social:hover {
 background-color: var(--bg-page);
 border-color: var(--text-muted);
 }

 .btn-social svg {
 width: 20px;
 height: 20px;
 }

 .auth-footer {
 margin-top: 2rem;
 text-align: center;
 font-size: 0.875rem;
 color: var(--text-muted);
 }

 .auth-footer a {
 color: var(--primary);
 text-decoration: none;
 font-weight: 500;
 transition: color 0.2s;
 }

 .auth-footer a:hover { text-decoration: underline; }

 .theme-toggle-btn {
 position: absolute;
 top: 1.5rem;
 right: 1.5rem;
 background: var(--bg-card);
 border: 1px solid var(--border-color);
 color: var(--text-muted);
 width: 40px;
 height: 40px;
 border-radius: 50%;
 display: flex;
 align-items: center;
 justify-content: center;
 cursor: pointer;
 transition: all 0.2s ease;
 z-index: 20;
 box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
 }

 .theme-toggle-btn:hover {
 color: var(--primary);
 border-color: var(--primary);
 transform: rotate(15deg);
 }
 </style>
</head>

<body>
 <div id="action-loader" style="display: none; position: fixed; inset: 0; background: rgba(255,255,255,0.8); backdrop-filter: blur(5px); z-index: 99999; justify-content: center; align-items: center; flex-direction: column;">
 <img src="../files/lcons/loading.gif" alt="Loading..." style="width: 80px; height: 80px; object-fit: contain;">
 <div style="margin-top: 16px; font-weight: 600; color: var(--primary);">Signing in...</div>
 </div>

 <div class="bg-mesh">
 <div class="mesh-blob mesh-1"></div>
 <div class="mesh-blob mesh-2"></div>
 </div>

 <button class="theme-toggle-btn" id="themeToggle" aria-label="Toggle Dark Mode">
 <i class="fa-solid fa-moon"></i>
 </button>

 <div class="auth-wrapper">
 <div class="auth-card">

 <div class="auth-header">
 <div class="logo-container">
 <?php if ($site_logo): ?>
 <img src="../<?= htmlspecialchars($site_logo) ?>" alt="Logo">
 <?php else: ?>
 <i class="fa-solid fa-layer-group"></i>
 <?php endif; ?>
 </div>
 <?php if ($view_state === 'login'): ?>
 <h1>Welcome Back</h1>
 <p>Sign in to manage <?= htmlspecialchars($site_name) ?></p>
 <?php elseif ($view_state === 'forgot'): ?>
 <h1>Forgot Password</h1>
 <p>Enter your email to receive a 6-digit OTP code</p>
 <?php elseif ($view_state === 'verify_otp'): ?>
 <h1>Verify OTP</h1>
 <p>Enter the 6-digit code sent to your email</p>
 <?php elseif ($view_state === 'reset_password'): ?>
 <h1>Reset Password</h1>
 <p>Set a new, strong password for your account</p>
 <?php endif; ?>
 </div>

 <?php if ($error): ?>
 <div class="alert-error" role="alert">
 <i class="fa-solid fa-circle-exclamation"></i>
 <span><?= htmlspecialchars($error) ?></span>
 </div>
 <?php endif; ?>

 <?php if ($success): ?>
 <div class="alert-success" role="alert">
 <i class="fa-solid fa-circle-check"></i>
 <span><?= htmlspecialchars($success) ?></span>
 </div>
 <?php endif; ?>

 <?php if ($view_state === 'login'): ?>
 <form method="POST" action="" id="loginForm">
 <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
 <input type="hidden" name="view_state" value="login">

 <div class="form-group">
 <label for="email" class="form-label">Email Address</label>
 <div class="input-group">
 <input type="email" id="email" name="email" class="form-control" placeholder="name@company.com" required autocomplete="email" autofocus>
 <i class="fa-regular fa-envelope input-icon"></i>
 </div>
 </div>

 <div class="form-group">
 <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
 <label for="password" class="form-label" style="margin-bottom: 0;">Password</label>
 <a href="login?state=forgot" style="color: var(--primary); font-size: 0.8rem; text-decoration: none; font-weight: 500;">Forgot Password?</a>
 </div>
 <div class="input-group">
 <input type="password" id="password" name="password" class="form-control" placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;" required autocomplete="current-password" style="padding-right: 2.75rem;">
 <i class="fa-solid fa-lock input-icon"></i>
 <button type="button" class="password-toggle" onclick="togglePassword('password', this)" aria-label="Show password">
 <i class="fa-regular fa-eye"></i>
 </button>
 </div>
 </div>

 <button type="submit" class="btn-submit">
 Sign In
 <i class="fa-solid fa-arrow-right"></i>
 </button>
 </form>

 <?php if ($googleLoginEnabled): ?>
 <div class="divider"><span>Or continue with</span></div>

 <a href="<?= htmlspecialchars($googleAuthUrl) ?>" class="btn-social">
 <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
 <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" />
 <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
 <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" />
 <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" />
 </svg>
 Google
 </a>
 <?php endif; ?>

 <?php elseif ($view_state === 'forgot'): ?>
 <form method="POST" action="" id="forgotForm">
 <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
 <input type="hidden" name="view_state" value="send_otp">

 <div class="form-group">
 <label for="forgot_email" class="form-label">Email Address</label>
 <div class="input-group">
 <input type="email" id="forgot_email" name="forgot_email" class="form-control" placeholder="name@company.com" required autocomplete="email" autofocus>
 <i class="fa-regular fa-envelope input-icon"></i>
 </div>
 </div>

 <button type="submit" class="btn-submit">
 Send OTP Code
 <i class="fa-solid fa-paper-plane"></i>
 </button>
 </form>

 <?php elseif ($view_state === 'verify_otp'): ?>
 <form method="POST" action="" id="verifyForm">
 <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
 <input type="hidden" name="view_state" value="verify_otp">

 <div class="form-group">
 <label for="otp" class="form-label">Verification OTP</label>
 <div class="input-group">
 <input type="text" id="otp" name="otp" class="form-control" placeholder="123456" value="" required autocomplete="off" autofocus maxlength="6" pattern="[0-9]{6}" inputmode="numeric" style="letter-spacing: 4px; font-weight: 600; text-align: center; padding-left: 1rem;">
 </div>
 </div>

 <button type="submit" class="btn-submit">
 Verify Code
 <i class="fa-solid fa-check"></i>
 </button>
 </form>

 <div class="divider" style="margin-top: 1.5rem;"><span>Didn't receive the code?</span></div>

 <form method="POST" action="" id="resendForm">
 <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
 <input type="hidden" name="view_state" value="resend_otp">
 <input type="hidden" name="forgot_email" value="<?= htmlspecialchars($_SESSION['reset_email'] ?? '') ?>">

 <button type="submit" class="btn-submit" id="resendBtn" style="background-color: var(--text-muted);">
 <i class="fa-solid fa-paper-plane"></i>
 <span>Resend OTP</span>
 </button>
 </form>

 <?php elseif ($view_state === 'reset_password'): ?>
 <form method="POST" action="" id="resetForm">
 <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
 <input type="hidden" name="view_state" value="reset_password">

 <div class="form-group">
 <label for="new_password" class="form-label">New Password</label>
 <div class="input-group">
 <input type="password" id="new_password" name="new_password" class="form-control" placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;" required autocomplete="new-password" autofocus style="padding-right: 2.75rem;">
 <i class="fa-solid fa-lock input-icon"></i>
 <button type="button" class="password-toggle" onclick="togglePassword('new_password', this)" aria-label="Show password">
 <i class="fa-regular fa-eye"></i>
 </button>
 </div>
 </div>

 <div class="form-group">
 <label for="confirm_password" class="form-label">Confirm New Password</label>
 <div class="input-group">
 <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;" required autocomplete="new-password" style="padding-right: 2.75rem;">
 <i class="fa-solid fa-lock input-icon"></i>
 <button type="button" class="password-toggle" onclick="togglePassword('confirm_password', this)" aria-label="Show password">
 <i class="fa-regular fa-eye"></i>
 </button>
 </div>
 </div>

 <button type="submit" class="btn-submit">
 Update Password
 <i class="fa-solid fa-key"></i>
 </button>
 </form>
 <?php endif; ?>

 <div class="auth-footer">
 <?php if ($view_state !== 'login'): ?>
 <a href="login?state=login">&larr; Back to Login</a>
 <?php else: ?>
 <a href="/">&larr; Back to Website</a>
 <?php endif; ?>
 </div>
 </div>
 </div>

 <script>
 function togglePassword(inputId, btn) {
 const input = document.getElementById(inputId);
 const icon = btn.querySelector('i');
 if (input.type === 'password') {
 input.type = 'text';
 icon.classList.remove('fa-eye');
 icon.classList.add('fa-eye-slash');
 btn.setAttribute('aria-label', 'Hide password');
 } else {
 input.type = 'password';
 icon.classList.remove('fa-eye-slash');
 icon.classList.add('fa-eye');
 btn.setAttribute('aria-label', 'Show password');
 }
 }

 const themeToggle = document.getElementById('themeToggle');
 const icon = themeToggle.querySelector('i');
 const html = document.documentElement;

 function updateIcon(theme) {
 icon.className = theme === 'dark' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
 }

 const savedTheme = localStorage.getItem('theme') || 'light';
 html.setAttribute('data-theme', savedTheme);
 updateIcon(savedTheme);

 themeToggle.addEventListener('click', () => {
 const newTheme = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
 html.setAttribute('data-theme', newTheme);
 localStorage.setItem('theme', newTheme);
 updateIcon(newTheme);
 });

 document.querySelectorAll('form').forEach(form => {
 form.addEventListener('submit', function() {
 const loaderText = document.querySelector('#action-loader div');
 const map = {forgotForm:'Sending OTP...', verifyForm:'Verifying Code...', resetForm:'Resetting Password...', resendForm:'Resending OTP...'};
 loaderText.textContent = map[form.id] || 'Signing in...';
 document.getElementById('action-loader').style.display = 'flex';
 });
 });

 const resendBtn = document.getElementById('resendBtn');
 if (resendBtn) {
 const resendSpan = resendBtn.querySelector('span');
 const RESEND_COOLDOWN = 30;

 function startCountdown(remaining) {
 let countdown = remaining !== undefined ? remaining : RESEND_COOLDOWN;
 resendBtn.disabled = true;
 resendBtn.style.opacity = '0.6';
 resendBtn.style.cursor = 'not-allowed';
 resendSpan.textContent = 'Resend in ' + countdown + 's';

 const timer = setInterval(function() {
 countdown--;
 if (countdown > 0) {
 resendSpan.textContent = 'Resend in ' + countdown + 's';
 } else {
 clearInterval(timer);
 resendBtn.disabled = false;
 resendBtn.style.opacity = '1';
 resendBtn.style.cursor = 'pointer';
 resendSpan.textContent = 'Resend OTP';
 localStorage.removeItem('otp_resend_time');
 }
 }, 1000);
 }

 const lastSentTime = localStorage.getItem('otp_resend_time');
 if (lastSentTime) {
 const elapsed = Math.floor((Date.now() - parseInt(lastSentTime)) / 1000);
 const remaining = RESEND_COOLDOWN - elapsed;
 if (remaining > 0) {
 startCountdown(remaining);
 } else {
 resendBtn.disabled = false;
 resendBtn.style.opacity = '1';
 resendSpan.textContent = 'Resend OTP';
 }
 } else {
 startCountdown(RESEND_COOLDOWN);
 }

 resendBtn.addEventListener('click', function() {
 localStorage.setItem('otp_resend_time', Date.now().toString());
 });
 }
 </script>
 </body>
 </html>