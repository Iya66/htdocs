<?php 
/**
 * Why Choose Us View - CherriDM Theme
 * 100% Dynamic with Database Integration
 */
$why_items = $why_items ?? [];
$faqs = $faqs ?? [];
$site_name = !empty($settings['site_name']) ? $settings['site_name'] : 'Cloud Aspire';
$contact_phone = !empty($settings['contact_phone']) ? $settings['contact_phone'] : '8098830937';

include __DIR__ . '/../../../includes/header.php'; 
?>

<!-- CHERRIDM INNER PAGE HEADER -->
<style>
    .cdm-page-hero {
        background: linear-gradient(135deg, rgba(2, 20, 74, 0.95) 0%, rgba(3, 34, 143, 0.92) 100%),
                    url('https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=1600&q=80') center/cover;
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

    /* Pillars Grid */
    .cdm-why-section {
        padding: 85px 0;
        background-color: #ffffff;
    }
    .cdm-why-container {
        max-width: 1240px;
        margin: 0 auto;
        padding: 0 20px;
    }
    .cdm-why-cards-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 30px;
        margin-top: 50px;
    }
    .cdm-why-card {
        background: #f8faff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 35px 28px;
        transition: all 0.35s ease;
        position: relative;
        overflow: hidden;
    }
    .cdm-why-card:hover {
        background: #ffffff;
        border-color: #fb9a27;
        box-shadow: 0 15px 40px rgba(3, 34, 143, 0.12);
        transform: translateY(-6px);
    }
    .cdm-why-icon-badge {
        width: 60px;
        height: 60px;
        background: #03228f;
        color: #ffffff;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        margin-bottom: 22px;
        transition: all 0.3s ease;
    }
    .cdm-why-card:hover .cdm-why-icon-badge {
        background: #fb9a27;
        transform: rotate(6deg);
    }
    .cdm-why-card h3 {
        font-size: 20px;
        font-weight: 800;
        color: #03228f;
        margin-bottom: 12px;
    }
    .cdm-why-card p {
        font-size: 14px;
        color: #64748b;
        line-height: 1.7;
    }

    /* FAQ Section */
    .cdm-faq-section {
        padding: 85px 0;
        background-color: #f8faff;
    }
    .cdm-faq-container {
        max-width: 900px;
        margin: 0 auto;
        padding: 0 20px;
    }
    .cdm-faq-item {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        margin-bottom: 16px;
        overflow: hidden;
        transition: all 0.3s ease;
    }
    .cdm-faq-header {
        padding: 20px 24px;
        font-size: 17px;
        font-weight: 700;
        color: #03228f;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        user-select: none;
    }
    .cdm-faq-header i {
        color: #fb9a27;
        transition: transform 0.3s ease;
    }
    .cdm-faq-body {
        padding: 0 24px 22px;
        font-size: 14px;
        color: #475569;
        line-height: 1.75;
        display: none;
    }
    .cdm-faq-item.active {
        border-color: #03228f;
        box-shadow: 0 8px 25px rgba(3, 34, 143, 0.08);
    }
    .cdm-faq-item.active .cdm-faq-header i {
        transform: rotate(180deg);
    }
    .cdm-faq-item.active .cdm-faq-body {
        display: block;
    }

    @media (max-width: 992px) {
        .cdm-why-cards-grid {
            grid-template-columns: 1fr 1fr;
        }
    }
    @media (max-width: 600px) {
        .cdm-why-cards-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<!-- PAGE HERO -->
<section class="cdm-page-hero">
    <div class="container">
        <h1>Why Choose <span class="stroke">Us</span></h1>
        <p>Discover the data-driven methodology, transparency, and relentless focus on ROI that sets <?= htmlspecialchars($site_name) ?> apart</p>
        <div class="cdm-breadcrumb-capsule">
            <a href="<?= $base_url ?>/">Home</a>
            <span class="sep">/</span>
            <span class="active">Why Choose Us</span>
        </div>
    </div>
</section>

<!-- PILLARS SECTION (DYNAMIC FROM DB `why_choose_us`) -->
<section class="cdm-why-section">
    <div class="cdm-why-container">
        <div style="text-align: center; max-width: 720px; margin: 0 auto;">
            <span style="color: #fb9a27; font-weight: 700; text-transform: uppercase; font-size: 13px; letter-spacing: 1px; display: block; margin-bottom: 8px;">
                // The Competitive Edge //
            </span>
            <h2 style="font-size: clamp(2rem, 3.5vw, 2.7rem); font-weight: 800; color: #03228f; margin-bottom: 12px;">
                Engineered for <span class="text-secondary">Compound Revenue Growth</span>
            </h2>
            <div style="width: 60px; height: 4px; background: #fb9a27; margin: 0 auto 20px; border-radius: 2px;"></div>
            <p style="color: #64748b; font-size: 15px;">We pair analytical rigor with creative finesse to transform underperforming digital channels into predictable revenue drivers.</p>
        </div>

        <div class="cdm-why-cards-grid">
            <?php 
            $standard_why = [
                ['question' => 'Proven Track Record', 'answer' => 'Over 500+ successful digital initiatives with measurable ROI across e-commerce, healthcare, and SaaS.', 'icon' => 'fa-trophy'],
                ['question' => 'Certified Specialists', 'answer' => 'Dedicated specialists in performance marketing, technical SEO, and conversion-focused web development.', 'icon' => 'fa-users-gear'],
                ['question' => 'Data-Driven Growth', 'answer' => 'Every campaign decision is backed by live analytics and deep audience behavior modeling.', 'icon' => 'fa-chart-line'],
                ['question' => 'Dedicated Support', 'answer' => 'Direct access to account leads and transparent bi-weekly strategy alignment calls.', 'icon' => 'fa-headset'],
                ['question' => 'Rapid Execution', 'answer' => 'Fast turnaround without sacrificing code quality, security, or creative finesse.', 'icon' => 'fa-bolt'],
                ['question' => 'Transparent Analytics', 'answer' => 'Live real-time performance dashboards so you always know your exact cost per acquisition.', 'icon' => 'fa-shield-halved'],
            ];

            $final_why = !empty($why_items) ? $why_items : $standard_why;

            foreach ($final_why as $w):
                $w_icon = !empty($w['icon']) ? $w['icon'] : 'fa-check-circle';
                if (!str_starts_with($w_icon, 'fa-') && !str_starts_with($w_icon, 'fas ')) {
                    $w_icon = 'fa-' . ltrim($w_icon, 'fa-');
                }
            ?>
            <div class="cdm-why-card cdm-reveal cdm-hover-lift">
                <div class="cdm-why-icon-badge">
                    <i class="fas <?= htmlspecialchars($w_icon) ?>"></i>
                </div>
                <h3><?= htmlspecialchars($w['question'] ?? '') ?></h3>
                <p><?= htmlspecialchars($w['answer'] ?? '') ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- FREQUENTLY ASKED QUESTIONS (DYNAMIC FROM DB `faqs`) -->
<section class="cdm-faq-section">
    <div class="cdm-faq-container">
        <div style="text-align: center; margin-bottom: 45px;">
            <span style="color: #fb9a27; font-weight: 700; text-transform: uppercase; font-size: 13px; letter-spacing: 1px; display: block; margin-bottom: 8px;">
                // Got Questions? //
            </span>
            <h2 style="font-size: clamp(2rem, 3.5vw, 2.7rem); font-weight: 800; color: #03228f; margin-bottom: 12px;">
                Frequently Asked <span class="text-secondary">Questions</span>
            </h2>
            <div style="width: 60px; height: 4px; background: #fb9a27; margin: 0 auto; border-radius: 2px;"></div>
        </div>

        <?php 
        $default_faqs = [
            ['question' => 'Why partner with ' . $site_name . '?', 'answer' => 'We treat your business as our own. By pairing data-driven analytics with creative excellence, we design campaigns that deliver measurable bottom-line growth, not vanity metrics.'],
            ['question' => 'How do you approach multi-channel digital growth?', 'answer' => 'We begin with a deep market audit, identify high-intent conversion bottlenecks, and deploy a synchronized mix of SEO, Meta/Google Ads, content, and high-speed landing pages.'],
            ['question' => 'How quickly can we expect to see tangible results?', 'answer' => 'Paid performance campaigns generate qualified inquiries within days of launch. SEO ranking momentum typically builds strong compounding returns within 60 to 90 days.'],
            ['question' => 'Do you offer custom tailored packages?', 'answer' => 'Yes! We craft bespoke growth roadmaps aligned with your immediate goals, target CAC (Customer Acquisition Cost), and market trajectory.'],
        ];

        $final_faqs = !empty($faqs) ? $faqs : $default_faqs;

        foreach ($final_faqs as $f_idx => $faq):
        ?>
        <div class="cdm-faq-item <?= $f_idx === 0 ? 'active' : '' ?>">
            <div class="cdm-faq-header" onclick="toggleFaq(this)">
                <span><?= htmlspecialchars($faq['question']) ?></span>
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="cdm-faq-body">
                <?= htmlspecialchars($faq['answer']) ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- BOTTOM CTA -->
<section style="background: linear-gradient(135deg, #02144a 0%, #03228f 100%); padding: 70px 20px; color: #ffffff; text-align: center;">
    <div style="max-width: 800px; margin: 0 auto;">
        <h3 style="font-size: clamp(1.8rem, 3.5vw, 2.4rem); font-weight: 800; margin-bottom: 12px;">Ready to Elevate Your Market Presence?</h3>
        <p style="font-size: 16px; color: rgba(255,255,255,0.85); margin-bottom: 28px;">
            Book a complimentary 30-minute growth session with our senior digital strategist.
        </p>
        <button type="button" class="cdm-btn-quote" style="display: inline-flex;" onclick="openContactPopup()">
            <span>Book Strategy Consultation</span>
            <i class="fas fa-arrow-right"></i>
        </button>
    </div>
</section>

<script>
function toggleFaq(header) {
    const item = header.parentElement;
    item.classList.toggle('active');
}
</script>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>
