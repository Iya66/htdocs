<?php
/**
 * Environment File Loader
 * Loads configuration from .env file
 */
function loadEnv(string $path): void {
 if (!file_exists($path)) {
 return;
 }

 $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
 foreach ($lines as $line) {
 // Skip comments
 if (str_starts_with(trim($line), '#')) {
 continue;
 }

 // Parse KEY=VALUE
 if (str_contains($line, '=')) {
 [$key, $value] = explode('=', $line, 2);
 $key = trim($key);
 $value = trim($value);

 // Remove quotes if present
 if (preg_match('/^["\'](.*)["\']$/', $value, $matches)) {
 $value = $matches[1];
 }

 // Only set if not already defined
 if (!defined($key) && !array_key_exists($key, $_ENV)) {
 $_ENV[$key] = $value;
 }
 }
 }
}

loadEnv(__DIR__ . '/.env');

// Define constants from env or fall back to defaults
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'qaschyan_cloudaspire');
define('DB_USER', $_ENV['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');
define('DB_CHARSET', $_ENV['DB_CHARSET'] ?? 'utf8mb4');

define('SITE_URL', $_ENV['SITE_URL'] ?? 'http://localhost');
define('SITE_NAME', $_ENV['SITE_NAME'] ?? 'Cloud Aspire');
define('ENCRYPTION_KEY', $_ENV['ENCRYPTION_KEY'] ?? 'cloudaspire_secure_encryption_key_2026');

define('SMTP_HOST', $_ENV['SMTP_HOST'] ?? '');
define('SMTP_PORT', (int)($_ENV['SMTP_PORT'] ?? 587));
define('SMTP_USER', $_ENV['SMTP_USER'] ?? '');
define('SMTP_PASS', $_ENV['SMTP_PASS'] ?? '');
define('SMTP_FROM', $_ENV['SMTP_FROM'] ?? '');
define('SMTP_FROM_NAME', $_ENV['SMTP_FROM_NAME'] ?? '');
