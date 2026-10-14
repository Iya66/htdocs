<?php 
/**
 * Projects / Portfolio View - CherriDM Theme
 * 100% Dynamic with Database Integration
 */
$projects = $projects ?? [];
$site_name = !empty($settings['site_name']) ? $settings['site_name'] : 'Cloud Aspire';

include __DIR__ . '/../../../includes/header.php'; 
?>

<!-- CHERRIDM INNER PAGE HEADER -->
<style>
    .cdm-page-hero {
        background: linear-gradient(135deg, rgba(2, 20, 74, 0.95) 0%, rgba(3, 34, 143, 0.92) 100%),
                    url('https://images.unsplash.com/photo-1551836022-d5d88e9218df?auto=format&fit=crop&w=1600&q=80') center/cover;
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

    /* Portfolio Section */
    .cdm-portfolio-section {
        padding: 80px 0;
        background-color: #f8faff;
    }
    .cdm-portfolio-container {
        max-width: 1240px;
        margin: 0 auto;
        padding: 0 20px;
    }

    /* Filter Bar */
    .cdm-filter-bar {
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 50px;
    }
    .cdm-filter-btn {
        background: #ffffff;
        color: #03228f;
        border: 2px solid #e2e8f0;
        padding: 10px 24px;
        border-radius: 30px;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .cdm-filter-btn:hover {
        border-color: #03228f;
        transform: translateY(-2px);
    }
    .cdm-filter-btn.active {
        background: #03228f;
        color: #ffffff;
        border-color: #03228f;
        box-shadow: 0 6px 20px rgba(3, 34, 143, 0.25);
    }

    /* Portfolio Grid */
    .cdm-portfolio-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
        gap: 30px;
    }
    .cdm-project-card {
        background: #ffffff;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(3, 34, 143, 0.08);
        transition: all 0.35s ease;
        display: flex;
        flex-direction: column;
        border-bottom: 3px solid transparent;
    }
    .cdm-project-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 45px rgba(3, 34, 143, 0.15);
        border-color: #fb9a27;
    }
    .cdm-project-thumb {
        height: 230px;
        position: relative;
        overflow: hidden;
    }
    .cdm-project-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.6s ease;
    }
    .cdm-project-card:hover .cdm-project-thumb img {
        transform: scale(1.08);
    }
    .cdm-result-badge {
        position: absolute;
        bottom: 15px;
        left: 15px;
        background: #fb9a27;
        color: #ffffff;
        font-size: 13px;
        font-weight: 700;
        padding: 6px 16px;
        border-radius: 20px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }
    .cdm-category-badge {
        position: absolute;
        top: 15px;
        right: 15px;
        background: rgba(3, 34, 143, 0.85);
        backdrop-filter: blur(4px);
        color: #ffffff;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        padding: 4px 12px;
        border-radius: 4px;
        letter-spacing: 0.5px;
    }

    .cdm-project-body {
        padding: 26px 24px;
        display: flex;
        flex-direction: column;
        flex-grow: 1;
    }
    .cdm-client-meta {
        font-size: 13px;
        color: #fb9a27;
        font-weight: 700;
        text-transform: uppercase;
        margin-bottom: 6px;
    }
    .cdm-project-body h3 {
        font-size: 20px;
        font-weight: 800;
        color: #03228f;
        line-height: 1.35;
        margin-bottom: 12px;
    }
    .cdm-project-body p {
        font-size: 14px;
        color: #64748b;
        line-height: 1.6;
        margin-bottom: 20px;
        flex-grow: 1;
    }
    .cdm-project-link {
        font-size: 14px;
        font-weight: 700;
        color: #03228f;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s ease;
    }
    .cdm-project-link:hover {
        color: #fb9a27;
        gap: 12px;
    }

    @media (max-width: 768px) {
        .cdm-portfolio-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<!-- PAGE HERO -->
<section class="cdm-page-hero">
    <div class="container">
        <h1>Our <span class="stroke">Portfolio</span></h1>
        <p>Explore our recent client success stories, enterprise campaigns, and high-impact digital initiatives</p>
        <div class="cdm-breadcrumb-capsule">
            <a href="<?= $base_url ?>/">Home</a>
            <span class="sep">/</span>
            <span class="active">Portfolio</span>
        </div>
    </div>
</section>

<!-- PORTFOLIO SECTION -->
<section class="cdm-portfolio-section">
    <div class="cdm-portfolio-container">
        <!-- Filter Tabs -->
        <div class="cdm-filter-bar">
            <button class="cdm-filter-btn active" onclick="filterProjects('all', this)">All Projects</button>
            <button class="cdm-filter-btn" onclick="filterProjects('seo', this)">SEO Campaigns</button>
            <button class="cdm-filter-btn" onclick="filterProjects('ads', this)">PPC & Paid Ads</button>
            <button class="cdm-filter-btn" onclick="filterProjects('web', this)">Web & App Development</button>
            <button class="cdm-filter-btn" onclick="filterProjects('social', this)">Social Media</button>
        </div>

        <!-- Projects Grid -->
        <div class="cdm-portfolio-grid">
            <?php 
            $project_fallbacks = [
                'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1551836022-d5d88e9218df?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1542744094-3a31f272c490?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1557804506-669a67965ba0?auto=format&fit=crop&w=800&q=80',
            ];

            if (!empty($projects)):
                foreach ($projects as $idx => $p):
                    $cat = strtolower($p['category'] ?? 'web');
                    $img = !empty($p['thumbnail']) ? $p['thumbnail'] : $project_fallbacks[$idx % count($project_fallbacks)];
                    if (!str_starts_with($img, 'http')) {
                        $img = $base_url . '/' . ltrim($img, '/');
                    }
                    $target = !empty($p['project_link']) ? $p['project_link'] : ($base_url . '/contact');
            ?>
            <div class="cdm-project-card cdm-reveal cdm-hover-lift" data-category="<?= htmlspecialchars($cat) ?>">
                <div class="cdm-project-thumb">
                    <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($p['project_name']) ?>" loading="lazy" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=800&q=80';">
                    <span class="cdm-category-badge"><?= htmlspecialchars($p['industry'] ?? 'Growth Campaign') ?></span>
                    <?php if (!empty($p['results'])): ?>
                    <span class="cdm-result-badge"><i class="fas fa-chart-line"></i> <?= htmlspecialchars($p['results']) ?></span>
                    <?php endif; ?>
                </div>
                <div class="cdm-project-body">
                    <?php if (!empty($p['client_name'])): ?>
                    <div class="cdm-client-meta"><?= htmlspecialchars($p['client_name']) ?></div>
                    <?php endif; ?>
                    <h3><?= htmlspecialchars($p['project_name']) ?></h3>
                    <p><?= htmlspecialchars($p['description'] ?? 'Tailored digital initiative engineered to maximize lead flow and elevate organic search authority.') ?></p>
                    <a href="<?= htmlspecialchars($target) ?>" class="cdm-project-link">
                        <span>View Details</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
            <?php endforeach; else: ?>
            <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px;">
                <p style="color: #64748b;">No projects available in this view.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- BOTTOM CTA -->
<section style="background: linear-gradient(135deg, #02144a 0%, #03228f 100%); padding: 70px 20px; color: #ffffff; text-align: center;">
    <div style="max-width: 800px; margin: 0 auto;">
        <h3 style="font-size: clamp(1.8rem, 3.5vw, 2.4rem); font-weight: 800; margin-bottom: 12px;">Ready to Scale Your Brand with Similar Results?</h3>
        <p style="font-size: 16px; color: rgba(255,255,255,0.85); margin-bottom: 28px;">
            Let our team engineer a customized, high-converting digital marketing campaign for your business.
        </p>
        <button type="button" class="cdm-btn-quote" style="display: inline-flex;" onclick="openContactPopup()">
            <span>Start Your Growth Journey</span>
            <i class="fas fa-arrow-right"></i>
        </button>
    </div>
</section>

<script>
function filterProjects(cat, btn) {
    document.querySelectorAll('.cdm-filter-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    const cards = document.querySelectorAll('.cdm-project-card');
    cards.forEach(card => {
        if (cat === 'all' || card.getAttribute('data-category').includes(cat)) {
            card.style.display = 'flex';
        } else {
            card.style.display = 'none';
        }
    });
}
</script>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>
