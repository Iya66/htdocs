<?php
/**
 * Single Blog Post Detail View - CherriDM Aesthetic
 * 100% Dynamically Connected to Database & Admin Panel
 */

$site_name = !empty($settings['site_name']) ? $settings['site_name'] : 'Cloud Aspire';
$contact_phone = !empty($settings['contact_phone']) ? $settings['contact_phone'] : '8098830937';
$contact_email = !empty($settings['contact_email']) ? $settings['contact_email'] : 'cloudaspire2025@gmail.com';
$contact_whatsapp = !empty($settings['contact_whatsapp']) ? $settings['contact_whatsapp'] : '8098830937';

// Blog attributes
$b_title = $blog['title'] ?? 'Blog Article';
$b_slug = $blog['slug'] ?? '';
$b_category = $blog['category'] ?? 'Digital Marketing';
$b_author = $blog['author'] ?? 'Cloud Aspire Team';
$b_date = !empty($blog['published_date']) ? date('M d, Y', strtotime($blog['published_date'])) : date('M d, Y');
$b_content = $blog['content'] ?? ($blog['excerpt'] ?? '');
$b_views = (int)($blog['views_count'] ?? 0);

$b_img = !empty($blog['image']) ? $blog['image'] : 'https://images.unsplash.com/photo-1499750310107-5fef28a66643?auto=format&fit=crop&w=1200&q=80';
if (!str_starts_with($b_img, 'http')) {
    $b_img = $base_url . '/' . ltrim($b_img, '/');
}

// Calculate estimated read time
$word_count = str_word_count(strip_tags($b_content));
$read_time = max(1, ceil($word_count / 200));

include __DIR__ . '/../../../includes/header.php';
?>

<!-- SCHEMA.ORG BLOGPOSTING STRUCTURED DATA -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "BlogPosting",
  "headline": <?= json_encode($b_title) ?>,
  "image": [<?= json_encode($b_img) ?>],
  "datePublished": <?= json_encode($blog['published_date'] ?? date('Y-m-d')) ?>,
  "dateModified": <?= json_encode(substr($blog['updated_at'] ?? date('Y-m-d'), 0, 10)) ?>,
  "author": {
    "@type": "Organization",
    "name": <?= json_encode($b_author) ?>
  },
  "publisher": {
    "@type": "Organization",
    "name": <?= json_encode($site_name) ?>,
    "logo": {
      "@type": "ImageObject",
      "url": <?= json_encode($base_url . '/uploads/site_favicon_1783239900.png') ?>
    }
  },
  "description": <?= json_encode($pageDesc) ?>,
  "mainEntityOfPage": {
    "@type": "WebPage",
    "@id": <?= json_encode($canonicalUrl) ?>
  }
}
</script>

