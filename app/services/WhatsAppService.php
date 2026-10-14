<?php
/**
 * Fast2SMS WhatsApp Business API (WABA) Service
 * 
 * Implements the official Fast2SMS WhatsApp Business API:
 * - WABA & Template Details: GET https://www.fast2sms.com/dev/dlt_manager/whatsapp
 * - Sending Template Messages: GET/POST https://www.fast2sms.com/dev/whatsapp
 * 
 * Documentation: https://docs.fast2sms.com/reference/get-waba-template-details
 */

class WhatsAppService {
    private ?PDO $conn = null;
    private string $apiKey = '';
    private bool $enabled = false;
    private string $adminNumber = '';
    private string $wabaId = '';
    private string $phoneNumberId = '';
    private string $leadTemplateId = '';
    private string $welcomeTemplateId = '';
    private string $reviewTemplateId = '';
    private string $baseUrl = 'https://www.fast2sms.com';

    public function __construct(?PDO $conn = null) {
        if ($conn !== null) {
            $this->conn = $conn;
        } else {
            global $conn;
            $this->conn = $conn;
        }
        $this->loadSettings();
    }

    /**
     * Load settings from database
     */
    private function loadSettings(): void {
        if (!$this->conn) return;

        try {
            $stmt = $this->conn->prepare("SELECT setting_key, setting_value FROM settings WHERE setting_key LIKE 'whatsapp_%'");
            $stmt->execute();
            $settings = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }

            $this->apiKey = trim($settings['whatsapp_api_key'] ?? '');
            $this->enabled = ($settings['whatsapp_enabled'] ?? '0') === '1';
            $this->adminNumber = trim($settings['whatsapp_admin_number'] ?? ($settings['contact_whatsapp'] ?? '8098830937'));
            $this->wabaId = trim($settings['whatsapp_waba_id'] ?? '');
            $this->phoneNumberId = trim($settings['whatsapp_phone_number_id'] ?? '');
            $this->leadTemplateId = trim($settings['whatsapp_lead_template_id'] ?? '');
            $this->welcomeTemplateId = trim($settings['whatsapp_welcome_template_id'] ?? '');
            $this->reviewTemplateId = trim($settings['whatsapp_review_template_id'] ?? '');
        } catch (Exception $e) {
            error_log("[WhatsAppService] Failed to load settings: " . $e->getMessage());
        }
    }

    /**
     * Check if WhatsApp service is configured and enabled
     */
    public function isEnabled(): bool {
        return $this->enabled && !empty($this->apiKey);
    }

    /**
     * Get API Key
     */
    public function getApiKey(): string {
        return $this->apiKey;
    }

    /**
     * Sanitize phone number (strip spaces, symbols, ensure format)
     */
    public function cleanPhoneNumber(string $number): string {
        $clean = preg_replace('/[^0-9]/', '', $number);
        // If 10 digits (India), add 91 prefix if needed or pass 10-digit number depending on Fast2SMS format
        if (strlen($clean) === 10) {
            return $clean;
        }
        if (strlen($clean) === 12 && str_starts_with($clean, '91')) {
            return substr($clean, 2); // 10 digit Indian number
        }
        return $clean;
    }

    /**
     * Fetch WABA & Template details from Fast2SMS
     * Endpoint: GET https://www.fast2sms.com/dev/dlt_manager/whatsapp
     * 
     * @param string|null $overrideKey Custom API key for testing
     * @return array
     */
    public function getWabaAndTemplateDetails(?string $overrideKey = null): array {
        $key = $overrideKey ?: $this->apiKey;
        if (empty($key)) {
            return [
                'success' => false,
                'error' => 'Fast2SMS API Key is required.'
            ];
        }

        $url = $this->baseUrl . '/dev/dlt_manager/whatsapp';
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_HTTPHEADER => [
                'authorization: ' . $key,
                'Accept: application/json',
                'User-Agent: CloudASPIRE-WABA-Client/1.0'
            ],
            CURLOPT_SSL_VERIFYPEER => true
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            return [
                'success' => false,
                'error' => 'Connection failed: ' . $curlError
            ];
        }

        $result = json_decode($response, true);
        if ($httpCode !== 200 || !$result) {
            $msg = $result['message'] ?? $result['error'] ?? "HTTP $httpCode Error: Unable to fetch WABA details.";
            return [
                'success' => false,
                'error' => $msg,
                'raw_response' => $response
            ];
        }

        return [
            'success' => true,
            'data' => $result['data'] ?? [],
            'raw' => $result
        ];
    }

    /**
     * Send WhatsApp Template Message using Fast2SMS
     * Endpoint: GET /dev/whatsapp?phone_number_id=...&message_id=...&numbers=...&variables_values=...
     * 
     * @param string $to Recipient phone number
     * @param int|string $messageId Fast2SMS unique message_id for template
     * @param string|null $phoneNumberId WABA phone number ID
     * @param array $variables Array of variable values (e.g. ['John', 'Web Design'])
     * @param string|null $mediaUrl Optional header media URL (image/video/doc)
     * @return array
     */
    public function sendTemplateMessage(
        string $to,
        int|string $messageId,
        ?string $phoneNumberId = null,
        array $variables = [],
        ?string $mediaUrl = null
    ): array {
        if (empty($this->apiKey)) {
            return ['success' => false, 'error' => 'Fast2SMS API Key is not configured.'];
        }

        $pId = $phoneNumberId ?: $this->phoneNumberId;
        if (empty($pId) && !empty($this->apiKey)) {
            // Auto-discover and cache phone_number_id from Fast2SMS WABA details
            $wabaDetails = $this->getWabaAndTemplateDetails();
            if (!empty($wabaDetails['data'][0]['phone_number_id'])) {
                $pId = (string)$wabaDetails['data'][0]['phone_number_id'];
                $this->phoneNumberId = $pId;
                if ($this->conn) {
                    try {
                        $this->conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('whatsapp_phone_number_id', ?) ON DUPLICATE KEY UPDATE setting_value = ?")->execute([$pId, $pId]);
                    } catch (Exception $pe) {}
                }
            }
        }

        if (empty($messageId)) {
            return ['success' => false, 'error' => 'Template Message ID is required.'];
        }

        $cleanTo = $this->cleanPhoneNumber($to);
        if (empty($cleanTo)) {
            return ['success' => false, 'error' => 'Invalid recipient phone number.'];
        }

        $params = [
            'phone_number_id' => $pId,
            'message_id'      => $messageId,
            'numbers'         => $cleanTo
        ];

        if (!empty($variables)) {
            $params['variables_values'] = implode('|', array_map('strval', $variables));
        }

        if (!empty($mediaUrl)) {
            $params['media_url'] = $mediaUrl;
        }

        $url = $this->baseUrl . '/dev/whatsapp?' . http_build_query($params);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_HTTPHEADER => [
                'authorization: ' . $this->apiKey,
                'Accept: application/json',
                'User-Agent: CloudASPIRE-WABA-Client/1.0'
            ],
            CURLOPT_SSL_VERIFYPEER => true
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            $this->logActivity('WHATSAPP_SEND_ERROR', "cURL Error: $curlError (To: $cleanTo, Template: $messageId)");
            return [
                'success' => false,
                'error' => 'Failed to connect to Fast2SMS: ' . $curlError
            ];
        }

        $result = json_decode($response, true);
        $isSuccess = ($httpCode === 200 && ($result['return'] ?? false) === true) || ($result['status_code'] ?? 0) === 200 || ($result['success'] ?? false) === true;

        if ($isSuccess) {
            $requestId = $result['request_id'] ?? $result['message_id'] ?? 'OK';
            $this->logActivity('WHATSAPP_SENT', "WhatsApp sent to $cleanTo (Template $messageId, Request ID: $requestId)");
            return [
                'success' => true,
                'message' => 'WhatsApp template message dispatched successfully.',
                'request_id' => $requestId,
                'data' => $result
            ];
        }

        $errMsg = $result['message'] ?? $result['error'] ?? "HTTP $httpCode: Message dispatch failed.";
        $this->logActivity('WHATSAPP_SEND_ERROR', "Fast2SMS Error: $errMsg (To: $cleanTo, Template: $messageId)");

        return [
            'success' => false,
            'error' => $errMsg,
            'raw_response' => $result ?: $response
        ];
    }

    /**
     * Send instant lead notification to Admin via WhatsApp
     */
    public function sendLeadNotificationWhatsApp(array $leadData): array {
        if (!$this->isEnabled() || empty($this->leadTemplateId) || empty($this->adminNumber)) {
            return ['success' => false, 'error' => 'WhatsApp lead notifications are not configured.'];
        }

        $name = $leadData['name'] ?? 'Prospective Client';
        $phone = $leadData['phone'] ?? 'Not provided';
        $email = $leadData['email'] ?? 'Not provided';
        $service = $leadData['service'] ?? 'General Inquiry';

        // Template variables for Lead Alert: e.g. {{1}}=Name, {{2}}=Service, {{3}}=Phone
        $variables = [$name, $service, $phone, $email];

        return $this->sendTemplateMessage(
            $this->adminNumber,
            $this->leadTemplateId,
            $this->phoneNumberId,
            $variables
        );
    }

    /**
     * Send Thank You / Confirmation WhatsApp to the Lead
     * Template: thank_you (Message ID: 19802)
     * Variables: {{1}} = Client Name, {{2}} = Business / Service Inquiry
     */
    public function sendLeadThankYouWhatsApp(string $mobile, string $name, string $service = ''): array {
        if (!$this->isEnabled() || empty($this->welcomeTemplateId) || empty($mobile)) {
            return ['success' => false, 'error' => 'WhatsApp welcome messages are not configured.'];
        }

        // Variables for template: {{1}}=Name, {{2}}=Business/Service
        $serviceClean = !empty(trim($service)) ? trim($service) : 'growth & digital solutions';
        $nameClean = !empty(trim($name)) ? trim($name) : 'Valued Client';
        $variables = [$nameClean, $serviceClean];

        return $this->sendTemplateMessage(
            $mobile,
            $this->welcomeTemplateId,
            $this->phoneNumberId,
            $variables
        );
    }

    /**
     * Send Review Moderation Alert to Admin via WhatsApp
     */
    public function sendReviewAlertWhatsApp(array $reviewData): array {
        if (!$this->isEnabled() || empty($this->reviewTemplateId) || empty($this->adminNumber)) {
            return ['success' => false, 'error' => 'WhatsApp review alerts are not configured.'];
        }

        $name = $reviewData['name'] ?? 'Client';
        $rating = ($reviewData['star_rating'] ?? '5') . ' Stars';
        $message = substr($reviewData['message'] ?? '', 0, 50);

        // Template variables: {{1}}=Name, {{2}}=Rating, {{3}}=Snippet
        $variables = [$name, $rating, $message];

        return $this->sendTemplateMessage(
            $this->adminNumber,
            $this->reviewTemplateId,
            $this->phoneNumberId,
            $variables
        );
    }

    /**
     * Send test WhatsApp message from admin panel
     */
    public function sendTestMessage(
        string $to,
        int|string $messageId,
        ?string $phoneNumberId = null,
        array $variables = []
    ): array {
        return $this->sendTemplateMessage($to, $messageId, $phoneNumberId, $variables);
    }

    /**
     * Log WhatsApp activity into admin_activities
     */
    private function logActivity(string $action, string $details): void {
        if (!$this->conn) return;
        try {
            $stmt = $this->conn->prepare("INSERT INTO admin_activities (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
            $stmt->execute([
                $_SESSION['admin_id'] ?? 0,
                $action,
                $details,
                $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
            ]);
        } catch (Exception $e) {
            // Silently ignore activity logging issues
        }
    }
}
