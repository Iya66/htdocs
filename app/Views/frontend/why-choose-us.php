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

<!-- REUSABLE PAGE HERO COMPONENT -->
<?php 
$hero_title = 'Why Choose ' . $site_name;
$hero_highlight = 'Why Choose';
$hero_subtitle = 'Discover why high-growth enterprises and ambitious startups trust ' . $site_name . ' for compound revenue growth and digital market dominance.';
$hero_breadcrumbs = [
    ['label' => 'Home', 'url' => $base_url . '/'],
    ['label' => 'Why Choose Us']
];
include __DIR__ . '/../../../includes/components/page_hero.php'; 
?>

<style>
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
                ['question' => 'Proven Track Record', 'answer' => 'Over ' . (int)($settings['stat_experience'] ?? 4) . '+ successful digital initiatives with measurable ROI across e-commerce, healthcare, and SaaS.', 'icon' => 'fa-trophy'],
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

<!-- FREQUENTLY ASKED QUESTIONS (REUSABLE COMPONENT CONNECTED TO DB `faqs`) -->
<?php include __DIR__ . '/../../../includes/components/faq_section.php'; ?>

<!-- REUSABLE CTA BANNER (CONNECTED TO DATABASE SETTINGS) -->
<?php include __DIR__ . '/../../../includes/components/cta_banner.php'; ?>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>
