<?php
/**
 * Blog Listing View - CherriDM Theme
 * Senior Full-Stack Implementation: Responsive Mobile Alignment, Balanced Filter & Search Layout, Clean Card Hierarchy
 */

$site_name = !empty($settings['site_name']) ? $settings['site_name'] : 'Cloud Aspire';
$contact_phone = !empty($settings['contact_phone']) ? $settings['contact_phone'] : '8098830937';

include __DIR__ . '/../../../includes/header.php';
require_once __DIR__ . '/../../../includes/components/page_hero.php';
?>

<!-- CHERRIDM BLOG PAGE STYLES -->
<style>
    /* ==========================================================
       HERO BANNER CUSTOMIZATIONS & SPACING
       ========================================================== */
    .cdm-blog-page-wrap {
        width: 100%;
        overflow-x: hidden;
        box-sizing: border-box;
    }

    /* ==========================================================
       FILTER & SEARCH CARD
       Clean floating card with balanced margins
       ========================================================== */
    .cdm-blog-filter-bar {
        max-width: 1240px;
        margin: -25px auto 35px;
        padding: 0 20px;
        position: relative;
        z-index: 10;
        width: 100%;
        box-sizing: border-box;
    }

    .cdm-filter-inner {
        background: #ffffff;
        border-radius: 14px;
        box-shadow: 0 10px 30px rgba(3, 34, 143, 0.08);
        border: 1px solid #e2e8f0;
        padding: 16px 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        flex-wrap: wrap;
        box-sizing: border-box;
    }

    /* Category Filter Pills */
    .cdm-category-pills {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        flex: 1;
        min-width: 260px;
    }

    .cdm-cat-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 18px;
        border-radius: 25px;
        font-size: 13px;
        font-weight: 700;
        color: #334155;
        background: #f8faff;
        border: 1.5px solid #dbeafe;
        text-decoration: none;
        transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        white-space: nowrap;
        min-height: 38px;
        box-sizing: border-box;
        line-height: 1.3;
        user-select: none;
        -webkit-tap-highlight-color: transparent;
    }

    .cdm-cat-pill:hover {
        background: #ffffff;
        color: #03228f;
        border-color: #03228f;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(3, 34, 143, 0.1);
    }

    .cdm-cat-pill.active {
        background: #03228f;
        color: #ffffff;
        border-color: #03228f;
        box-shadow: 0 4px 14px rgba(3, 34, 143, 0.25);
        transform: translateY(-1px);
    }

    .cdm-cat-pill .count-badge {
        font-size: 11px;
        font-weight: 700;
        opacity: 0.75;
    }

    .cdm-cat-pill.active .count-badge {
        color: #fb9a27;
        opacity: 1;
    }

    /* Search Bar with Centered Icon inside Input */
    .cdm-search-form {
        position: relative;
        display: flex;
        align-items: center;
        min-width: 260px;
        max-width: 320px;
        width: 100%;
        box-sizing: border-box;
    }

    .cdm-search-input {
        width: 100%;
        height: 42px;
        padding: 0 46px 0 16px;
        border-radius: 25px;
        border: 1.5px solid #cbd5e1;
        font-size: 13.5px;
        outline: none;
        font-family: inherit;
        background: #ffffff;
        box-sizing: border-box;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .cdm-search-input:focus {
        border-color: #03228f;
        box-shadow: 0 0 0 3px rgba(3, 34, 143, 0.08);
    }

    .cdm-search-btn {
        position: absolute;
        right: 4px;
        top: 50%;
        transform: translateY(-50%);
        background: #fb9a27;
        color: #ffffff;
        border: none;
        border-radius: 50%;
        width: 34px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        padding: 0;
        transition: background 0.2s ease, transform 0.2s ease;
        box-shadow: 0 2px 6px rgba(251, 154, 39, 0.3);
    }

    .cdm-search-btn i {
        font-size: 13px;
        line-height: 1;
        margin: 0;
    }

    .cdm-search-btn:hover {
        background: #e08316;
        transform: translateY(-50%) scale(1.05);
    }

    /* ==========================================================
       SECTION HEADING & DIVIDER
       ========================================================== */
    .cdm-blog-section {
        padding: 15px 0 75px;
        width: 100%;
        box-sizing: border-box;
    }

    .cdm-heading-center {
        text-align: center;
        margin-bottom: 35px;
        padding: 0 16px;
    }

    .cdm-heading-center .sub-title {
        color: #fb9a27;
        font-weight: 800;
        text-transform: uppercase;
        font-size: 12px;
        letter-spacing: 1.5px;
        display: block;
        margin-bottom: 6px;
    }

    .cdm-heading-center h2 {
        font-size: clamp(1.8rem, 3.5vw, 2.5rem);
        font-weight: 800;
        color: #03228f;
        line-height: 1.25;
        margin: 0 0 10px;
    }

    .cdm-heading-center h2 span.stroke {
        color: #fb9a27;
    }

    .cdm-heading-center .divider {
        width: 50px;
        height: 3px;
        background: #fb9a27;
        margin: 0 auto;
        border-radius: 2px;
    }

    /* ==========================================================
       3-COLUMN BLOG CARD GRID
       ========================================================== */
    .cdm-blog-grid {
        max-width: 1240px;
        margin: 0 auto;
        padding: 0 20px;
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 28px;
        width: 100%;
        box-sizing: border-box;
        align-items: stretch;
    }

    .cdm-card-style10 {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-bottom: 3.5px solid transparent;
        border-radius: 14px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        box-shadow: 0 8px 24px rgba(3, 34, 143, 0.06);
        transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.35s ease, border-color 0.3s ease;
        position: relative;
        width: 100%;
        box-sizing: border-box;
    }

    .cdm-card-style10:hover {
        transform: translateY(-6px);
        box-shadow: 0 18px 40px rgba(3, 34, 143, 0.12);
        border-bottom-color: #fb9a27;
    }

    /* Card Thumbnail & Date Badge */
    .cdm-card-image-box {
        position: relative;
        width: 100%;
        aspect-ratio: 16 / 10;
        height: auto;
        min-height: 190px;
        overflow: hidden;
        background: #02144a;
        box-sizing: border-box;
    }

    .cdm-card-image-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        display: block;
    }

    .cdm-card-style10:hover .cdm-card-image-box img {
        transform: scale(1.06);
    }

    .cdm-card-date {
        position: absolute;
        top: 12px;
        left: 12px;
        background: rgba(3, 34, 143, 0.92);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        border-radius: 8px;
        padding: 5px 11px;
        text-align: center;
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        z-index: 2;
        display: flex;
        flex-direction: column;
        line-height: 1.2;
    }

    .cdm-card-date .d-month {
        font-size: 11.5px;
        font-weight: 800;
        text-transform: uppercase;
        color: #fb9a27;
        letter-spacing: 0.5px;
    }

    .cdm-card-date .d-year {
        font-size: 11px;
        font-weight: 700;
        color: #ffffff;
    }

    /* Card Body */
    .cdm-card-body {
        padding: 22px 20px 24px;
        display: flex;
        flex-direction: column;
        flex-grow: 1;
        box-sizing: border-box;
    }

    .cdm-card-category {
        margin-bottom: 10px;
    }

    .cdm-card-category span {
        display: inline-block;
        background: rgba(251, 154, 39, 0.12);
        color: #fb9a27;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        padding: 4px 12px;
        border-radius: 20px;
        letter-spacing: 0.5px;
    }

    .cdm-card-title {
        font-size: 17.5px;
        font-weight: 800;
        line-height: 1.4;
        margin: 0 0 10px;
        color: #03228f;
        overflow-wrap: break-word;
        word-break: break-word;
    }

    .cdm-card-title a {
        color: #03228f;
        text-decoration: none;
        transition: color 0.2s ease;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .cdm-card-title a:hover {
        color: #fb9a27;
    }

    .cdm-card-desc {
        font-size: 13.5px;
        color: #64748b;
        line-height: 1.6;
        margin: 0 0 16px;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
        flex-grow: 1;
        overflow-wrap: break-word;
    }

    /* Read More Link Button */
    .cdm-card-link-btn {
        display: inline-flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
        padding-top: 12px;
        border-top: 1px solid #f1f5f9;
        margin-top: auto;
        font-size: 13px;
        font-weight: 800;
        text-transform: uppercase;
        color: #03228f;
        letter-spacing: 0.5px;
        text-decoration: none;
        transition: color 0.25s ease;
        box-sizing: border-box;
    }

    .cdm-card-link-btn .icon-circle {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: rgba(3, 34, 143, 0.08);
        color: #03228f;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        transition: all 0.25s ease;
    }

    .cdm-card-style10:hover .cdm-card-link-btn {
        color: #fb9a27;
    }

    .cdm-card-style10:hover .cdm-card-link-btn .icon-circle {
        background: #fb9a27;
        color: #ffffff;
        transform: translateX(3px);
    }

    /* ==========================================================
       PAGINATION
       ========================================================== */
    .cdm-pagination-wrap {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 8px;
        margin-top: 45px;
        flex-wrap: wrap;
    }

    .cdm-page-link {
        min-width: 40px;
        height: 40px;
        padding: 0 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        background: #ffffff;
        color: #1e293b;
        font-weight: 700;
        font-size: 13.5px;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .cdm-page-link:hover {
        border-color: #03228f;
        color: #03228f;
    }

    .cdm-page-link.active {
        background: #03228f;
        border-color: #03228f;
        color: #ffffff;
    }

    /* ==========================================================
       RESPONSIVE BREAKPOINTS (MOBILE, TABLET, DESKTOP)
       ========================================================== */
    @media (max-width: 1024px) {
        .cdm-blog-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 24px;
        }
        .cdm-blog-filter-bar {
            margin: -20px auto 30px;
        }
    }

    @media (max-width: 640px) {
        .cdm-blog-filter-bar {
            margin: 16px auto 25px;
            padding: 0 16px;
        }
        .cdm-filter-inner {
            flex-direction: column;
            align-items: stretch;
            padding: 16px;
            gap: 14px;
        }
        .cdm-category-pills {
            justify-content: center;
            gap: 6px 8px;
            width: 100%;
        }
        .cdm-cat-pill {
            padding: 6px 14px;
            font-size: 12px;
            min-height: 34px;
        }
        .cdm-search-form {
            max-width: 100%;
            width: 100%;
        }
        .cdm-search-input {
            height: 40px;
            font-size: 13px;
        }
        .cdm-heading-center {
            margin-bottom: 25px;
        }
        .cdm-heading-center h2 {
            font-size: 24px;
        }
        .cdm-blog-section {
            padding: 10px 0 50px;
        }
        .cdm-blog-grid {
            grid-template-columns: 1fr;
            padding: 0 16px;
            gap: 20px;
        }
        .cdm-card-style10 {
            border-radius: 12px;
        }
        .cdm-card-body {
            padding: 18px 16px 20px;
        }
        .cdm-card-title {
            font-size: 16.5px;
            line-height: 1.35;
        }
        .cdm-card-desc {
            font-size: 13px;
            margin-bottom: 14px;
        }
    }
</style>

<div class="cdm-blog-page-wrap">
    <!-- 1. HERO BANNER COMPONENT -->
    <?php
    render_page_hero([
        'title' => 'Our',
        'highlight' => 'Blog',
        'subtitle' => 'Explore expert digital marketing tips, SEO strategies, PPC benchmarks, and growth insights from Pondicherry\'s leading digital agency.',
        'breadcrumb_active' => 'Our Blog',
        'base_url' => $base_url
    ]);
    ?>

    <!-- 2. FILTER & SEARCH STRIP -->
    <div class="cdm-blog-filter-bar">
        <div class="cdm-filter-inner">
            <!-- Category Filter Pills -->
            <nav class="cdm-category-pills" aria-label="Blog Categories">
                <a href="<?= $base_url ?>/blog" class="cdm-cat-pill <?= empty($selected_category) ? 'active' : '' ?>">
                    <span>All Articles</span>
                </a>
                <?php foreach ($categories as $cat): ?>
                <a href="<?= $base_url ?>/blog?category=<?= urlencode($cat['category']) ?>" class="cdm-cat-pill <?= ($selected_category === $cat['category']) ? 'active' : '' ?>">
                    <span><?= htmlspecialchars($cat['category']) ?></span>
                    <span class="count-badge">(<?= $cat['count'] ?>)</span>
                </a>
                <?php endforeach; ?>
            </nav>

            <!-- Aligned Search Form -->
            <form action="<?= $base_url ?>/blog" method="GET" class="cdm-search-form" role="search">
                <?php if (!empty($selected_category)): ?>
                <input type="hidden" name="category" value="<?= htmlspecialchars($selected_category) ?>">
                <?php endif; ?>
                <input type="text" name="q" class="cdm-search-input" placeholder="Search marketing articles..." value="<?= htmlspecialchars($search_query) ?>" aria-label="Search marketing articles">
                <button type="submit" class="cdm-search-btn" aria-label="Submit Search">
                    <i class="fas fa-search" aria-hidden="true"></i>
                </button>
            </form>
        </div>
    </div>

    <!-- 3. BLOG GRID (CherriDM card-style10) -->
    <section class="cdm-blog-section">
        <div class="container" style="max-width: 1240px; margin: 0 auto; box-sizing: border-box;">
            
            <div class="cdm-heading-center">
                <span class="sub-title">// Proven Insights & Tactical Strategy //</span>
                <h2>Latest <span class="stroke">Blog</span></h2>
                <div class="divider"></div>
            </div>

            <?php if (!empty($blogs)): ?>
            <div class="cdm-blog-grid">
                <?php 
                $stock_blog_images = [
                    'https://images.unsplash.com/photo-1497215728101-856f4ea42174?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1551836022-d5d88e9218df?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1571786256017-aee7a0c009b6?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1507238691740-187a5b1d37b8?auto=format&fit=crop&w=800&q=80'
                ];

                foreach ($blogs as $idx => $b): 
                    $b_img = !empty($b['image']) ? $b['image'] : $stock_blog_images[$idx % count($stock_blog_images)];
                    if (!str_starts_with($b_img, 'http')) {
                        $b_img = $base_url . '/' . ltrim($b_img, '/');
                    }

                    // Format Date badge (e.g. Jul-15 / 2025)
                    $d_timestamp = !empty($b['published_date']) ? strtotime($b['published_date']) : time();
                    $d_month = date('M-d', $d_timestamp);
                    $d_year = date('Y', $d_timestamp);
                    $b_url = $base_url . '/blog/' . htmlspecialchars($b['slug']);
                ?>
                <article class="cdm-card-style10 cdm-reveal">
                    <!-- Image Box with Date Badge -->
                    <div class="cdm-card-image-box">
                        <img src="<?= htmlspecialchars($b_img) ?>" alt="<?= htmlspecialchars($b['title']) ?>" loading="lazy" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=800&q=80';">
                        <div class="cdm-card-date">
                            <span class="d-month"><?= htmlspecialchars($d_month) ?></span>
                            <span class="d-year"><?= htmlspecialchars($d_year) ?></span>
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div class="cdm-card-body">
                        <div class="cdm-card-category">
                            <span><?= htmlspecialchars($b['category'] ?? 'Digital Marketing') ?></span>
                        </div>

                        <h3 class="cdm-card-title">
                            <a href="<?= $b_url ?>"><?= htmlspecialchars($b['title']) ?></a>
                        </h3>

                        <p class="cdm-card-desc">
                            <?= htmlspecialchars($b['excerpt'] ?? 'Discover key industry tips, execution benchmarks, and strategic methodologies to grow and scale your online market share.') ?>
                        </p>

                        <a href="<?= $b_url ?>" class="cdm-card-link-btn">
                            <span class="link-text">Read More</span>
                            <span class="icon-circle" aria-hidden="true">
                                <i class="fas fa-arrow-right"></i>
                            </span>
                        </a>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>

            <!-- 4. PAGINATION -->
            <?php if ($total_pages > 1): ?>
            <nav class="cdm-pagination-wrap" aria-label="Blog Pagination">
                <?php if ($page > 1): ?>
                <a href="<?= $base_url ?>/blog?page=<?= $page - 1 ?><?= !empty($selected_category) ? '&category='.urlencode($selected_category) : '' ?><?= !empty($search_query) ? '&q='.urlencode($search_query) : '' ?>" class="cdm-page-link" aria-label="Previous Page">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <?php endif; ?>

                <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                <a href="<?= $base_url ?>/blog?page=<?= $p ?><?= !empty($selected_category) ? '&category='.urlencode($selected_category) : '' ?><?= !empty($search_query) ? '&q='.urlencode($search_query) : '' ?>" class="cdm-page-link <?= $p === $page ? 'active' : '' ?>">
                    <?= $p ?>
                </a>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                <a href="<?= $base_url ?>/blog?page=<?= $page + 1 ?><?= !empty($selected_category) ? '&category='.urlencode($selected_category) : '' ?><?= !empty($search_query) ? '&q='.urlencode($search_query) : '' ?>" class="cdm-page-link" aria-label="Next Page">
                    <i class="fas fa-arrow-right"></i>
                </a>
                <?php endif; ?>
            </nav>
            <?php endif; ?>

            <?php else: ?>
            <div style="text-align: center; padding: 60px 20px; background: #ffffff; border-radius: 14px; border: 1px dashed #cbd5e1; max-width: 600px; margin: 0 auto;">
                <i class="fas fa-newspaper" style="font-size: 46px; color: #03228f; margin-bottom: 16px;"></i>
                <h3 style="color: #03228f; font-size: 20px; font-weight: 800; margin-bottom: 8px;">No Articles Found</h3>
                <p style="color: #64748b; font-size: 14px; margin-bottom: 22px;">No articles match your current search query or category filter.</p>
                <a href="<?= $base_url ?>/blog" class="cdm-cat-pill active" style="display: inline-flex; padding: 10px 24px; font-size: 13.5px;">View All Articles</a>
            </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<!-- REUSABLE CTA BANNER (CONNECTED TO DATABASE SETTINGS) -->
<?php include __DIR__ . '/../../../includes/components/cta_banner.php'; ?>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>
