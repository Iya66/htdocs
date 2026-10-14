<?php
/**
 * CloudASPIRE Configuration - Enhanced Security & Modular Version
 */

// Start session with secure settings
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_samesite', 'Strict');
    ini_set('session.gc_maxlifetime', 1800); // 30 minutes
    ini_set('session.cookie_lifetime', 0); // Session only

    // Only mark session cookies as Secure over HTTPS.
    $httpsFlag = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);
    ini_set('session.cookie_secure', $httpsFlag ? '1' : '0');

    session_start();
}

// Regenerate session ID periodically to prevent fixation
if (!isset($_SESSION['created'])) {
    $_SESSION['created'] = time();
} else if (time() - $_SESSION['created'] > 1800) {
    session_regenerate_id(true);
    $_SESSION['created'] = time();
}

// Security Headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com https://cdn.jsdelivr.net https://cdn.lordicon.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdnjs.cloudflare.com; font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com; img-src 'self' data: https:; connect-src 'self' https://cdn.lordicon.com");

// Require Database Connection & Schema Layer
require_once __DIR__ . '/database.php';

// Global Site Settings Cache
$settings = [];
$site_name = 'Cloud Aspire';
$site_favicon = '';
$site_logo = '';
$contact_email = 'tamilaninstamedia1@gmail.com';
$contact_phone = '8098830937';
$contact_whatsapp = '8098830937';
$contact_hours = 'Mon - Sat: 9:00 AM - 7:00 PM';
$contact_map_iframe = '';

if (isset($conn)) {
    try {
        $stmt = $conn->query("SELECT setting_key, setting_value FROM settings");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }

        $site_name = !empty($settings['site_name']) ? $settings['site_name'] : 'Cloud Aspire';
        $site_favicon = $settings['site_favicon'] ?? '';
        $site_logo = $settings['site_logo'] ?? '';
        $contact_email = !empty($settings['contact_email']) ? $settings['contact_email'] : 'tamilaninstamedia1@gmail.com';
        $contact_phone = !empty($settings['contact_phone']) ? $settings['contact_phone'] : '8098830937';
        $contact_whatsapp = !empty($settings['contact_whatsapp']) ? $settings['contact_whatsapp'] : '8098830937';
        $contact_hours = !empty($settings['contact_hours']) ? $settings['contact_hours'] : 'Mon - Sat: 9:00 AM - 7:00 PM';
        $contact_map_iframe = $settings['contact_map_iframe'] ?? '';
        $google_maps_api_key = $settings['google_maps_api_key'] ?? '';
    } catch (Exception $e) {
        error_log("[Config] Failed to fetch settings: " . $e->getMessage());
    }
}

// ==================== CSRF TOKEN FUNCTIONS ====================

function generateCSRFToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    if (empty($_SESSION['csrf_token_expire']) || $_SESSION['csrf_token_expire'] < time()) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_expire'] = time() + 3600;
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken(?string $token): bool {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    if (!empty($_SESSION['csrf_token_expire']) && $_SESSION['csrf_token_expire'] < time()) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

function csrfField(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(generateCSRFToken()) . '">';
}

// ==================== AUDIT LOG FUNCTION ====================

function logAudit(PDO $conn, string $action, string $details = '', ?int $user_id = null): void {
    try {
        $uid = $user_id ?? ($_SESSION['admin_id'] ?? null);
        $stmt = $conn->prepare("INSERT INTO admin_activities (user_id, action, details, ip_address, user_agent, device_info) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $uid,
            $action,
            $details,
            $_SERVER['REMOTE_ADDR'] ?? '',
            substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500),
            getDeviceInfo()
        ]);
    } catch (Exception $e) {
        error_log("[Audit] Log failed: " . $e->getMessage());
    }
}

function getDeviceInfo(): string {
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $device = 'Unknown';

    if (preg_match('/mobile|android|iphone|ipad|ipod/i', $ua)) {
        $device = 'Mobile';
    } elseif (preg_match('/tablet|ipad/i', $ua)) {
        $device = 'Tablet';
    } else {
        $device = 'Desktop';
    }

    if (preg_match('/Chrome\/[\d.]+/i', $ua)) {
        $device .= ' (Chrome)';
    } elseif (preg_match('/Firefox\/[\d.]+/i', $ua)) {
        $device .= ' (Firefox)';
    } elseif (preg_match('/Safari\/[\d.]+/i', $ua) && !preg_match('/Chrome/i', $ua)) {
        $device .= ' (Safari)';
    }

    return $device;
}

