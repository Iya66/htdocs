<?php
/**
 * CloudASPIRE Database Connection Layer
 * Pure, high-performance PDO connection setup with ZERO auto-run/auto-import queries.
 */

require_once __DIR__ . '/.env.php';

// Database Configuration Constants from .env
$host    = DB_HOST;
$db      = DB_NAME;
$user    = DB_USER;
$pass    = DB_PASS;
$charset = DB_CHARSET;

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
];

try {
    $conn = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    error_log("[Database] Connection failed: " . $e->getMessage());
    $msg = $e->getMessage();
    if (strpos($msg, '2002') !== false) {
        die("Database connection error: MySQL service is not running. Please start MySQL from the XAMPP Control Panel.");
    } elseif (strpos($msg, '1049') !== false) {
        die("Database connection error: Unknown database '{$db}'. Please verify DB_NAME in app/.env and ensure the database is imported.");
    } elseif (strpos($msg, '1045') !== false) {
        die("Database connection error: Access denied for user '{$user}'. Please verify DB_USER and DB_PASS in app/.env.");
    } else {
        die("Database connection error: " . htmlspecialchars($msg) . ". Please verify settings in app/.env.");
    }
}
