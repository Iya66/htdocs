<?php
/**
 * Blog Listing View - CherriDM Aesthetic
 * 100% Dynamically Connected to Database & Admin Panel
 * Formatted directly after: https://www.cherridm.com/blog.php
 */

$site_name = !empty($settings['site_name']) ? $settings['site_name'] : 'Cloud Aspire';
$contact_phone = !empty($settings['contact_phone']) ? $settings['contact_phone'] : '8098830937';

include __DIR__ . '/../../../includes/header.php';
?>

<!-- CHERRIDM BLOG PAGE STYLES -->
<style>
    /* Hero Section */
    .cdm-blog-hero {
        background: linear-gradient(135deg, rgba(2, 20, 74, 0.95) 0%, rgba(3, 34, 143, 0.92) 100%),
                    url('https://images.unsplash.com/photo-1499750310107-5fef28a66643?auto=format&fit=crop&w=1600&q=80') center/cover;
        padding: 95px 20px 85px;
        text-align: center;
        color: #ffffff;
        position: relative;
    }
    .cdm-blog-hero h1 {
        font-size: clamp(2.4rem, 4.5vw, 3.6rem);
        font-weight: 800;
        color: #ffffff;
        margin-bottom: 14px;
        letter-spacing: -0.5px;
    }
    .cdm-blog-hero h1 span.stroke {
        color: #fb9a27;
    }
    .cdm-blog-hero p {
        font-size: 16px;
        color: rgba(255, 255, 255, 0.9);
        max-width: 680px;
        margin: 0 auto 24px;
        line-height: 1.6;
    }
    .cdm-blog-breadcrumbs {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(255, 255, 255, 0.12);
        padding: 7px 22px;
        border-radius: 30px;
        font-size: 13px;
        font-weight: 600;
    }
    .cdm-blog-breadcrumbs a {
        color: #ffffff;
        transition: color 0.2s ease;
    }
    .cdm-blog-breadcrumbs a:hover {
        color: #fb9a27;
    }
    .cdm-blog-breadcrumbs span.sep {
        color: rgba(255, 255, 255, 0.5);
    }
    .cdm-blog-breadcrumbs span.active {
        color: #fb9a27;
    }

    /* Filter & Search Bar */
    .cdm-blog-filter-bar {
        max-width: 1240px;
        margin: -30px auto 40px;
        padding: 0 20px;
        position: relative;
        z-index: 10;
    }
    .cdm-filter-inner {
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 15px 35px rgba(3, 34, 143, 0.08);
        border: 1px solid #e2e8f0;
        padding: 16px 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        flex-wrap: wrap;
    }
    .cdm-category-pills {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }
    .cdm-cat-pill {
        display: inline-flex;
        align-items: center;
        padding: 8px 18px;
        border-radius: 30px;
        font-size: 13px;
        font-weight: 700;
        color: #475569;
        background: #f1f5f9;
        transition: all 0.25s ease;
    }
    .cdm-cat-pill:hover {
        background: #e2e8f0;
        color: #03228f;
    }
    .cdm-cat-pill.active {
        background: #03228f;
        color: #ffffff;
    }
    .cdm-search-form {
        display: flex;
        align-items: center;
        gap: 8px;
        min-width: 260px;
    }
    .cdm-search-input {
        width: 100%;
        padding: 9px 16px;
        border-radius: 30px;
        border: 1px solid #cbd5e1;
        font-size: 13px;
        outline: none;
        font-family: inherit;
        transition: border-color 0.2s ease;
    }
    .cdm-search-input:focus {
        border-color: #03228f;
    }
    .cdm-search-btn {
        background: #fb9a27;
        color: #ffffff;
        border: none;
        border-radius: 50%;
        width: 38px;
        height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        flex-shrink: 0;
        transition: background 0.2s ease, transform 0.2s ease;
    }
    .cdm-search-btn:hover {
        background: #e08316;
        transform: scale(1.05);
    }

    /* CherriDM Section Heading */
    .cdm-heading-center {
        text-align: center;
        margin-bottom: 45px;
    }
    .cdm-heading-center .sub-title {
        color: #fb9a27;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 13px;
        letter-spacing: 1.5px;
        display: block;
        margin-bottom: 8px;
    }
    .cdm-heading-center h2 {
        font-size: clamp(2rem, 3.5vw, 2.7rem);
        font-weight: 800;
        color: #03228f;
        line-height: 1.25;
        margin-bottom: 14px;
    }
    .cdm-heading-center .divider {
        width: 60px;
        height: 3px;
        background: #fb9a27;
        margin: 0 auto;
        border-radius: 2px;
    }

    /* 3-Column Card Grid (card-style10 CherriDM) */
    .cdm-blog-section {
        padding: 40px 0 90px;
    }
    .cdm-blog-grid {
        max-width: 1240px;
        margin: 0 auto;
        padding: 0 20px;
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 30px;
    }
    .cdm-card-style10 {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        box-shadow: 0 10px 30px rgba(3, 34, 143, 0.06);
        transition: all 0.35s ease;
        position: relative;
    }
    .cdm-card-style10:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 45px rgba(3, 34, 143, 0.12);
        border-color: #cbd5e1;
    }
    .cdm-card-image-box {
        position: relative;
        height: 230px;
        overflow: hidden;
        background: #03228f;
    }
    .cdm-card-image-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.6s ease;
        display: block;
    }
    .cdm-card-style10:hover .cdm-card-image-box img {
        transform: scale(1.08);
    }

    /* CherriDM Date Badge Overlay */
    .cdm-card-date {
        position: absolute;
        top: 15px;
        left: 15px;
        background: #03228f;
        border-radius: 8px;
        padding: 6px 12px;
        text-align: center;
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        z-index: 2;
        display: flex;
        flex-direction: column;
        line-height: 1.2;
    }
    .cdm-card-date .d-month {
        font-size: 12px;
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
        padding: 24px 22px 26px;
        display: flex;
        flex-direction: column;
        flex-grow: 1;
    }
    .cdm-card-category {
        margin-bottom: 12px;
    }
    .cdm-card-category span {
        display: inline-block;
        background: rgba(251, 154, 39, 0.12);
        color: #fb9a27;
        font-size: 11.5px;
        font-weight: 800;
        text-transform: uppercase;
        padding: 4px 12px;
        border-radius: 20px;
        letter-spacing: 0.5px;
    }
    .cdm-card-title {
        font-size: 18px;
        font-weight: 800;
        line-height: 1.4;
        margin-bottom: 12px;
        color: #03228f;
    }
    .cdm-card-title a {
        color: #03228f;
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
        line-height: 1.65;
        margin-bottom: 20px;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
        flex-grow: 1;
    }

    /* CherriDM Read More Link Button */
    .cdm-card-link-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        font-weight: 800;
        text-transform: uppercase;
        color: #03228f;
        letter-spacing: 0.5px;
        transition: all 0.25s ease;
        align-self: flex-start;
    }
    .cdm-card-link-btn i {
        font-size: 11px;
        transition: transform 0.25s ease;
        color: #fb9a27;
    }
    .cdm-card-style10:hover .cdm-card-link-btn {
        color: #fb9a27;
    }
    .cdm-card-style10:hover .cdm-card-link-btn i {
        transform: translateX(4px);
    }

    /* Pagination */
    .cdm-pagination-wrap {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 8px;
        margin-top: 50px;
    }
    .cdm-page-link {
        width: 42px;
        height: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        background: #ffffff;
        color: #1e293b;
        font-weight: 700;
        font-size: 14px;
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

    /* Responsive */
    @media (max-width: 992px) {
        .cdm-blog-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    @media (max-width: 640px) {
        .cdm-blog-grid {
            grid-template-columns: 1fr;
        }
        .cdm-filter-inner {
            flex-direction: column;
            align-items: stretch;
        }
        .cdm-search-form {
            width: 100%;
        }
    }
</style>

<!-- 1. PAGE TITLE HERO BANNER -->
<section class="cdm-blog-hero">
    <div class="container">
        <h1>Our <span class="stroke">Blog</span></h1>
        <p>Explore expert digital marketing tips, SEO strategies, PPC benchmarks, and growth insights from Pondicherry's leading digital agency.</p>
        <div class="cdm-blog-breadcrumbs">
            <a href="<?= $base_url ?>/">Home</a>
            <span class="sep">/</span>
            <span class="active">Our Blog</span>
        </div>
    </div>
</section>

<!-- 2. FILTER & SEARCH STRIP -->
<div class="cdm-blog-filter-bar">
    <div class="cdm-filter-inner">
        <!-- Category Filter Pills -->
        <div class="cdm-category-pills">
            <a href="<?= $base_url ?>/blog" class="cdm-cat-pill <?= empty($selected_category) ? 'active' : '' ?>">
                All Articles
            </a>
            <?php foreach ($categories as $cat): ?>
            <a href="<?= $base_url ?>/blog?category=<?= urlencode($cat['category']) ?>" class="cdm-cat-pill <?= ($selected_category === $cat['category']) ? 'active' : '' ?>">
                <?= htmlspecialchars($cat['category']) ?>
                <span style="opacity: 0.6; margin-left: 4px; font-size: 11px;">(<?= $cat['count'] ?>)</span>
            </a>
            <?php endforeach; ?>
        </div>

        <!-- Search Input -->
        <form action="<?= $base_url ?>/blog" method="GET" class="cdm-search-form">
            <?php if (!empty($selected_category)): ?>
            <input type="hidden" name="category" value="<?= htmlspecialchars($selected_category) ?>">
            <?php endif; ?>
            <input type="text" name="q" class="cdm-search-input" placeholder="Search marketing articles..." value="<?= htmlspecialchars($search_query) ?>">
            <button type="submit" class="cdm-search-btn" aria-label="Search">
                <i class="fas fa-search"></i>
            </button>
        </form>
    </div>
</div>

<!-- 3. BLOG GRID (CherriDM card-style10) -->
<section class="cdm-blog-section">
    <div class="container">
        <div class="cdm-heading-center">
            <span class="sub-title">// We Offer a Full Range of Digital Marketing Services //</span>
            <h2>Latest <span class="text-secondary">Blog</span></h2>
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

                // Format Date badge (like CherriDM: Jul / 2025 or Dec-18 / 2024)
                $d_timestamp = !empty($b['published_date']) ? strtotime($b['published_date']) : time();
                $d_month = date('M-d', $d_timestamp);
                $d_year = date('Y', $d_timestamp);
                $b_url = $base_url . '/blog/' . htmlspecialchars($b['slug']);
            ?>
            <article class="cdm-card-style10 cdm-reveal">
                <!-- Image Box with CherriDM Date Badge -->
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
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </article>
            <?php endforeach; ?>
        </div>

        <!-- 4. PAGINATION -->
        <?php if ($total_pages > 1): ?>
        <div class="cdm-pagination-wrap">
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
        </div>
        <?php endif; ?>

        <?php else: ?>
        <div style="text-align: center; padding: 70px 20px;">
            <i class="fas fa-newspaper" style="font-size: 50px; color: #03228f; margin-bottom: 20px;"></i>
            <h3 style="color: #03228f;">No Articles Found</h3>
            <p style="color: #64748b; margin-bottom: 24px;">No posts match your current search or category filter.</p>
            <a href="<?= $base_url ?>/blog" class="cdm-btn-service-cta">View All Articles</a>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- 5. BOTTOM CALLOUT BANNER -->
<section style="background: linear-gradient(135deg, #02144a 0%, #03228f 100%); padding: 75px 20px; color: #ffffff; text-align: center;">
    <div style="max-width: 820px; margin: 0 auto;">
        <span style="color: #fb9a27; font-weight: 700; text-transform: uppercase; font-size: 13px; letter-spacing: 1px; display: block; margin-bottom: 10px;">
            // Ready to Transform Your Business Online? //
        </span>
        <h3 style="font-size: clamp(1.8rem, 3.5vw, 2.5rem); font-weight: 800; margin-bottom: 14px;">Want These Same Strategies Applied to Your Brand?</h3>
        <p style="font-size: 16px; color: rgba(255,255,255,0.85); margin-bottom: 28px; line-height: 1.6;">
            Speak directly with our senior growth strategists in Pondicherry for a complimentary audit and revenue roadmap.
        </p>
        <button type="button" class="cdm-btn-quote" style="display: inline-flex;" onclick="openContactPopup()">
            <span>Request Free Strategy Consultation</span>
            <i class="fas fa-arrow-right"></i>
        </button>
    </div>
</section>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>
