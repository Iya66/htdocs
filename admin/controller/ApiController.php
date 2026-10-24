<?php
/**
 * Admin API Controller
 * Handles administrative AJAX requests, map generation, settings management, and live metrics.
 */

class ApiController {
    private PDO $conn;
    private int $adminId;
    private string $adminRole;

    public function __construct(PDO $conn) {
        $this->conn = $conn;
        $this->adminId = (int)($_SESSION['admin_id'] ?? 0);
        $this->adminRole = $_SESSION['admin_role'] ?? 'viewer';
    }

    /**
     * Check if current session is authenticated as admin
     */
    public function authorize(): bool {
        return $this->adminId > 0;
    }

    /**
     * Verify CSRF Token for mutating requests
     */
    public function verifyCSRF(?string $token): bool {
        if (empty($token) || empty($_SESSION['csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Check if user has edit privileges
     */
    public function canEdit(): bool {
        return in_array($this->adminRole, ['admin', 'manager', 'superadmin']);
    }

    /**
     * Generate / Preview Google Maps embed from address or raw iframe
     */
    public function previewMap(string $input): array {
        $trimmed = trim($input);
        if (empty($trimmed)) {
            return [
                'success' => false,
                'message' => 'Location or embed code cannot be empty.'
            ];
        }

        $iframeSrc = '';
        $locationName = $trimmed;

        // Case 1: Full <iframe> code provided
        if (preg_match('/<iframe\b[^>]*src=["\'](https:\/\/[^"\']+)["\'][^>]*>/i', $trimmed, $matches)) {
            $iframeSrc = htmlspecialchars($matches[1], ENT_QUOTES, 'UTF-8');
            $locationName = 'Custom Embed Location';
        }
        // Case 2: Direct HTTPS Map URL provided
        elseif (filter_var($trimmed, FILTER_VALIDATE_URL) && str_starts_with($trimmed, 'https://')) {
            if (str_contains($trimmed, 'maps.google.com/maps?q=')) {
                $iframeSrc = 'https://www.google.com/maps/embed?origin=mfe&pb=!1m2!2m1!1s' . urlencode($locationName);
            } else {
                $iframeSrc = htmlspecialchars($trimmed, ENT_QUOTES, 'UTF-8');
            }
        }
        // Case 3: Plain address, street, or city name provided
        else {
            $iframeSrc = 'https://www.google.com/maps/embed?origin=mfe&pb=!1m2!2m1!1s' . urlencode($trimmed);
        }

        $iframeHTML = '<iframe src="' . $iframeSrc . '" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>';
        $mapsDirectUrl = 'https://maps.google.com/?q=' . urlencode($locationName);

        return [
            'success' => true,
            'location' => $locationName,
            'src' => $iframeSrc,
            'iframe_html' => $iframeHTML,
            'maps_url' => $mapsDirectUrl
        ];
    }

    /**
     * Save Map Embed directly to database
     */
    public function saveMap(string $mapInput, string $csrfToken): array {
        if (!$this->canEdit()) {
            return ['success' => false, 'message' => 'Access denied. You have view-only privileges.'];
        }
        if (!$this->verifyCSRF($csrfToken)) {
            return ['success' => false, 'message' => 'Security token invalid or expired.'];
        }

        $preview = $this->previewMap($mapInput);
        if (!$preview['success']) {
            return $preview;
        }

        try {
            $stmt = $this->conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('contact_map_iframe', ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
            $stmt->execute([$preview['iframe_html']]);

            if (function_exists('logAudit')) {
                logAudit($this->conn, 'UPDATE_MAP', 'Updated Google Maps embed location', $this->adminId);
            }

            return [
                'success' => true,
                'message' => 'Map settings saved successfully.',
                'iframe_html' => $preview['iframe_html'],
                'location' => $preview['location'],
                'maps_url' => $preview['maps_url']
            ];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    /**
     * Save all contact settings via AJAX
     */
    public function saveContactSettings(array $data, string $csrfToken): array {
        if (!$this->canEdit()) {
            return ['success' => false, 'message' => 'Access denied. View-only mode.'];
        }
        if (!$this->verifyCSRF($csrfToken)) {
            return ['success' => false, 'message' => 'Security token invalid or expired.'];
        }

        $fields = [
            'contact_email' => trim($data['contact_email'] ?? ''),
            'contact_phone' => trim($data['contact_phone'] ?? ''),
            'contact_whatsapp' => trim($data['contact_whatsapp'] ?? ''),
            'contact_hours' => trim($data['contact_hours'] ?? ''),
            'social_facebook' => trim($data['social_facebook'] ?? ''),
            'social_instagram' => trim($data['social_instagram'] ?? ''),
            'social_twitter' => trim($data['social_twitter'] ?? ''),
            'social_linkedin' => trim($data['social_linkedin'] ?? '')
        ];

        // Format map iframe
        $rawMap = trim($data['contact_map_iframe'] ?? '');
        if (!empty($rawMap)) {
            $preview = $this->previewMap($rawMap);
            if ($preview['success']) {
                $fields['contact_map_iframe'] = $preview['iframe_html'];
            }
        }

        try {
            $stmt = $this->conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
            foreach ($fields as $key => $val) {
                $stmt->execute([$key, $val]);
            }

            if (function_exists('logAudit')) {
                logAudit($this->conn, 'UPDATE_CONTACT_SETTINGS', 'Updated Contact Us configuration via ApiController', $this->adminId);
            }

            return ['success' => true, 'message' => 'All contact settings updated successfully.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    /**
     * Get live dashboard analytics and metrics directly from database
     */
    public function getLiveStats(): array {
        try {
            $leadsCount = (int)$this->conn->query("SELECT COUNT(*) FROM leads")->fetchColumn();
            // Both the current "New Lead" stage and the legacy "new" value count as new.
            $newLeads = (int)$this->conn->query("SELECT COUNT(*) FROM leads WHERE status IN ('new_lead','new')")->fetchColumn();
            $projectsCount = (int)$this->conn->query("SELECT COUNT(*) FROM projects")->fetchColumn();
            $servicesCount = (int)$this->conn->query("SELECT COUNT(*) FROM services WHERE status = 'active'")->fetchColumn();
            
            $revStats = $this->conn->query("
                SELECT 
                    COUNT(*) as total_reviews,
                    COALESCE(AVG(star_rating), 0) as avg_rating,
                    COALESCE(SUM(CASE WHEN star_rating >= 4 THEN 1 ELSE 0 END), 0) as pos_reviews
                FROM reviews 
                WHERE status = 'approved' OR status IS NULL
            ")->fetch(PDO::FETCH_ASSOC);

            $totalReviews = (int)($revStats['total_reviews'] ?? 0);
            $rawAvg = (float)($revStats['avg_rating'] ?? 0);
            $posReviews = (int)($revStats['pos_reviews'] ?? 0);

            $satisfaction = $rawAvg > 0 ? round(($rawAvg / 5) * 100) : 100;
            $positiveRate = $totalReviews > 0 ? round(($posReviews / $totalReviews) * 100) : 100;

            return [
                'success' => true,
                'data' => [
                    'leads_total' => $leadsCount,
                    'leads_new' => $newLeads,
                    'projects_total' => $projectsCount,
                    'services_active' => $servicesCount,
                    'reviews_total' => $totalReviews,
                    'average_rating' => number_format($rawAvg > 0 ? $rawAvg : 5.0, 1),
                    'satisfaction_rate' => $satisfaction . '%',
                    'positive_rate' => $positiveRate . '%'
                ]
            ];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
}
