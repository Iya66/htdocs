<?php 
/**
 * Services View - CherriDM Theme
 * 100% Dynamic with Database Integration
 */
$services = $services ?? [];
$site_name = !empty($settings['site_name']) ? $settings['site_name'] : 'Cloud Aspire';
$contact_phone = !empty($settings['contact_phone']) ? $settings['contact_phone'] : '8098830937';

include __DIR__ . '/../../../includes/header.php'; 
?>

<!-- REUSABLE PAGE HERO COMPONENT -->
<?php 
$hero_title = 'Our Services';
$hero_highlight = 'Services';
$hero_subtitle = 'Discover tailored digital marketing, search optimization, and modern web development solutions designed to scale your business.';
$hero_breadcrumbs = [
    ['label' => 'Home', 'url' => $base_url . '/'],
    ['label' => 'Our Services']
];
include __DIR__ . '/../../../includes/components/page_hero.php'; 
?>

<style>
    /* Service Rows */
    .cdm-services-container {
        padding: 50px 0;
    }
    .cdm-service-block {
        padding: 45px 0;
        border-bottom: 1px solid #e2e8f0;
    }
    .cdm-service-block:nth-child(even) {
        background-color: #f8faff;
    }
    .cdm-service-inner {
        max-width: 1240px;
        margin: 0 auto;
        padding: 0 20px;
        display: grid;
        grid-template-columns: 1.1fr 1fr;
        gap: 45px;
        align-items: center;
    }
    .cdm-service-block:nth-child(even) .cdm-service-inner {
        grid-template-columns: 1fr 1.1fr;
    }
    .cdm-service-block:nth-child(even) .cdm-svc-text {
        order: 2;
    }
    .cdm-service-block:nth-child(even) .cdm-svc-image {
        order: 1;
    }

    .cdm-svc-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(251, 154, 39, 0.12);
        color: #fb9a27;
        font-size: 13px;
        font-weight: 700;
        padding: 6px 14px;
        border-radius: 20px;
        margin-bottom: 16px;
    }
    .cdm-svc-title {
        font-size: clamp(1.8rem, 3vw, 2.5rem);
        font-weight: 800;
        color: #03228f;
        line-height: 1.25;
        margin-bottom: 18px;
    }
    .cdm-svc-desc {
        font-size: 15px;
        color: #475569;
        line-height: 1.7;
        margin-bottom: 22px;
        text-align: left;
    }
    .cdm-svc-pricing {
        font-size: 18px;
        font-weight: 800;
        color: #03228f;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .cdm-svc-pricing span.tag {
        font-size: 13px;
        font-weight: 600;
        background: #e9eeff;
        color: #03228f;
        padding: 4px 10px;
        border-radius: 4px;
    }

    .cdm-svc-image-card {
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 12px 30px rgba(3, 34, 143, 0.1);
        height: 320px;
        position: relative;
    }
    .cdm-svc-image-card img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.6s ease;
    }
    .cdm-svc-image-card:hover img {
        transform: scale(1.06);
    }

    .cdm-btn-service-cta {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        background: #03228f;
        color: #ffffff;
        font-size: 15px;
        font-weight: 700;
        padding: 13px 32px;
        border-radius: 4px;
        border: 2px solid #03228f;
        transition: all 0.3s ease;
    }
    .cdm-btn-service-cta:hover {
        background: #fb9a27;
        border-color: #fb9a27;
        color: #ffffff;
        transform: translateY(-2px);
    }

    @media (max-width: 900px) {
        .cdm-service-inner,
        .cdm-service-block:nth-child(even) .cdm-service-inner {
            grid-template-columns: 1fr;
            gap: 35px;
        }
        .cdm-service-block:nth-child(even) .cdm-svc-text {
            order: 1;
        }
        .cdm-service-block:nth-child(even) .cdm-svc-image {
            order: 2;
        }
        .cdm-svc-image-card {
            height: 280px;
        }
    }
</style>

<!-- SERVICES LISTING -->
<?php if (!empty($services)): ?>
    <div class="cdm-services-container">
        <?php 
        $stock_images = [
            'https://images.unsplash.com/photo-1571786256017-aee7a0c009b6?auto=format&fit=crop&w=800&q=80',
            'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=800&q=80',
            'https://images.unsplash.com/photo-1611162617474-5b21e879e113?auto=format&fit=crop&w=800&q=80',
            'https://images.unsplash.com/photo-1551288049-bebda4e38f71?auto=format&fit=crop&w=800&q=80',
            'https://images.unsplash.com/photo-1542744094-3a31f272c490?auto=format&fit=crop&w=800&q=80',
            'https://images.unsplash.com/photo-1507238691740-187a5b1d37b8?auto=format&fit=crop&w=800&q=80',
        ];

        foreach($services as $i => $s): 
            $img = !empty($s['image']) ? $s['image'] : ($stock_images[$i % count($stock_images)]);
            if (!str_starts_with($img, 'http')) {
                $img = $base_url . '/' . ltrim($img, '/');
            }
        ?>
        <div class="cdm-service-block cdm-reveal">
            <div class="cdm-service-inner">
                <div class="cdm-svc-text cdm-reveal-left">
                    <div class="cdm-svc-badge">
                        <i class="fas <?= htmlspecialchars($s['icon'] ?: 'fa-check-circle') ?>"></i> Premium Growth Solution
                    </div>
                    <h2 class="cdm-svc-title"><?= htmlspecialchars($s['service_name'] ?? '') ?></h2>
                    <p class="cdm-svc-desc">
                        <?= htmlspecialchars($s['full_description'] ?? ($s['short_description'] ?? 'Tailored digital marketing campaigns engineered to drive targeted traffic, maximize qualified sales inquiries, and elevate brand authority.')) ?>
                    </p>
                    <div style="display: flex; gap: 14px; flex-wrap: wrap;">
                        <?php 
                        $exp_text = !empty($s['explore_btn_text']) ? $s['explore_btn_text'] : 'Explore Deliverables & Scope';
                        $exp_url = !empty($s['explore_btn_url']) ? $s['explore_btn_url'] : (!empty($s['slug']) ? $base_url . '/service/' . htmlspecialchars($s['slug']) : $base_url . '/services');
                        ?>
                        <a href="<?= $exp_url ?>" class="cdm-btn-service-cta">
                            <span><?= htmlspecialchars($exp_text) ?></span>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                        <button type="button" class="cdm-btn-service-cta" style="background: transparent; color: #03228f; border-color: #03228f; cursor: pointer;" onclick="openContactPopup('<?= addslashes(htmlspecialchars($s['service_name'] ?? '')) ?>')">
                            <span>Request Quote</span>
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </div>

                <div class="cdm-svc-image cdm-reveal-right">
                    <div class="cdm-svc-image-card cdm-hover-lift">
                        <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($s['service_name'] ?? '') ?>" loading="lazy" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=800&q=80';">
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div style="text-align: center; padding: 100px 20px;">
        <i class="fas fa-cubes" style="font-size: 50px; color: #03228f; margin-bottom: 20px;"></i>
        <h3 style="color: #03228f;">Our Services are Being Updated</h3>
        <p style="color: #64748b; margin-bottom: 25px;">Please check back shortly or reach out to our team directly.</p>
        <a href="<?= $base_url ?>/contact" class="cdm-btn-service-cta">Contact Our Team</a>
    </div>
<?php endif; ?>

<!-- REUSABLE CTA BANNER (CONNECTED TO DATABASE SETTINGS) -->
<?php include __DIR__ . '/../../../includes/components/cta_banner.php'; ?>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>