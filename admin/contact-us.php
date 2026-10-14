<?php
/**
 * Admin Contact Us Settings
 */
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../app/config.php';

$csrf_token = generateCSRFToken();

// Fetch Settings
if (!function_exists('getSetting')) {
    function getSetting(PDO $conn, string $key, $default = '') {
        $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $row = $stmt->fetch();
        return $row ? $row['setting_value'] : $default;
    }
}

$userRole = $_SESSION['admin_role'] ?? 'viewer';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_contact'])) {
    if (in_array($userRole, ['viewer'])) {
        $_SESSION['msg'] = 'Access denied. You have view-only privileges.';
        $_SESSION['type'] = 'error';
    } elseif (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $_SESSION['msg'] = 'Invalid security token. Please refresh.';
        $_SESSION['type'] = 'error';
    } else {
        $contact_email = trim($_POST['contact_email'] ?? '');
        $contact_phone = trim($_POST['contact_phone'] ?? '');
        $contact_whatsapp = trim($_POST['contact_whatsapp'] ?? '');
        $contact_hours = trim($_POST['contact_hours'] ?? '');
        $map_location = trim($_POST['map_location'] ?? '');
        $google_maps_api_key = trim($_POST['google_maps_api_key'] ?? '');
        $raw_map_iframe = trim($_POST['contact_map_iframe'] ?? '');
        $social_facebook = trim($_POST['social_facebook'] ?? '');
        $social_instagram = trim($_POST['social_instagram'] ?? '');
        $social_twitter = trim($_POST['social_twitter'] ?? '');
        $social_linkedin = trim($_POST['social_linkedin'] ?? '');

        // If map location/company name provided, generate unblocked Google Maps embed
        $contact_map_iframe = '';
        if (!empty($raw_map_iframe) && str_starts_with($raw_map_iframe, '<iframe')) {
            if (preg_match('/<iframe\b[^>]*src=["\'](https:\/\/[^"\']+)["\'][^>]*>/i', $raw_map_iframe, $m)) {
                $rawSrc = $m[1];
                if (str_contains($rawSrc, 'maps.google.com/maps?q=')) {
                    $src = 'https://www.google.com/maps/embed?origin=mfe&pb=!1m2!2m1!1s' . urlencode($map_location ?: 'Cloud Aspire');
                } else {
                    $src = htmlspecialchars($rawSrc, ENT_QUOTES, 'UTF-8');
                }
                $contact_map_iframe = '<iframe src="' . $src . '" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>';
            }
        } elseif (!empty($map_location)) {
            $src = 'https://www.google.com/maps/embed?origin=mfe&pb=!1m2!2m1!1s' . urlencode($map_location);
            $contact_map_iframe = '<iframe src="' . $src . '" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>';
        } elseif (!empty($raw_map_iframe)) {
            $src = 'https://www.google.com/maps/embed?origin=mfe&pb=!1m2!2m1!1s' . urlencode($raw_map_iframe);
            $contact_map_iframe = '<iframe src="' . htmlspecialchars($src, ENT_QUOTES, 'UTF-8') . '" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>';
        }
        
        try {
            $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
            
            $stmt->execute(['contact_email', $contact_email]);
            $stmt->execute(['contact_phone', $contact_phone]);
            $stmt->execute(['contact_whatsapp', $contact_whatsapp]);
            $stmt->execute(['contact_hours', $contact_hours]);
            if (!empty($contact_map_iframe)) {
                $stmt->execute(['contact_map_iframe', $contact_map_iframe]);
            }
            if (!empty($map_location)) {
                $stmt->execute(['contact_map_location', $map_location]);
            }
            if (isset($_POST['google_maps_api_key'])) {
                $stmt->execute(['google_maps_api_key', $google_maps_api_key]);
            }
            $stmt->execute(['social_facebook', $social_facebook]);
            $stmt->execute(['social_instagram', $social_instagram]);
            $stmt->execute(['social_twitter', $social_twitter]);
            $stmt->execute(['social_linkedin', $social_linkedin]);
            
            logAudit($conn, 'UPDATE_CONTACT', 'Updated Contact Us settings', $_SESSION['admin_id']);
            
            $_SESSION['msg'] = 'Contact settings updated successfully!';
            $_SESSION['type'] = 'success';
            
            // Redirect to prevent form resubmission
            header('Location: contact-us.php');
            exit;
            
        } catch (Exception $e) {
            $_SESSION['msg'] = 'Error: ' . $e->getMessage();
            $_SESSION['type'] = 'error';
        }
    }
}

// Get current values
$contact_email = getSetting($conn, 'contact_email', 'tamilaninstamedia1@gmail.com');
$contact_phone = getSetting($conn, 'contact_phone', '8098830937');
$contact_whatsapp = getSetting($conn, 'contact_whatsapp', '8098830937');
$contact_hours = getSetting($conn, 'contact_hours', 'Mon - Sat: 9:00 AM - 7:00 PM');
$contact_map_iframe = getSetting($conn, 'contact_map_iframe', '<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d193595.2527998699!2d-74.14448787425354!3d40.69763123337346!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c24fa5d33f083b%3A0xc80b8f06e177fe62!2sNew%20York%2C%20NY%2C%20USA!5e0!3m2!1sen!2sbd!4v1692291580970!5m2!1sen!2sbd" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>');
$social_facebook = getSetting($conn, 'social_facebook', 'https://facebook.com');
$social_instagram = getSetting($conn, 'social_instagram', 'https://instagram.com');
$social_twitter = getSetting($conn, 'social_twitter', 'https://twitter.com');
$social_linkedin = getSetting($conn, 'social_linkedin', 'https://linkedin.com');
$google_maps_api_key = getSetting($conn, 'google_maps_api_key', '');

// Extract location from stored setting or iframe string
$current_location = getSetting($conn, 'contact_map_location', '');
if (empty($current_location)) {
    if (preg_match('/maps\.google\.com\/maps\?q=([^&"]+)/', $contact_map_iframe, $matches)) {
        $current_location = urldecode(str_replace('+', ' ', $matches[1]));
    } elseif (preg_match('/!1s([^!&"]+)/', $contact_map_iframe, $matches)) {
        $current_location = urldecode(str_replace(['%2C', '+'], [',', ' '], $matches[1]));
    } elseif (preg_match('/!2s([^!&"]+)/', $contact_map_iframe, $matches)) {
        $current_location = urldecode(str_replace(['%2C', '+'], [',', ' '], $matches[1]));
    } else {
        $current_location = $site_name ?: 'Cloud Aspire';
    }
}

$msg = $_SESSION['msg'] ?? '';
$type = $_SESSION['type'] ?? '';
unset($_SESSION['msg'], $_SESSION['type']);

require_once __DIR__ . '/../app/Views/admin/contact-us.php';
