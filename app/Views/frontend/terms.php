<?php 
/**
 * Terms & Conditions View - CherriDM Theme
 * 100% Dynamic with Database Integration
 */
$policy_content = $settings['terms_conditions'] ?? '';
$site_name = !empty($settings['site_name']) ? $settings['site_name'] : ($site_name ?? 'Cloud Aspire');
$contact_email = !empty($settings['contact_email']) ? $settings['contact_email'] : ($contact_email ?? 'cloudaspire2025@gmail.com');

if (empty($policy_content)) {
    $policy_content = <<<EOT
<div class="cdm-legal-clause">
    <div class="cdm-clause-badge">01</div>
    <div class="cdm-clause-body">
        <h2>1. Acceptance of Terms</h2>
        <p>By browsing, accessing, or engaging services through {$site_name}, you agree to adhere to these Terms &amp; Conditions, all applicable laws, and relevant industry regulations.</p>
    </div>
</div>

<div class="cdm-legal-clause">
    <div class="cdm-clause-badge">02</div>
    <div class="cdm-clause-body">
        <h2>2. Service Engagement &amp; Deliverables</h2>
        <p>All digital marketing, SEO, paid advertising, and web development deliverables are executed according to predefined project proposals and milestone agreements agreed upon between the client and {$site_name}.</p>
    </div>
</div>

<div class="cdm-legal-clause">
    <div class="cdm-clause-badge">03</div>
    <div class="cdm-clause-body">
        <h2>3. Intellectual Property Rights</h2>
        <p>Upon full settlement of agreed project dues, all bespoke creative assets, website source codes, and campaign configurations developed specifically for the client become the proprietary property of the client.</p>
    </div>
</div>

<div class="cdm-legal-clause">
    <div class="cdm-clause-badge">04</div>
    <div class="cdm-clause-body">
        <h2>4. Client Responsibilities</h2>
        <p>Clients are expected to provide timely feedback, necessary brand assets, and required account access to maintain agreed project schedules and campaign launch dates.</p>
    </div>
</div>

<div class="cdm-legal-clause">
    <div class="cdm-clause-badge">05</div>
    <div class="cdm-clause-body">
        <h2>5. Limitation of Liability</h2>
        <p>While {$site_name} employs best-in-class industry methodologies, we do not guarantee third-party platform algorithm behaviors (e.g., search engine indexing timelines or ad platform policy updates) beyond direct control.</p>
    </div>
</div>

<div class="cdm-legal-clause">
    <div class="cdm-clause-badge">06</div>
    <div class="cdm-clause-body">
        <h2>6. Inquiries &amp; Legal Notices</h2>
        <p>For questions regarding our terms of service, contact our legal desk at <a href="mailto:{$contact_email}" class="cdm-legal-link">{$contact_email}</a>.</p>
    </div>
</div>
EOT;
}

include __DIR__ . '/../../../includes/header.php'; 
?>

<!-- CHERRIDM INNER PAGE HERO -->
<style>
    .cdm-page-hero {
        background: linear-gradient(135deg, rgba(2, 20, 74, 0.95) 0%, rgba(3, 34, 143, 0.92) 100%),
                    url('https://images.unsplash.com/photo-1450133064473-71024230f91b?auto=format&fit=crop&w=1600&q=80') center/cover;
        padding: 90px 20px 80px;
        text-align: center;
        color: #ffffff;
        position: relative;
    }
    .cdm-page-hero h1 {
        font-size: clamp(2.4rem, 4.5vw, 3.6rem);
        font-weight: 800;
        color: #ffffff;
        margin-bottom: 14px;
        letter-spacing: -0.5px;
    }
    .cdm-page-hero h1 span.stroke {
        color: #fb9a27;
    }
    .cdm-page-hero p {
        font-size: 16px;
        color: rgba(255, 255, 255, 0.9);
        max-width: 680px;
        margin: 0 auto 24px;
        line-height: 1.6;
    }
    .cdm-breadcrumb-capsule {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(255, 255, 255, 0.12);
        padding: 7px 22px;
        border-radius: 30px;
        font-size: 13px;
        font-weight: 600;
    }
    .cdm-breadcrumb-capsule a {
        color: #ffffff;
        transition: color 0.2s ease;
        text-decoration: none;
    }
    .cdm-breadcrumb-capsule a:hover {
        color: #fb9a27;
    }
    .cdm-breadcrumb-capsule span.sep {
        color: rgba(255, 255, 255, 0.5);
    }
    .cdm-breadcrumb-capsule span.active {
        color: #fb9a27;
    }

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

<section class="cdm-page-hero">
    <div class="container">
        <h1>Terms &amp; <span class="stroke">Conditions</span></h1>
        <p>Standard agreement, service deliverables, and guidelines for client engagements</p>
        <div class="cdm-breadcrumb-capsule">
            <a href="<?= $base_url ?>/"><i class="fas fa-home"></i> Home</a>
            <span class="sep">/</span>
            <span class="active">Terms &amp; Conditions</span>
        </div>
    </div>
</section>

<section style="padding: 60px 0; background-color: #f8faff;">
    <div class="container">
        
        <!-- Navigation Switcher Pills -->
        <div class="cdm-legal-switcher">
            <a href="<?= $base_url ?>/terms" class="cdm-legal-pill active">
                <i class="fas fa-file-contract"></i> Terms &amp; Conditions
            </a>
            <a href="<?= $base_url ?>/privacy-policy" class="cdm-legal-pill">
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
                    <i class="fas fa-file-signature" style="color: #10b981; margin-right: 4px;"></i> Legally Binding
                </span>
            </div>

            <div>
                <?= $policy_content ?>
            </div>

            <div class="cdm-legal-support-card">
                <div>
                    <h4>Questions Regarding Our Terms?</h4>
                    <p>Our contracts and legal advisory team is available to assist with custom enterprise service agreements.</p>
                </div>
                <a href="mailto:<?= htmlspecialchars($contact_email) ?>" class="cdm-btn-quote" style="padding: 10px 22px; font-size: 0.9rem;">
                    <i class="fas fa-envelope"></i> <span>Contact Legal Desk</span>
                </a>
            </div>
        </div>

    </div>
</section>

<!-- BOTTOM CTA BANNER -->
<section style="background: linear-gradient(135deg, #02144a 0%, #03228f 100%); padding: 70px 20px; color: #ffffff; text-align: center;">
    <div style="max-width: 800px; margin: 0 auto;">
        <span style="color: #fb9a27; font-weight: 700; text-transform: uppercase; font-size: 13px; letter-spacing: 1px; display: block; margin-bottom: 8px;">
            // Transparent Contracts //
        </span>
        <h3 style="font-size: clamp(1.8rem, 3.5vw, 2.4rem); font-weight: 800; margin-bottom: 12px;">Need a Tailored Enterprise Contract?</h3>
        <p style="font-size: 16px; color: rgba(255,255,255,0.85); margin-bottom: 28px;">
            We accommodate custom NDAs, dedicated SLAs, and enterprise master service agreements.
        </p>
        <button type="button" class="cdm-btn-quote" style="display: inline-flex;" onclick="openContactPopup()">
            <span>Request Custom Proposal</span>
            <i class="fas fa-arrow-right"></i>
        </button>
    </div>
</section>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>
