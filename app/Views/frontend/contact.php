<?php 
/**
 * Contact Us View - CherriDM Theme
 * Redesigned with "Request a Free Quote" Visual Language
 * 100% Dynamic with Database Integration
 */
$active_services = $active_services ?? [];
$selected_service = $selected_service ?? '';
$contact_email = !empty($settings['contact_email']) ? $settings['contact_email'] : ($contact_email ?? 'cloudaspire2025@gmail.com');
$contact_phone = !empty($settings['contact_phone']) ? $settings['contact_phone'] : ($contact_phone ?? '8098830937');
$contact_whatsapp = !empty($settings['contact_whatsapp']) ? $settings['contact_whatsapp'] : ($contact_whatsapp ?? '8098830937');
$contact_hours = !empty($settings['contact_hours']) ? $settings['contact_hours'] : ($contact_hours ?? 'Mon - Sat: 9:00 AM - 7:00 PM');
$contact_address = !empty($settings['contact_address']) ? $settings['contact_address'] : 'No. 53, Auvai Nagar, Puducherry, 605008';
$site_name = !empty($settings['site_name']) ? $settings['site_name'] : 'Cloud Aspire';
$msg_status = $msg_status ?? null;
$msg_text = $msg_text ?? '';

// Fallback: If active_services is not passed, fetch directly from DB
if (empty($active_services) && isset($conn)) {
    try {
        $active_services = $conn->query("SELECT id, service_name, slug, price FROM services WHERE status = 'active' ORDER BY created_at ASC")->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $active_services = [];
    }
}

// Normalize WhatsApp number for direct wa.me link
$whatsapp_clean = preg_replace('/[^0-9]/', '', $contact_whatsapp);
if (strlen($whatsapp_clean) === 10) {
    $whatsapp_clean = '91' . $whatsapp_clean;
}

include __DIR__ . '/../../../includes/header.php'; 
?>

<!-- REUSABLE PAGE HERO COMPONENT -->
<?php 
$hero_title = 'Contact ' . $site_name;
$hero_highlight = 'Contact';
$hero_subtitle = 'Get in touch with Pondicherry\'s leading digital marketing team. Let\'s discuss your goals, scale your reach, and generate verified revenue.';
$hero_breadcrumbs = [
    ['label' => 'Home', 'url' => $base_url . '/'],
    ['label' => 'Contact Us']
];
include __DIR__ . '/../../../includes/components/page_hero.php'; 
?>

