<?php 
/**
 * Privacy Policy View - CherriDM Theme
 * 100% Dynamic with Database Integration
 */
$policy_content = $settings['privacy_policy'] ?? '';
$site_name = !empty($settings['site_name']) ? $settings['site_name'] : ($site_name ?? 'Cloud Aspire');
$contact_email = !empty($settings['contact_email']) ? $settings['contact_email'] : ($contact_email ?? 'cloudaspire2025@gmail.com');
$contact_phone = !empty($settings['contact_phone']) ? $settings['contact_phone'] : ($contact_phone ?? '8098830937');

if (empty($policy_content)) {
    $policy_content = <<<EOT
<div class="cdm-legal-clause">
    <div class="cdm-clause-badge">01</div>
    <div class="cdm-clause-body">
        <h2>1. Information We Collect</h2>
        <p>At {$site_name}, we collect information you provide directly to us, such as your name, email address, phone number, and business details when you fill out contact forms, request proposals, subscribe to updates, or engage our services.</p>
    </div>
</div>

<div class="cdm-legal-clause">
    <div class="cdm-clause-badge">02</div>
    <div class="cdm-clause-body">
        <h2>2. How We Use Your Information</h2>
        <p>We use the data we collect for purposeful and transparent business operations:</p>
        <ul class="cdm-legal-bullets">
            <li>To deliver, execute, and monitor our digital marketing and web development services</li>
            <li>To communicate critical project milestones, campaign performance, and strategy updates</li>
            <li>To enhance website responsiveness, security, and customer experience</li>
            <li>To adhere strictly to applicable statutory and legal frameworks</li>
        </ul>
    </div>
</div>

<div class="cdm-legal-clause">
    <div class="cdm-clause-badge">03</div>
    <div class="cdm-clause-body">
        <h2>3. Information Sharing &amp; Third Parties</h2>
        <p>We strictly do not sell, trade, or monetize your personal or commercial data. We only share essential metadata with vetted infrastructure providers (such as hosting, email delivery, and analytics) who are bound by non-disclosure agreements.</p>
    </div>
</div>

<div class="cdm-legal-clause">
    <div class="cdm-clause-badge">04</div>
    <div class="cdm-clause-body">
        <h2>4. Enterprise Data Security</h2>
        <p>We employ enterprise-grade security protocols, encryption in transit (SSL/TLS), access controls, and regular vulnerability assessments to safeguard your data against unauthorized access or breaches.</p>
    </div>
</div>

<div class="cdm-legal-clause">
    <div class="cdm-clause-badge">05</div>
    <div class="cdm-clause-body">
        <h2>5. Cookies &amp; Tracking Technologies</h2>
        <p>Our website utilizes necessary cookies and privacy-respecting analytics to optimize user navigation and performance. You may configure cookie preferences directly within your browser settings.</p>
    </div>
</div>

<div class="cdm-legal-clause">
    <div class="cdm-clause-badge">06</div>
    <div class="cdm-clause-body">
        <h2>6. Your Data Rights &amp; Access</h2>
        <p>You have the right to access, verify, export, or request deletion of your personal records. For inquiries, please reach out to our privacy compliance team at <a href="mailto:{$contact_email}" class="cdm-legal-link">{$contact_email}</a>.</p>
    </div>
</div>

<div class="cdm-legal-clause">
    <div class="cdm-clause-badge">07</div>
    <div class="cdm-clause-body">
        <h2>7. Policy Updates &amp; Notifications</h2>
        <p>We may update this Privacy Policy periodically to reflect evolving legal standards. Changes will be posted on this page with an updated timestamp.</p>
    </div>
</div>
EOT;
}

include __DIR__ . '/../../../includes/header.php'; 
require_once __DIR__ . '/../../../includes/components/page_hero.php';
?>

