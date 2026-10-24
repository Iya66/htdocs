<?php
/**
 * Client Testimonials View - CherriDM Theme
 * 100% Dynamic with Database Integration
 */
$testimonials = $testimonials ?? [];
$site_name = !empty($settings['site_name']) ? $settings['site_name'] : ($site_name ?? 'Cloud Aspire');
$stat_partners = $stat_partners ?? '0+';
$stat_growth = $stat_growth ?? '0%';
$stat_satisfaction = $stat_satisfaction ?? '0%';
$stat_projects = $stat_projects ?? '0+';

$target_partners = (int)filter_var($stat_partners, FILTER_SANITIZE_NUMBER_INT);
$target_growth = (int)filter_var($stat_growth, FILTER_SANITIZE_NUMBER_INT);
$target_satisfaction = (int)filter_var($stat_satisfaction, FILTER_SANITIZE_NUMBER_INT);
$target_projects = (int)filter_var($stat_projects, FILTER_SANITIZE_NUMBER_INT);

include __DIR__ . '/../../../includes/header.php'; 
require_once __DIR__ . '/../../../includes/components/page_hero.php';
?>

<style>
    /* Metrics Grid */
    .cdm-testimonial-metrics {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 24px;
        margin-bottom: 55px;
    }
    .cdm-t-metric-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 30px 20px;
        text-align: center;
        box-shadow: 0 4px 16px rgba(3, 34, 143, 0.05);
        transition: all 0.35s ease;
    }
    .cdm-t-metric-card:hover {
        transform: translateY(-6px);
        border-color: #fb9a27;
        box-shadow: 0 14px 35px rgba(3, 34, 143, 0.12);
    }
    .cdm-t-metric-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        background: #03228f;
        color: #ffffff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        margin-bottom: 12px;
        transition: all 0.3s ease;
    }
    .cdm-t-metric-card:hover .cdm-t-metric-icon {
        background: #fb9a27;
        transform: scale(1.1);
    }
    .cdm-t-metric-card h3 {
        font-size: 2.2rem;
        font-weight: 800;
        color: #03228f;
        margin: 0 0 6px 0;
        line-height: 1.1;
    }
    .cdm-t-metric-card p {
        margin: 0;
        color: #64748b;
        font-size: 0.92rem;
        font-weight: 600;
    }

    /* Testimonials Showcase Grid */
    .cdm-testimonials-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
        gap: 30px;
    }
    .cdm-testimonial-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 36px 30px 28px;
        box-shadow: 0 4px 18px rgba(3, 34, 143, 0.05);
        display: flex;
        flex-direction: column;
        position: relative;
        transition: all 0.35s ease;
        overflow: hidden;
    }
    .cdm-testimonial-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: #03228f;
        transition: background 0.3s ease;
    }
    .cdm-testimonial-card:hover::before {
        background: #fb9a27;
    }
    .cdm-testimonial-card:hover {
        transform: translateY(-8px);
        border-color: #fb9a27;
        box-shadow: 0 16px 45px rgba(3, 34, 143, 0.12);
    }
    .cdm-t-quote-icon {
        position: absolute;
        top: 24px;
        right: 24px;
        font-size: 2.5rem;
        color: #fb9a27;
        opacity: 0.15;
        transition: all 0.3s ease;
        pointer-events: none;
    }
    .cdm-testimonial-card:hover .cdm-t-quote-icon {
        opacity: 0.38;
        transform: scale(1.15) rotate(-6deg);
    }
    .cdm-t-stars {
        color: #fb9a27;
        font-size: 1.05rem;
        margin-bottom: 16px;
        display: flex;
        gap: 3px;
    }
    .cdm-t-message {
        font-size: 1rem;
        line-height: 1.8;
        color: #475569;
        margin-bottom: 24px;
        font-style: italic;
        flex-grow: 1;
    }
    .cdm-client-footer {
        display: flex;
        align-items: center;
        gap: 16px;
        border-top: 1px solid #f1f5f9;
        padding-top: 20px;
        margin-top: auto;
    }
    .cdm-avatar-wrap {
        width: 58px;
        height: 58px;
        border-radius: 50%;
        overflow: hidden;
        border: 2px solid #fb9a27;
        background: rgba(3, 34, 143, 0.08);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .cdm-avatar-wrap img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .cdm-avatar-fallback {
        font-size: 1.4rem;
        color: #03228f;
    }
    .cdm-client-info h4 {
        margin: 0 0 3px 0;
        font-size: 1.05rem;
        font-weight: 700;
        color: #03228f;
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }
    .cdm-t-verified-pill {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: rgba(16, 185, 129, 0.1);
        border: 1px solid rgba(16, 185, 129, 0.25);
        color: #10b981;
        font-size: 0.72rem;
        font-weight: 700;
        padding: 2px 7px;
        border-radius: 50px;
    }
    .cdm-t-designation {
        margin: 0;
        color: #64748b;
        font-weight: 500;
        font-size: 0.85rem;
    }

    @media (max-width: 992px) {
        .cdm-testimonial-metrics {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    @media (max-width: 640px) {
        .cdm-testimonial-metrics {
            grid-template-columns: 1fr;
        }
        .cdm-testimonials-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<?php
render_page_hero([
    'title' => 'Client',
    'highlight' => 'Testimonials',
    'subtitle' => 'Real stories of growth, transformation, and impact from our valued business partners',
    'breadcrumb_active' => 'Testimonials',
    'base_url' => $base_url
]);
?>

<section style="padding: 70px 0; background-color: #f8faff;">
    <div class="container">
        
        <!-- Trust & Performance Metrics Strip -->
        <div class="cdm-testimonial-metrics">
            <div class="cdm-t-metric-card cdm-reveal">
                <div class="cdm-t-metric-icon"><i class="fas fa-handshake"></i></div>
                <h3 class="counter-num" data-count="<?= $target_partners ?>+"><?= htmlspecialchars($stat_partners) ?></h3>
                <p>Enterprise &amp; Growth Partners</p>
            </div>
            <div class="cdm-t-metric-card cdm-reveal">
                <div class="cdm-t-metric-icon"><i class="fas fa-chart-line"></i></div>
                <h3 class="counter-num" data-count="<?= $target_growth ?>%"><?= htmlspecialchars($stat_growth) ?></h3>
                <p>Average Client Growth</p>
            </div>
            <div class="cdm-t-metric-card cdm-reveal">
                <div class="cdm-t-metric-icon"><i class="fas fa-smile-beam"></i></div>
                <h3 class="counter-num" data-count="<?= $target_satisfaction ?>%"><?= htmlspecialchars($stat_satisfaction) ?></h3>
                <p>Client Satisfaction Rate</p>
            </div>
            <div class="cdm-t-metric-card cdm-reveal">
                <div class="cdm-t-metric-icon"><i class="fas fa-trophy"></i></div>
                <h3 class="counter-num" data-count="<?= $target_projects ?>+"><?= htmlspecialchars($stat_projects) ?></h3>
                <p>Successful Projects</p>
            </div>
        </div>

        <!-- Section Header -->
        <div style="text-align: center; max-width: 720px; margin: 0 auto 45px;">
            <span style="color: #fb9a27; font-weight: 700; text-transform: uppercase; font-size: 13px; letter-spacing: 1px; display: block; margin-bottom: 8px;">
                // What Our Partners Say //
            </span>
            <h2 style="font-size: clamp(2rem, 3.5vw, 2.7rem); font-weight: 800; color: #03228f; margin-bottom: 12px;">
                Direct Words of <span class="text-secondary">Praise &amp; Impact</span>
            </h2>
            <div style="width: 60px; height: 4px; background: #fb9a27; margin: 0 auto 16px; border-radius: 2px;"></div>
            <p style="color: #64748b; font-size: 15px;">
                Direct testimonials from founders, marketing leaders, and enterprise directors who scale with <?= htmlspecialchars($site_name) ?>.
            </p>
        </div>
        
        <!-- Testimonials Cards Grid -->
        <div class="cdm-testimonials-grid">
            <?php if (!empty($testimonials)): ?>
                <?php foreach($testimonials as $k => $t): ?>
                    <?php 
                    $image_src = '';
                    if (!empty($t['image'])) {
                        if (str_starts_with($t['image'], 'http://') || str_starts_with($t['image'], 'https://')) {
                            $image_src = $t['image'];
                        } else {
                            $image_src = rtrim($base_url, '/') . '/' . ltrim($t['image'], '/');
                        }
                    }
                    ?>
                    <div class="cdm-testimonial-card cdm-reveal cdm-hover-lift">
                        <i class="fas fa-quote-right cdm-t-quote-icon"></i>
                        <div class="cdm-t-stars">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p class="cdm-t-message">"<?= nl2br(htmlspecialchars($t['message'])) ?>"</p>
                        
                        <div class="cdm-client-footer">
                            <div class="cdm-avatar-wrap">
                                <?php if (!empty($image_src)): ?>
                                    <img src="<?= htmlspecialchars($image_src) ?>" alt="<?= htmlspecialchars($t['name']) ?>" loading="lazy" decoding="async">
                                <?php else: ?>
                                    <i class="fas fa-user cdm-avatar-fallback"></i>
                                <?php endif; ?>
                            </div>
                            
                            <div class="cdm-client-info">
                                <h4>
                                    <?= htmlspecialchars($t['name']) ?>
                                    <span class="cdm-t-verified-pill"><i class="fas fa-check-circle"></i> Verified</span>
                                </h4>
                                <?php if (!empty($t['designation'])): ?>
                                    <p class="cdm-t-designation">
                                        <?= htmlspecialchars($t['designation']) ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="grid-column: 1/-1; text-align: center; padding: 70px 20px; background: #ffffff; border-radius: 14px; border: 1px dashed #cbd5e1;">
                    <div style="width: 70px; height: 70px; border-radius: 50%; background: rgba(3, 34, 143, 0.08); color: #03228f; display: inline-flex; align-items: center; justify-content: center; font-size: 1.8rem; margin-bottom: 20px;">
                        <i class="fas fa-comment-dots"></i>
                    </div>
                    <h3 style="font-size: 1.4rem; color: #03228f; margin-bottom: 8px;">No Testimonials Yet</h3>
                    <p style="color: #64748b; max-width: 460px; margin: 0 auto 24px;">Client video and written testimonials will appear here as they are published.</p>
                    <a href="<?= $base_url ?>/contact" class="cdm-btn-quote" style="display: inline-flex;">
                        <span>Work With Us</span>
                    </a>
                </div>
            <?php endif; ?>
        </div>

    </div>
</section>

<?php include __DIR__ . '/../../../includes/components/cta_banner.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Animated Numbers Count-Up
    const counterElements = document.querySelectorAll('.counter-num');
    const countObserver = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                const el = entry.target;
                if (!el.dataset.counted) {
                    el.dataset.counted = 'true';
                    const target = parseInt(el.getAttribute('data-target'), 10) || 0;
                    const suffix = el.getAttribute('data-suffix') || '';
                    const duration = 1600;
                    let startTime = null;

                    function animateCount(timestamp) {
                        if (!startTime) startTime = timestamp;
                        const progress = Math.min((timestamp - startTime) / duration, 1);
                        const ease = progress === 1 ? 1 : 1 - Math.pow(2, -10 * progress);
                        const currentVal = Math.floor(ease * target);
                        el.textContent = currentVal + suffix;
                        if (progress < 1) {
                            requestAnimationFrame(animateCount);
                        } else {
                            el.textContent = target + suffix;
                        }
                    }

                    requestAnimationFrame(animateCount);
                }
                countObserver.unobserve(el);
            }
        });
    }, { threshold: 0.25 });

    counterElements.forEach((el) => countObserver.observe(el));
});
</script>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>