<!-- SCOPED CONTACT PAGE STYLES -->
<style>
    /* ==========================================================================
       CLOUD ASPIRE CONTACT PAGE REDESIGN
       Scoped styling inspired by the "Request a Free Quote" visual language
       ========================================================================== */
    .cdm-contact-section {
        padding: 70px 0 85px;
        background-color: #f8faff;
        position: relative;
    }

    .cdm-contact-container {
        width: 100%;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
        box-sizing: border-box;
    }

    /* Two-column layout */
    .cdm-contact-wrapper {
        display: grid;
        grid-template-columns: 1.28fr 0.82fr;
        gap: 36px;
        align-items: start;
    }

    /* Common Card Styling */
    .cdm-contact-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        box-shadow: 0 10px 32px rgba(3, 34, 143, 0.06);
        position: relative;
        overflow: hidden;
        transition: box-shadow 0.3s ease;
    }

    .cdm-contact-card:hover {
        box-shadow: 0 16px 42px rgba(3, 34, 143, 0.09);
    }

    /* Card Accent Top Line */
    .cdm-contact-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #03228f 0%, #fb9a27 100%);
        z-index: 2;
    }

    /* --------------------------------------------------------------------------
       Left Column: Send Us a Message Form Card
       -------------------------------------------------------------------------- */
    .cdm-form-card-inner {
        padding: 44px 40px;
    }

    .cdm-contact-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #fb9a27;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 12px;
        letter-spacing: 1.2px;
        margin-bottom: 8px;
    }

    .cdm-contact-title {
        font-size: clamp(1.6rem, 2.8vw, 1.95rem);
        font-weight: 800;
        color: #03228f;
        line-height: 1.25;
        margin: 0 0 8px 0;
    }

    .cdm-contact-subtitle {
        color: #64748b;
        font-size: 0.95rem;
        line-height: 1.6;
        margin: 0 0 28px 0;
    }

    /* Status Notifications */
    .cdm-alert-status {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        padding: 16px 20px;
        border-radius: 10px;
        font-size: 0.95rem;
        font-weight: 500;
        line-height: 1.5;
        margin-bottom: 26px;
        animation: cdmFadeDown 0.35s ease-out forwards;
    }

    @keyframes cdmFadeDown {
        from { opacity: 0; transform: translateY(-8px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .cdm-alert-status.success {
        background: #ecfdf5;
        border: 1.5px solid #a7f3d0;
        color: #065f46;
    }
    .cdm-alert-status.success i {
        color: #10b981;
        font-size: 1.35rem;
        margin-top: 2px;
    }

    .cdm-alert-status.error {
        background: #fff1f2;
        border: 1.5px solid #fecdd3;
        color: #9f1239;
    }
    .cdm-alert-status.error i {
        color: #ef4444;
        font-size: 1.35rem;
        margin-top: 2px;
    }

    /* Form Fields & Layout */
    .cdm-form-grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 20px;
    }

    .cdm-form-group {
        display: flex;
        flex-direction: column;
    }

    .cdm-field-label {
        font-size: 0.88rem;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .cdm-field-label .required {
        color: #ef4444;
        font-weight: 800;
    }

    .cdm-input-field {
        width: 100%;
        height: 48px;
        padding: 12px 16px;
        border: 1.5px solid #cbd5e1;
        border-radius: 10px;
        font-size: 0.95rem;
        font-family: inherit;
        color: #1e293b;
        background-color: #ffffff;
        outline: none;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        box-sizing: border-box;
    }

    .cdm-input-field::placeholder {
        color: #94a3b8;
    }

    .cdm-input-field:hover {
        border-color: #94a3b8;
    }

    .cdm-input-field:focus {
        border-color: #03228f;
        background-color: #ffffff;
        box-shadow: 0 0 0 3.5px rgba(3, 34, 143, 0.12);
    }

    /* Custom Select with styled arrow */
    select.cdm-input-field {
        background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2303228f' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 16px center;
        background-size: 15px;
        padding-right: 44px;
        cursor: pointer;
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
    }

    /* Textarea Field */
    textarea.cdm-input-field {
        height: auto;
        min-height: 115px;
        resize: vertical;
        padding: 14px 16px;
        line-height: 1.55;
    }

    /* Primary Orange Submit Button */
    .cdm-btn-contact-submit {
        background: linear-gradient(135deg, #fb9a27 0%, #f58220 100%);
        color: #ffffff;
        border: none;
        padding: 15px 28px;
        border-radius: 10px;
        font-size: 1rem;
        font-weight: 700;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        width: 100%;
        box-shadow: 0 6px 20px rgba(251, 154, 39, 0.35);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        margin-top: 8px;
        position: relative;
        overflow: hidden;
    }

    .cdm-btn-contact-submit:hover {
        background: linear-gradient(135deg, #e68200 0%, #de7012 100%);
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(251, 154, 39, 0.45);
        color: #ffffff;
    }

    .cdm-btn-contact-submit:active {
        transform: translateY(0);
        box-shadow: 0 4px 14px rgba(251, 154, 39, 0.3);
    }

    .cdm-btn-contact-submit i {
        font-size: 1rem;
        transition: transform 0.3s ease;
    }

    .cdm-btn-contact-submit:hover i {
        transform: translateX(4px) rotate(-8deg);
    }

    .cdm-btn-contact-submit.loading {
        pointer-events: none;
        opacity: 0.85;
    }

    /* --------------------------------------------------------------------------
       Right Column: Direct Connect
       -------------------------------------------------------------------------- */
    .cdm-sidebar-card-inner {
        padding: 38px 32px;
    }

    .cdm-info-list {
        display: flex;
        flex-direction: column;
        margin-top: 12px;
    }

    .cdm-info-item {
        display: flex;
        align-items: flex-start;
        gap: 16px;
        padding: 18px 0;
        border-bottom: 1px solid #f1f5f9;
        transition: transform 0.2s ease;
    }

    .cdm-info-item:last-child {
        border-bottom: none;
    }

    .cdm-info-item:hover {
        transform: translateX(4px);
    }

    .cdm-info-icon-box {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        background: rgba(3, 34, 143, 0.08);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #03228f;
        font-size: 1.2rem;
        flex-shrink: 0;
        transition: all 0.3s ease;
    }

    .cdm-info-item:hover .cdm-info-icon-box {
        background: #03228f;
        color: #ffffff;
        transform: scale(1.05);
        box-shadow: 0 4px 14px rgba(3, 34, 143, 0.25);
    }

    .cdm-info-text {
        flex: 1;
    }

    .cdm-info-label {
        font-size: 0.78rem;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        margin-bottom: 4px;
        display: block;
    }

    .cdm-info-value {
        font-size: 0.96rem;
        color: #0f172a;
        font-weight: 600;
        line-height: 1.45;
        display: block;
    }

    .cdm-info-link {
        font-size: 1.05rem;
        font-weight: 700;
        color: #03228f;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: color 0.2s ease;
    }

    .cdm-info-link:hover {
        color: #fb9a27;
    }

    .cdm-pulse-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #10b981;
        display: inline-block;
        margin-right: 6px;
        vertical-align: middle;
        animation: cdmDotPulse 2s infinite ease-in-out;
    }

    @keyframes cdmDotPulse {
        0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.6); }
        70% { transform: scale(1.2); box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
        100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
    }
    /* --------------------------------------------------------------------------
       Map Card Section
       -------------------------------------------------------------------------- */
    .cdm-map-card {
        margin-top: 55px;
        border-radius: 16px;
        overflow: hidden;
        border: 1px solid #e2e8f0;
        box-shadow: 0 10px 32px rgba(3, 34, 143, 0.06);
        background: #ffffff;
        position: relative;
    }

    .cdm-map-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, #03228f, #fb9a27, #03228f);
        z-index: 2;
    }

    .cdm-map-header {
        padding: 16px 24px;
        background: #ffffff;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
    }

    .cdm-map-title {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 0.95rem;
    }

    .cdm-map-title i {
        color: #fb9a27;
        font-size: 1.15rem;
    }

    .cdm-map-title strong {
        color: #03228f;
        font-weight: 700;
    }

    .cdm-map-title span {
        color: #64748b;
        font-size: 0.85rem;
    }

    .cdm-map-link {
        color: #03228f;
        font-weight: 700;
        font-size: 0.85rem;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: color 0.2s ease;
    }

    .cdm-map-link:hover {
        color: #fb9a27;
    }

    /* --------------------------------------------------------------------------
       Responsive Breakpoints
       -------------------------------------------------------------------------- */
    @media (max-width: 992px) {
        .cdm-contact-wrapper {
            grid-template-columns: 1fr;
            gap: 32px;
        }
        .cdm-form-card-inner {
            padding: 36px 30px;
        }
        .cdm-sidebar-card-inner {
            padding: 34px 28px;
        }
    }

    @media (max-width: 680px) {
        .cdm-contact-section {
            padding: 50px 0 65px;
        }
        .cdm-contact-container {
            padding: 0 16px;
        }
        .cdm-form-card-inner {
            padding: 28px 20px;
        }
        .cdm-sidebar-card-inner {
            padding: 28px 20px;
        }
        .cdm-form-grid-2 {
            grid-template-columns: 1fr;
            gap: 16px;
            margin-bottom: 16px;
        }
        .cdm-map-header {
            flex-direction: column;
            align-items: flex-start;
        }
    }
</style>

<!-- MAIN CONTACT SECTION -->
<section class="cdm-contact-section">
    <div class="cdm-contact-container">
        
        <div class="cdm-contact-wrapper">
            
            <!-- LEFT COLUMN: SEND US A MESSAGE -->
            <div class="cdm-contact-card cdm-reveal-left">
                <div class="cdm-form-card-inner">
                    
                    <span class="cdm-contact-badge">
                        <i class="fas fa-paper-plane" style="font-size: 11px;"></i>
                        Send Us a Message
                    </span>
                    <h3 class="cdm-contact-title">Send Us a Message</h3>
                    <p class="cdm-contact-subtitle">Fill out the form below and our strategy team will respond within 2 hours.</p>

                    <!-- Alert / Status Notification -->
                    <?php if ($msg_status === 'success'): ?>
                        <div class="cdm-alert-status success">
                            <i class="fas fa-check-circle"></i>
                            <div><?= $msg_text ?></div>
                        </div>
                    <?php elseif ($msg_status === 'error'): ?>
                        <div class="cdm-alert-status error">
                            <i class="fas fa-exclamation-circle"></i>
                            <div><?= $msg_text ?></div>
                        </div>
                    <?php endif; ?>

                    <!-- Form preserving all original processing & CSRF -->
                    <form action="" method="POST" id="contactForm" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCSRFToken()) ?>">
                        
                        <!-- Row 1: Full Name & Email Address -->
                        <div class="cdm-form-grid-2">
                            <div class="cdm-form-group">
                                <label class="cdm-field-label" for="contact_name">
                                    Full Name <span class="required">*</span>
                                </label>
                                <input 
                                    type="text" 
                                    id="contact_name" 
                                    name="name" 
                                    class="cdm-input-field" 
                                    placeholder="e.g. John Doe" 
                                    value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>" 
                                    required
                                    autocomplete="name"
                                >
                            </div>

                            <div class="cdm-form-group">
                                <label class="cdm-field-label" for="contact_email">
                                    Email Address <span class="required">*</span>
                                </label>
                                <input 
                                    type="email" 
                                    id="contact_email" 
                                    name="email" 
                                    class="cdm-input-field" 
                                    placeholder="e.g. john@company.com" 
                                    value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" 
                                    required
                                    autocomplete="email"
                                >
                            </div>
                        </div>

                        <!-- Row 2: Phone Number & Service Select -->
                        <div class="cdm-form-grid-2">
                            <div class="cdm-form-group">
                                <label class="cdm-field-label" for="contact_phone">
                                    Phone Number <span class="required">*</span>
                                </label>
                                <input 
                                    type="tel" 
                                    id="contact_phone" 
                                    name="phone" 
                                    class="cdm-input-field" 
                                    placeholder="+91 80988 30937" 
                                    value="<?= isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : '' ?>" 
                                    required
                                    autocomplete="tel"
                                >
                            </div>

                            <div class="cdm-form-group">
                                <label class="cdm-field-label" for="contact_service">
                                    Select Service <span class="required">*</span>
                                </label>
                                <select id="contact_service" name="service" class="cdm-input-field" required>
                                    <option value="" disabled <?= empty($_POST['service']) && empty($_GET['service']) && empty($selected_service) ? 'selected' : '' ?>>Select Desired Service*</option>
                                    <?php
                                    $current_sel = !empty($_POST['service']) ? $_POST['service'] : (!empty($_GET['service']) ? $_GET['service'] : $selected_service);
                                    foreach ($active_services as $svc):
                                        $sname = $svc['service_name'] ?? '';
                                        $is_sel = (strcasecmp($sname, $current_sel) === 0);
                                    ?>
                                        <option value="<?= htmlspecialchars($sname) ?>" <?= $is_sel ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($sname) ?>
                                        </option>
                                    <?php endforeach; ?>
                                    <?php if (empty($active_services)): ?>
                                        <option value="SEO Optimization">SEO Optimization</option>
                                        <option value="Google & Meta Ads">Google & Meta Ads</option>
                                        <option value="Social Media Marketing">Social Media Marketing</option>
                                        <option value="Website & App Development">Website & App Development</option>
                                        <option value="Brand Identity & Design">Brand Identity & Design</option>
                                        <option value="Content Marketing & Copywriting">Content Marketing & Copywriting</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Row 3: Message / Project Goals -->
                        <div class="cdm-form-group" style="margin-bottom: 22px;">
                            <label class="cdm-field-label" for="contact_message">
                                Your Message / Project Goals <span class="required">*</span>
                            </label>
                            <textarea 
                                id="contact_message" 
                                name="message" 
                                class="cdm-input-field" 
                                rows="4" 
                                placeholder="Tell us about your business goals, target audience, or current digital challenges..." 
                                required
                            ><?= isset($_POST['message']) ? htmlspecialchars($_POST['message']) : '' ?></textarea>
                        </div>

                        <!-- Primary Orange Submit Button with Paper Plane Icon -->
                        <button type="submit" class="cdm-btn-contact-submit" id="btnContactSubmit">
                            <span>Send Message</span>
                            <i class="fas fa-paper-plane"></i>
                        </button>

                    </form>

                </div>
            </div>

            <!-- RIGHT COLUMN: DIRECT CONNECT -->
            <div class="cdm-contact-sidebar cdm-reveal-right">
                
                <div class="cdm-contact-card">
                    <div class="cdm-sidebar-card-inner">
                        
                        <span class="cdm-contact-badge">
                            <i class="fas fa-bolt" style="font-size: 11px;"></i>
                            Direct Connect
                        </span>
                        <h3 class="cdm-contact-title" style="font-size: 1.55rem; margin-bottom: 6px;">Direct Connect</h3>
                        <p style="color: #64748b; font-size: 0.9rem; line-height: 1.5; margin: 0 0 16px 0;">
                            Reach our agency strategists directly or schedule an on-site consultation.
                        </p>

                        <div class="cdm-info-list">
                            
                            <!-- Phone Item -->
                            <div class="cdm-info-item">
                                <div class="cdm-info-icon-box">
                                    <i class="fas fa-phone-alt"></i>
                                </div>
                                <div class="cdm-info-text">
                                    <span class="cdm-info-label">Direct Phone</span>
                                    <a href="tel:<?= htmlspecialchars($contact_phone) ?>" class="cdm-info-link">
                                        <?= htmlspecialchars($contact_phone) ?>
                                    </a>
                                </div>
                            </div>

                            <!-- Email Item -->
                            <div class="cdm-info-item">
                                <div class="cdm-info-icon-box">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div class="cdm-info-text">
                                    <span class="cdm-info-label">Email Inquiries</span>
                                    <a href="mailto:<?= htmlspecialchars($contact_email) ?>" class="cdm-info-link" style="font-size: 0.95rem;">
                                        <?= htmlspecialchars($contact_email) ?>
                                    </a>
                                </div>
                            </div>

                            <!-- Headquarters Item -->
                            <div class="cdm-info-item">
                                <div class="cdm-info-icon-box">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div class="cdm-info-text">
                                    <span class="cdm-info-label">Headquarters</span>
                                    <span class="cdm-info-value" style="font-size: 0.92rem; font-weight: 500;">
                                        <?= htmlspecialchars($contact_address) ?>
                                    </span>
                                </div>
                            </div>

                            <!-- Operating Hours Item -->
                            <div class="cdm-info-item">
                                <div class="cdm-info-icon-box">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="cdm-info-text">
                                    <span class="cdm-info-label">Operating Hours</span>
                                    <span class="cdm-info-value" style="font-size: 0.92rem;">
                                        <span class="cdm-pulse-dot"></span> <?= htmlspecialchars($contact_hours) ?>
                                    </span>
                                </div>
                            </div>

                        </div>

                    </div>
                </div>

            </div>

        </div>

        <!-- GOOGLE MAP & INTERACTIVE DIRECTIONS SECTION -->
        <?php
        $display_location = !empty($settings['contact_map_location']) ? $settings['contact_map_location'] : $contact_address;
        $directions_url = 'https://maps.google.com/?q=' . urlencode($display_location);
        ?>
        <div class="cdm-map-card">
            <div class="cdm-map-header">
                <div class="cdm-map-title">
                    <i class="fas fa-location-dot"></i>
                    <div>
                        <strong><?= htmlspecialchars($site_name) ?> Headquarters</strong>
                        <span>&bull; <?= htmlspecialchars($display_location) ?></span>
                    </div>
                </div>
                <a href="<?= $directions_url ?>" target="_blank" rel="noopener noreferrer" class="cdm-map-link">
                    <span>Open in Google Maps</span>
                    <i class="fas fa-arrow-up-right-from-square" style="font-size: 0.8rem;"></i>
                </a>
            </div>

            <!-- Map DOM Target -->
            <div id="contactMapDOM" style="width: 100%; height: 380px;"></div>
        </div>

    </div>
</section>

<!-- MAP SCRIPTS WITH AUTOMATIC FALLBACK TO LEAFLET -->
<?php 
$gmaps_api_param = !empty($settings['google_maps_api_key']) ? '&key=' . urlencode($settings['google_maps_api_key']) : '';
?>
<script src="https://maps.googleapis.com/maps/api/js?v=weekly<?= $gmaps_api_param ?>&callback=initGoogleMapsAPI&loading=async" async defer></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css" />
<style>
.leaflet-control-attribution, .leaflet-bottom.leaflet-right { display: none !important; }
</style>
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>

<script>
let googleMapsLoaded = false;
const mapLat = <?= (float)($settings['contact_map_lat'] ?? 11.93605) ?>;
const mapLng = <?= (float)($settings['contact_map_lng'] ?? 79.80387) ?>;
const companyTitle = <?= json_encode($display_location) ?>;
const directionsUrl = <?= json_encode($directions_url) ?>;

window.initGoogleMapsAPI = function() {
    const mapElement = document.getElementById('contactMapDOM');
    if (!mapElement) return;

    googleMapsLoaded = true;
    mapElement.innerHTML = '';

    const centerCoords = { lat: mapLat, lng: mapLng };
    const googleMapsInstance = new google.maps.Map(mapElement, {
        zoom: 15,
        center: centerCoords,
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        mapTypeControl: false,
        streetViewControl: false,
        fullscreenControl: true,
        zoomControl: true
    });

    const googleMarker = new google.maps.Marker({
        position: centerCoords,
        map: googleMapsInstance,
        title: companyTitle,
        animation: google.maps.Animation.DROP
    });

    const googleInfoWindow = new google.maps.InfoWindow({
        content: `
            <div style="font-family: inherit; padding: 4px; min-width: 210px;">
                <strong style="font-size: 14px; color: #03228f; display: block; margin-bottom: 2px;">${companyTitle}</strong>
                <a href="${directionsUrl}" target="_blank" rel="noopener noreferrer" style="color: #fb9a27; font-weight: 700; font-size: 12px; text-decoration: none; margin-top: 6px; display: inline-flex; align-items: center; gap: 4px;">
                    Get Directions &rarr;
                </a>
            </div>
        `
    });

    googleMarker.addListener('click', function() {
        googleInfoWindow.open(googleMapsInstance, googleMarker);
    });
};

window.gm_authFailure = function() {
    fallbackToLeafletMap();
};

setTimeout(function() {
    if (!googleMapsLoaded) {
        fallbackToLeafletMap();
    }
}, 2000);

function fallbackToLeafletMap() {
    const mapElement = document.getElementById('contactMapDOM');
    if (!mapElement || mapElement.querySelector('.leaflet-pane') || googleMapsLoaded) return;

    mapElement.innerHTML = '';
    const leafletMapInstance = L.map('contactMapDOM', {
        center: [mapLat, mapLng],
        zoom: 15,
        zoomControl: true,
        scrollWheelZoom: false,
        attributionControl: false
    });

    L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
        maxZoom: 20,
        subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
        attribution: ''
    }).addTo(leafletMapInstance);

    const pinIcon = L.divIcon({
        className: 'custom-google-pin',
        html: `<div style="width: 32px; height: 42px; filter: drop-shadow(0 4px 8px rgba(0,0,0,0.3)); cursor: pointer;">
            <svg viewBox="0 0 24 24" width="32" height="42" fill="#03228f">
                <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                <circle cx="12" cy="9" r="3" fill="#fb9a27"/>
            </svg>
        </div>`,
        iconSize: [32, 42],
        iconAnchor: [16, 42]
    });

    const marker = L.marker([mapLat, mapLng], { icon: pinIcon }).addTo(leafletMapInstance);
    marker.bindPopup(`
        <div style="font-family: inherit; padding: 4px;">
            <strong style="font-size: 13px; color: #03228f;">${companyTitle}</strong><br>
            <a href="${directionsUrl}" target="_blank" rel="noopener noreferrer" style="color: #fb9a27; font-weight: 700; font-size: 11px;">Directions &rarr;</a>
        </div>
    `);
}

