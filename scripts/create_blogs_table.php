<?php
/**
 * Database Migration: Blogs Table & Seeding
 */
require_once __DIR__ . '/../app/database.php';

try {
    $conn->exec("
    CREATE TABLE IF NOT EXISTS `blogs` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `title` varchar(255) NOT NULL,
      `slug` varchar(255) NOT NULL,
      `category` varchar(100) DEFAULT 'Digital Marketing',
      `author` varchar(100) DEFAULT 'Cloud Aspire Team',
      `image` varchar(500) DEFAULT NULL,
      `excerpt` text DEFAULT NULL,
      `content` longtext DEFAULT NULL,
      `published_date` date DEFAULT NULL,
      `status` enum('published','draft') DEFAULT 'published',
      `views_count` int(11) DEFAULT 0,
      `seo_title` varchar(255) DEFAULT NULL,
      `meta_description` text DEFAULT NULL,
      `meta_keywords` varchar(255) DEFAULT NULL,
      `created_at` timestamp NULL DEFAULT current_timestamp(),
      `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
      PRIMARY KEY (`id`),
      UNIQUE KEY `idx_blog_slug` (`slug`),
      KEY `idx_blog_category` (`category`),
      KEY `idx_blog_status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "Blogs table created successfully.\n";

    // Initial Blog Posts matching CherriDM format
    $sample_posts = [
        [
            'title' => 'Local vs National Digital Marketing Agencies – Which Is Right for You?',
            'slug' => 'local-vs-national-digital-marketing-agencies',
            'category' => 'Marketing Strategy',
            'author' => 'Cloud Aspire Team',
            'image' => 'https://images.unsplash.com/photo-1497215728101-856f4ea42174?auto=format&fit=crop&w=800&q=80',
            'excerpt' => 'Choosing the right digital marketing partner can define the success or stagnation of your business in the digital age. Discover whether a local specialist or national enterprise agency aligns best with your growth objectives.',
            'content' => '<p>Choosing the right digital marketing partner can define the success or stagnation of your business in the digital age. If you are a business owner, marketing lead, or startup founder on the cusp of hiring a new agency, understanding the key structural differences between local and national digital marketing agencies is essential.</p>
<h3>Understanding the Difference: Local vs National Agency</h3>
<p>Local digital marketing agencies specialize in localized search engine optimization, regional audience sentiment, and geo-targeted social advertising. They understand the hyper-local nuances of Pondicherry and surrounding markets, offering direct face-to-face strategic consultations.</p>
<p>On the other hand, national agencies typically operate with vast, multi-disciplinary teams managing broad scale campaigns across diverse geographies, but may lack the agile responsiveness and local market familiarity that high-growth businesses require.</p>
<h3>Key Evaluation Criteria</h3>
<ul>
<li><strong>Hyper-Local Market Knowledge:</strong> Local agencies understand nearby consumer behavior, regional search intent, and community landmarks.</li>
<li><strong>Agile Communication:</strong> Direct access to senior marketing architects rather than junior account managers.</li>
<li><strong>Cost Efficiency:</strong> Local specialists frequently provide transparent, performance-driven packages without unnecessary corporate overhead.</li>
</ul>
<p>For most businesses targeting sustained local dominance and regional customer acquisition, partnering with a proven local digital marketing firm delivers superior ROI and compounded growth.</p>',
            'published_date' => '2025-07-15',
            'seo_title' => 'Local vs National Digital Marketing Agencies - Growth Guide',
            'meta_description' => 'Explore the key differences between local and national digital marketing agencies and discover which partner delivers faster ROI for your business.',
            'meta_keywords' => 'local vs national digital marketing agencies, digital marketing agency pondicherry, hire marketing agency'
        ],
        [
            'title' => 'SEO vs Google Ads: Which One Delivers Faster ROI for Your Business?',
            'slug' => 'seo-vs-google-ads-faster-roi',
            'category' => 'SEO Services',
            'author' => 'SEO Division',
            'image' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=800&q=80',
            'excerpt' => 'In today’s competitive digital landscape, the quest for faster, cost-effective results drives most marketing decisions. Explore whether search engine optimization or pay-per-click Google Ads generates faster returns.',
            'content' => '<p>In today’s competitive digital landscape, the quest for faster, cost-effective results drives most marketing decisions. Whether you are a startup founder, small business owner, or digital strategist, the burning question remains: Which one delivers faster ROI for your business—SEO or Google Ads?</p>
<h3>The Core Fundamentals of Both Channels</h3>
<p>Both channels are vital pillars in the online visibility game, but they operate at different gears:</p>
<ul>
<li><strong>Google Ads (PPC):</strong> Places your brand directly in front of active searchers within 48 to 72 hours of campaign activation. You pay per click, giving you immediate control over traffic volume, keywords, and landing page testing.</li>
<li><strong>SEO (Organic):</strong> A long-term strategic compounding asset. It takes 60 to 90 days of consistent technical optimization, search intent clustering, and link acquisition to gain first-page momentum, but generates high-intent leads round the clock without ongoing per-click fees.</li>
</ul>
<h3>Evaluating ROI Timelines</h3>
<p>If your business needs immediate inquiries this week, Google Ads is the undisputed winner. However, for compounding lifetime enterprise value and sustainable profit margins, pairing high-converting Google Ads with an authoritative organic SEO foundation produces the ultimate growth engine.</p>',
            'published_date' => '2025-07-02',
            'seo_title' => 'SEO vs Google Ads: Which Delivers Faster ROI? | Cloud Aspire',
            'meta_description' => 'Detailed comparison between SEO and Google Ads. Learn how to balance immediate PPC lead generation with long-term organic authority.',
            'meta_keywords' => 'seo vs google ads, faster roi marketing, ppc vs seo pondicherry'
        ],
        [
            'title' => 'Affordable Digital Marketing Packages for Startups & Small Businesses',
            'slug' => 'affordable-digital-marketing-packages-for-startups',
            'category' => 'Startup Growth',
            'author' => 'Growth Architect',
            'image' => 'https://images.unsplash.com/photo-1551836022-d5d88e9218df?auto=format&fit=crop&w=800&q=80',
            'excerpt' => 'In today’s digital-first economy, affordable digital marketing packages aren’t just helpful—they are essential. Learn how small businesses scale without drowning in unnecessary agency overhead.',
            'content' => '<p>In today\'s fiercely competitive digital space, affordable digital marketing packages aren\'t just helpful—they are essential for survival. For many budding entrepreneurs and small businesses, the primary challenge lies in making a meaningful splash online without burning runway on vanity metrics.</p>
<h3>What Should a High-Impact Startup Package Include?</h3>
<ol>
<li><strong>Technical Foundation & Fast Mobile Web:</strong> Core Web Vitals optimization and seamless lead capture forms.</li>
<li><strong>Targeted Search Engine Visibility:</strong> Local SEO optimization for Google Maps 3-Pack and high-intent commercial keywords.</li>
<li><strong>High-Intent Social Advertising:</strong> Micro-targeted Meta Ads (Instagram & Facebook) capturing customer interest with scroll-stopping creatives.</li>
<li><strong>Conversion Tracking:</strong> Accurate measurement of every inquiry, phone call, and WhatsApp message.</li>
</ol>
<p>By focusing on high-ROI channels first, startups can reinvest generated revenues into multi-channel digital scale.</p>',
            'published_date' => '2025-06-20',
            'seo_title' => 'Affordable Digital Marketing Packages for Startups in Pondicherry',
            'meta_description' => 'Discover how budget-friendly digital marketing packages help startups and local businesses achieve rapid customer acquisition.',
            'meta_keywords' => 'digital marketing packages startups, affordable marketing pondicherry'
        ],
        [
            'title' => 'How to Generate Quality Leads: 10 Proven Digital Strategies',
            'slug' => 'how-to-generate-quality-leads',
            'category' => 'Lead Generation',
            'author' => 'Cloud Aspire Team',
            'image' => 'https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&w=800&q=80',
            'excerpt' => 'Generating high-quality leads isn’t just a checkbox in your marketing strategy—it is the lifeblood of business revenue. Explore 10 proven digital tactics to capture high-intent inquiries.',
            'content' => '<p>Generating high-quality leads isn’t just a vanity metric in your marketing strategy—it is the foundational lifeblood of predictable business revenue. Too many businesses generate traffic that bounces without ever taking action.</p>
<h3>10 Actionable Tactics for High-Intent Lead Acquisition:</h3>
<ul>
<li><strong>1. Search-Intent Keyword Clustering:</strong> Target keywords with commercial modifiers like "services in", "near me", and "hire".</li>
<li><strong>2. Frictionless Lead Forms:</strong> Keep form fields under 4 inputs for mobile users.</li>
<li><strong>3. Click-to-WhatsApp Integration:</strong> Instant two-way conversation boosts lead response rates by over 400%.</li>
<li><strong>4. Social Proof & Verified Reviews:</strong> Prominently display Google reviews and client video testimonials near conversion points.</li>
<li><strong>5. Audience Remarketing:</strong> Retarget visitors who spent over 60 seconds on service pages.</li>
</ul>
<p>Implementing these foundational conversion rate optimization (CRO) principles transforms passive website visitors into warm, ready-to-buy inquiries.</p>',
            'published_date' => '2025-05-18',
            'seo_title' => 'How to Generate Quality Leads: 10 Proven Digital Strategies',
            'meta_description' => 'Master high-converting digital lead generation with 10 proven strategies engineered to scale inquiries and lower customer acquisition costs.',
            'meta_keywords' => 'generate quality leads, lead generation tactics, b2b lead generation pondicherry'
        ],
        [
            'title' => 'Top 10 SEO Companies in Pondicherry | Comprehensive 2025 Guide',
            'slug' => 'top-10-seo-companies-in-pondicherry',
            'category' => 'SEO Services',
            'author' => 'Research Team',
            'image' => 'https://images.unsplash.com/photo-1571786256017-aee7a0c009b6?auto=format&fit=crop&w=800&q=80',
            'excerpt' => 'In a vibrant hub like Pondicherry where French heritage meets rapid entrepreneurial expansion, businesses need proven SEO partners. Here is your definitive guide to top SEO firms.',
            'content' => '<p>In a town like Pondicherry where colonial heritage meets a booming digital commercial sector, local businesses are evolving faster than ever. Having a stellar website is no longer sufficient if customers cannot find you when searching for your services.</p>
<h3>Why Local SEO Matters in Pondicherry</h3>
<p>Over 82% of smartphone users conduct "near me" searches when looking for local businesses, clinics, resorts, and professional services. When your business appears in the top 3 spots of Google Local Pack and organic results, you capture the highest-intent market share.</p>
<h3>What Distinguishes Top SEO Agencies:</h3>
<ul>
<li>Transparent monthly rank tracking and conversion reporting.</li>
<li>Zero black-hat shortcuts or private blog networks (PBNs) that risk Google penalties.</li>
<li>Comprehensive technical, on-page, and high-authority outreach capabilities.</li>
</ul>',
            'published_date' => '2025-04-16',
            'seo_title' => 'Top SEO Companies in Pondicherry | Best Picks 2025',
            'meta_description' => 'Explore the leading SEO agencies in Pondicherry and learn how to select a proven search engine optimization partner.',
            'meta_keywords' => 'top seo companies pondicherry, best seo agency pondicherry'
        ],
        [
            'title' => 'How to Increase Domain Authority and Build High-Quality Backlinks',
            'slug' => 'how-to-increase-domain-authority',
            'category' => 'SEO Services',
            'author' => 'SEO Specialist',
            'image' => 'https://images.unsplash.com/photo-1507238691740-187a5b1d37b8?auto=format&fit=crop&w=800&q=80',
            'excerpt' => 'Backlinks remain the primary currency of search engine trust. Learn white-hat methodologies to elevate your domain rating and outrank legacy competitors.',
            'content' => '<p>Backlinks are the backbone of gaining domain authority and search engine trust. While Google’s algorithms have grown increasingly sophisticated with AI and helpful content updates, verified external votes of confidence from authoritative publications remain a top ranking factor.</p>
<h3>White-Hat Strategies to Elevate Domain Authority:</h3>
<ul>
<li><strong>1. Original Research & Industry Data:</strong> Publishing localized industry reports naturally attracts citations from journalists and bloggers.</li>
<li><strong>2. Broken Link Building:</strong> Identify broken resource links on niche authority sites and suggest your updated guide as the replacement.</li>
<li><strong>3. Digital PR & Editorial Features:</strong> Secure features in reputable business journals and local news publications.</li>
<li><strong>4. Comprehensive Pillar Pages:</strong> In-depth pillar content establishes topical authority, signaling expertise to search spiders.</li>
</ul>
<p>Quality always trumps quantity. Five backlinks from authentic, high-traffic industry domains outperform hundreds of low-tier directory submissions.</p>',
            'published_date' => '2024-12-19',
            'seo_title' => 'How to Increase Domain Authority | White-Hat SEO Guide',
            'meta_description' => 'Learn proven white-hat link building and digital PR strategies to increase your website domain authority and organic rankings.',
            'meta_keywords' => 'increase domain authority, link building strategies, white hat backlinks'
        ]
    ];

    $checkStmt = $conn->prepare("SELECT id FROM blogs WHERE slug = ? LIMIT 1");
    $insertStmt = $conn->prepare("
        INSERT INTO blogs (title, slug, category, author, image, excerpt, content, published_date, status, seo_title, meta_description, meta_keywords)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'published', ?, ?, ?)
    ");

    $inserted = 0;
    foreach ($sample_posts as $post) {
        $checkStmt->execute([$post['slug']]);
        if (!$checkStmt->fetch()) {
            $insertStmt->execute([
                $post['title'],
                $post['slug'],
                $post['category'],
                $post['author'],
                $post['image'],
                $post['excerpt'],
                $post['content'],
                $post['published_date'],
                $post['seo_title'],
                $post['meta_description'],
                $post['meta_keywords']
            ]);
            $inserted++;
        }
    }
    echo "Seeded $inserted initial blog posts.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
