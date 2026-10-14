<?php
/**
 * Individual Service Detail View - CherriDM Aesthetic
 * 100% Dynamic - Controlled directly via Admin Panel & Live MySQL Database
 */

$site_name = !empty($settings['site_name']) ? $settings['site_name'] : 'Cloud Aspire';
$contact_phone = !empty($settings['contact_phone']) ? $settings['contact_phone'] : '8098830937';
$contact_email = !empty($settings['contact_email']) ? $settings['contact_email'] : 'cloudaspire2025@gmail.com';
$contact_whatsapp = !empty($settings['contact_whatsapp']) ? $settings['contact_whatsapp'] : '8098830937';

// Service attributes
$s_id = (int)($service['id'] ?? 0);
$s_name = $service['service_name'] ?? 'Digital Marketing Service';
$s_slug = $service['slug'] ?? '';
$s_icon = $service['icon'] ?? 'fa-chart-line';
if (!str_starts_with($s_icon, 'fa-') && !str_starts_with($s_icon, 'fas ') && !str_starts_with($s_icon, 'fab ')) {
    $s_icon = 'fa-' . ltrim($s_icon, 'fa-');
}
$s_short = $service['short_description'] ?? '';
$s_full = $service['full_description'] ?? $s_short;
$s_image = $service['image'] ?? '';

// Fallback stock image based on slug
$stock_service_images = [
    'seo-optimization' => 'https://images.unsplash.com/photo-1571786256017-aee7a0c009b6?auto=format&fit=crop&w=1200&q=80',
    'google-meta-ads' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=1200&q=80',
    'social-media-marketing' => 'https://images.unsplash.com/photo-1611162617474-5b21e879e113?auto=format&fit=crop&w=1200&q=80',
    'website-app-development' => 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?auto=format&fit=crop&w=1200&q=80',
    'brand-identity-design' => 'https://images.unsplash.com/photo-1542744094-3a31f272c490?auto=format&fit=crop&w=1200&q=80',
    'content-marketing' => 'https://images.unsplash.com/photo-1507238691740-187a5b1d37b8?auto=format&fit=crop&w=1200&q=80',
];

if (empty($s_image)) {
    $s_image = $stock_service_images[$s_slug] ?? 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=1200&q=80';
} elseif (!str_starts_with($s_image, 'http')) {
    $s_image = $base_url . '/' . ltrim($s_image, '/');
}

// Split service name into words for styled hero header
$name_parts = explode(' ', $s_name);
$first_word = $name_parts[0] ?? '';
$remaining_words = count($name_parts) > 1 ? implode(' ', array_slice($name_parts, 1)) : '';

include __DIR__ . '/../../../includes/header.php';
?>

<!-- SENIOR LEVEL SCHEMA.ORG STRUCTURED DATA -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Service",
  "name": <?= json_encode($s_name) ?>,
  "serviceType": <?= json_encode($s_name) ?>,
  "provider": {
    "@type": "LocalBusiness",
    "name": <?= json_encode($site_name) ?>,
    "telephone": <?= json_encode($contact_phone) ?>,
    "email": <?= json_encode($contact_email) ?>
  },
  "description": <?= json_encode($pageDesc) ?>,
  "url": <?= json_encode($canonicalUrl) ?>
}
</script>

