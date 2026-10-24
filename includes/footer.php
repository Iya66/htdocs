<?php
/**
 * Site Footer - CherriDM Theme
 * 100% Connected to Database Settings, Dynamic Services, and Live Lead Pipeline
 */

$site_name = !empty($settings['site_name']) ? $settings['site_name'] : 'Cloud Aspire';
$site_logo = !empty($settings['site_logo']) ? $settings['site_logo'] : '';
$contact_email = !empty($settings['contact_email']) ? $settings['contact_email'] : 'cloudaspire2025@gmail.com';
$contact_phone = !empty($settings['contact_phone']) ? $settings['contact_phone'] : '8098830937';
$contact_whatsapp = !empty($settings['contact_whatsapp']) ? $settings['contact_whatsapp'] : '8098830937';
$contact_address = !empty($settings['contact_address']) ? $settings['contact_address'] : 'No. 53, Auvai Nagar, Puducherry, 605008';

$facebook_url = !empty($settings['social_facebook']) ? $settings['social_facebook'] : (!empty($settings['social_facebook_url']) ? $settings['social_facebook_url'] : 'https://facebook.com');
$twitter_url = !empty($settings['social_twitter']) ? $settings['social_twitter'] : (!empty($settings['social_twitter_url']) ? $settings['social_twitter_url'] : 'https://twitter.com');
$instagram_url = !empty($settings['social_instagram']) ? $settings['social_instagram'] : (!empty($settings['social_instagram_url']) ? $settings['social_instagram_url'] : 'https://www.instagram.com/cloudaspire.in/');
$linkedin_url = !empty($settings['social_linkedin']) ? $settings['social_linkedin'] : (!empty($settings['social_linkedin_url']) ? $settings['social_linkedin_url'] : 'https://www.linkedin.com/in/cloud-aspire-3819b43a3');
$maps_url = !empty($settings['maps_url']) ? $settings['maps_url'] : 'https://maps.google.com/?q=No+53+Auvai+Nagar+Puducherry';

// Clean WhatsApp number
$clean_wa = preg_replace('/[^0-9]/', '', $contact_whatsapp);
if (!str_starts_with($clean_wa, '91') && strlen($clean_wa) === 10) {
    $clean_wa = '91' . $clean_wa;
}
$base_url = $base_url ?? '';

// Dynamic services for footer and modal from DB
$footer_services = [];
if (isset($conn)) {
    try {
        $footer_services = $conn->query("SELECT service_name, slug FROM services WHERE status = 'active' ORDER BY created_at ASC")->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $footer_services = [];
    }
}
?>