// Client-Side Validation & Loading State Feedback
document.addEventListener('DOMContentLoaded', function() {
    const contactForm = document.getElementById('contactForm');
    const submitBtn = document.getElementById('btnContactSubmit');

    if (contactForm && submitBtn) {
        contactForm.addEventListener('submit', function(e) {
            let isValid = true;
            const requiredFields = contactForm.querySelectorAll('[required]');
            
            requiredFields.forEach(function(field) {
                if (!field.value.trim()) {
                    isValid = false;
                    field.style.borderColor = '#ef4444';
                    field.style.boxShadow = '0 0 0 3px rgba(239, 68, 68, 0.12)';
                } else {
                    field.style.borderColor = '';
                    field.style.boxShadow = '';
                }
            });

            // Email format check
            const emailField = document.getElementById('contact_email');
            if (emailField && emailField.value.trim()) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(emailField.value.trim())) {
                    isValid = false;
                    emailField.style.borderColor = '#ef4444';
                    emailField.style.boxShadow = '0 0 0 3px rgba(239, 68, 68, 0.12)';
                }
            }

            if (!isValid) {
                e.preventDefault();
                // Scroll smoothly to first invalid field
                const firstInvalid = contactForm.querySelector('[required]:invalid, [style*="border-color: rgb(239, 68, 68)"]');
                if (firstInvalid) {
                    firstInvalid.focus();
                }
                return false;
            }

            // Valid submission - show active loading state
            submitBtn.classList.add('loading');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Sending Message...</span>';
        });

        // Reset error highlight on user input
        contactForm.querySelectorAll('.cdm-input-field').forEach(function(input) {
            input.addEventListener('input', function() {
                if (this.value.trim()) {
                    this.style.borderColor = '';
                    this.style.boxShadow = '';
                }
            });
            input.addEventListener('change', function() {
                if (this.value.trim()) {
                    this.style.borderColor = '';
                    this.style.boxShadow = '';
                }
            });
        });
    }
});
</script>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>