<style>
    /* Hero Banner */
    .cdm-post-hero {
        background: linear-gradient(135deg, rgba(2, 20, 74, 0.95) 0%, rgba(3, 34, 143, 0.92) 100%),
                    url('<?= htmlspecialchars($b_img) ?>') center/cover;
        padding: 95px 20px 85px;
        text-align: center;
        color: #ffffff;
        position: relative;
    }
    .cdm-post-hero-badge {
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
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .cdm-post-hero h1 {
        font-size: clamp(2rem, 3.8vw, 3.2rem);
        font-weight: 800;
        color: #ffffff;
        max-width: 950px;
        margin: 0 auto 18px;
        line-height: 1.3;
        letter-spacing: -0.5px;
    }
    .cdm-post-breadcrumbs {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(255, 255, 255, 0.12);
        padding: 7px 22px;
        border-radius: 30px;
        font-size: 13px;
        font-weight: 600;
    }
    .cdm-post-breadcrumbs a {
        color: #ffffff;
        transition: color 0.2s ease;
    }
    .cdm-post-breadcrumbs a:hover {
        color: #fb9a27;
    }
    .cdm-post-breadcrumbs span.sep {
        color: rgba(255, 255, 255, 0.5);
    }
    .cdm-post-breadcrumbs span.active {
        color: #fb9a27;
        max-width: 250px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Layout */
    .cdm-post-layout {
        max-width: 1240px;
        margin: 0 auto;
        padding: 80px 20px 100px;
        display: grid;
        grid-template-columns: 1fr 360px;
        gap: 45px;
        align-items: start;
    }

    /* Main Article */
    .cdm-post-main {
        min-width: 0;
        background: #ffffff;
        border-radius: 14px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 15px 35px rgba(3, 34, 143, 0.06);
        overflow: hidden;
    }
    .cdm-post-featured-image {
        width: 100%;
        height: 420px;
        object-fit: cover;
        display: block;
    }
    .cdm-post-inner {
        padding: 40px 38px;
    }
    .cdm-post-meta-strip {
        display: flex;
        align-items: center;
        gap: 20px;
        padding-bottom: 24px;
        margin-bottom: 28px;
        border-bottom: 1px solid #e2e8f0;
        font-size: 13.5px;
        color: #64748b;
        flex-wrap: wrap;
    }
    .cdm-post-meta-strip span {
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .cdm-post-meta-strip i {
        color: #fb9a27;
    }

    /* Content Typography */
    .cdm-post-body {
        font-size: 16px;
        line-height: 1.85;
        color: #334155;
    }
    .cdm-post-body h2 {
        font-size: clamp(1.6rem, 2.8vw, 2.2rem);
        font-weight: 800;
        color: #03228f;
        margin: 35px 0 16px;
        line-height: 1.3;
    }
    .cdm-post-body h3 {
        font-size: clamp(1.3rem, 2.3vw, 1.7rem);
        font-weight: 800;
        color: #03228f;
        margin: 28px 0 14px;
        line-height: 1.35;
    }
    .cdm-post-body p {
        margin-bottom: 20px;
        text-align: justify;
    }
    .cdm-post-body ul, .cdm-post-body ol {
        margin: 20px 0 24px 24px;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .cdm-post-body li {
        font-size: 15.5px;
        line-height: 1.7;
    }
    .cdm-post-body blockquote {
        background: #f8faff;
        border-left: 4px solid #fb9a27;
        padding: 20px 24px;
        border-radius: 0 10px 10px 0;
        margin: 30px 0;
        font-style: italic;
        color: #03228f;
        font-weight: 600;
        font-size: 16.5px;
    }

    /* Social Share Strip */
    .cdm-share-strip {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 24px 0;
        margin-top: 40px;
        border-top: 1px solid #e2e8f0;
        border-bottom: 1px solid #e2e8f0;
        flex-wrap: wrap;
        gap: 15px;
    }
    .cdm-share-label {
        font-weight: 700;
        font-size: 14px;
        color: #03228f;
    }
    .cdm-share-btns {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .cdm-share-btn {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #ffffff;
        font-size: 15px;
        transition: transform 0.2s ease;
    }
    .cdm-share-btn:hover {
        transform: translateY(-3px);
        color: #ffffff;
    }
    .cdm-share-btn.wa { background: #25d366; }
    .cdm-share-btn.fb { background: #1877f2; }
    .cdm-share-btn.tw { background: #1da1f2; }
    .cdm-share-btn.li { background: #0077b5; }

    /* Sticky Sidebar */
    .cdm-post-sidebar {
        position: sticky;
        top: 95px;
        display: flex;
        flex-direction: column;
        gap: 30px;
    }
    .cdm-widget-card {
        background: #ffffff;
        border-radius: 14px;
        border: 1px solid #e2e8f0;
        padding: 28px 24px;
        box-shadow: 0 12px 30px rgba(3, 34, 143, 0.06);
    }
    .cdm-widget-title {
        font-size: 18px;
        font-weight: 800;
        color: #03228f;
        margin-bottom: 18px;
        padding-bottom: 12px;
        border-bottom: 2px solid #e9eeff;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    /* Recent Posts List */
    .cdm-recent-list {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }
    .cdm-recent-item {
        display: flex;
        gap: 14px;
        align-items: center;
    }
    .cdm-recent-thumb {
        width: 75px;
        height: 65px;
        border-radius: 8px;
        overflow: hidden;
        flex-shrink: 0;
        background: #03228f;
    }
    .cdm-recent-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .cdm-recent-info h5 {
        font-size: 13.5px;
        font-weight: 700;
        line-height: 1.4;
        margin-bottom: 4px;
    }
    .cdm-recent-info h5 a {
        color: #1e293b;
        transition: color 0.2s ease;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .cdm-recent-info h5 a:hover {
        color: #fb9a27;
    }
    .cdm-recent-date {
        font-size: 11.5px;
        color: #94a3b8;
    }

    /* Category List */
    .cdm-widget-cat-list {
        list-style: none;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .cdm-widget-cat-list a {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 14px;
        border-radius: 8px;
        background: #f8faff;
        color: #334155;
        font-size: 13.5px;
        font-weight: 600;
        border: 1px solid #e2e8f0;
        transition: all 0.2s ease;
    }
    .cdm-widget-cat-list a:hover {
        background: #03228f;
        color: #ffffff;
        border-color: #03228f;
    }
    .cdm-widget-cat-list span.count {
        font-size: 11px;
        background: #e2e8f0;
        color: #03228f;
        padding: 2px 8px;
        border-radius: 12px;
    }
    .cdm-widget-cat-list a:hover span.count {
        background: #fb9a27;
        color: #ffffff;
    }

    @media (max-width: 992px) {
        .cdm-post-layout {
            grid-template-columns: 1fr;
            gap: 50px;
        }
        .cdm-post-sidebar {
            position: static;
        }
    }
    @media (max-width: 640px) {
        .cdm-post-featured-image {
            height: 250px;
        }
        .cdm-post-inner {
            padding: 28px 20px;
        }
    }
</style>

<!-- 1. HERO BANNER -->
<section class="cdm-post-hero">
    <div class="container">
        <div class="cdm-post-hero-badge">
            <i class="fas fa-tag"></i> <?= htmlspecialchars($b_category) ?>
        </div>
        <h1><?= htmlspecialchars($b_title) ?></h1>
        <div class="cdm-post-breadcrumbs">
            <a href="<?= $base_url ?>/">Home</a>
            <span class="sep">/</span>
            <a href="<?= $base_url ?>/blog">Our Blog</a>
            <span class="sep">/</span>
            <span class="active"><?= htmlspecialchars($b_title) ?></span>
        </div>
    </div>
</section>

<!-- 2. ARTICLE & SIDEBAR LAYOUT -->
<div class="cdm-post-layout">
    
    <!-- LEFT: ARTICLE CONTENT -->
    <main class="cdm-post-main cdm-reveal">
        <img src="<?= htmlspecialchars($b_img) ?>" alt="<?= htmlspecialchars($b_title) ?>" class="cdm-post-featured-image" loading="lazy">
        
        <div class="cdm-post-inner">
            <!-- Meta Info Strip -->
            <div class="cdm-post-meta-strip">
                <span><i class="fas fa-user-circle"></i> By <?= htmlspecialchars($b_author) ?></span>
                <span><i class="fas fa-calendar-alt"></i> <?= htmlspecialchars($b_date) ?></span>
                <span><i class="fas fa-clock"></i> <?= $read_time ?> min read</span>
                <span><i class="fas fa-eye"></i> <?= number_format($b_views) ?> views</span>
            </div>

            <!-- Content Body -->
            <article class="cdm-post-body">
                <?= $b_content ?>
            </article>

            <!-- Social Sharing Strip -->
            <div class="cdm-share-strip">
                <div class="cdm-share-label">
                    <i class="fas fa-share-nodes" style="color: #fb9a27; margin-right: 6px;"></i> Share this article:
                </div>
                <div class="cdm-share-btns">
                    <a href="https://api.whatsapp.com/send?text=<?= urlencode($b_title . ' - ' . $canonicalUrl) ?>" target="_blank" rel="noopener" class="cdm-share-btn wa" title="Share on WhatsApp" aria-label="Share on WhatsApp">
                        <i class="fab fa-whatsapp"></i>
                    </a>
                    <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= urlencode($canonicalUrl) ?>" target="_blank" rel="noopener" class="cdm-share-btn li" title="Share on LinkedIn" aria-label="Share on LinkedIn">
                        <i class="fab fa-linkedin-in"></i>
                    </a>
                    <a href="https://twitter.com/intent/tweet?text=<?= urlencode($b_title) ?>&url=<?= urlencode($canonicalUrl) ?>" target="_blank" rel="noopener" class="cdm-share-btn tw" title="Share on Twitter" aria-label="Share on Twitter">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($canonicalUrl) ?>" target="_blank" rel="noopener" class="cdm-share-btn fb" title="Share on Facebook" aria-label="Share on Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                </div>
            </div>
        </div>
    </main>

    <!-- RIGHT: STICKY SIDEBAR -->
    <aside class="cdm-post-sidebar">

        <!-- 1. Search Box -->
        <div class="cdm-widget-card cdm-reveal">
            <h4 class="cdm-widget-title"><i class="fas fa-search" style="color: #fb9a27;"></i> Search Blog</h4>
            <form action="<?= $base_url ?>/blog" method="GET" style="display: flex; gap: 8px;">
                <input type="text" name="q" placeholder="Type keyword..." style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; outline: none;">
                <button type="submit" style="background: #03228f; color: #ffffff; border: none; padding: 0 16px; border-radius: 6px; cursor: pointer;">
                    <i class="fas fa-arrow-right"></i>
                </button>
            </form>
        </div>

        <!-- 2. Recent Posts Widget -->
        <?php if (!empty($recent_blogs)): ?>
        <div class="cdm-widget-card cdm-reveal">
            <h4 class="cdm-widget-title"><i class="fas fa-fire" style="color: #fb9a27;"></i> Recent Articles</h4>
            <div class="cdm-recent-list">
                <?php foreach ($recent_blogs as $rec): 
                    $r_img = !empty($rec['image']) ? $rec['image'] : 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=200&q=80';
                    if (!str_starts_with($r_img, 'http')) {
                        $r_img = $base_url . '/' . ltrim($r_img, '/');
                    }
                    $r_date = !empty($rec['published_date']) ? date('M d, Y', strtotime($rec['published_date'])) : '';
                ?>
                <div class="cdm-recent-item">
                    <div class="cdm-recent-thumb">
                        <img src="<?= htmlspecialchars($r_img) ?>" alt="<?= htmlspecialchars($rec['title']) ?>" loading="lazy">
                    </div>
                    <div class="cdm-recent-info">
                        <h5><a href="<?= $base_url ?>/blog/<?= htmlspecialchars($rec['slug']) ?>"><?= htmlspecialchars($rec['title']) ?></a></h5>
                        <span class="cdm-recent-date"><i class="far fa-calendar-alt"></i> <?= htmlspecialchars($r_date) ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- 3. Categories Widget -->
        <?php if (!empty($categories)): ?>
        <div class="cdm-widget-card cdm-reveal">
            <h4 class="cdm-widget-title"><i class="fas fa-folder-open" style="color: #fb9a27;"></i> Topics</h4>
            <ul class="cdm-widget-cat-list">
                <?php foreach ($categories as $cat): ?>
                <li>
                    <a href="<?= $base_url ?>/blog?category=<?= urlencode($cat['category']) ?>">
                        <span><?= htmlspecialchars($cat['category']) ?></span>
                        <span class="count"><?= $cat['count'] ?></span>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <!-- 4. Quick Strategy Audit Form Widget -->
        <div class="cdm-widget-card cdm-reveal" style="background: linear-gradient(135deg, #02144a 0%, #03228f 100%); color: #ffffff;">
            <h4 style="font-size: 18px; font-weight: 800; color: #ffffff; margin-bottom: 8px;">Need Custom Strategy?</h4>
            <p style="font-size: 13px; color: rgba(255,255,255,0.85); margin-bottom: 18px;">Get a personalized growth roadmap for your business.</p>
            <button type="button" class="cdm-btn-quote" style="width: 100%; justify-content: center;" onclick="openContactPopup()">
                <span>Claim Free Consultation</span>
                <i class="fas fa-arrow-right"></i>
            </button>
        </div>

    </aside>

</div>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>