function getClientIP(): string {
    $trustedProxies = ['127.0.0.1', '::1'];
    $remoteAddr = $_SERVER['REMOTE_ADDR'] ?? '';

    if (!in_array($remoteAddr, $trustedProxies, true)) {
        return filter_var($remoteAddr, FILTER_VALIDATE_IP) ?: 'Unknown';
    }

    $ipKeys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP'];
    foreach ($ipKeys as $key) {
        if (!empty($_SERVER[$key])) {
            $ip = $_SERVER[$key];
            if (strpos($ip, ',') !== false) {
                $ip = trim(explode(',', $ip)[0]);
            }
            $validIp = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
            if ($validIp) {
                return $validIp;
            }
        }
    }

    return filter_var($remoteAddr, FILTER_VALIDATE_IP) ?: 'Unknown';
}

// ==================== INPUT SANITIZATION ====================

function sanitize(mixed $input): mixed {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(trim((string)$input), ENT_QUOTES, 'UTF-8');
}

function sanitizeFilename(string $filename): string {
    $filename = basename($filename);
    $filename = preg_replace('/\.{2,}/', '.', $filename);
    $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
    $filename = preg_replace('/_+/', '_', $filename);
    return trim($filename, '._');
}

if (!function_exists('encryptSensitive')) {
    function encryptSensitive(string $plaintext): string {
        if (empty($plaintext)) return $plaintext;
        $envKey = defined('ENCRYPTION_KEY') && ENCRYPTION_KEY !== '' ? ENCRYPTION_KEY : '';
        if ($envKey === '') {
            return $plaintext;
        }
        $key = hash('sha256', $envKey, true);
        $iv = random_bytes(16);
        $encrypted = openssl_encrypt($plaintext, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        return base64_encode($iv . $encrypted);
    }
}

if (!function_exists('decryptSensitive')) {
    function decryptSensitive(string $encoded): string {
        if (empty($encoded)) return $encoded;
        $envKey = defined('ENCRYPTION_KEY') && ENCRYPTION_KEY !== '' ? ENCRYPTION_KEY : '';
        if ($envKey === '') {
            return $encoded;
        }
        $data = base64_decode($encoded, true);
        if ($data === false || strlen($data) < 17) return $encoded;
        $iv = substr($data, 0, 16);
        $ciphertext = substr($data, 16);
        $key = hash('sha256', $envKey, true);
        $decrypted = openssl_decrypt($ciphertext, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        return $decrypted !== false ? $decrypted : $encoded;
    }
}

function validateEmail(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validateURL(string $url): bool {
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

// ==================== PASSWORD VALIDATION ====================

function validatePasswordStrength(string $password): array {
    $errors = [];
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters';
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Password must contain at least one uppercase letter';
    }
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'Password must contain at least one lowercase letter';
    }
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'Password must contain at least one number';
    }
    if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
        $errors[] = 'Password must contain at least one special character';
    }
    return $errors;
}

// ==================== RATE LIMITING ====================

function checkRateLimit(string $key, int $maxAttempts = 5, int $windowSeconds = 300): bool {
    $rateKey = 'rate_' . $key;
    if (!isset($_SESSION[$rateKey])) {
        $_SESSION[$rateKey] = ['count' => 0, 'start' => time()];
    }
    $elapsed = time() - $_SESSION[$rateKey]['start'];
    if ($elapsed > $windowSeconds) {
        $_SESSION[$rateKey] = ['count' => 0, 'start' => time()];
    }
    $_SESSION[$rateKey]['count']++;
    if ($_SESSION[$rateKey]['count'] > $maxAttempts) {
        return false;
    }
    return true;
}

// ==================== JSON RESPONSE ====================

function jsonResponse(bool $success, array $data = [], string $message = '', int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'data' => $data,
        'message' => $message
    ]);
    exit;
}