<?php if (!empty($service_faqs)): ?>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [
    <?php 
    $faq_json_items = [];
    foreach ($service_faqs as $sfq) {
        if (!empty($sfq['question']) && !empty($sfq['answer'])) {
            $faq_json_items[] = json_encode([
                "@type" => "Question",
                "name" => $sfq['question'],
                "acceptedAnswer" => [
                    "@type" => "Answer",
                    "text" => $sfq['answer']
                ]
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }
    }
    echo implode(",\n    ", $faq_json_items);
    ?>
  ]
}
</script>
<?php endif; ?>

<!-- SERVICE DETAIL SPECIFIC STYLES -->
<style>
    /* Hero Banner */
    .cdm-svc-hero {
        background: linear-gradient(135deg, rgba(2, 20, 74, 0.95) 0%, rgba(3, 34, 143, 0.92) 100%),
                    url('<?= htmlspecialchars($s_image) ?>') center/cover;
        padding: 95px 20px 85px;
        text-align: center;
        color: #ffffff;
        position: relative;
    }
    .cdm-svc-hero-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(251, 154, 39, 0.2);
        border: 1px solid rgba(251, 154, 39, 0.45);
        color: #fb9a27;
        font-size: 13px;
        font-weight: 700;
        padding: 6px 18px;
        border-radius: 30px;
        margin-bottom: 18px;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }
    .cdm-svc-hero h1 {
        font-size: clamp(2.3rem, 4.5vw, 3.5rem);
        font-weight: 800;
        color: #ffffff;
        margin-bottom: 16px;
        letter-spacing: -0.5px;
        line-height: 1.2;
    }
    .cdm-svc-hero h1 span.stroke {
        color: #fb9a27;
    }
    .cdm-svc-hero p {
        font-size: 17px;
        color: rgba(255, 255, 255, 0.9);
        max-width: 760px;
        margin: 0 auto 26px;
        line-height: 1.65;
    }
    .cdm-svc-breadcrumbs {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(255, 255, 255, 0.12);
        padding: 8px 24px;
        border-radius: 30px;
        font-size: 13px;
        font-weight: 600;
    }
    .cdm-svc-breadcrumbs a {
        color: #ffffff;
        transition: color 0.2s ease;
    }
    .cdm-svc-breadcrumbs a:hover {
        color: #fb9a27;
    }
    .cdm-svc-breadcrumbs span.sep {
        color: rgba(255, 255, 255, 0.45);
    }
    .cdm-svc-breadcrumbs span.active {
        color: #fb9a27;
    }

    /* Main Content Layout */
    .cdm-svc-layout {
        max-width: 1240px;
        margin: 0 auto;
        padding: 80px 20px 100px;
        display: grid;
        grid-template-columns: 1fr 360px;
        gap: 45px;
        align-items: start;
    }

    /* Left Column Details */
    .cdm-svc-main {
        min-width: 0;
    }
    .cdm-svc-featured-card {
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 15px 35px rgba(3, 34, 143, 0.09);
        margin-bottom: 40px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
    }
    .cdm-svc-featured-card img {
        width: 100%;
        height: 420px;
        object-fit: cover;
        display: block;
    }
    .cdm-svc-featured-content {
        padding: 35px 35px 30px;
    }
    .cdm-svc-featured-content h2 {
        font-size: clamp(1.8rem, 3vw, 2.3rem);
        font-weight: 800;
        color: #03228f;
        margin-bottom: 18px;
        line-height: 1.3;
    }
    .cdm-svc-featured-content .lead-text {
        font-size: 16px;
        color: #334155;
        line-height: 1.8;
        margin-bottom: 24px;
        text-align: justify;
    }

    /* Section Subheadings */
    .cdm-svc-heading {
        margin-bottom: 28px;
    }
    .cdm-svc-heading span.tagline {
        color: #fb9a27;
        font-weight: 700;
        font-size: 13px;
        letter-spacing: 1px;
        text-transform: uppercase;
        display: block;
        margin-bottom: 6px;
    }
    .cdm-svc-heading h3 {
        font-size: clamp(1.6rem, 2.8vw, 2.1rem);
        font-weight: 800;
        color: #03228f;
        line-height: 1.3;
    }

    /* Features / Deliverables Grid */
    .cdm-features-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 22px;
        margin-bottom: 60px;
    }
    .cdm-feat-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 26px 24px;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    .cdm-feat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        background: #03228f;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    .cdm-feat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 15px 30px rgba(3, 34, 143, 0.1);
        border-color: #cbd5e1;
    }
    .cdm-feat-card:hover::before {
        opacity: 1;
    }
    .cdm-feat-icon {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        background: rgba(3, 34, 143, 0.08);
        color: #03228f;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        margin-bottom: 16px;
        transition: all 0.3s ease;
    }
    .cdm-feat-card:hover .cdm-feat-icon {
        background: #fb9a27;
        color: #ffffff;
    }
    .cdm-feat-card h4 {
        font-size: 17px;
        font-weight: 700;
        color: #03228f;
        margin-bottom: 10px;
        line-height: 1.4;
    }
    .cdm-feat-card p {
        font-size: 14px;
        color: #64748b;
        line-height: 1.6;
        margin: 0;
    }

    /* Process Steps (4-Step Workflow) */
    .cdm-process-section {
        background: #f8faff;
        border-radius: 16px;
        padding: 40px 35px;
        margin-bottom: 60px;
        border: 1px solid #e2e8f0;
    }
    .cdm-process-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 25px;
        margin-top: 25px;
    }
    .cdm-process-card {
        background: #ffffff;
        border-radius: 12px;
        padding: 24px;
        border: 1px solid #e2e8f0;
        position: relative;
    }
    .cdm-step-badge {
        display: inline-block;
        font-size: 13px;
        font-weight: 800;
        color: #fb9a27;
        background: rgba(251, 154, 39, 0.12);
        padding: 4px 12px;
        border-radius: 6px;
        margin-bottom: 12px;
        letter-spacing: 0.5px;
    }
    .cdm-process-card h4 {
        font-size: 17px;
        font-weight: 700;
        color: #03228f;
        margin-bottom: 10px;
    }
    .cdm-process-card p {
        font-size: 13.5px;
        color: #64748b;
        line-height: 1.6;
        margin: 0;
    }

    /* Benefits List Grid */
    .cdm-benefits-box {
        background: #ffffff;
        border-radius: 14px;
        border: 1px solid #e2e8f0;
        padding: 35px;
        margin-bottom: 60px;
        box-shadow: 0 10px 25px rgba(3, 34, 143, 0.05);
    }
    .cdm-benefits-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 16px 24px;
        margin-top: 20px;
    }
    .cdm-benefit-item {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        font-size: 15px;
        color: #1e293b;
        font-weight: 600;
        line-height: 1.5;
    }
    .cdm-benefit-item i {
        color: #fb9a27;
        font-size: 17px;
        margin-top: 3px;
        flex-shrink: 0;
    }

    /* Service FAQs Accordion */
    .cdm-faqs-wrap {
        margin-bottom: 60px;
    }
    .cdm-faq-item {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        margin-bottom: 14px;
        overflow: hidden;
        transition: all 0.3s ease;
    }
    .cdm-faq-item.active {
        border-color: #03228f;
        box-shadow: 0 6px 18px rgba(3, 34, 143, 0.08);
    }
    .cdm-faq-trigger {
        width: 100%;
        background: none;
        border: none;
        padding: 20px 24px;
        text-align: left;
        font-size: 16px;
        font-weight: 700;
        color: #03228f;
        display: flex;
        justify-content: space-between;
        align-items: center;
        cursor: pointer;
        font-family: inherit;
    }
    .cdm-faq-trigger i {
        color: #fb9a27;
        font-size: 14px;
        transition: transform 0.3s ease;
    }
    .cdm-faq-item.active .cdm-faq-trigger i {
        transform: rotate(180deg);
    }
    .cdm-faq-content {
        display: none;
        padding: 0 24px 22px;
        font-size: 14.5px;
        color: #475569;
        line-height: 1.7;
    }
    .cdm-faq-item.active .cdm-faq-content {
        display: block;
    }

    /* Bottom High-Converting Callout */
    .cdm-svc-callout {
        background: linear-gradient(135deg, #02144a 0%, #03228f 100%);
        border-radius: 14px;
        padding: 45px 35px;
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 30px;
    }
    .cdm-svc-callout h3 {
        font-size: clamp(1.4rem, 2.5vw, 1.8rem);
        font-weight: 800;
        margin-bottom: 8px;
    }
    .cdm-svc-callout p {
        font-size: 14.5px;
        color: rgba(255, 255, 255, 0.85);
        margin: 0;
    }

    /* Right Sidebar (Sticky) */
    .cdm-svc-sidebar {
        position: sticky;
        top: 95px;
        display: flex;
        flex-direction: column;
        gap: 30px;
    }

    /* Sidebar Form Card */
    .cdm-side-card {
        background: #ffffff;
        border-radius: 14px;
        border: 1px solid #e2e8f0;
        padding: 30px 24px;
        box-shadow: 0 12px 30px rgba(3, 34, 143, 0.07);
    }
    .cdm-side-card h4 {
        font-size: 19px;
        font-weight: 800;
        color: #03228f;
        margin-bottom: 6px;
    }
    .cdm-side-card p.subtitle {
        font-size: 13px;
        color: #64748b;
        margin-bottom: 22px;
    }
    .cdm-form-group {
        margin-bottom: 16px;
    }
    .cdm-form-group label {
        display: block;
        font-size: 12.5px;
        font-weight: 700;
        color: #334155;
        margin-bottom: 6px;
    }
    .cdm-form-control {
        width: 100%;
        padding: 11px 14px;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        font-size: 13.5px;
        font-family: inherit;
        color: #1e293b;
        outline: none;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .cdm-form-control:focus {
        border-color: #03228f;
        box-shadow: 0 0 0 3px rgba(3, 34, 143, 0.1);
    }
    textarea.cdm-form-control {
        resize: vertical;
        min-height: 85px;
    }
    .cdm-side-submit {
        width: 100%;
        background: #03228f;
        color: #ffffff;
        font-weight: 700;
        font-size: 14px;
        padding: 13px;
        border-radius: 6px;
        border: 2px solid #03228f;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: all 0.3s ease;
    }
    .cdm-side-submit:hover {
        background: #fb9a27;
        border-color: #fb9a27;
        color: #ffffff;
        transform: translateY(-2px);
    }

    /* All Services Navigation Menu in Sidebar */
    .cdm-svc-nav-list {
        list-style: none;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .cdm-svc-nav-item a {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 13px 18px;
        background: #f8faff;
        border-radius: 8px;
        color: #1e293b;
        font-size: 14px;
        font-weight: 700;
        border: 1px solid #e2e8f0;
        transition: all 0.25s ease;
    }
    .cdm-svc-nav-item a i.icon {
        color: #03228f;
        margin-right: 12px;
        width: 18px;
        text-align: center;
    }
    .cdm-svc-nav-item a i.arrow {
        color: #94a3b8;
        font-size: 12px;
        transition: transform 0.2s ease;
    }
    .cdm-svc-nav-item a:hover {
        background: #ffffff;
        border-color: #03228f;
        color: #03228f;
        transform: translateX(4px);
    }
    .cdm-svc-nav-item a:hover i.arrow {
        color: #fb9a27;
        transform: translateX(3px);
    }
    .cdm-svc-nav-item.active a {
        background: #03228f;
        border-color: #03228f;
        color: #ffffff;
    }
    .cdm-svc-nav-item.active a i.icon {
        color: #fb9a27;
    }
    .cdm-svc-nav-item.active a i.arrow {
        color: #fb9a27;
    }

    /* Direct Contact Card */
    .cdm-help-card {
        background: linear-gradient(135deg, #02144a 0%, #03228f 100%);
        border-radius: 14px;
        padding: 30px 24px;
        color: #ffffff;
        text-align: center;
    }
    .cdm-help-icon {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: rgba(251, 154, 39, 0.2);
        color: #fb9a27;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        margin-bottom: 16px;
    }
    .cdm-help-card h4 {
        font-size: 18px;
        font-weight: 800;
        color: #ffffff;
        margin-bottom: 8px;
    }
    .cdm-help-card p {
        font-size: 13.5px;
        color: rgba(255, 255, 255, 0.85);
        margin-bottom: 20px;
        line-height: 1.5;
    }
    .cdm-help-phone {
        font-size: 19px;
        font-weight: 800;
        color: #fb9a27;
        display: block;
        margin-bottom: 18px;
    }
    .cdm-help-phone:hover {
        color: #ffffff;
    }
    .cdm-btn-whatsapp {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        background: #25d366;
        color: #ffffff;
        font-weight: 700;
        font-size: 13.5px;
        padding: 10px 20px;
        border-radius: 6px;
        width: 100%;
        transition: transform 0.2s ease;
    }
    .cdm-btn-whatsapp:hover {
        transform: translateY(-2px);
        color: #ffffff;
    }

    /* Responsive */
    @media (max-width: 992px) {
        .cdm-svc-layout {
            grid-template-columns: 1fr;
            gap: 50px;
        }
        .cdm-svc-sidebar {
            position: static;
        }
    }
    @media (max-width: 640px) {
        .cdm-features-grid,
        .cdm-process-grid,
        .cdm-benefits-grid {
            grid-template-columns: 1fr;
        }
        .cdm-svc-featured-card img {
            height: 250px;
        }
        .cdm-svc-callout {
            flex-direction: column;
            text-align: center;
        }
        .cdm-svc-callout .cdm-btn-quote {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<!-- 1. HERO BANNER -->
<section class="cdm-svc-hero">
    <div class="container">
        <div class="cdm-svc-hero-badge">
            <i class="fas <?= htmlspecialchars($s_icon) ?>"></i> Specialized Growth Division
        </div>
        <h1>
            <?= htmlspecialchars($first_word) ?> <span class="stroke"><?= htmlspecialchars($remaining_words) ?></span>
        </h1>
        <p>
            <?= htmlspecialchars($pageDesc) ?>
        </p>
        <div class="cdm-svc-breadcrumbs">
            <a href="<?= $base_url ?>/">Home</a>
            <span class="sep">/</span>
            <a href="<?= $base_url ?>/services">Services</a>
            <span class="sep">/</span>
            <span class="active"><?= htmlspecialchars($s_name) ?></span>
        </div>
    </div>
</section>

<!-- 2. MAIN TWO-COLUMN SERVICE DETAIL LAYOUT -->
<div class="cdm-svc-layout">
    
    <!-- LEFT MAIN COLUMN -->
    <main class="cdm-svc-main">
        
        <!-- Service Hero Card -->
        <article class="cdm-svc-featured-card cdm-reveal">
            <img src="<?= htmlspecialchars($s_image) ?>" alt="<?= htmlspecialchars($s_name) ?>" loading="lazy">
            <div class="cdm-svc-featured-content">
                <h2>Scalable & Revenue-Driven <?= htmlspecialchars($s_name) ?> in Pondicherry</h2>
                <div class="lead-text">
                    <?= nl2br(htmlspecialchars($s_full)) ?>
                </div>
            </div>
        </article>

        <!-- Deliverables & Core Features Grid -->
        <?php if (!empty($features)): ?>
        <section class="cdm-features-wrap cdm-reveal">
            <div class="cdm-svc-heading">
                <span class="tagline">// Comprehensive Scope & Solutions //</span>
                <h3>What We Deliver in <?= htmlspecialchars($s_name) ?></h3>
            </div>
            <div class="cdm-features-grid">
                <?php foreach ($features as $f_idx => $feat): 
                    $f_title = is_array($feat) ? ($feat['title'] ?? '') : (string)$feat;
                    $f_desc = is_array($feat) ? ($feat['desc'] ?? '') : '';
                    $card_icons = ['fa-bolt', 'fa-chart-pie', 'fa-layer-group', 'fa-bullseye', 'fa-magnifying-glass-chart', 'fa-award'];
                    $c_icon = $card_icons[$f_idx % count($card_icons)];
                ?>
                <div class="cdm-feat-card cdm-hover-lift">
                    <div class="cdm-feat-icon">
                        <i class="fas <?= $c_icon ?>"></i>
                    </div>
                    <h4><?= htmlspecialchars($f_title) ?></h4>
                    <?php if (!empty($f_desc)): ?>
                    <p><?= htmlspecialchars($f_desc) ?></p>
                    <?php else: ?>
                    <p>Designed and managed by senior digital marketers to ensure industry-leading retention and return on spend.</p>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- 4-Step Strategic Workflow -->
        <?php if (!empty($process_steps)): ?>
        <section class="cdm-process-section cdm-reveal">
            <div class="cdm-svc-heading">
                <span class="tagline">// Proven Methodology //</span>
                <h3>Our 4-Step Strategic Roadmap</h3>
            </div>
            <div class="cdm-process-grid">
                <?php foreach ($process_steps as $p_idx => $step): 
                    $step_num = $step['step'] ?? sprintf('%02d', $p_idx + 1);
                    $step_title = $step['title'] ?? '';
                    $step_desc = $step['desc'] ?? '';
                ?>
                <div class="cdm-process-card">
                    <span class="cdm-step-badge">STEP <?= htmlspecialchars($step_num) ?></span>
                    <h4><?= htmlspecialchars($step_title) ?></h4>
                    <p><?= htmlspecialchars($step_desc) ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- Key Outcomes & Business Benefits -->
        <?php if (!empty($benefits)): ?>
        <section class="cdm-benefits-box cdm-reveal">
            <div class="cdm-svc-heading" style="margin-bottom: 12px;">
                <span class="tagline">// The Value Impact //</span>
                <h3>Tangible Advantages for Your Business</h3>
            </div>
            <div class="cdm-benefits-grid">
                <?php foreach ($benefits as $benefit_text): ?>
                <div class="cdm-benefit-item">
                    <i class="fas fa-circle-check"></i>
                    <span><?= htmlspecialchars($benefit_text) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- Service-Specific FAQs Accordion -->
        <?php if (!empty($service_faqs)): ?>
        <section class="cdm-faqs-wrap cdm-reveal">
            <div class="cdm-svc-heading">
                <span class="tagline">// Got Questions? We Have Answers //</span>
                <h3>Frequently Asked Questions</h3>
            </div>
            <div class="cdm-faqs-list" id="cdmServiceFaqs">
                <?php foreach ($service_faqs as $fq_idx => $sfq): ?>
                <div class="cdm-faq-item <?= $fq_idx === 0 ? 'active' : '' ?>">
                    <button type="button" class="cdm-faq-trigger" onclick="toggleSvcFaq(this)">
                        <span><?= htmlspecialchars($sfq['question'] ?? '') ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="cdm-faq-content">
                        <?= nl2br(htmlspecialchars($sfq['answer'] ?? '')) ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- Bottom Conversion Callout Box -->
        <section class="cdm-svc-callout cdm-reveal">
            <div>
                <h3>Ready to Accelerate Growth with <?= htmlspecialchars($s_name) ?>?</h3>
                <p>Schedule a 1-on-1 strategy discovery session with our senior digital specialists today.</p>
            </div>
            <div>
                <button type="button" class="cdm-btn-quote" onclick="openContactPopup()">
                    <span>Claim Free Consultation</span>
                    <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </section>

    </main>

    <!-- RIGHT STICKY SIDEBAR -->
    <aside class="cdm-svc-sidebar">

        <!-- 1. Quick Free Quote / Audit Form -->
        <div class="cdm-side-card cdm-reveal">
            <h4>Request Free Strategy Audit</h4>
            <p class="subtitle">Receive a complimentary performance analysis & roadmap for <?= htmlspecialchars($s_name) ?>.</p>

            <form id="cdmSideAuditForm" onsubmit="handleSideLeadSubmit(event)">
                <input type="hidden" name="service" value="<?= htmlspecialchars($s_name) ?>">
                
                <div class="cdm-form-group">
                    <label for="sideName">Full Name *</label>
                    <input type="text" id="sideName" name="name" class="cdm-form-control" placeholder="Your Name" required>
                </div>

                <div class="cdm-form-group">
                    <label for="sidePhone">Phone / WhatsApp Number *</label>
                    <input type="tel" id="sidePhone" name="phone" class="cdm-form-control" placeholder="+91 98765 43210" required>
                </div>

                <div class="cdm-form-group">
                    <label for="sideEmail">Business Email *</label>
                    <input type="email" id="sideEmail" name="email" class="cdm-form-control" placeholder="name@company.com" required>
                </div>

                <div class="cdm-form-group">
                    <label for="sideWebsite">Website / Company URL</label>
                    <input type="url" id="sideWebsite" name="website" class="cdm-form-control" placeholder="https://yourwebsite.com">
                </div>

                <div class="cdm-form-group">
                    <label for="sideMessage">Project Goals / Details</label>
                    <textarea id="sideMessage" name="message" class="cdm-form-control" placeholder="Tell us about your target goals, monthly budget, or timeline..."></textarea>
                </div>

                <div id="sideFormStatus" style="display:none; font-size:13px; margin-bottom:14px; font-weight:600; padding:10px; border-radius:6px;"></div>

                <button type="submit" class="cdm-side-submit" id="sideSubmitBtn">
                    <span>Submit Audit Request</span>
                    <i class="fas fa-arrow-right"></i>
                </button>
            </form>
        </div>

        <!-- 2. All Services Navigation Menu -->
        <?php if (!empty($other_services)): ?>
        <div class="cdm-side-card cdm-reveal">
            <h4>All Marketing Services</h4>
            <p class="subtitle">Explore our specialized performance divisions</p>
            <ul class="cdm-svc-nav-list">
                <?php foreach ($other_services as $os): 
                    $is_current = ($os['slug'] === $s_slug);
                    $os_icon = !empty($os['icon']) ? $os['icon'] : 'fa-chart-line';
                    if (!str_starts_with($os_icon, 'fa-') && !str_starts_with($os_icon, 'fas ') && !str_starts_with($os_icon, 'fab ')) {
                        $os_icon = 'fa-' . ltrim($os_icon, 'fa-');
                    }
                ?>
                <li class="cdm-svc-nav-item <?= $is_current ? 'active' : '' ?>">
                    <a href="<?= $base_url ?>/service/<?= htmlspecialchars($os['slug']) ?>">
                        <span>
                            <i class="fas <?= htmlspecialchars($os_icon) ?> icon"></i>
                            <?= htmlspecialchars($os['service_name']) ?>
                        </span>
                        <i class="fas fa-chevron-right arrow"></i>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <!-- 3. Direct Contact Help Card -->
        <div class="cdm-help-card cdm-reveal">
            <div class="cdm-help-icon">
                <i class="fas fa-headset"></i>
            </div>
            <h4>Have Specific Questions?</h4>
            <p>Our senior digital marketing architects are available to answer your technical questions.</p>
            <a href="tel:<?= preg_replace('/[^0-9+]/', '', $contact_phone) ?>" class="cdm-help-phone">
                <?= htmlspecialchars($contact_phone) ?>
            </a>
            <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $contact_whatsapp) ?>?text=<?= urlencode('Hi ' . $site_name . ', I would like to inquire about your ' . $s_name . ' service.') ?>" target="_blank" rel="noopener" class="cdm-btn-whatsapp">
                <i class="fab fa-whatsapp"></i> Chat on WhatsApp
            </a>
        </div>

    </aside>

</div>

<script>
// Service FAQ Accordion Toggle
function toggleSvcFaq(btn) {
    const item = btn.closest('.cdm-faq-item');
    const isActive = item.classList.contains('active');
    
    // Close other FAQs in list
    document.querySelectorAll('#cdmServiceFaqs .cdm-faq-item').forEach(el => {
        el.classList.remove('active');
    });

    if (!isActive) {
        item.classList.add('active');
    }
}

// Sidebar Lead AJAX Submit
function handleSideLeadSubmit(e) {
    e.preventDefault();
    const form = e.target;
    const btn = document.getElementById('sideSubmitBtn');
    const statusBox = document.getElementById('sideFormStatus');
    const formData = new FormData(form);

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting Request...';
    statusBox.style.display = 'none';

    fetch('<?= $base_url ?>/api/lead-capture.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<span>Submit Audit Request</span> <i class="fas fa-arrow-right"></i>';
        statusBox.style.display = 'block';

        if (data.status === 'success') {
            statusBox.style.background = '#dcfce7';
            statusBox.style.color = '#15803d';
            statusBox.style.border = '1px solid #86efac';
            statusBox.innerHTML = '<i class="fas fa-check-circle"></i> Thank you! Your request has been received. Our team will contact you shortly.';
            form.reset();
        } else {
            statusBox.style.background = '#fee2e2';
            statusBox.style.color = '#b91c1c';
            statusBox.style.border = '1px solid #fca5a5';
            statusBox.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + (data.message || 'Submission error. Please check your inputs.');
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = '<span>Submit Audit Request</span> <i class="fas fa-arrow-right"></i>';
        statusBox.style.display = 'block';
        statusBox.style.background = '#fee2e2';
        statusBox.style.color = '#b91c1c';
        statusBox.style.border = '1px solid #fca5a5';
        statusBox.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Network error. Please call us directly at <?= htmlspecialchars($contact_phone) ?>.';
    });
}
</script>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>
