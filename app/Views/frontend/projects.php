<?php 
/**
 * Projects / Portfolio View - CherriDM Theme
 * Senior Full-Stack Implementation: Responsive Mobile Alignment, Balanced Category Filters & Polished Card Hierarchy
 */
$projects = $projects ?? [];
$site_name = !empty($settings['site_name']) ? $settings['site_name'] : 'Cloud Aspire';

include __DIR__ . '/../../../includes/header.php'; 
?>

<!-- REUSABLE PAGE HERO COMPONENT -->
<?php 
$hero_title = 'Our Projects';
$hero_highlight = 'Projects';
$hero_subtitle = 'Explore our verified portfolio of successful campaigns, web platforms, and compounding revenue growth engines.';
$hero_breadcrumbs = [
    ['label' => 'Home', 'url' => $base_url . '/'],
    ['label' => 'Our Projects']
];
include __DIR__ . '/../../../includes/components/page_hero.php'; 
?>

<style>
    /* ==========================================================
       PORTFOLIO SECTION & CONTAINER
       ========================================================== */
    .cdm-portfolio-section {
        padding: 55px 0 75px;
        background-color: #f8faff;
        width: 100%;
        overflow-x: hidden;
        box-sizing: border-box;
    }

    .cdm-portfolio-container {
        max-width: 1240px;
        margin: 0 auto;
        padding: 0 20px;
        width: 100%;
        box-sizing: border-box;
    }

    /* ==========================================================
       CATEGORY FILTERS
       Clean, centered, responsive flexbox with equal button heights
       ========================================================== */
    .cdm-filter-bar {
        display: flex;
        justify-content: center;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
        margin: 0 auto 40px;
        max-width: 960px;
        padding: 0 10px;
        box-sizing: border-box;
    }

    .cdm-filter-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #ffffff;
        color: #03228f;
        border: 1.5px solid #dbeafe;
        padding: 9px 20px;
        border-radius: 30px;
        font-size: 13.5px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        white-space: nowrap;
        box-shadow: 0 2px 8px rgba(3, 34, 143, 0.04);
        line-height: 1.3;
        min-height: 42px;
        user-select: none;
        -webkit-tap-highlight-color: transparent;
        text-align: center;
        box-sizing: border-box;
    }

    .cdm-filter-btn:hover {
        border-color: #03228f;
        color: #03228f;
        transform: translateY(-2px);
        box-shadow: 0 4px 14px rgba(3, 34, 143, 0.1);
    }

    .cdm-filter-btn.active {
        background: #03228f;
        color: #ffffff;
        border-color: #03228f;
        box-shadow: 0 6px 18px rgba(3, 34, 143, 0.25);
        transform: translateY(-1px);
    }

    .cdm-filter-btn:focus-visible {
        outline: 2px solid #fb9a27;
        outline-offset: 2px;
    }

    /* ==========================================================
       PORTFOLIO GRID & PROJECT CARDS
       ========================================================== */
    .cdm-portfolio-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 30px;
        width: 100%;
        box-sizing: border-box;
        align-items: stretch;
    }

    .cdm-project-card {
        background: #ffffff;
        border-radius: 14px;
        overflow: hidden;
        border: 1px solid #e2e8f0;
        border-bottom: 3.5px solid transparent;
        box-shadow: 0 8px 24px rgba(3, 34, 143, 0.06);
        transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.35s ease, border-color 0.3s ease;
        display: flex;
        flex-direction: column;
        width: 100%;
        box-sizing: border-box;
    }

    .cdm-project-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 18px 40px rgba(3, 34, 143, 0.12);
        border-bottom-color: #fb9a27;
    }

    /* Project Thumbnail & Badges */
    .cdm-project-thumb {
        position: relative;
        overflow: hidden;
        width: 100%;
        aspect-ratio: 16 / 10;
        background: #02144a;
        box-sizing: border-box;
    }

    .cdm-project-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        transition: transform 0.6s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .cdm-project-card:hover .cdm-project-thumb img {
        transform: scale(1.06);
    }

    /* Industry / Category Badge (Top Right) */
    .cdm-category-badge {
        position: absolute;
        top: 12px;
        right: 12px;
        background: rgba(3, 34, 143, 0.9);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        color: #ffffff;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        padding: 5px 12px;
        border-radius: 6px;
        letter-spacing: 0.6px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        z-index: 2;
    }

    /* Measurable Result Badge (Bottom Left) */
    .cdm-result-badge {
        position: absolute;
        bottom: 12px;
        left: 12px;
        background: #fb9a27;
        color: #ffffff;
        font-size: 12px;
        font-weight: 700;
        padding: 5px 14px;
        border-radius: 20px;
        box-shadow: 0 4px 14px rgba(251, 154, 39, 0.4);
        display: inline-flex;
        align-items: center;
        gap: 6px;
        z-index: 2;
        letter-spacing: 0.2px;
    }

    /* ==========================================================
       CARD CONTENT & TYPOGRAPHY HIERARCHY
       ========================================================== */
    .cdm-project-body {
        padding: 24px 22px;
        display: flex;
        flex-direction: column;
        flex-grow: 1;
        box-sizing: border-box;
    }

    .cdm-client-meta {
        font-size: 12px;
        color: #fb9a27;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.7px;
        margin-bottom: 6px;
    }

    .cdm-project-body h3 {
        font-size: 18.5px;
        font-weight: 800;
        color: #03228f;
        line-height: 1.35;
        margin: 0 0 10px;
        overflow-wrap: break-word;
        word-break: break-word;
    }

    .cdm-project-body p {
        font-size: 14px;
        color: #64748b;
        line-height: 1.6;
        margin: 0 0 18px;
        flex-grow: 1;
        text-align: left;
        overflow-wrap: break-word;
    }

    .cdm-project-link {
        font-size: 13.5px;
        font-weight: 700;
        color: #03228f;
        display: inline-flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
        text-decoration: none;
        padding-top: 14px;
        border-top: 1px solid #f1f5f9;
        margin-top: auto;
        transition: color 0.25s ease;
        box-sizing: border-box;
    }

    .cdm-project-link span.link-text {
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .cdm-project-link .icon-circle {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: rgba(3, 34, 143, 0.08);
        color: #03228f;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        transition: all 0.25s ease;
    }

    .cdm-project-card:hover .cdm-project-link {
        color: #fb9a27;
    }

    .cdm-project-card:hover .cdm-project-link .icon-circle {
        background: #fb9a27;
        color: #ffffff;
        transform: translateX(4px);
    }

    /* Empty State Container */
    .cdm-empty-projects {
        grid-column: 1 / -1;
        text-align: center;
        padding: 50px 20px;
        background: #ffffff;
        border-radius: 12px;
        border: 1px dashed #cbd5e1;
        margin: 10px 0;
    }

    .cdm-empty-projects i {
        font-size: 42px;
        color: #94a3b8;
        margin-bottom: 14px;
    }

    .cdm-empty-projects h4 {
        font-size: 17px;
        font-weight: 700;
        color: #03228f;
        margin-bottom: 6px;
    }

    .cdm-empty-projects p {
        font-size: 14px;
        color: #64748b;
        margin-bottom: 18px;
    }

    /* ==========================================================
       RESPONSIVE BREAKPOINTS (MOBILE, TABLET, DESKTOP)
       ========================================================== */
    @media (max-width: 1024px) {
        .cdm-portfolio-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 24px;
        }
        .cdm-portfolio-section {
            padding: 48px 0 65px;
        }
    }

    @media (max-width: 640px) {
        .cdm-portfolio-section {
            padding: 32px 0 45px;
        }
        .cdm-portfolio-container {
            padding: 0 16px;
        }
        .cdm-filter-bar {
            gap: 8px;
            margin-bottom: 26px;
            padding: 0;
        }
        .cdm-filter-btn {
            padding: 7px 14px;
            font-size: 12.5px;
            min-height: 36px;
            border-radius: 20px;
        }
        .cdm-portfolio-grid {
            grid-template-columns: 1fr;
            gap: 20px;
        }
        .cdm-project-card {
            border-radius: 12px;
        }
        .cdm-project-body {
            padding: 18px 16px;
        }
        .cdm-project-body h3 {
            font-size: 17px;
            line-height: 1.35;
        }
        .cdm-project-body p {
            font-size: 13.5px;
            margin-bottom: 14px;
        }
        .cdm-result-badge {
            font-size: 11px;
            padding: 4px 11px;
            bottom: 10px;
            left: 10px;
        }
        .cdm-category-badge {
            font-size: 10px;
            padding: 4px 10px;
            top: 10px;
            right: 10px;
        }
    }
