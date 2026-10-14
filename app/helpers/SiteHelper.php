<?php
/**
 * Site Helper Class
 * Centralized site name and settings management
 */

class SiteHelper {
    /**
     * Get site name from database or return fallback
     */
    public static function getSiteName(): string {
        global $settings;
        return !empty($settings['site_name']) ? $settings['site_name'] : 'Cloud Aspire';
    }

    /**
     * Get site logo from database
     */
    public static function getSiteLogo(): string {
        global $settings;
        return $settings['site_logo'] ?? '';
    }

    /**
     * Get site favicon from database
     */
    public static function getSiteFavicon(): string {
        global $settings;
        return $settings['site_favicon'] ?? '';
    }

    /**
     * Get all site settings
     */
    public static function getSiteSettings(): array {
        global $settings;

        $defaults = [
            'site_name' => 'Cloud Aspire',
            'site_favicon' => '',
            'site_logo' => '',
            'contact_email' => 'tamilaninstamedia1@gmail.com',
            'contact_phone' => '8098830937',
            'contact_whatsapp' => '8098830937',
            'contact_hours' => 'Mon - Sat: 9:00 AM - 7:00 PM',
            'contact_map_iframe' => ''
        ];

        if (is_array($settings)) {
            foreach ($defaults as $key => $fallback) {
                if (isset($settings[$key]) && $settings[$key] !== '') {
                    $defaults[$key] = $settings[$key];
                }
            }
        }

        return $defaults;
    }

    /**
     * Display site name safely in HTML
     */
    public static function displaySiteName(): void {
        echo htmlspecialchars(self::getSiteName());
    }

    /**
     * Get site name for display
     */
    public static function getSiteNameForDisplay(): string {
        return htmlspecialchars(self::getSiteName());
    }
}
?>