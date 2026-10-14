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

<!-- CHERRIDM INNER PAGE HEADER -->
<style>
    .cdm-page-hero {
        background: linear-gradient(135deg, rgba(2, 20, 74, 0.95) 0%, rgba(3, 34, 143, 0.92) 100%),
                    url('https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=1600&q=80') center/cover;
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

    /* Service Rows */
    .cdm-services-container {
        padding: 80px 0;
    }
    .cdm-service-block {
        padding: 70px 0;
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
        gap: 60px;
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
        line-height: 1.75;
        margin-bottom: 24px;
        text-align: justify;
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
        box-shadow: 0 15px 35px rgba(3, 34, 143, 0.12);
        height: 380px;
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

<!-- PAGE HERO -->
<section class="cdm-page-hero">
    <div class="container">
        <h1>Our <span class="stroke">Services</span></h1>
        <p>Result-driven digital marketing and development solutions engineered to scale your business online</p>
        <div class="cdm-breadcrumb-capsule">
            <a href="<?= $base_url ?>/">Home</a>
            <span class="sep">/</span>
            <span class="active">Services</span>
        </div>
    </div>
</section>

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
                        <a href="<?= !empty($s['slug']) ? $base_url . '/service/' . htmlspecialchars($s['slug']) : $base_url . '/services' ?>" class="cdm-btn-service-cta">
                            <span>Explore Deliverables & Scope</span>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                        <a href="<?= $base_url ?>/contact?service=<?= urlencode($s['service_name'] ?? '') ?>" class="cdm-btn-service-cta" style="background: transparent; color: #03228f; border-color: #03228f;">
                            <span>Request Quote</span>
                            <i class="fas fa-paper-plane"></i>
                        </a>
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

<!-- BOTTOM CALLOUT BANNER -->
<section style="background: linear-gradient(135deg, #02144a 0%, #03228f 100%); padding: 70px 20px; color: #ffffff; text-align: center;">
    <div style="max-width: 800px; margin: 0 auto;">
        <h3 style="font-size: clamp(1.8rem, 3.5vw, 2.4rem); font-weight: 800; margin-bottom: 12px;">Need a Tailored Multi-Channel Strategy?</h3>
        <p style="font-size: 16px; color: rgba(255,255,255,0.85); margin-bottom: 28px;">
            Our specialists build custom digital marketing roadmaps aligned specifically with your revenue goals and acquisition timeline.
        </p>
        <button type="button" class="cdm-btn-quote" style="display: inline-flex;" onclick="openContactPopup()">
            <span>Request Free Custom Consultation</span>
            <i class="fas fa-arrow-right"></i>
        </button>
    </div>
</section>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>