</style>

<!-- PORTFOLIO SECTION -->
<section class="cdm-portfolio-section">
    <div class="cdm-portfolio-container">
        
        <!-- Category Filter Buttons -->
        <nav class="cdm-filter-bar" aria-label="Portfolio Category Filters">
            <button type="button" class="cdm-filter-btn active" onclick="filterProjects('all', this)" id="filter-all">
                All Projects
            </button>
            <button type="button" class="cdm-filter-btn" onclick="filterProjects('seo', this)" id="filter-seo">
                SEO Campaigns
            </button>
            <button type="button" class="cdm-filter-btn" onclick="filterProjects('ads', this)" id="filter-ads">
                PPC & Paid Ads
            </button>
            <button type="button" class="cdm-filter-btn" onclick="filterProjects('web', this)" id="filter-web">
                Web & App Development
            </button>
            <button type="button" class="cdm-filter-btn" onclick="filterProjects('social', this)" id="filter-social">
                Social Media
            </button>
        </nav>

        <!-- Projects Grid -->
        <div class="cdm-portfolio-grid" id="cdmProjectsGrid">
            <?php 
            $project_fallbacks = [
                'seo'    => 'https://images.unsplash.com/photo-1571786256017-aee7a0c009b6?auto=format&fit=crop&w=800&q=80',
                'ads'    => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=800&q=80',
                'web'    => 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?auto=format&fit=crop&w=800&q=80',
                'social' => 'https://images.unsplash.com/photo-1611162617474-5b21e879e113?auto=format&fit=crop&w=800&q=80',
            ];

            if (!empty($projects)):
                foreach ($projects as $idx => $p):
                    // Standardize category key using helper function if available
                    $cat = function_exists('getProjectCategory') ? getProjectCategory($p) : strtolower(trim($p['category'] ?? 'web'));
                    $industry = function_exists('getProjectIndustry') ? getProjectIndustry($p) : ($p['industry'] ?? 'Growth Campaign');
                    
                    // Thumbnail resolution with fallback
                    $img = !empty($p['thumbnail']) ? $p['thumbnail'] : ($project_fallbacks[$cat] ?? 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=800&q=80');
                    if (!str_starts_with($img, 'http')) {
                        $img = $base_url . '/' . ltrim($img, '/');
                    }
                    $target = !empty($p['project_link']) ? $p['project_link'] : ($base_url . '/contact');

                    // Clean encoding of results (e.g. non-utf8 rupees)
                    $raw_results = $p['results'] ?? '';
                    $clean_results = str_replace('???', '₹', $raw_results);
            ?>
            <article class="cdm-project-card cdm-reveal cdm-hover-lift" data-category="<?= htmlspecialchars($cat) ?>">
                <!-- Thumbnail Area -->
                <div class="cdm-project-thumb">
                    <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($p['project_name']) ?>" loading="lazy" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=800&q=80';">
                    
                    <span class="cdm-category-badge">
                        <?= htmlspecialchars($industry) ?>
                    </span>

                    <?php if (!empty($clean_results)): ?>
                    <span class="cdm-result-badge">
                        <i class="fas fa-chart-line" aria-hidden="true"></i>
                        <span><?= htmlspecialchars($clean_results) ?></span>
                    </span>
                    <?php endif; ?>
                </div>

                <!-- Body Area -->
                <div class="cdm-project-body">
                    <?php if (!empty($p['client_name'])): ?>
                    <div class="cdm-client-meta"><?= htmlspecialchars($p['client_name']) ?></div>
                    <?php endif; ?>

                    <h3><?= htmlspecialchars($p['project_name']) ?></h3>
                    
                    <p><?= htmlspecialchars($p['description'] ?? 'Tailored digital initiative engineered to maximize lead flow and elevate organic search authority.') ?></p>
                    
                    <a href="<?= htmlspecialchars($target) ?>" class="cdm-project-link">
                        <span class="link-text">
                            <strong>View Case Study</strong>
                        </span>
                        <span class="icon-circle" aria-hidden="true">
                            <i class="fas fa-arrow-right"></i>
                        </span>
                    </a>
                </div>
            </article>
            <?php endforeach; ?>
            <?php endif; ?>

            <!-- Dynamic Empty State Container -->
            <div id="cdmEmptyProjects" class="cdm-empty-projects" style="display: <?= empty($projects) ? 'block' : 'none' ?>;">
                <i class="fas fa-folder-open" aria-hidden="true"></i>
                <h4>No projects found in this category</h4>
                <p>We are continuously deploying new client case studies. Please explore our other categories or contact our team.</p>
                <button type="button" class="btn btn-primary btn-sm" onclick="filterProjects('all', document.getElementById('filter-all'))" style="background:#03228f; color:#fff; border:none; padding:8px 20px; border-radius:20px; font-weight:700; cursor:pointer;">
                    View All Projects
                </button>
            </div>
        </div>

    </div>
</section>

<!-- REUSABLE CTA BANNER (CONNECTED TO DATABASE SETTINGS) -->
<?php include __DIR__ . '/../../../includes/components/cta_banner.php'; ?>

<script>
/**
 * Dynamic Client-Side Category Filtering
 * Smoothly toggles display of project cards matching selected category
 */
function filterProjects(cat, btn) {
    // 1. Update active button state
    document.querySelectorAll('.cdm-filter-btn').forEach(function(b) {
        b.classList.remove('active');
        b.setAttribute('aria-pressed', 'false');
    });
    if (btn) {
        btn.classList.add('active');
        btn.setAttribute('aria-pressed', 'true');
    }

    // 2. Filter cards
    var cards = document.querySelectorAll('.cdm-project-card');
    var matchCount = 0;

    cards.forEach(function(card) {
        var cardCat = (card.getAttribute('data-category') || '').toLowerCase().trim();
        if (cat === 'all' || cardCat === cat || cardCat.indexOf(cat) !== -1) {
            card.style.display = 'flex';
            matchCount++;
        } else {
            card.style.display = 'none';
        }
    });

    // 3. Toggle empty state
    var emptyState = document.getElementById('cdmEmptyProjects');
    if (emptyState) {
        emptyState.style.display = (matchCount === 0) ? 'block' : 'none';
    }
}
</script>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>
