<?php
/**
 * WhatsApp Helper Functions
 * Global procedural interface for Fast2SMS WhatsApp Business API
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/services/WhatsAppService.php';

/**
 * Get singleton instance of WhatsAppService
 */
function getWhatsAppService(): WhatsAppService {
    static $service = null;
    if ($service === null) {
        global $conn;
        $service = new WhatsAppService($conn);
    }
    return $service;
}

/**
 * Check if WhatsApp notifications are enabled
 */
function isWhatsAppEnabled(): bool {
    return getWhatsAppService()->isEnabled();
}

/**
 * Send WhatsApp template message
 */
function sendWhatsAppTemplate(
    string $to,
    int|string $messageId,
    array $variables = [],
    ?string $phoneNumberId = null,
    ?string $mediaUrl = null
): array {
    return getWhatsAppService()->sendTemplateMessage($to, $messageId, $phoneNumberId, $variables, $mediaUrl);
}

/**
 * Send WhatsApp notification to Admin when a new lead is created
 */
function sendLeadWhatsAppAlert(array $leadData): array {
    return getWhatsAppService()->sendLeadNotificationWhatsApp($leadData);
}

/**
 * Send WhatsApp thank you message to the client
 */
function sendLeadWhatsAppThankYou(string $mobile, string $name, string $service = ''): array {
    return getWhatsAppService()->sendLeadThankYouWhatsApp($mobile, $name, $service);
}

/**
 * Send WhatsApp alert to Admin when a new review is submitted
 */
function sendReviewWhatsAppAlert(array $reviewData): array {
    return getWhatsAppService()->sendReviewAlertWhatsApp($reviewData);
}

/**
 * Fetch WABA & Template details from Fast2SMS
 */
function fetchFast2SMSWabaDetails(?string $apiKey = null): array {
    return getWhatsAppService()->getWabaAndTemplateDetails($apiKey);
}