<style>
    /* Switcher Pills */
    .cdm-legal-switcher {
        display: flex;
        justify-content: center;
        gap: 12px;
        margin-bottom: 35px;
        flex-wrap: wrap;
    }
    .cdm-legal-pill {
        padding: 10px 22px;
        border-radius: 30px;
        font-size: 0.92rem;
        font-weight: 600;
        text-decoration: none;
        color: #64748b;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        transition: all 0.25s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .cdm-legal-pill:hover {
        transform: translateY(-2px);
        color: #03228f;
        border-color: #fb9a27;
    }
    .cdm-legal-pill.active {
        background: #03228f;
        color: #ffffff;
        border-color: #03228f;
        box-shadow: 0 4px 16px rgba(3, 34, 143, 0.25);
    }

    /* Card Wrapper */
    .cdm-legal-card {
        max-width: 960px;
        margin: 0 auto;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 50px 45px;
        box-shadow: 0 8px 30px rgba(3, 34, 143, 0.06);
        position: relative;
        overflow: hidden;
    }
    .cdm-legal-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #03228f, #fb9a27, #03228f);
    }

    /* Clauses */
    .cdm-legal-clause {
        display: flex;
        gap: 20px;
        padding: 24px 0;
        border-bottom: 1px solid #f1f5f9;
    }
    .cdm-legal-clause:last-of-type {
        border-bottom: none;
    }
    .cdm-clause-badge {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: rgba(3, 34, 143, 0.08);
        color: #03228f;
        font-weight: 800;
        font-size: 0.95rem;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border: 1px solid rgba(3, 34, 143, 0.15);
        transition: all 0.3s ease;
    }
    .cdm-legal-clause:hover .cdm-clause-badge {
        background: #fb9a27;
        color: #ffffff;
        border-color: #fb9a27;
        transform: scale(1.08);
    }
    .cdm-clause-body {
        flex-grow: 1;
    }
    .cdm-clause-body h2 {
        font-size: 1.28rem;
        font-weight: 700;
        color: #03228f;
        margin: 0 0 8px 0;
    }
    .cdm-clause-body p {
        color: #475569;
        font-size: 0.98rem;
        margin: 0 0 8px 0;
        line-height: 1.8;
    }
    .cdm-legal-bullets {
        margin: 8px 0 0 0;
        padding-left: 20px;
        color: #475569;
    }
    .cdm-legal-bullets li {
        margin-bottom: 6px;
        font-size: 0.96rem;
    }
    .cdm-legal-link {
        color: #03228f;
        font-weight: 600;
        text-decoration: underline;
    }
    .cdm-legal-link:hover {
        color: #fb9a27;
    }

    .cdm-legal-support-card {
        margin-top: 35px;
        padding: 24px 28px;
        border-radius: 14px;
        background: #f8faff;
        border: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        flex-wrap: wrap;
    }
    .cdm-legal-support-card h4 {
        margin: 0 0 4px 0;
        font-size: 1.05rem;
        font-weight: 700;
        color: #03228f;
    }
    .cdm-legal-support-card p {
        margin: 0;
        color: #64748b;
        font-size: 0.9rem;
    }

    @media (max-width: 768px) {
        .cdm-legal-card {
            padding: 30px 20px;
        }
        .cdm-legal-clause {
            flex-direction: column;
            gap: 12px;
        }
    }
</style>

<?php
render_page_hero([
    'title' => 'Privacy',
    'highlight' => 'Policy',
    'subtitle' => 'Our commitment to protecting your personal data, confidentiality, and digital privacy',
    'breadcrumb_active' => 'Privacy Policy',
    'base_url' => $base_url
]);
?>

<section style="padding: 60px 0; background-color: #f8faff;">
    <div class="container">
        
        <!-- Navigation Switcher Pills -->
        <div class="cdm-legal-switcher">
            <a href="<?= $base_url ?>/terms" class="cdm-legal-pill">
                <i class="fas fa-file-contract"></i> Terms &amp; Conditions
            </a>
            <a href="<?= $base_url ?>/privacy-policy" class="cdm-legal-pill active">
                <i class="fas fa-shield-alt"></i> Privacy Policy
            </a>
            <a href="<?= $base_url ?>/refund-policy" class="cdm-legal-pill">
                <i class="fas fa-undo"></i> Refund Policy
            </a>
        </div>

        <div class="cdm-legal-card">
            <div style="margin-bottom: 25px; padding-bottom: 15px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                <span style="font-size: 0.85rem; color: #64748b; font-weight: 600;">
                    <i class="fas fa-calendar-check" style="color: #fb9a27; margin-right: 5px;"></i> Effective Date: <?= date('F j, Y'); ?>
                </span>
                <span style="font-size: 0.85rem; color: #03228f; font-weight: 700;">
                    <i class="fas fa-shield-halved" style="color: #10b981; margin-right: 4px;"></i> Enterprise Verified
                </span>
            </div>

            <div>
                <?= $policy_content ?>
            </div>

            <div class="cdm-legal-support-card">
                <div>
                    <h4>Data Privacy &amp; GDPR Inquiries</h4>
                    <p>Have questions or wish to request data verification? Contact our privacy team directly.</p>
                </div>
                <a href="mailto:<?= htmlspecialchars($contact_email) ?>" class="cdm-btn-quote" style="padding: 10px 22px; font-size: 0.9rem;">
                    <i class="fas fa-envelope"></i> <span>Contact Privacy Desk</span>
                </a>
            </div>
        </div>

    </div>
</section>

<!-- BOTTOM CTA BANNER -->
<section style="background: linear-gradient(135deg, #02144a 0%, #03228f 100%); padding: 70px 20px; color: #ffffff; text-align: center;">
    <div style="max-width: 800px; margin: 0 auto;">
        <span style="color: #fb9a27; font-weight: 700; text-transform: uppercase; font-size: 13px; letter-spacing: 1px; display: block; margin-bottom: 8px;">
            // Trusted &amp; Transparent //
        </span>
        <h3 style="font-size: clamp(1.8rem, 3.5vw, 2.4rem); font-weight: 800; margin-bottom: 12px;">Partner with an Ethical Growth Agency</h3>
        <p style="font-size: 16px; color: rgba(255,255,255,0.85); margin-bottom: 28px;">
            We operate with complete clarity, robust security, and data transparency in every campaign we launch.
        </p>
        <button type="button" class="cdm-btn-quote" style="display: inline-flex;" onclick="openContactPopup()">
            <span>Speak With Our Team</span>
            <i class="fas fa-arrow-right"></i>
        </button>
    </div>
</section>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>
