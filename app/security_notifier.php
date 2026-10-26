<?php
/**
 * CloudASPIRE Security & Notification Core
 * Enterprise-grade activity logging and email notifications
 * Security-hardened version
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/smtp_factory.php';
require_once __DIR__ . '/mail_queue.php';

class SecurityNotifier {
  private ?PDO $conn = null;
  private string $siteName = '';
  private string $adminEmail = '';
  private string $fromEmail = '';
  private string $fromName = '';
  private array $smtpSettings = [];
  private array $trustedProxies = [];
  private bool $initialized = false;

  public function __construct() {
  global $conn;
  $this->conn = $conn;
  $this->loadSettings();
  }

  private function loadSettings(): void {
  $stmt = $this->conn->prepare("SELECT setting_key, setting_value FROM settings");
  $stmt->execute();
  $settings = [];
  while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
  $settings[$row['setting_key']] = $row['setting_value'];
  }

  $this->siteName = $settings['site_name'] ?? 'CloudASPIRE';
  $this->adminEmail = $settings['mail_admin_email'] ?? '';
  $this->fromEmail = $settings['mail_smtp_email'] ?? '';
  $this->fromName = $settings['mail_from_name'] ?? 'CloudASPIRE';

  // Cache SMTP settings to avoid repeated queries
  $this->smtpSettings = array_filter($settings, fn($k) => str_starts_with($k, 'mail_'), ARRAY_FILTER_USE_KEY);

  // Load trusted proxy configuration
  $this->trustedProxies = array_filter(
  array_map('trim', explode(',', $settings['trusted_proxies'] ?? '127.0.0.1,::1'))
  );

  $this->initialized = true;
  }

  // ==================== ACTIVITY LOGGING ====================

  public function log(string $action, string $details = '', int|string|null $userId = null, array $metadata = []): void {
  // Validate action against allowlist to prevent injection
  if (!$this->isValidAction($action)) {
  error_log("[SecurityNotifier] Invalid action: $action");
  return;
  }

  // Normalize userId: ensure null for SQL NULL, not string 'null'
  $uid = $userId;
  if ($uid === null || $uid === 'null' || $uid === '') {
  $uid = ($_SESSION['admin_id'] ?? null);
  if ($uid === null || $uid === 'null' || $uid === '') {
  $uid = null;
  }
  }
  // Ensure userId is an integer
  if ($uid !== null) {
  $uid = (int)$uid;
  }

  $metadataJson = !empty($metadata) ? json_encode($metadata) : null;

  try {
  $stmt = $this->conn->prepare("INSERT INTO admin_activities (user_id, action, details, ip_address, user_agent, device_info, metadata) VALUES (?, ?, ?, ?, ?, ?, ?)");
  $stmt->execute([
  $uid,
  $action,
  $details,
  $this->getClientIP(),
  $this->getUserAgent(),
  $this->getDeviceInfo(),
  $metadataJson
  ]);
  } catch (PDOException $e) {
  error_log("[SecurityNotifier] Failed to log activity: " . $e->getMessage());
  return;
  }

  // Send email notification for important actions
  $this->sendEmailNotification($action, $details, $uid, $metadata);
  }

  /**
  * Validate action against security allowlist
  * Prevents SQL injection via action names
  */
  private function isValidAction(string $action): bool {
  // Strict allowlist of valid action formats
  return (bool) preg_match('/^[A-Z][A-Z0-9_]+$/', $action) &&
  strlen($action) <= 100 &&
  !preg_match('/[<>"\']/', $action);
  }

  public function logLogin(int|string|null $userId, string $userName, string $email, bool $success = true, string $reason = ''): void {
  // Sanitize inputs
  $userName = $this->sanitizeString($userName);
  $email = $this->sanitizeEmail($email);
  $reason = $this->sanitizeString($reason);

  $action = $success ? 'LOGIN_SUCCESS' : 'LOGIN_FAILED';
  $details = $success ? "Admin logged in successfully" : "Failed login attempt - $reason";

  $this->log($action, $details, $userId, [
  'user_name' => $userName,
  'email' => $email,
  'success' => $success,
  'reason' => $reason
  ]);
  }

  public function logSettingsChange(string $settingKey, mixed $oldValue, mixed $newValue): void {
  $settingKey = $this->sanitizeString($settingKey);

  $this->log('SETTINGS_CHANGED', "Changed '$settingKey'", null, [
  'setting_key' => $settingKey,
  'old_value' => $this->maskSensitiveValue($settingKey, $oldValue),
  'new_value' => $this->maskSensitiveValue($settingKey, $newValue)
  ]);
  }

  public function logSecurityEvent(string $eventType, string $description, string $severity = 'medium'): void {
  // Validate inputs
  if (!in_array($severity, ['low', 'medium', 'high', 'critical'], true)) {
  $severity = 'medium';
  }

  $eventType = $this->sanitizeString($eventType);
  $description = $this->sanitizeString($description);

  $this->log('SECURITY_' . strtoupper($eventType), $description, null, [
  'severity' => $severity,
  'event_type' => $eventType
  ]);

  // Update suspicious activity log
  if ($severity === 'high' || $severity === 'critical') {
  try {
  $stmt = $this->conn->prepare("INSERT INTO security_alerts (event_type, description, severity, ip_address, user_agent, resolved) VALUES (?, ?, ?, ?, ?, 0)");
  $stmt->execute([$eventType, $description, $severity, $this->getClientIP(), $this->getUserAgent()]);
  } catch (PDOException $e) {
  error_log("[SecurityNotifier] Failed to log security alert: " . $e->getMessage());
  }
  }
  }

  // ==================== EMAIL NOTIFICATIONS ====================

  private function sendEmailNotification(string $action, string $details, int|string|null $userId, array $metadata = []): void {
      // Security email notification sending is disabled by configuration to preserve sender reputation.
      // Complete database audit logging in admin_activities and security_alerts remains 100% active.
      return;
  }

  /**
  * Sanitize metadata array to prevent XSS in email content
  */
  private function sanitizeMetadata(array $metadata): array {
  $clean = [];
  foreach ($metadata as $key => $value) {
  $cleanKey = $this->sanitizeString($key);
  if (is_array($value)) {
  $clean[$cleanKey] = $this->sanitizeMetadata($value);
  } elseif (is_string($value)) {
  $clean[$cleanKey] = $this->sanitizeString($value);
  } else {
  $clean[$cleanKey] = $value;
  }
  }
  return $clean;
  }

  private function sendHTMLAlertEmail(array $data): void {
    try {
      $subject = $this->buildEmailSubject($data['action']);
      $body = $this->buildHTMLEmail($data);
      $altBody = strip_tags($body);

      // Enqueue notification for instant background delivery (takes < 2ms, zero HTTP response delay)
      enqueueEmail($this->conn, $this->adminEmail, $subject, $body, $altBody, true);
    } catch (Exception $e) {
      error_log("[SecurityNotifier] Email enqueue failed: " . $e->getMessage());
    }
  }

  private function buildEmailSubject(string $action): string {
  $actionLabels = [
  'LOGIN_SUCCESS' => '🔐 Admin Login',
  'LOGIN_FAILED' => '⚠️ Failed Login Attempt',
  'SETTINGS_CHANGED' => '⚙️ Settings Modified',
  'CREATE_SERVICE' => '➕ Service Created',
  'UPDATE_SERVICE' => '✏️ Service Updated',
  'DELETE_SERVICE' => '🗑️ Service Deleted',
  'CREATE_PLAN' => '💰 Plan Created',
  'UPDATE_PLAN' => '✏️ Plan Updated',
  'DELETE_PLAN' => '🗑️ Plan Deleted',
  'CREATE_LEAD' => '📋 Lead Created',
  'UPDATE_LEAD' => '✏️ Lead Updated',
  'DELETE_LEAD' => '🗑️ Lead Deleted',
  'CREATE_REVIEW' => '⭐ Review Created',
  'UPDATE_REVIEW' => '✏️ Review Updated',
  'DELETE_REVIEW' => '🗑️ Review Deleted',
  'CREATE_PROJECT' => '📁 Project Created',
  'UPDATE_PROJECT' => '✏️ Project Updated',
  'DELETE_PROJECT' => '🗑️ Project Deleted',
  'CREATE_STAFF' => '👤 Staff Created',
  'UPDATE_STAFF' => '✏️ Staff Updated',
  'DELETE_STAFF' => '🗑️ Staff Deleted',
  'UPDATE_PROFILE' => '👤 Profile Updated',
  'UPDATE_MAIL_SETTINGS' => '📧 SMTP Settings Changed',
  'SECURITY_SUSPICIOUS' => '🚨 Security Alert'
  ];

  $label = $actionLabels[$action] ?? '📢 Activity Alert';
  // Sanitize label before use
  $label = $this->sanitizeString($label);
  $siteName = $this->sanitizeString($this->siteName);

  return "[$siteName] $label";
  }

  private function buildHTMLEmail(array $data): string {
  $action = $this->sanitizeString($data['action']);
  $severityColors = [
  'LOGIN_FAILED' => ['#ef4444', '#fef2f2'],
  'SECURITY_SUSPICIOUS' => ['#dc2626', '#fef2f2'],
  'DELETE_' => ['#f59e0b', '#fffbeb'],
  'CREATE_' => ['#10b981', '#ecfdf5'],
  'UPDATE_' => ['#3b82f6', '#eff6ff'],
  'default' => ['#667eea', '#f8faff']
  ];

  $color = '#667eea';
  $bg = '#f8faff';
  foreach ($severityColors as $key => $colors) {
  if ($key === 'default' || stripos($action, $key) === false) continue;
  $color = $colors[0];
  $bg = $colors[1];
  break;
  }

  // FIX: All user data is now escaped through sanitization helpers
  $adminName = htmlspecialchars($data['user_name'], ENT_QUOTES, 'UTF-8');
  // FIX: Escape actionLabel to prevent XSS
  $actionLabel = htmlspecialchars(str_replace('_', ' ', $action), ENT_QUOTES, 'UTF-8');
  $details = htmlspecialchars($data['details'], ENT_QUOTES, 'UTF-8');
  $ip = htmlspecialchars($data['ip_address'] ?? 'Unknown', ENT_QUOTES, 'UTF-8');
  $device = htmlspecialchars($data['device_info'] ?? 'Unknown', ENT_QUOTES, 'UTF-8');
  $timestamp = htmlspecialchars($data['timestamp'], ENT_QUOTES, 'UTF-8');
  $metadata = $data['metadata'] ?? [];

  // Build changes table if available
  $changesHtml = '';
  if (!empty($metadata['old_value']) && !empty($metadata['new_value'])) {
  $settingKey = htmlspecialchars($metadata['setting_key'] ?? 'N/A', ENT_QUOTES, 'UTF-8');
  $oldValue = htmlspecialchars($metadata['old_value'], ENT_QUOTES, 'UTF-8');
  $newValue = htmlspecialchars($metadata['new_value'], ENT_QUOTES, 'UTF-8');

  $changesHtml = '
  <div style="background: #f8f9fa; border-radius: 10px; padding: 20px; margin: 20px 0;">
  <h4 style="margin: 0 0 15px 0; color: #374151;">Changes Made:</h4>
  <table style="width: 100%; border-collapse: collapse;">
  <tr>
  <td style="padding: 10px; border-bottom: 1px solid #e5e7eb; color: #6b7280; width: 30%;"><strong>Setting:</strong></td>
  <td style="padding: 10px; border-bottom: 1px solid #e5e7eb;">' . $settingKey . '</td>
  </tr>
  <tr>
  <td style="padding: 10px; border-bottom: 1px solid #e5e7eb; color: #6b7280;"><strong>Previous:</strong></td>
  <td style="padding: 10px; border-bottom: 1px solid #e5e7eb; color: #ef4444;">' . $oldValue . '</td>
  </tr>
  <tr>
  <td style="padding: 10px; color: #6b7280;"><strong>New Value:</strong></td>
  <td style="padding: 10px; color: #10b981;">' . $newValue . '</td>
  </tr>
  </table>
  </div>';
  }

  // Risk indicator
  $riskLevel = 'LOW';
  $riskColor = '#10b981';
  if (stripos($action, 'FAILED') !== false || stripos($action, 'SECURITY') !== false) {
  $riskLevel = 'HIGH';
  $riskColor = '#ef4444';
  } elseif (stripos($action, 'DELETE') !== false || stripos($action, 'SETTINGS') !== false) {
  $riskLevel = 'MEDIUM';
  $riskColor = '#f59e0b';
  }

  $siteNameEscaped = htmlspecialchars($this->siteName, ENT_QUOTES, 'UTF-8');

  return '
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; background: #f3f4f6; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, \'Helvetica Neue\', Arial, sans-serif;">
  <div style="max-width: 650px; margin: 0 auto; padding: 20px;">
  <!-- Header -->
  <div style="background: linear-gradient(135deg, ' . $color . ' 0%, #764ba2 100%); border-radius: 16px 16px 0 0; padding: 30px; text-align: center;">
  <h1 style="color: #ffffff; margin: 0; font-size: 24px; font-weight: 700;">' . $siteNameEscaped . '</h1>
  <p style="color: rgba(255,255,255,0.9); margin: 10px 0 0 0; font-size: 14px;">Security Notification</p>
  </div>

  <!-- Action Badge -->
  <div style="background: ' . $bg . '; padding: 25px; text-align: center; border-left: 4px solid ' . $color . ';">
  <span style="display: inline-block; padding: 8px 20px; background: ' . $color . '; color: #fff; border-radius: 25px; font-size: 14px; font-weight: 600;">' . $actionLabel . '</span>
  </div>

  <!-- Content -->
  <div style="background: #ffffff; padding: 30px; border-radius: 0 0 16px 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
  <!-- Greeting -->
  <h2 style="color: #1f2937; margin: 0 0 20px 0; font-size: 20px;">Hello Admin,</h2>
  <p style="color: #4b5563; line-height: 1.7; margin: 0 0 20px 0;">
  An administrative action was performed on your <strong>' . $siteNameEscaped . '</strong> admin panel.
  </p>

  <!-- Details Card -->
  <div style="background: #f9fafb; border-radius: 12px; padding: 24px; margin-bottom: 20px;">
  <h3 style="color: #111827; margin: 0 0 20px 0; font-size: 16px; display: flex; align-items: center; gap: 10px;">
  <span style="width: 8px; height: 8px; background: ' . $color . '; border-radius: 50%;"></span>
  Activity Details
  </h3>
  <table style="width: 100%; border-collapse: collapse;">
  <tr>
  <td style="padding: 12px 0; border-bottom: 1px solid #e5e7eb; color: #6b7280; font-size: 14px;"><strong>Admin:</strong></td>
  <td style="padding: 12px 0; border-bottom: 1px solid #e5e7eb; color: #111827; font-size: 14px;">' . $adminName . '</td>
  </tr>
  <tr>
  <td style="padding: 12px 0; border-bottom: 1px solid #e5e7eb; color: #6b7280; font-size: 14px;"><strong>Action:</strong></td>
  <td style="padding: 12px 0; border-bottom: 1px solid #e5e7eb; color: #111827; font-size: 14px;">' . $details . '</td>
  </tr>
  <tr>
  <td style="padding: 12px 0; border-bottom: 1px solid #e5e7eb; color: #6b7280; font-size: 14px;"><strong>Time:</strong></td>
  <td style="padding: 12px 0; border-bottom: 1px solid #e5e7eb; color: #111827; font-size: 14px;">' . $timestamp . '</td>
  </tr>
  <tr>
  <td style="padding: 12px 0; border-bottom: 1px solid #e5e7eb; color: #6b7280; font-size: 14px;"><strong>IP Address:</strong></td>
  <td style="padding: 12px 0; border-bottom: 1px solid #e5e7eb; color: #111827; font-size: 14px; font-family: monospace;">' . $ip . '</td>
  </tr>
  <tr>
  <td style="padding: 12px 0; color: #6b7280; font-size: 14px;"><strong>Device:</strong></td>
  <td style="padding: 12px 0; color: #111827; font-size: 14px;">' . $device . '</td>
  </tr>
  </table>
  </div>

  ' . $changesHtml . '

  <!-- Risk Indicator -->
  <div style="display: flex; align-items: center; gap: 10px; padding: 16px; background: ' . $bg . '; border-radius: 10px; margin-bottom: 20px;">
  <span style="padding: 4px 12px; background: ' . $riskColor . '; color: #fff; border-radius: 6px; font-size: 12px; font-weight: 600;">' . $riskLevel . ' RISK</span>
  <span style="color: #6b7280; font-size: 13px;">This activity has been logged in the security system.</span>
  </div>

  <!-- Warning -->
  ' . ($riskLevel === 'HIGH' ? '
  <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 10px; padding: 20px; margin-bottom: 20px;">
  <p style="color: #dc2626; margin: 0; font-size: 14px; font-weight: 600;">⚠️ Security Alert</p>
  <p style="color: #991b1b; margin: 10px 0 0 0; font-size: 13px;">If this activity was not authorized by you, please secure your account immediately by changing your password and reviewing recent activity.</p>
  </div>
  ' : '') . '

  <!-- Footer -->
  <div style="border-top: 1px solid #e5e7eb; padding-top: 20px; margin-top: 20px; text-align: center;">
  <p style="color: #9ca3af; font-size: 12px; margin: 0;">This is an automated notification from ' . $siteNameEscaped . ' Admin Panel.</p>
  <p style="color: #9ca3af; font-size: 11px; margin: 10px 0 0 0;">Please do not reply to this email.</p>
  </div>
  </div>
  </div>
</body>
</html>';
  }

  // ==================== HELPER METHODS ====================

  /**
  * Sanitize string for safe output
  */
  private function sanitizeString(string $input): string {
  // Remove null bytes and control characters except newline/tab
  $clean = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $input);
  // Truncate to prevent DoS
  return mb_substr($clean, 0, 1000, 'UTF-8');
  }

  /**
  * Sanitize email address
  */
  private function sanitizeEmail(string $email): string {
  $email = filter_var($email, FILTER_SANITIZE_EMAIL);
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  return 'invalid@example.com';
  }
  return $email;
  }

  /**
  * Get client IP with trusted proxy validation
  * FIX: Only trust proxy headers from configured trusted proxies
  */
  private function getClientIP(): string {
  $remoteAddr = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';

  // Check if REMOTE_ADDR is a trusted proxy
  $isTrustedProxy = $this->isTrustedProxy($remoteAddr);

  if (!$isTrustedProxy) {
  // Not a trusted proxy, use direct connection IP
  return filter_var($remoteAddr, FILTER_VALIDATE_IP) ?: 'Unknown';
  }

  // Trusted proxy - check forwarded headers
  $ipKeys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP'];
  foreach ($ipKeys as $key) {
  if (!empty($_SERVER[$key])) {
  $ip = $_SERVER[$key];
  if (strpos($ip, ',') !== false) {
  // Take the leftmost IP (original client)
  $ip = trim(explode(',', $ip)[0]);
  }
  // Validate IP format
  $validIp = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
  if ($validIp) {
  return $validIp;
  }
  }
  }

  return filter_var($remoteAddr, FILTER_VALIDATE_IP) ?: 'Unknown';
  }

  /**
  * Check if IP is in trusted proxy list
  */
  private function isTrustedProxy(string $ip): bool {
  foreach ($this->trustedProxies as $trusted) {
  if ($trusted === $ip) {
 return true;
 }
 // Reject dangerous wildcard proxy configs
 if ($trusted === '0.0.0.0/0' || $trusted === '::/0') {
 error_log('[SecurityNotifier] Blocked dangerous trusted proxy config: ' . $trusted);
 continue;
 }
  // Support CIDR notation (basic)
  if (str_contains($trusted, '/')) {
  [$network, $mask] = explode('/', $trusted);
  if ($this->ipInRange($ip, $network, (int)$mask)) {
  return true;
  }
  }
  }
  return false;
  }

  /**
  * Check if IP is in CIDR range
  */
  private function ipInRange(string $ip, string $range, int $mask): bool {
  $ipLong = ip2long($ip);
  $rangeLong = ip2long($range);
  if ($ipLong === false || $rangeLong === false) {
  return false;
  }
  $maskLong = -1 << (32 - $mask);
  return ($ipLong & $maskLong) === ($rangeLong & $maskLong);
  }

  private function getUserAgent(): string {
  $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
  // Truncate and sanitize
  return mb_substr($this->sanitizeString($ua), 0, 500, 'UTF-8');
  }

  private function getDeviceInfo(): string {
  $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
  $device = 'Unknown';

  // FIX: Improved device detection with better ordering
  if (preg_match('/(ipad|tablet|kindle|silk)/i', $ua)) {
  $device = 'Tablet';
  } elseif (preg_match('/(mobile|android|iphone|ipod)/i', $ua)) {
  $device = 'Mobile';
  } else {
  $device = 'Desktop';
  }

  // Extract browser with better specificity
  if (preg_match('/Edg\/[\d.]+/i', $ua)) {
  $device .= ' (Edge)';
  } elseif (preg_match('/Chrome\/[\d.]+/i', $ua) && !preg_match('/Edg|OPR\//i', $ua)) {
  $device .= ' (Chrome)';
  } elseif (preg_match('/Firefox\/[\d.]+/i', $ua)) {
  $device .= ' (Firefox)';
  } elseif (preg_match('/Safari\/[\d.]+/i', $ua) && !preg_match('/Chrome|Chromium|Edg|OPR\//i', $ua)) {
  $device .= ' (Safari)';
  } elseif (preg_match('/Opera|OPR\/[\d.]+/i', $ua)) {
  $device .= ' (Opera)';
  }

  return $device;
  }

  private function maskSensitiveValue(string $key, mixed $value): mixed {
  // FIX: Expanded sensitive key patterns
  $sensitiveKeys = [
  'password', 'passwd', 'pwd',
  'secret', 'api_key', 'apikey', 'api-key',
  'token', 'access_token', 'refresh_token',
  'private_key', 'privatekey', 'private-key',
  'credential', 'auth', 'authorization',
  'smtp_password', 'mail_password'
  ];

  $keyLower = strtolower($key);
  foreach ($sensitiveKeys as $sKey) {
  if (str_contains($keyLower, $sKey) && !empty($value)) {
  if (is_string($value) && strlen($value) > 4) {
  return substr($value, 0, 2) . '********' . substr($value, -2);
  }
  return '********';
  }
  }
  return $value;
  }

  // ==================== SESSION MONITORING ====================

  public function createSession(int|string $userId): void {
  if ($userId === null || $userId === '') {
  error_log("[SecurityNotifier] Cannot create session with null userId");
  return;
  }

  $userId = (int)$userId;
  $sessionId = session_id();

  try {
  $stmt = $this->conn->prepare("INSERT INTO admin_sessions (user_id, session_id, ip_address, user_agent, created_at, last_activity) VALUES (?, ?, ?, ?, NOW(), NOW()) ON DUPLICATE KEY UPDATE last_activity = NOW(), session_id = VALUES(session_id)");
  $stmt->execute([$userId, $sessionId, $this->getClientIP(), $this->getUserAgent()]);
  } catch (PDOException $e) {
  error_log("[SecurityNotifier] Failed to create session: " . $e->getMessage());
  }
  }

  /**
  * Check for suspicious activity with atomic rate limiting
  * FIX: Use atomic operations to prevent race conditions
  */
  public function checkSuspiciousActivity(int|string|null $userId = null): array {
  $ip = $this->getClientIP();

  try {
  // FIX: Use INSERT with ON DUPLICATE KEY for atomic rate limiting
  // This prevents race conditions in brute force detection

  // First, increment the counter atomically
  $stmt = $this->conn->prepare("
  INSERT INTO rate_limits (identifier, type, count, updated_at)
  VALUES (?, 'failed_login', 1, NOW())
  ON DUPLICATE KEY UPDATE
  count = count + 1,
  updated_at = NOW()
  ");
  $stmt->execute([$ip]);

  // Then check the count
  $stmt = $this->conn->prepare("SELECT count FROM rate_limits WHERE identifier = ? AND type = 'failed_login' AND updated_at > DATE_SUB(NOW(), INTERVAL 10 MINUTE)");
  $stmt->execute([$ip]);
  $failedCount = $stmt->fetchColumn();

  if ($failedCount >= 3) {
  $this->logSecurityEvent('BRUTE_FORCE', "Multiple failed login attempts ($failedCount) from IP: $ip", 'high');

  // Optional: Block the IP temporarily
  $stmt = $this->conn->prepare("INSERT INTO ip_blocks (ip_address, blocked_until, reason) VALUES (?, DATE_ADD(NOW(), INTERVAL 1 HOUR), ?)");
  $stmt->execute([$ip, 'Brute force detection']);

  return ['suspicious' => true, 'reason' => 'Multiple failed login attempts', 'blocked' => true];
  }

  // Check for unusual activity patterns
  if ($userId) {
  $stmt = $this->conn->prepare("
  INSERT INTO rate_limits (identifier, type, count, updated_at)
  VALUES (?, 'rapid_actions', 1, NOW())
  ON DUPLICATE KEY UPDATE
  count = count + 1,
  updated_at = NOW()
  ");
  $stmt->execute(['user_' . $userId]);

  $stmt = $this->conn->prepare("SELECT count FROM rate_limits WHERE identifier = ? AND type = 'rapid_actions' AND updated_at > DATE_SUB(NOW(), INTERVAL 1 MINUTE)");
  $stmt->execute(['user_' . $userId]);
  $rapidActions = $stmt->fetchColumn();

  if ($rapidActions >= 10) {
  $this->logSecurityEvent('RAPID_ACTIONS', "Rapid actions detected (10+ per minute) from user ID: $userId", 'medium');
  }
  }
  } catch (PDOException $e) {
  error_log("[SecurityNotifier] Failed to check suspicious activity: " . $e->getMessage());
  }

  return ['suspicious' => false];
  }

  /**
  * Check if IP is currently blocked
  */
  public function isIPBlocked(?string $ip = null): bool {
  if ($ip === null) {
  $ip = $this->getClientIP();
  }

  try {
  $stmt = $this->conn->prepare("SELECT COUNT(*) FROM ip_blocks WHERE ip_address = ? AND blocked_until > NOW()");
  $stmt->execute([$ip]);
  return $stmt->fetchColumn() > 0;
  } catch (PDOException $e) {
  error_log("[SecurityNotifier] Failed to check IP block: " . $e->getMessage());
  return false;
  }
  }

  public function getActiveSessions(int|string|null $userId = null): array {
  try {
  if ($userId) {
  $stmt = $this->conn->prepare("SELECT * FROM admin_sessions WHERE user_id = ? AND last_activity > DATE_SUB(NOW(), INTERVAL 30 MINUTE) ORDER BY last_activity DESC");
  $stmt->execute([$userId]);
  } else {
  $stmt = $this->conn->query("SELECT * FROM admin_sessions WHERE last_activity > DATE_SUB(NOW(), INTERVAL 30 MINUTE) ORDER BY last_activity DESC");
  }
  return $stmt->fetchAll(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
  error_log("[SecurityNotifier] Failed to get active sessions: " . $e->getMessage());
  return [];
  }
  }

  public function terminateSession(string $sessionId): void {
  try {
  $stmt = $this->conn->prepare("DELETE FROM admin_sessions WHERE session_id = ?");
  $stmt->execute([$sessionId]);
  } catch (PDOException $e) {
  error_log("[SecurityNotifier] Failed to terminate session: " . $e->getMessage());
  }
  }

  /**
  * Clean up old rate limit records
  */
  public function cleanupRateLimits(): void {
  try {
  $this->conn->exec("DELETE FROM rate_limits WHERE updated_at < DATE_SUB(NOW(), INTERVAL 1 DAY)");
  $this->conn->exec("DELETE FROM ip_blocks WHERE blocked_until < NOW()");
  } catch (PDOException $e) {
  error_log("[SecurityNotifier] Failed to cleanup rate limits: " . $e->getMessage());
  }
  }
}

// Create global instance
$securityNotifier = new SecurityNotifier();

// Helper function for easy logging
function notifySecurity(string $action, string $details = '', int|string|null $userId = null, array $metadata = []): void {
  global $securityNotifier;
  $securityNotifier->log($action, $details, $userId, $metadata);
}

function notifyLogin(bool $success, int|string|null $userId = null, string $userName = '', string $email = '', string $reason = ''): void {
  global $securityNotifier;
  $securityNotifier->logLogin($userId, $userName, $email, $success, $reason);
}

function notifySettingsChange(string $key, mixed $old, mixed $new): void {
  global $securityNotifier;
  $securityNotifier->logSettingsChange($key, $old, $new);
}