<!-- CHERRIDM FOOTER STYLES -->
<style>
    .cdm-footer {
        background: linear-gradient(135deg, #02144a 0%, #03228f 100%);
        color: #ffffff;
        position: relative;
        padding-top: 60px;
        font-family: var(--font-main, 'Plus Jakarta Sans', sans-serif);
    }

    .cdm-footer .container {
        max-width: 1240px;
        margin: 0 auto;
        padding: 0 20px;
    }

    /* Top Strip */
    .cdm-footer-top-strip {
        border-bottom: 1px solid rgba(255, 255, 255, 0.12);
        padding-bottom: 40px;
        margin-bottom: 50px;
    }

    .cdm-footer-top-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 30px;
        text-align: center;
    }

    .cdm-info-box {
        padding: 10px 15px;
        border-right: 1px solid rgba(255, 255, 255, 0.1);
    }

    .cdm-info-box:last-child {
        border-right: none;
    }

    .cdm-info-box i {
        font-size: 28px;
        color: var(--secondary-color, #fb9a27);
        margin-bottom: 12px;
        display: inline-block;
    }

    .cdm-info-box h4 {
        font-size: 16px;
        font-weight: 700;
        margin-bottom: 8px;
        color: #ffffff;
    }

    .cdm-info-box a, .cdm-info-box p {
        color: rgba(255, 255, 255, 0.85);
        font-size: 14px;
        line-height: 1.5;
    }

    .cdm-info-box a:hover {
        color: var(--secondary-color, #fb9a27);
    }

    /* 4 Column Main Footer */
    .cdm-footer-main-grid {
        display: grid;
        grid-template-columns: 1.2fr 0.9fr 0.9fr 1.3fr;
        gap: 40px;
        padding-bottom: 50px;
    }

    .cdm-footer-col h3 {
        font-size: 18px;
        font-weight: 700;
        color: #ffffff;
        margin-bottom: 24px;
        position: relative;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .cdm-footer-col h3::before {
        content: '//';
        color: var(--secondary-color, #fb9a27);
        font-weight: 800;
    }

    .cdm-footer-bio {
        color: rgba(255, 255, 255, 0.82);
        font-size: 14px;
        line-height: 1.7;
        margin-bottom: 20px;
    }

    .cdm-rating-badges {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .cdm-rating-badge {
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.15);
        border-radius: 6px;
        padding: 8px 12px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 12px;
        color: #ffffff;
    }

    .cdm-rating-badge i.fa-star {
        color: #facc15;
    }

    .cdm-footer-links {
        list-style: none;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .cdm-footer-links a {
        color: rgba(255, 255, 255, 0.8);
        font-size: 14px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.25s ease;
    }

    .cdm-footer-links a i {
        color: var(--secondary-color, #fb9a27);
        font-size: 10px;
    }

    .cdm-footer-links a:hover {
        color: #ffffff;
        transform: translateX(4px);
    }

    /* "Get Free Audit" Footer Form */
    .cdm-footer-form {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .cdm-form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }

    .cdm-footer-form input {
        width: 100%;
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.18);
        border-bottom: 2px solid var(--secondary-color, #fb9a27);
        border-radius: 4px;
        color: #ffffff;
        padding: 10px 14px;
        font-size: 13px;
        outline: none;
        transition: all 0.3s ease;
    }

    .cdm-footer-form input::placeholder {
        color: rgba(255, 255, 255, 0.6);
    }

    .cdm-footer-form input:focus {
        background: rgba(255, 255, 255, 0.15);
        border-color: var(--secondary-color, #fb9a27);
    }

    .cdm-btn-audit {
        background: var(--secondary-color, #fb9a27);
        color: #ffffff;
        border: none;
        padding: 12px 20px;
        border-radius: 4px;
        font-weight: 700;
        font-size: 14px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: all 0.3s ease;
        margin-top: 5px;
    }

    .cdm-btn-audit:hover {
        background: #ffffff;
        color: var(--primary-color, #03228f);
        transform: translateY(-2px);
    }

    /* Copyright Bar */
    .cdm-footer-bottom {
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        padding: 22px 0;
        font-size: 13px;
    }

    .cdm-footer-bottom .container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
    }

    .cdm-footer-bottom p {
        color: rgba(255, 255, 255, 0.75);
    }

    .cdm-footer-bottom a {
        color: var(--secondary-color, #fb9a27);
        font-weight: 600;
    }

    .cdm-footer-social {
        display: flex;
        align-items: center;
        gap: 12px;
        list-style: none;
    }

    .cdm-footer-social a {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #243e94;
        color: #ffffff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        transition: all 0.3s ease;
    }

    .cdm-footer-social a:hover {
        background: var(--secondary-color, #fb9a27);
        color: #ffffff;
        transform: translateY(-3px);
    }

    /* Floating WhatsApp Button */
    .cdm-float-wa {
        position: fixed;
        width: 58px;
        height: 58px;
        bottom: 30px;
        right: 30px;
        background-color: #25d366;
        color: #ffffff !important;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        box-shadow: 0 8px 25px rgba(37, 211, 102, 0.45);
        z-index: 9990;
        transition: transform 0.3s ease, opacity 0.25s ease, visibility 0.25s ease;
        animation: cdmPulse 2s infinite;
    }

    .cdm-float-wa:hover {
        transform: scale(1.12);
    }

    @keyframes cdmPulse {
        0% { box-shadow: 0 0 0 0 rgba(37, 211, 102, 0.6); }
        70% { box-shadow: 0 0 0 16px rgba(37, 211, 102, 0); }
        100% { box-shadow: 0 0 0 0 rgba(37, 211, 102, 0); }
    }

    /* Pop-up Lead Capture Modal */
    .cdm-modal-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(3, 34, 143, 0.65);
        backdrop-filter: blur(6px);
        z-index: 10000;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .cdm-modal-backdrop.show {
        display: flex;
        opacity: 1;
    }

    .cdm-modal-card {
        background: #ffffff;
        border-radius: 12px;
        width: 100%;
        max-width: 820px;
        box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
        position: relative;
        overflow: hidden;
        display: grid;
        grid-template-columns: 1fr 1.2fr;
        animation: cdmZoomIn 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }

    @keyframes cdmZoomIn {
        from { transform: scale(0.92); opacity: 0; }
        to { transform: scale(1); opacity: 1; }
    }

    .cdm-modal-close {
        position: absolute;
        top: 15px;
        right: 15px;
        background: #f1f5f9;
        border: none;
        width: 34px;
        height: 34px;
        border-radius: 50%;
        font-size: 18px;
        color: #475569;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10;
        transition: all 0.2s ease;
    }

    .cdm-modal-close:hover {
        background: #ef4444;
        color: #ffffff;
    }

    .cdm-modal-left {
        background: linear-gradient(135deg, #03228f 0%, #1e3bb3 100%);
        color: #ffffff;
        padding: 40px 30px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        position: relative;
    }

    .cdm-modal-left h3 {
        font-size: 26px;
        font-weight: 800;
        line-height: 1.25;
        margin-bottom: 15px;
    }

    .cdm-modal-left p {
        font-size: 14px;
        line-height: 1.6;
        color: rgba(255, 255, 255, 0.85);
        margin-bottom: 25px;
    }

    .cdm-modal-left ul {
        list-style: none;
        display: flex;
        flex-direction: column;
        gap: 12px;
        font-size: 13px;
    }

    .cdm-modal-left ul li {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .cdm-modal-left ul li i {
        color: var(--secondary-color, #fb9a27);
    }

    .cdm-modal-right {
        padding: 40px 35px;
    }

    .cdm-modal-right h4 {
        font-size: 20px;
        font-weight: 800;
        color: #03228f;
        margin-bottom: 6px;
    }

    .cdm-modal-right p {
        font-size: 13px;
        color: #64748b;
        margin-bottom: 20px;
    }

    .cdm-popup-form {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .cdm-input-group {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .cdm-popup-form input,
    .cdm-popup-form select,
    .cdm-popup-form textarea {
        width: 100%;
        padding: 11px 14px;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        font-size: 14px;
        color: #1e293b;
        font-family: inherit;
        outline: none;
        transition: border-color 0.2s ease;
    }

    .cdm-popup-form input:focus,
    .cdm-popup-form select:focus,
    .cdm-popup-form textarea:focus {
        border-color: #03228f;
        box-shadow: 0 0 0 3px rgba(3, 34, 143, 0.1);
    }

    .cdm-btn-submit-popup {
        background: #03228f;
        color: #ffffff;
        border: none;
        padding: 13px 20px;
        border-radius: 6px;
        font-size: 15px;
        font-weight: 700;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        transition: all 0.3s ease;
        margin-top: 5px;
    }

    .cdm-btn-submit-popup:hover {
        background: #fb9a27;
        color: #ffffff;
    }

    .cdm-alert-box {
        padding: 10px 14px;
        border-radius: 6px;
        font-size: 13px;
        display: none;
        margin-top: 10px;
    }

    .cdm-alert-box.success {
        background: #dcfce7;
        color: #15803d;
        border: 1px solid #bbf7d0;
        display: block;
    }

    .cdm-alert-box.error {
        background: #fee2e2;
        color: #b91c1c;
        border: 1px solid #fecaca;
        display: block;
    }

    @media (max-width: 992px) {
        .cdm-footer-top-grid {
            grid-template-columns: 1fr;
            text-align: left;
        }
        .cdm-info-box {
            border-right: none;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding-bottom: 20px;
        }
        .cdm-footer-main-grid {
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
    }

    @media (max-width: 768px) {
        .cdm-footer {
            padding-top: 45px;
        }
        .cdm-footer .container {
            padding: 0 16px;
        }
        .cdm-modal-card {
            grid-template-columns: 1fr;
        }
        .cdm-modal-left {
            display: none;
        }
        .cdm-footer-main-grid {
            grid-template-columns: 1fr;
            gap: 30px;
        }
        .cdm-form-row {
            grid-template-columns: 1fr;
        }
        .cdm-footer-bottom .container {
            flex-direction: column;
            text-align: center;
            gap: 12px;
        }
        .cdm-float-wa {
            width: 50px;
            height: 50px;
            bottom: 20px;
            right: 18px;
            font-size: 26px;
        }
    }
</style>

<footer class="cdm-footer">
    <!-- Top Contact Strip -->
    <div class="container cdm-footer-top-strip">
        <div class="cdm-footer-top-grid">
            <div class="cdm-info-box">
                <i class="fas fa-phone-alt"></i>
                <h4>Phone Number</h4>
                <a href="tel:<?= preg_replace('/[^0-9+]/', '', $contact_phone) ?>"><?= htmlspecialchars($contact_phone) ?></a><br>
                <?php if ($contact_whatsapp && $contact_whatsapp !== $contact_phone): ?>
                <a href="https://wa.me/<?= $clean_wa ?>" target="_blank" rel="noopener">WhatsApp: <?= htmlspecialchars($contact_whatsapp) ?></a>
                <?php endif; ?>
            </div>

            <div class="cdm-info-box">
                <i class="fas fa-envelope-open-text"></i>
                <h4>Email Address</h4>
                <a href="mailto:<?= htmlspecialchars($contact_email) ?>"><?= htmlspecialchars($contact_email) ?></a><br>
                <span style="font-size:12px; color:rgba(255,255,255,0.7);"><?= htmlspecialchars($contact_hours) ?></span>
            </div>

            <div class="cdm-info-box">
                <i class="fas fa-map-marker-alt"></i>
                <h4>Head Office</h4>
                <p><?= htmlspecialchars($contact_address) ?></p>
            </div>
        </div>
    </div>

    <!-- Main Footer Grid -->
    <div class="container cdm-footer-main-grid">
        <!-- Col 1: Brand & Ratings -->
        <div class="cdm-footer-col">
            <div style="margin-bottom: 18px;">
                <a href="<?= $base_url ?>/" class="cdm-logo" style="color: #ffffff;">
                    <?php if ($site_logo): ?>
                    <img src="<?= $base_url ?>/<?= htmlspecialchars(ltrim($site_logo, '/')) ?>" alt="<?= htmlspecialchars($site_name) ?>" style="max-height: 48px;">
                    <?php else: ?>
                    <span><?= htmlspecialchars(explode(' ', $site_name)[0]) ?></span><span style="color: #fb9a27;"><?= htmlspecialchars(substr($site_name, strlen(explode(' ', $site_name)[0]))) ?></span>
                    <?php endif; ?>
                </a>
            </div>
            <p class="cdm-footer-bio">
                Boost your business with <?= htmlspecialchars($site_name) ?>'s expert Digital Marketing Services, designed to drive massive organic traffic, increase qualified leads, and maximize your online ROI!
            </p>
            <div class="cdm-rating-badges">
                <div class="cdm-rating-badge">
                    <i class="fab fa-google" style="color: #4285f4; font-size: 16px;"></i>
                    <span><strong>4.9/5</strong> Rating</span>
                    <div>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                </div>
                <div class="cdm-rating-badge">
                    <i class="fab fa-facebook" style="color: #1877f2; font-size: 16px;"></i>
                    <span><strong>5.0/5</strong> Rating</span>
                </div>
            </div>
        </div>

        <!-- Col 2: Quick Links (Dynamic Services) -->
        <div class="cdm-footer-col">
            <h3>Quick Links</h3>
            <ul class="cdm-footer-links">
                <?php if (!empty($footer_services)): ?>
                    <?php foreach (array_slice($footer_services, 0, 5) as $fs): ?>
                    <li>
                        <a href="<?= $base_url ?>/services">
                            <i class="fas fa-chevron-right"></i>
                            <?= htmlspecialchars($fs['service_name']) ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li><a href="<?= $base_url ?>/services"><i class="fas fa-chevron-right"></i> Social Media Marketing</a></li>
                    <li><a href="<?= $base_url ?>/services"><i class="fas fa-chevron-right"></i> SEO Optimization</a></li>
                    <li><a href="<?= $base_url ?>/services"><i class="fas fa-chevron-right"></i> Google & Meta Ads</a></li>
                <?php endif; ?>
                <li><a href="<?= $base_url ?>/blog"><i class="fas fa-chevron-right"></i> Our Blog & Insights</a></li>
                <li><a href="<?= $base_url ?>/projects"><i class="fas fa-chevron-right"></i> Our Portfolio & Results</a></li>
                <li><a href="<?= $base_url ?>/why-choose-us"><i class="fas fa-chevron-right"></i> Why Choose Us</a></li>
            </ul>
        </div>

        <!-- Col 3: Regional & Legal -->
        <div class="cdm-footer-col">
            <h3>Solutions</h3>
            <ul class="cdm-footer-links">
                <li><a href="<?= $base_url ?>/services"><i class="fas fa-chevron-right"></i> Performance PPC Management</a></li>
                <li><a href="<?= $base_url ?>/services"><i class="fas fa-chevron-right"></i> Conversion Web & Apps</a></li>
                <li><a href="<?= $base_url ?>/contact"><i class="fas fa-chevron-right"></i> Schedule Consultation</a></li>
                <li><a href="<?= $base_url ?>/privacy-policy"><i class="fas fa-chevron-right"></i> Privacy Policy</a></li>
                <li><a href="<?= $base_url ?>/terms"><i class="fas fa-chevron-right"></i> Terms & Conditions</a></li>
            </ul>
        </div>

        <!-- Col 4: Get Free Audit Form -->
        <div class="cdm-footer-col">
            <h3>Get Free Audit</h3>
            <form class="cdm-footer-form" id="cdmFooterAuditForm" onsubmit="submitFooterAudit(event)">
                <div class="cdm-form-row">
                    <input type="text" name="name" placeholder="Your Name*" required>
                    <input type="email" name="email" placeholder="Your Email*" required>
                </div>
                <div class="cdm-form-row">
                    <input type="tel" name="phone" placeholder="Phone Number*" required>
                    <input type="text" name="website" placeholder="Website URL (optional)">
                </div>
                <button type="submit" class="cdm-btn-audit" id="btnAuditSubmit">
                    <span>Get Free Audit</span>
                    <i class="fas fa-arrow-right"></i>
                </button>
                <div id="footerAuditMsg" class="cdm-alert-box"></div>
            </form>
        </div>
    </div>

    <!-- Bottom Copyright -->
    <div class="cdm-footer-bottom">
        <div class="container">
            <p>
                &copy; <?= date('Y') ?> <strong><?= htmlspecialchars($site_name) ?></strong>. All rights reserved.
            </p>
            <ul class="cdm-footer-social">
                <li><a href="<?= htmlspecialchars($facebook_url) ?>" target="_blank" rel="noopener" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a></li>
                <li><a href="<?= htmlspecialchars($twitter_url) ?>" target="_blank" rel="noopener" aria-label="Twitter"><i class="fab fa-twitter"></i></a></li>
                <li><a href="<?= htmlspecialchars($instagram_url) ?>" target="_blank" rel="noopener" aria-label="Instagram"><i class="fab fa-instagram"></i></a></li>
                <li><a href="<?= htmlspecialchars($linkedin_url) ?>" target="_blank" rel="noopener" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a></li>
                <li><a href="<?= htmlspecialchars($maps_url) ?>" target="_blank" rel="noopener" aria-label="Maps"><i class="fas fa-map-marker-alt"></i></a></li>
            </ul>
        </div>
    </div>
</footer>

<!-- SINGLE FLOATING WHATSAPP BUTTON -->
<?php if (!empty($clean_wa)): ?>
<a href="https://wa.me/<?= htmlspecialchars($clean_wa) ?>?text=Hi%20<?= urlencode($site_name) ?>!%20I%E2%80%99m%20interested%20in%20your%20digital%20marketing%20services.%20Can%20you%20share%20more%20details%3F" 
   class="cdm-float-wa" 
   target="_blank" 
   rel="noopener noreferrer" 
   title="Chat on WhatsApp" 
   aria-label="Chat with our strategist on WhatsApp">
    <i class="fab fa-whatsapp"></i>
</a>
<?php endif; ?>

<!-- LEAD CAPTURE POPUP MODAL -->
<div class="cdm-modal-backdrop" id="cdmLeadModal">
    <div class="cdm-modal-card">
        <button type="button" class="cdm-modal-close" onclick="closeContactPopup()">&times;</button>
        
        <div class="cdm-modal-left">
            <span style="color: #fb9a27; font-weight: 700; text-transform: uppercase; font-size: 12px; letter-spacing: 1px; margin-bottom: 8px; display: block;">Pondicherry's #1 Agency</span>
            <h3>Scale Your Business With <?= htmlspecialchars($site_name) ?></h3>
            <p>Get a customized growth audit, competitor analysis, and high-converting traffic plan tailored to your industry.</p>
            <ul>
                <li><i class="fas fa-check-circle"></i> 100% Free Consultation</li>
                <li><i class="fas fa-check-circle"></i> Proven Lead Generation Funnels</li>
                <li><i class="fas fa-check-circle"></i> Top Google & Social Media Rankings</li>
            </ul>
        </div>

        <div class="cdm-modal-right">
            <h4>Request a Free Quote</h4>
            <p>Fill out the form and our growth consultant will call you within 2 business hours.</p>

            <form class="cdm-popup-form" id="cdmPopupLeadForm" onsubmit="submitPopupLead(event)">
                <div class="cdm-input-group">
                    <input type="text" name="name" id="popup_lead_name" placeholder="Your Name*" required autocomplete="name">
                </div>
                <div class="cdm-input-group">
                    <input type="tel" name="mobile" id="popup_lead_mobile" placeholder="Your Mobile Number*" maxlength="14" required autocomplete="tel">
                </div>
                <div class="cdm-input-group">
                    <input type="email" name="email" id="popup_lead_email" placeholder="Your Email Address" autocomplete="email">
                </div>
                <div class="cdm-input-group">
                    <select name="services" id="popup_lead_services" required>
                        <option value="" disabled selected>Select Desired Service*</option>
                        <?php if (!empty($footer_services)): ?>
                            <?php foreach ($footer_services as $ms): ?>
                            <option value="<?= htmlspecialchars($ms['service_name']) ?>"><?= htmlspecialchars($ms['service_name']) ?></option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="Social Media Marketing">Social Media Marketing</option>
                            <option value="SEO Optimization">SEO Optimization</option>
                            <option value="Google & Meta Ads">Google & Meta Ads (PPC)</option>
                            <option value="Content Marketing">Content Marketing</option>
                            <option value="Web & App Development">Web & App Development</option>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="cdm-input-group">
                    <textarea name="message" rows="3" placeholder="Brief about your goals or requirements..."></textarea>
                </div>
                <button type="submit" class="cdm-btn-submit-popup" id="btnPopupSubmit">
                    <span>Submit Request</span>
                    <i class="fas fa-paper-plane"></i>
                </button>
                <div id="popupLeadMsg" class="cdm-alert-box"></div>
            </form>
        </div>
    </div>
</div>

<!-- SCRIPTS FOR CHERRIDM INTERACTIVITY -->
<script>
// Mobile Drawer
function initMobileNavigationDrawer() {
    if (window.cdmDrawerInitDone) return;
    const toggle = document.getElementById('cdmMenuToggle');
    const drawer = document.getElementById('cdmMobileDrawer');
    const overlay = document.getElementById('cdmDrawerOverlay');
    const closeBtn = document.getElementById('cdmDrawerClose');
    const header = document.getElementById('cdmHeader');
    const servicesToggle = document.getElementById('mobileServicesToggle');
    const servicesSubmenu = document.getElementById('mobileServicesSubmenu');

    const openDrawer = () => {
        if (!drawer || !overlay) return;
        drawer.classList.add('active');
        overlay.classList.add('active');
        document.body.classList.add('cdm-drawer-open');
        document.body.style.overflow = 'hidden';
        if (toggle) toggle.setAttribute('aria-expanded', 'true');
        if (closeBtn) {
            setTimeout(() => closeBtn.focus(), 100);
        }
    };

    const closeDrawer = () => {
        if (!drawer || !overlay) return;
        drawer.classList.remove('active');
        overlay.classList.remove('active');
        document.body.classList.remove('cdm-drawer-open');
        document.body.style.overflow = '';
        if (toggle) {
            toggle.setAttribute('aria-expanded', 'false');
            toggle.focus();
        }
    };

    if (toggle) toggle.addEventListener('click', openDrawer);
    if (closeBtn) closeBtn.addEventListener('click', closeDrawer);
    if (overlay) {
        overlay.addEventListener('click', closeDrawer);
        overlay.addEventListener('touchmove', function(e) {
            e.preventDefault();
        }, { passive: false });
    }

    // Escape key closes mobile navigation drawer
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && drawer && drawer.classList.contains('active')) {
            closeDrawer();
        }
    });

    // Services Submenu Accordion Toggle
    if (servicesToggle && servicesSubmenu) {
        servicesToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const isOpen = servicesSubmenu.classList.toggle('open');
            servicesToggle.classList.toggle('open', isOpen);
            const arrow = servicesToggle.querySelector('.cdm-drawer-arrow');
            if (arrow) arrow.classList.toggle('open', isOpen);
            servicesToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });
    }

    // Auto close drawer when tapping any navigation link (except submenu toggle)
    if (drawer) {
        const navLinks = drawer.querySelectorAll('.cdm-drawer-nav a, .cdm-drawer-brand');
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                closeDrawer();
            });
        });
    }

    // Header scroll shadow effect
    const updateHeaderScroll = () => {
        if (!header) return;
        if (window.scrollY > 20) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    };
    window.addEventListener('scroll', updateHeaderScroll, { passive: true });
    updateHeaderScroll();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initMobileNavigationDrawer);
} else {
    initMobileNavigationDrawer();
}

    // Auto-open modal after 6 seconds
    if (!sessionStorage.getItem('cdm_popup_seen')) {
        setTimeout(() => {
            openContactPopup();
            sessionStorage.setItem('cdm_popup_seen', 'true');
        }, 6000);
    }

// Modal open & close with context preservation
function openContactPopup(serviceName = '') {
    const modal = document.getElementById('cdmLeadModal');
    if (modal) {
        if (serviceName && typeof serviceName === 'string') {
            const select = document.getElementById('popup_lead_services');
            if (select) {
                const term = serviceName.toLowerCase().trim();
                for (let i = 0; i < select.options.length; i++) {
                    const optText = select.options[i].text.toLowerCase();
                    const optVal = select.options[i].value.toLowerCase();
                    if (optText.includes(term) || term.includes(optText) || optVal.includes(term)) {
                        select.selectedIndex = i;
                        break;
                    }
                }
            }
            const modalTitle = document.querySelector('.cdm-modal-right h4');
            if (modalTitle) {
                if (!modalTitle.dataset.defaultTitle) {
                    modalTitle.dataset.defaultTitle = modalTitle.textContent;
                }
                modalTitle.textContent = 'Free Quote for ' + serviceName;
            }
        }
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
}

function closeContactPopup() {
    const modal = document.getElementById('cdmLeadModal');
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = '';
        const modalTitle = document.querySelector('.cdm-modal-right h4');
        if (modalTitle && modalTitle.dataset.defaultTitle) {
            modalTitle.textContent = modalTitle.dataset.defaultTitle;
        }
    }
}

// Close modal when clicking outside backdrop
document.getElementById('cdmLeadModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeContactPopup();
    }
});

// AJAX Handler for Popup Lead Form
function submitPopupLead(e) {
    e.preventDefault();
    const form = e.target;
    const btn = document.getElementById('btnPopupSubmit');
    const msg = document.getElementById('popupLeadMsg');

    // Optional email validation: validate format only if provided
    const emailField = form.querySelector('input[name="email"]');
    if (emailField && emailField.value.trim() !== '') {
        const emailVal = emailField.value.trim();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(emailVal)) {
            msg.className = 'cdm-alert-box error';
            msg.textContent = 'Please enter a valid email address.';
            msg.style.display = 'block';
            emailField.focus();
            return;
        }
    }

    const formData = new FormData(form);

    btn.disabled = true;
    btn.innerHTML = '<span>Submitting...</span> <i class="fas fa-spinner fa-spin"></i>';

    fetch('<?= $base_url ?>/contact-insert.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.status === 'success') {
            msg.className = 'cdm-alert-box success';
            msg.textContent = data.message;
            msg.style.display = 'block';
            form.reset();
            setTimeout(() => {
                closeContactPopup();
                msg.style.display = 'none';
            }, 3000);
        } else {
            msg.className = 'cdm-alert-box error';
            msg.textContent = data.message || 'Submission failed. Please try again.';
            msg.style.display = 'block';
        }
    })
    .catch(err => {
        msg.className = 'cdm-alert-box error';
        msg.textContent = 'Network error. Please try again.';
        msg.style.display = 'block';
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<span>Submit Request</span> <i class="fas fa-paper-plane"></i>';
    });
}

// AJAX Handler for Footer Audit Form
function submitFooterAudit(e) {
    e.preventDefault();
    const form = e.target;
    const btn = document.getElementById('btnAuditSubmit');
    const msg = document.getElementById('footerAuditMsg');
    const formData = new FormData(form);

    btn.disabled = true;
    btn.innerHTML = '<span>Submitting...</span> <i class="fas fa-spinner fa-spin"></i>';

    fetch('<?= $base_url ?>/footer-form.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.status === 'success') {
            msg.className = 'cdm-alert-box success';
            msg.textContent = data.message;
            form.reset();
        } else {
            msg.className = 'cdm-alert-box error';
            msg.textContent = data.message || 'Submission failed. Please try again.';
        }
    })
    .catch(err => {
        msg.className = 'cdm-alert-box error';
        msg.textContent = 'Network error. Please try again.';
    })
    .finally(() => {
    });
}
</script>


<!-- CherriDM Animation & Interaction Controller -->
<script src="<?= $base_url ?>/assets/js/cherridm-animations.js?v=2.0" defer></script>
</div><!-- /.cdm-site-wrapper -->
</body>
</html>