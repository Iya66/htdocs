<?php
/**
 * MapController
 * Dedicated Admin Controller for Google Maps configuration, preview generation,
 * address geocoding, and settings persistence.
 */

class MapController {
    private PDO $conn;
    private int $adminId;
    private string $adminRole;

    public const DEFAULT_LOCATION = 'No 53 Auvai Nagar, Puducherry';
    public const DEFAULT_ZOOM = 14;

    public function __construct(PDO $conn) {
        $this->conn = $conn;
        $this->adminId = (int)($_SESSION['admin_id'] ?? 0);
        $this->adminRole = $_SESSION['admin_role'] ?? 'viewer';
    }

    /**
     * Verify administrative session
     */
    public function authorize(): bool {
        return $this->adminId > 0;
    }

    /**
     * Check permission for updating settings
     */
    public function canEdit(): bool {
        return in_array($this->adminRole, ['admin', 'manager', 'superadmin']);
    }

    /**
     * Verify CSRF Token
     */
    public function verifyCSRF(?string $token): bool {
        if (empty($token) || empty($_SESSION['csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Resolve and build responsive Google Maps embed preview
     * Supports:
     * 1. Plain address/city/landmark (e.g. "No 53 Auvai Nagar, Puducherry")
     * 2. Coordinates: "11.93605, 79.77977"
     * 3. Google Maps direct URL / Short link
     * 4. Full <iframe> embed code
     */
    public function preview(string $input, int $zoom = self::DEFAULT_ZOOM, string $mapType = 'm'): array {
        $trimmed = trim($input);
        if (empty($trimmed)) {
            $trimmed = self::DEFAULT_LOCATION;
        }

        $locationName = $trimmed;
        $iframeSrc = '';

        // Check if raw iframe was passed
        if (preg_match('/<iframe\b[^>]*src=["\'](https:\/\/[^"\']+)["\'][^>]*>/i', $trimmed, $m)) {
            $extractedSrc = $m[1];
            $locationName = $this->extractLocationFromUrl($extractedSrc) ?: 'Google Maps Embed';
            // Convert any legacy blocked maps.google.com query URL to direct unblocked embed URL
            if (str_contains($extractedSrc, 'maps.google.com/maps?q=')) {
                $iframeSrc = 'https://www.google.com/maps/embed?origin=mfe&pb=!1m2!2m1!1s' . urlencode($locationName ?: self::DEFAULT_LOCATION);
            } else {
                $iframeSrc = htmlspecialchars($extractedSrc, ENT_QUOTES, 'UTF-8');
            }
        }
        // Direct HTTPS URL
        elseif (filter_var($trimmed, FILTER_VALIDATE_URL) && str_starts_with($trimmed, 'https://')) {
            $locationName = $this->extractLocationFromUrl($trimmed) ?: 'Custom Map URL';
            if (str_contains($trimmed, 'maps.google.com/maps?q=')) {
                $iframeSrc = 'https://www.google.com/maps/embed?origin=mfe&pb=!1m2!2m1!1s' . urlencode($locationName ?: self::DEFAULT_LOCATION);
            } else {
                $iframeSrc = htmlspecialchars($trimmed, ENT_QUOTES, 'UTF-8');
            }
        }
        // Plain address or coordinates - use direct official unblocked embed endpoint
        else {
            $cln = urlencode($trimmed);
            $iframeSrc = "https://www.google.com/maps/embed?origin=mfe&pb=!1m2!2m1!1s{$cln}";
        }

        $iframeHtml = '<iframe src="' . $iframeSrc . '" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>';
        $googleMapsUrl = 'https://maps.google.com/?q=' . urlencode($locationName);
        $directionsUrl = 'https://www.google.com/maps/dir/?api=1&destination=' . urlencode($locationName);

        return [
            'success' => true,
            'location' => $locationName,
            'zoom' => $zoom,
            'map_type' => $mapType,
            'src' => $iframeSrc,
            'iframe_html' => $iframeHtml,
            'maps_url' => $googleMapsUrl,
            'directions_url' => $directionsUrl
        ];
    }

    /**
     * Extract human-readable location name from Google Maps URL
     */
    public function extractLocationFromUrl(string $url): string {
        if (preg_match('/maps\.google\.com\/maps\?q=([^&"]+)/', $url, $m)) {
            return urldecode(str_replace('+', ' ', $m[1]));
        }
        if (preg_match('/!1s([^!&"]+)/', $url, $m)) {
            return urldecode(str_replace(['%2C', '+'], [',', ' '], $m[1]));
        }
        if (preg_match('/!2s([^!&"]+)/', $url, $m)) {
            return urldecode(str_replace(['%2C', '+'], [',', ' '], $m[1]));
        }
        return '';
    }

    /**
     * Get current saved map from settings table
     */
    public function get(): array {
        $saved = '';
        $savedLoc = '';
        try {
            $stmt = $this->conn->prepare("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('contact_map_iframe', 'contact_map_location')");
            $stmt->execute();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if ($row['setting_key'] === 'contact_map_iframe') $saved = (string)$row['setting_value'];
                if ($row['setting_key'] === 'contact_map_location') $savedLoc = (string)$row['setting_value'];
            }
        } catch (Exception $e) {
            error_log("[MapController] Failed to fetch map: " . $e->getMessage());
        }

        if (!empty($savedLoc)) {
            $res = $this->preview(!empty($saved) ? $saved : $savedLoc);
            $res['location'] = $savedLoc;
            return $res;
        }

        if (empty($saved)) {
            return $this->preview(self::DEFAULT_LOCATION);
        }

        return $this->preview($saved);
    }

    /**
     * Save map configuration to database
     */
    public function save(string $input, ?string $token, int $zoom = self::DEFAULT_ZOOM, string $mapType = 'm'): array {
        if (!$this->canEdit()) {
            return [
                'success' => false,
                'message' => 'Access denied. You have view-only privileges.'
            ];
        }

        if (!$this->verifyCSRF($token)) {
            return [
                'success' => false,
                'message' => 'Security token invalid or expired. Please refresh the page.'
            ];
        }

        $preview = $this->preview($input, $zoom, $mapType);
        if (!$preview['success']) {
            return $preview;
        }

        try {
            $stmt = $this->conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
            $stmt->execute(['contact_map_iframe', $preview['iframe_html']]);
            $stmt->execute(['contact_map_location', $preview['location']]);

            if (function_exists('logAudit')) {
                logAudit($this->conn, 'UPDATE_MAP', 'Updated Google Maps configuration: ' . $preview['location'], $this->adminId);
            }

            return [
                'success' => true,
                'message' => 'Map location updated and saved successfully.',
                'data' => $preview
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Reset map to agency default headquarters
     */
    public function resetDefault(?string $token): array {
        return $this->save(self::DEFAULT_LOCATION, $token, self::DEFAULT_ZOOM, 'm');
    }
}
