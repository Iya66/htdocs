<?php
/**
 * Cloud Aspire - Rich Service Content Seeder
 * Populates high-impact senior-level content for all 6 core services.
 */
require_once __DIR__ . '/../app/database.php';

$services_data = [
    'seo-optimization' => [
        'seo_title' => 'SEO Optimization Services in Pondicherry - Cloud Aspire',
        'meta_description' => 'Dominate Google search results and drive compounding organic traffic with proven technical and local SEO strategies in Pondicherry.',
        'meta_keywords' => 'seo services pondicherry, search engine optimization pondicherry, best seo agency puducherry, local seo pondicherry, organic traffic growth',
        'features' => json_encode([
            ['title' => 'Technical SEO & Core Web Vitals Audit', 'desc' => 'In-depth audit fixing crawl errors, indexing bottlenecks, mobile rendering, and site speed optimization.'],
            ['title' => 'Search Intent & Keyword Intelligence', 'desc' => 'Uncover high-intent, high-conversion commercial keywords that your highest-paying prospects search for.'],
            ['title' => 'On-Page Architecture & Semantic Schema', 'desc' => 'Optimization of meta titles, headings, internal linking siloing, and structured JSON-LD schema markup.'],
            ['title' => 'High-Authority Backlink Outreach', 'desc' => '100% white-hat link acquisition from relevant, niche-authoritative publishers to skyrocket domain rating.'],
            ['title' => 'Google Business Profile (Local SEO)', 'desc' => 'Map pack 3-pack dominance for Pondicherry local geo-queries, citation cleanup, and review funnels.'],
            ['title' => 'Live Rank Tracking & ROI Analytics', 'desc' => 'Real-time dashboard reporting keyword rank improvements, organic impression growth, and lead conversions.']
        ], JSON_UNESCAPED_UNICODE),
        'benefits' => json_encode([
            'Compounding organic traffic that grows monthly without ongoing cost-per-click ad bills',
            'Higher search rankings than legacy competitors across top commercial queries',
            'Verified, high-intent inquiries from prospects actively searching for your solutions',
            'Stronger brand credibility and digital authority across Pondicherry and South India'
        ], JSON_UNESCAPED_UNICODE),
        'process_steps' => json_encode([
            ['step' => '01', 'title' => 'Discovery & Deep Technical Audit', 'desc' => 'We perform an exhaustive 80-point audit of your domain architecture, crawl budget, backlink profile, and competitor positioning.'],
            ['step' => '02', 'title' => 'Keyword Strategy & Content Architecture', 'desc' => 'We map high-intent commercial keywords into structured content clusters that satisfy user intent and Google algorithms.'],
            ['step' => '03', 'title' => 'On-Page Tuning & Authority Outreach', 'desc' => 'We optimize on-page signals and execute white-hat PR and editorial outreach to acquire tier-1 domain backlinks.'],
            ['step' => '04', 'title' => 'Continuous Measurement, Iteration & Scale', 'desc' => 'We monitor ranking volatility, analyze conversion rates, and continuously double down on top-performing search paths.']
        ], JSON_UNESCAPED_UNICODE),
        'faqs' => json_encode([
            ['q' => 'How long does it take to see results from SEO?', 'a' => 'While initial technical improvements show positive movement in 30-45 days, substantial compound traffic and first-page rankings typically materialize within 60 to 90 days.'],
            ['q' => 'Do you guarantee #1 ranking on Google?', 'a' => 'No ethical agency guarantees a specific #1 spot as search engine algorithms are constantly evolving. However, our data-backed methodology has consistently placed 90%+ of our client keywords in the top 3 results.'],
            ['q' => 'What is the difference between Local SEO and National SEO?', 'a' => 'Local SEO focuses on capturing searchers within a specific geographic area (e.g. Pondicherry) through Google Maps optimization and geo-citations, whereas National SEO targets broad search volumes across countries.'],
            ['q' => 'Will I receive monthly performance reports?', 'a' => 'Yes. Every client receives a comprehensive monthly report with keyword rank trajectories, organic traffic sources, and conversion attribution data, alongside bi-weekly strategy calls.']
        ], JSON_UNESCAPED_UNICODE)
    ],

    'google-meta-ads' => [
        'seo_title' => 'Google & Meta Ads Management in Pondicherry - Cloud Aspire',
        'meta_description' => 'Maximize ROI with high-converting paid ad campaigns across Google Search, Display, YouTube, Instagram, and Facebook managed by certified specialists.',
        'meta_keywords' => 'google ads pondicherry, meta ads agency pondicherry, ppc company puducherry, facebook ads pondicherry, instagram marketing agency',
        'features' => json_encode([
            ['title' => 'Precision Google Search & PMax Campaigns', 'desc' => 'Capture buyers at the exact second they search for your services with optimized ad copy and bidding.'],
            ['title' => 'High-Converting Meta Ad Creative & Copy', 'desc' => 'Visual creative frameworks, compelling hooks, and storytelling that stop the scroll on Instagram and Facebook.'],
            ['title' => 'Conversion Rate Optimized (CRO) Landing Pages', 'desc' => 'Frictionless, ultra-fast landing pages engineered with high psychological triggers to maximize form submissions.'],
            ['title' => 'Advanced Retargeting & Lookalike Audiences', 'desc' => 'Multi-step retargeting funnels that re-engage previous site visitors and bring back unconverted leads.'],
            ['title' => 'Negative Keyword Filtering & Budget Protection', 'desc' => 'Continuous search term hygiene ensuring 0% of your budget is wasted on unqualified, irrelevant clicks.'],
            ['title' => 'Full-Funnel Conversion API Tracking', 'desc' => 'Server-side tracking (Meta CAPI & Google Offline Conversions) bypassing iOS 14+ tracking restrictions.']
        ], JSON_UNESCAPED_UNICODE),
        'benefits' => json_encode([
            'Immediate influx of qualified leads within 48 to 72 hours of campaign activation',
            'Predictable, scalable customer acquisition with measurable cost-per-lead (CPL)',
            'Average 4.5x - 6.2x Return on Ad Spend (ROAS) across active client accounts',
            'Complete transparency on ad budget allocation and daily conversion numbers'
        ], JSON_UNESCAPED_UNICODE),
        'process_steps' => json_encode([
            ['step' => '01', 'title' => 'Audience Research & Offer Crafting', 'desc' => 'We dissect your target demographic, competitive benchmarks, and construct irresistible high-converting value propositions.'],
            ['step' => '02', 'title' => 'Creative Production & Tracking Architecture', 'desc' => 'We design ad graphics, write compelling copy, construct dedicated landing pages, and configure server-side conversion pixels.'],
            ['step' => '03', 'title' => 'Campaign Launch & Algorithmic Calibration', 'desc' => 'We launch targeted ad sets with strict bid caps, allowing machine learning algorithms to isolate top-performing audiences.'],
            ['step' => '04', 'title' => 'Scale, Budget Expansion & CRO Optimization', 'desc' => 'We scale winning ad creatives, prune underperforming placements, and iterate copy to systematically decrease CPL.']
        ], JSON_UNESCAPED_UNICODE),
        'faqs' => json_encode([
            ['q' => 'How much should I spend on ads each month?', 'a' => 'We recommend starting with a minimum ad budget of ₹15,000 - ₹30,000 per month for local campaigns to allow ad algorithms sufficient conversion data to calibrate effectively.'],
            ['q' => 'Is Google Ads or Meta Ads better for my business?', 'a' => 'Google Ads is ideal for high-intent capture (people searching actively for a plumber, lawyer, clinic, or product). Meta Ads excels at impulse discovery, visual storytelling, and brand lifestyle nurturing.'],
            ['q' => 'Who pays the ad spend to Google and Meta?', 'a' => 'You pay Google and Meta directly via your own billing account. We charge a transparent campaign management and optimization fee.'],
            ['q' => 'How do you prevent click fraud and fake leads?', 'a' => 'We implement server-side verification, reCAPTCHA v3, negative keyword siloing, and geo-fencing to ensure only legitimate inquiries reach your sales pipeline.']
        ], JSON_UNESCAPED_UNICODE)
    ],

    'social-media-marketing' => [
        'seo_title' => 'Social Media Marketing in Pondicherry - Cloud Aspire',
        'meta_description' => 'Build brand authority, engage your audience, and scale follower growth across Instagram, LinkedIn, and Facebook with viral creative production.',
        'meta_keywords' => 'social media marketing pondicherry, smm agency puducherry, instagram marketing pondicherry, reels production pondicherry, social media management',
        'features' => json_encode([
            ['title' => 'Viral Reels & Short-Form Video Production', 'desc' => 'Studio-grade short-form video content optimized for algorithmic reach on Instagram Reels and YouTube Shorts.'],
            ['title' => 'Bespoke Visual Feed Design & Carousels', 'desc' => 'Aesthetic, branded graphic templates, carousel slide decks, and stories that position you as an industry leader.'],
            ['title' => 'Content Calendar & Strategic Storytelling', 'desc' => 'Monthly structured scheduling balancing promotional, educational, behind-the-scenes, and testimonial posts.'],
            ['title' => 'Active Community & Direct Message Nurturing', 'desc' => 'Rapid comment response, community engagement, and conversational qualification of prospects reaching out via DMs.'],
            ['title' => 'Local Influencer & Creator Collaborations', 'desc' => 'Partnerships with verified Pondicherry and Tamil Nadu creators that drive hyper-local organic buzz and foot traffic.'],
            ['title' => 'Monthly Reach, Engagement & ROI Audits', 'desc' => 'Detailed analytics reviewing follower growth rates, save/share engagement, and link click attribution.']
        ], JSON_UNESCAPED_UNICODE),
        'benefits' => json_encode([
            'Consistent, high-aesthetic brand presence that builds trust and instant credibility',
            'Exponential reach and audience discovery through algorithmic short-form video assets',
            'Active community that advocates for your brand and turns followers into loyal customers',
            'Free up your internal team while creative experts handle 100% of production and publishing'
        ], JSON_UNESCAPED_UNICODE),
        'process_steps' => json_encode([
            ['step' => '01', 'title' => 'Brand Tone & Aesthetic Blueprint', 'desc' => 'We define your brand visual identity, typography rules, color palette, and unique narrative voice across channels.'],
            ['step' => '02', 'title' => 'Monthly Content Calendar Planning', 'desc' => 'We map out a structured 30-day calendar featuring strategic themes, hooks, video concepts, and promotional milestones.'],
            ['step' => '03', 'title' => 'Creative Production & Video Editing', 'desc' => 'Our design and video editing team produces high-polish motion graphics, captions, audio syncs, and carousel graphics.'],
            ['step' => '04', 'title' => 'Publishing, Community Engagement & Analysis', 'desc' => 'We publish at peak audience engagement hours, monitor viral metrics, and refine the creative pipeline monthly.']
        ], JSON_UNESCAPED_UNICODE),
        'faqs' => json_encode([
            ['q' => 'Which social media platforms will you manage for us?', 'a' => 'We primarily specialize in Instagram, Facebook, and LinkedIn, tailored to whether your model is B2C (lifestyle, hospitality, retail) or B2B (corporate, legal, tech).'],
            ['q' => 'Do you come to our location to shoot videos?', 'a' => 'Yes! For local clients across Pondicherry, our media team coordinates on-site shoot days to capture authentic footage, interviews, and product highlights.'],
            ['q' => 'Will I have approval over posts before they go live?', 'a' => 'Absolutely. Every asset and caption is compiled into a shared monthly content deck for your review and sign-off prior to publishing.']
        ], JSON_UNESCAPED_UNICODE)
    ],

    'website-app-development' => [
        'seo_title' => 'Website & App Development in Pondicherry - Cloud Aspire',
        'meta_description' => 'Ultra-fast, mobile-responsive custom websites, web portals, and web applications built to turn visitors into paying customers.',
        'meta_keywords' => 'web development pondicherry, website design pondicherry, web application development puducherry, ecommerce website pondicherry, custom php development',
        'features' => json_encode([
            ['title' => 'High-Speed Custom Web Architecture', 'desc' => 'Clean, lightweight PHP/HTML5/JavaScript architecture delivering sub-second load times and 95+ Core Web Vitals.'],
            ['title' => 'Mobile-First, Responsive UX/UI Design', 'desc' => 'Pixel-perfect interfaces tailored for smartphone users, tablets, and ultra-wide desktop displays.'],
            ['title' => 'Integrated CRM & Automated Lead Pipelines', 'desc' => 'Instant WhatsApp notifications, database lead capture, email auto-responders, and CRM webhooks.'],
            ['title' => 'E-Commerce Portals & Payment Gateways', 'desc' => 'Secure product catalogs, shopping carts, and automated payment integration with Razorpay, Cashfree, and Stripe.'],
            ['title' => 'Enterprise Security & SSL Enforcement', 'desc' => 'CSRF tokens, SQL injection protection, rate limiting, and encrypted session architectures.'],
            ['title' => 'Custom CMS & Self-Serve Admin Portals', 'desc' => 'Intuitive administrative dashboard enabling your team to edit content, services, testimonials, and blogs with ease.']
        ], JSON_UNESCAPED_UNICODE),
        'benefits' => json_encode([
            'Sub-second page speeds that improve SEO rankings and decrease visitor bounce rates',
            'Modern, premium visual aesthetics that instantly position your business above local competitors',
            'Automated lead capture funnels that deliver customer inquiries directly to WhatsApp and email 24/7',
            '100% full source code ownership without recurring proprietary builder lock-in'
        ], JSON_UNESCAPED_UNICODE),
        'process_steps' => json_encode([
            ['step' => '01', 'title' => 'Architecture Blueprint & Wireframing', 'desc' => 'We define user journey paths, conversion triggers, sitemap hierarchy, and core functional specifications.'],
            ['step' => '02', 'title' => 'UI/UX Visual Design System', 'desc' => 'We create bespoke responsive mockups adhering to modern typography, micro-interactions, and conversion psychology.'],
            ['step' => '03', 'title' => 'Clean Code Engineering & API Integration', 'desc' => 'We build the front-end and back-end logic with optimized databases, zero bloat, and automated notification hooks.'],
            ['step' => '04', 'title' => 'Security Audit, Core Web Vitals Testing & Launch', 'desc' => 'We run comprehensive stress tests, SSL and security checks, cross-device QA, and deploy to high-speed cloud infrastructure.']
        ], JSON_UNESCAPED_UNICODE),
        'faqs' => json_encode([
            ['q' => 'How long does it take to build a custom website?', 'a' => 'A standard high-performance business website typically takes 2 to 3 weeks from wireframing to production deployment. Complex custom web applications take 4 to 6 weeks.'],
            ['q' => 'Will my website be mobile-friendly and fast?', 'a' => 'Yes. Every project we engineer is built mobile-first, ensuring sub-second load times and passing all Google Core Web Vitals audits with green scores.'],
            ['q' => 'Can I update content on my own after launch?', 'a' => 'Yes. We provide a tailored, intuitive admin panel allowing you to easily update text, add new services, upload photos, and manage leads without writing a single line of code.']
        ], JSON_UNESCAPED_UNICODE)
    ],

    'brand-identity-design' => [
        'seo_title' => 'Brand Identity & Graphic Design in Pondicherry - Cloud Aspire',
        'meta_description' => 'Establish an iconic brand identity with bespoke logos, comprehensive brand guidelines, vector asset kits, and modern digital visual design.',
        'meta_keywords' => 'brand identity design pondicherry, logo design puducherry, graphic design agency pondicherry, corporate branding pondicherry, visual identity kit',
        'features' => json_encode([
            ['title' => 'Bespoke Logo Design & Variations', 'desc' => 'Primary, secondary, and sub-mark logo designs engineered in vector formats (SVG, AI, EPS, PDF, PNG).'],
            ['title' => 'Comprehensive Brand Style Guidelines', 'desc' => 'A complete brand manual outlining color codes (HEX, RGB, CMYK), typography hierarchy, and spacing rules.'],
            ['title' => 'Business Stationery & Print Collateral', 'desc' => 'Premium business cards, letterheads, invoice templates, envelopes, and corporate presentation decks.'],
            ['title' => 'Social Media Branding & Avatar Kit', 'desc' => 'High-resolution profile avatars, banner headers, and reusable graphic templates for all major platforms.'],
            ['title' => 'Packaging & Product Label Design', 'desc' => 'Shelf-popping product labels, packaging boxes, and retail merchandise graphics ready for high-grade printing.'],
            ['title' => 'Full Vector Copyright Ownership', 'desc' => '100% intellectual property rights and all master raw vector source files transferred upon project delivery.']
        ], JSON_UNESCAPED_UNICODE),
        'benefits' => json_encode([
            'Stand out in crowded markets with a distinctive visual signature that commands premium pricing',
            'Consistent visual language across digital websites, social media, print, and physical storefronts',
            'Deep emotional resonance and trust with prospective buyers from their very first interaction',
            'Full vector raw source files ready for any future scaling, billboard printing, or digital campaign'
        ], JSON_UNESCAPED_UNICODE),
        'process_steps' => json_encode([
            ['step' => '01', 'title' => 'Brand Discovery & Moodboarding', 'desc' => 'We uncover your company values, target demographics, competitor positioning, and synthesize visual moodboards.'],
            ['step' => '02', 'title' => 'Concept Exploration & Sketching', 'desc' => 'Our design team sketches multiple unique concept directions experimenting with geometry, negative space, and typography.'],
            ['step' => '03', 'title' => 'Refinement, Color Palette & Type Pairing', 'desc' => 'We take the winning concept into vector software, refining proportions, curating harmonious color psychology, and setting fonts.'],
            ['step' => '04', 'title' => 'Final Deliverables & Brand Guidelines Manual', 'desc' => 'We assemble all production file formats (web and print) alongside a complete brand guidelines PDF manual for your team.']
        ], JSON_UNESCAPED_UNICODE),
        'faqs' => json_encode([
            ['q' => 'How many logo concepts do you provide?', 'a' => 'We present 3 to 4 distinct, fully fleshed-out concept directions, and work with you through unlimited refinement rounds on your chosen direction.'],
            ['q' => 'What file formats will I receive?', 'a' => 'You receive industry-standard vector source files (.AI, .EPS, .SVG, .PDF) for infinite scalability without quality loss, alongside web-ready formats (.PNG with transparent background, .JPG, .WEBP).'],
            ['q' => 'Do I own the copyright to my brand designs?', 'a' => 'Yes, 100%. Upon final project completion, all intellectual property rights and master source files belong entirely to you.']
        ], JSON_UNESCAPED_UNICODE)
    ],

    'content-marketing' => [
        'seo_title' => 'Content Marketing & Copywriting in Pondicherry - Cloud Aspire',
        'meta_description' => 'Compelling storytelling and conversion copywriting that captivates your audience, drives search engine rankings, and accelerates sales.',
        'meta_keywords' => 'content marketing pondicherry, copywriting services puducherry, seo blog writing pondicherry, website content writing, sales copywriting',
        'features' => json_encode([
            ['title' => 'Search-Intent SEO Blog Writing', 'desc' => 'In-depth, well-researched blog posts structured around target keywords that rank on Google and answer customer queries.'],
            ['title' => 'High-Converting Website Copywriting', 'desc' => 'Compelling landing page copy, value propositions, and calls-to-action engineered to maximize conversions.'],
            ['title' => 'Email Marketing Sequences & Newsletters', 'desc' => 'Automated welcome series, lead nurturing workflows, and promotional newsletters that drive repeat sales.'],
            ['title' => 'Case Studies & Client Success Stories', 'desc' => 'Data-backed customer case studies highlighting problem-solving, methodology, and ROI to close high-ticket clients.'],
            ['title' => 'Lead Magnets, Guides & Whitepapers', 'desc' => 'High-value downloadable assets designed to capture qualified business leads and grow your email subscriber list.'],
            ['title' => 'Ad Copywriting & Social Ad Scripts', 'desc' => 'Hook-driven copy variations for Google Search, Meta ads, and video sales letters (VSL) that maximize click-throughs.']
        ], JSON_UNESCAPED_UNICODE),
        'benefits' => json_encode([
            'Build undeniable industry authority as the trusted expert voice in your business vertical',
            'Attract organic search traffic for long-tail, high-intent buyer questions on autopilot',
            'Convert more site visitors into qualified phone inquiries with persuasive conversion copywriting',
            'Nurture cold leads into ready-to-buy prospects through automated email content sequences'
        ], JSON_UNESCAPED_UNICODE),
        'process_steps' => json_encode([
            ['step' => '01', 'title' => 'Customer Persona & Content Gap Analysis', 'desc' => 'We research your audience pain points, objections, and evaluate competitive content gaps in your niche.'],
            ['step' => '02', 'title' => 'Content Strategy & Editorial Calendar', 'desc' => 'We construct a strategic editorial calendar mapping keyword themes, target search volumes, and conversion hooks.'],
            ['step' => '03', 'title' => 'Research, Drafting & Conversion Polishing', 'desc' => 'Our experienced copywriters draft compelling content optimized with formatting, storytelling, and psychological CTA placement.'],
            ['step' => '04', 'title' => 'Publishing, Distribution & Performance Tracking', 'desc' => 'We assist with CMS publishing, internal link siloing, newsletter distribution, and analyze readership engagement metrics.']
        ], JSON_UNESCAPED_UNICODE),
        'faqs' => json_encode([
            ['q' => 'Is all content written by human writers or AI?', 'a' => 'Every single article, landing page, and email is researched and written by our professional in-house copywriters with verified domain expertise, edited strictly for human nuance and conversion power.'],
            ['q' => 'How do you ensure the content aligns with our brand voice?', 'a' => 'We conduct a brand tone questionnaire and review your existing materials to build a custom style guide, ensuring consistent voice across all published pieces.'],
            ['q' => 'Can you publish the content directly to our WordPress or custom website?', 'a' => 'Yes! We can format, upload, optimize metadata, add royalty-free imagery, and schedule posts directly inside your CMS.']
        ], JSON_UNESCAPED_UNICODE)
    ]
];

$stmt = $conn->prepare("UPDATE services SET 
    seo_title = ?, 
    meta_description = ?, 
    meta_keywords = ?, 
    features = ?, 
    benefits = ?, 
    process_steps = ?, 
    faqs = ? 
WHERE slug = ?");

$updated = 0;
foreach ($services_data as $slug => $data) {
    $stmt->execute([
        $data['seo_title'],
        $data['meta_description'],
        $data['meta_keywords'],
        $data['features'],
        $data['benefits'],
        $data['process_steps'],
        $data['faqs'],
        $slug
    ]);
    if ($stmt->rowCount() > 0) {
        $updated++;
        echo "Updated service: $slug\n";
    } else {
        echo "No change or slug not found: $slug\n";
    }
}

echo "Seeded rich content for $updated services.\n";
