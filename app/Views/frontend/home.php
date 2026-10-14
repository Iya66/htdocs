<?php
/**
 * Homepage View - CherriDM Aesthetic
 * 100% Dynamically Connected to Database (Services, Reviews, Testimonials, Settings, Why Choose Us, Projects)
 */

$site_name = !empty($settings['site_name']) ? $settings['site_name'] : 'Cloud Aspire';
$contact_phone = !empty($settings['contact_phone']) ? $settings['contact_phone'] : '8098830937';
$services = $services ?? [];
$reviews = $reviews ?? [];
$testimonials = $testimonials ?? [];
$why_choose_us_items = $why_choose_us_items ?? [];
$projects = $projects ?? [];

$stat_projects = $stat_projects ?? 159;
$stat_clients = $stat_clients ?? 156;
$stat_services = $stat_services ?? count($services);

// SEO
$pageTitle = $site_name . " - Best Digital Marketing Company in Pondicherry";
$pageDesc = $site_name . " is a premier digital marketing agency in Pondicherry specializing in SEO, PPC, Google Ads, Meta Ads, and High-Performance Web Development.";

include __DIR__ . '/../../../includes/header.php';
?>

<!-- CHERRIDM AESTHETIC & THEME STYLES -->
<style>
    .text-secondary {
        color: #fb9a27 !important;
    }
    .text-primary {
        color: #03228f !important;
    }

    /* ══════════════════════════════════════════════
       1. HERO SLIDER BANNER
       ══════════════════════════════════════════════ */
    .cdm-hero-slider {
        position: relative;
        background: #03228f;
        overflow: hidden;
        min-height: 640px;
    }

    .cdm-slide {
        display: none;
        position: relative;
        padding: 120px 20px 180px;
        min-height: 640px;
        background-size: cover;
        background-position: center;
        align-items: center;
        justify-content: center;
        text-align: center;
        animation: cdmFadeIn 0.8s ease-in-out;
    }

    .cdm-slide.active {
        display: flex;
    }

    @keyframes cdmFadeIn {
        from { opacity: 0; transform: scale(1.02); }
        to { opacity: 1; transform: scale(1); }
    }

    .cdm-slide-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, rgba(3, 34, 143, 0.92) 0%, rgba(2, 20, 74, 0.95) 100%);
        z-index: 1;
    }

    .cdm-slide-content {
        position: relative;
        z-index: 2;
        max-width: 900px;
        margin: 0 auto;
    }

    .cdm-slide-title {
        font-size: clamp(2.4rem, 5vw, 4.2rem);
        font-weight: 800;
        color: #ffffff;
        line-height: 1.18;
        margin-bottom: 20px;
        letter-spacing: -0.5px;
    }

    .cdm-slide-title span.stroke {
        color: #fb9a27;
        text-shadow: 0 4px 20px rgba(251, 154, 39, 0.3);
    }

    .cdm-slide-desc {
        font-size: clamp(1.05rem, 2vw, 1.35rem);
        color: rgba(255, 255, 255, 0.9);
        margin-bottom: 35px;
        font-weight: 400;
        max-width: 720px;
        margin-left: auto;
        margin-right: auto;
    }

    .cdm-slide-actions {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 25px;
    }

    .cdm-btn-learn {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        background: #ffffff;
        color: #03228f;
        font-size: 15px;
        font-weight: 700;
        padding: 14px 34px;
        border-radius: 4px;
        transition: all 0.3s ease;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    }

    .cdm-btn-learn:hover {
        background: #fb9a27;
        color: #ffffff;
        transform: translateY(-3px);
        box-shadow: 0 15px 30px rgba(251, 154, 39, 0.35);
    }

    .cdm-btn-learn i {
        transition: transform 0.3s ease;
    }

    .cdm-btn-learn:hover i {
        transform: translateX(4px);
    }

    .cdm-scroll-down {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        border: 2px solid rgba(255, 255, 255, 0.4);
        color: #ffffff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        animation: cdmBounce 2s infinite;
        transition: all 0.3s ease;
    }

    .cdm-scroll-down:hover {
        border-color: #fb9a27;
        color: #fb9a27;
        background: rgba(255, 255, 255, 0.1);
    }

    @keyframes cdmBounce {
        0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
        40% { transform: translateY(8px); }
        60% { transform: translateY(4px); }
    }

    .cdm-slider-dots {
        position: absolute;
        bottom: 120px;
        left: 0;
        right: 0;
        display: flex;
        justify-content: center;
        gap: 10px;
        z-index: 3;
    }

    .cdm-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.35);
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .cdm-dot.active {
        width: 32px;
        border-radius: 10px;
        background: #fb9a27;
    }

    /* ══════════════════════════════════════════════
       2. FLIP BOXES (OVERLAPPING CARDS)
       ══════════════════════════════════════════════ */
    .cdm-flip-section {
        position: relative;
        z-index: 10;
        margin-top: -95px;
        margin-bottom: 70px;
    }

    .cdm-flip-grid {
        max-width: 1240px;
        margin: 0 auto;
        padding: 0 20px;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
        gap: 20px;
    }

    .cdm-flip-card {
        background-color: transparent;
        height: 250px;
        perspective: 1000px;
    }

    .cdm-flip-inner {
        position: relative;
        width: 100%;
        height: 100%;
        text-align: center;
        transition: transform 0.7s cubic-bezier(0.16, 1, 0.3, 1);
        transform-style: preserve-3d;
    }

    .cdm-flip-card:hover .cdm-flip-inner {
        transform: rotateY(180deg);
    }

    .cdm-flip-front, .cdm-flip-back {
        position: absolute;
        width: 100%;
        height: 100%;
        -webkit-backface-visibility: hidden;
        backface-visibility: hidden;
        border-radius: 8px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        padding: 24px 18px;
        box-shadow: 0 10px 30px rgba(3, 34, 143, 0.1);
    }

    .cdm-flip-front {
        background: #ffffff;
        border-bottom: 4px solid #fb9a27;
    }

    .cdm-flip-icon-wrap {
        width: 64px;
        height: 64px;
        border-radius: 16px;
        background: linear-gradient(135deg, rgba(3, 34, 143, 0.08) 0%, rgba(251, 154, 39, 0.12) 100%);
        border: 1.5px solid rgba(3, 34, 143, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 16px;
        transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .cdm-flip-icon-wrap i {
        font-size: 28px;
        color: #03228f;
        margin-bottom: 0 !important;
        transition: all 0.35s ease;
    }

    .cdm-flip-card:hover .cdm-flip-icon-wrap {
        background: #fb9a27;
        border-color: #fb9a27;
        transform: scale(1.1) rotate(4deg);
        box-shadow: 0 8px 20px rgba(251, 154, 39, 0.35);
    }

    .cdm-flip-card:hover .cdm-flip-icon-wrap i {
        color: #ffffff;
    }

    .cdm-flip-front h4 {
        font-size: 16px;
        font-weight: 700;
        color: #1e293b;
        line-height: 1.35;
    }

    .cdm-flip-back {
        background: linear-gradient(135deg, #03228f 0%, #1738af 100%);
        color: #ffffff;
        transform: rotateY(180deg);
    }

    .cdm-flip-back h4 {
        font-size: 15px;
        font-weight: 700;
        color: #ffffff;
        margin-bottom: 10px;
    }

    .cdm-flip-back p {
        font-size: 12px;
        line-height: 1.5;
        color: rgba(255, 255, 255, 0.9);
        margin-bottom: 15px;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .cdm-flip-back a {
        font-size: 12px;
        font-weight: 700;
        color: #fb9a27;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .cdm-flip-back a:hover {
        color: #ffffff;
    }

    /* ══════════════════════════════════════════════
       3. ABOUT US SECTION
       ══════════════════════════════════════════════ */
    .cdm-about-section {
        padding: 60px 0;
    }

    .cdm-about-grid {
        max-width: 1240px;
        margin: 0 auto;
        padding: 0 20px;
        display: grid;
        grid-template-columns: 1fr 1.15fr;
        gap: 60px;
        align-items: center;
    }

    .cdm-about-images {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 16px;
        align-items: center;
    }

    .cdm-about-img1 {
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 15px 35px rgba(3, 34, 143, 0.12);
        height: 380px;
    }

    .cdm-about-img1 img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .cdm-about-img2 {
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 15px 35px rgba(3, 34, 143, 0.12);
        height: 260px;
    }

    .cdm-about-img2 img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .cdm-about-content h2 {
        font-size: clamp(1.9rem, 3.5vw, 2.7rem);
        font-weight: 800;
        color: #03228f;
        line-height: 1.25;
        margin-bottom: 22px;
    }

    .cdm-about-content p {
        font-size: 15px;
        color: #475569;
        line-height: 1.75;
        margin-bottom: 18px;
        text-align: justify;
    }

    .cdm-metrics-strip {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        border-top: 1px solid #e2e8f0;
        border-bottom: 1px solid #e2e8f0;
        padding: 22px 0;
        margin: 28px 0;
    }

    .cdm-metric-item {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .cdm-metric-item i {
        font-size: 32px;
        color: #fb9a27;
    }

    .cdm-metric-item h4 {
        font-size: 17px;
        font-weight: 700;
        color: #03228f;
    }

    .cdm-btn-primary {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        background: #03228f;
        color: #ffffff;
        font-size: 15px;
        font-weight: 700;
        padding: 13px 32px;
        border-radius: 4px;
        transition: all 0.3s ease;
    }

    .cdm-btn-primary:hover {
        background: #fb9a27;
        color: #ffffff;
        transform: translateY(-2px);
    }

    /* ══════════════════════════════════════════════
       4. CLIENTS 3D FLIP LOGO GRID
       ══════════════════════════════════════════════ */
    .cdm-clients-section {
        padding: 80px 0;
        background-color: #e9eeff;
    }

    .cdm-section-heading {
        text-align: center;
        margin-bottom: 50px;
    }

    .cdm-section-heading h2 {
        font-size: clamp(2rem, 3.5vw, 2.8rem);
        font-weight: 800;
        color: #03228f;
        margin-bottom: 12px;
    }

    .cdm-divider {
        width: 60px;
        height: 4px;
        background: #fb9a27;
        margin: 0 auto;
        border-radius: 2px;
    }

    .cdm-clients-grid {
        max-width: 1240px;
        margin: 0 auto;
        padding: 0 20px;
        display: grid;
        grid-template-columns: repeat(8, 1fr);
        gap: 18px;
    }

    .cdm-client-card {
        height: 110px;
        background: #ffffff;
        border-radius: 8px;
        box-shadow: 0 4px 15px rgba(3, 34, 143, 0.08);
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        transform-style: preserve-3d;
        transition: transform 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        padding: 12px;
        text-align: center;
    }

    .cdm-client-card.flip {
        transform: rotateY(180deg);
    }

    .cdm-client-face {
        position: absolute;
        inset: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 12px;
        -webkit-backface-visibility: hidden;
        backface-visibility: hidden;
        border-radius: 8px;
    }

    .cdm-client-face.back {
        transform: rotateY(180deg);
        background: #ffffff;
    }

    .cdm-client-logo-icon {
        font-size: 24px;
        color: #03228f;
        margin-bottom: 6px;
    }

    .cdm-client-name {
        font-size: 11px;
        font-weight: 700;
        color: #334155;
        line-height: 1.25;
    }

    /* ══════════════════════════════════════════════
       5. WHY CHOOSE US (FROM DATABASE)
       ══════════════════════════════════════════════ */
    .cdm-partner-section {
        padding: 80px 0;
    }

    .cdm-partner-grid {
        max-width: 1240px;
        margin: 0 auto;
        padding: 0 20px;
        display: grid;
        grid-template-columns: 1.15fr 1fr;
        gap: 60px;
        align-items: center;
    }

    .cdm-why-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        margin: 25px 0 30px;
    }

    .cdm-why-item {
        background: #f8faff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 16px;
        transition: all 0.25s ease;
    }

    .cdm-why-item:hover {
        border-color: #fb9a27;
        background: #ffffff;
        box-shadow: 0 6px 20px rgba(3, 34, 143, 0.08);
        transform: translateY(-2px);
    }

    .cdm-why-item i {
        color: #fb9a27;
        font-size: 20px;
        margin-bottom: 8px;
        display: block;
    }

    .cdm-why-item h5 {
        font-size: 15px;
        font-weight: 700;
        color: #03228f;
        margin-bottom: 4px;
    }

    .cdm-why-item p {
        font-size: 12px;
        color: #64748b;
        line-height: 1.45;
    }

    .cdm-featured-card {
        background: #ffffff;
        border-radius: 12px;
        padding: 16px;
        box-shadow: 0 15px 40px rgba(3, 34, 143, 0.1);
        text-align: center;
    }

    .cdm-featured-card img {
        width: 100%;
        height: 320px;
        object-fit: cover;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .cdm-featured-card h3 {
        font-size: 20px;
        font-weight: 800;
        color: #03228f;
        margin-bottom: 6px;
    }

    .cdm-featured-card span {
        color: #fb9a27;
        font-weight: 600;
        font-size: 15px;
    }

    /* ══════════════════════════════════════════════
       6. CALL US PARALLAX BANNER
       ══════════════════════════════════════════════ */
    .cdm-cta-banner {
        background: linear-gradient(135deg, rgba(2, 20, 74, 0.95) 0%, rgba(3, 34, 143, 0.92) 100%),
                    url('https://images.unsplash.com/photo-1557804506-669a67965ba0?auto=format&fit=crop&w=1600&q=80') center/cover fixed;
        padding: 70px 20px;
        color: #ffffff;
    }

    .cdm-cta-container {
        max-width: 1240px;
        margin: 0 auto;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 30px;
    }

    .cdm-cta-text h3 {
        font-size: clamp(1.8rem, 3.5vw, 2.5rem);
        font-weight: 800;
        line-height: 1.25;
        margin-bottom: 8px;
    }

    .cdm-cta-text p {
        font-size: 22px;
        color: #fb9a27;
        font-weight: 600;
    }

    .cdm-btn-call {
        background: #fb9a27;
        color: #03228f;
        font-size: 20px;
        font-weight: 800;
        padding: 14px 42px;
        border-radius: 40px;
        border: 2px solid #fb9a27;
        display: inline-flex;
        align-items: center;
        gap: 12px;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .cdm-btn-call:hover {
        background: #ffffff;
        color: #fb9a27;
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(251, 154, 39, 0.4);
    }

    /* ══════════════════════════════════════════════
       7. PARALLAX STATS COUNTER
       ══════════════════════════════════════════════ */
    .cdm-counter-section {
        background: linear-gradient(135deg, rgba(3, 34, 143, 0.95) 0%, rgba(2, 20, 74, 0.98) 100%),
                    url('https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=1600&q=80') center/cover fixed;
        padding: 80px 20px;
        color: #ffffff;
    }

    .cdm-counter-grid {
        max-width: 1240px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: 1fr 2fr;
        gap: 60px;
        align-items: center;
    }

    .cdm-counter-left {
        text-align: center;
        border-right: 1px solid rgba(255, 255, 255, 0.15);
        padding-right: 40px;
    }

    .cdm-counter-left .number {
        font-size: 80px;
        font-weight: 800;
        color: #fb9a27;
        line-height: 1;
        margin-bottom: 10px;
    }

    .cdm-counter-left h4 {
        font-size: 20px;
        letter-spacing: 2px;
        text-transform: uppercase;
        font-weight: 600;
    }

    .cdm-counter-right {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        text-align: center;
    }

    .cdm-stat-box h3 {
        font-size: 42px;
        font-weight: 800;
        color: #ffffff;
        margin-bottom: 6px;
    }

    .cdm-stat-box p {
        font-size: 14px;
        color: rgba(255, 255, 255, 0.85);
        font-weight: 500;
    }

    /* ══════════════════════════════════════════════
       8. SERVICES FROM DATABASE
       ══════════════════════════════════════════════ */
    .cdm-services-section {
        padding: 85px 0;
        background: #f8faff;
    }

    .cdm-services-grid {
        max-width: 1240px;
        margin: 0 auto;
        padding: 0 20px;
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 30px;
    }

    .cdm-service-card {
        background: #ffffff;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(3, 34, 143, 0.06);
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
        border-bottom: 3px solid transparent;
    }

    .cdm-service-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 20px 40px rgba(3, 34, 143, 0.12);
        border-color: #fb9a27;
    }

    .cdm-service-thumb {
        height: 200px;
        position: relative;
        overflow: hidden;
    }

    .cdm-service-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }

    .cdm-service-card:hover .cdm-service-thumb img {
        transform: scale(1.08);
    }

    .cdm-service-badge {
        position: absolute;
        top: 15px;
        right: 15px;
        background: #fb9a27;
        color: #ffffff;
        font-size: 12px;
        font-weight: 700;
        padding: 4px 12px;
        border-radius: 20px;
    }

    .cdm-service-body {
        padding: 26px 24px;
        display: flex;
        flex-direction: column;
        flex-grow: 1;
    }

    .cdm-service-body h3 {
        font-size: 20px;
        font-weight: 700;
        color: #03228f;
        margin-bottom: 12px;
    }

    .cdm-service-body p {
        font-size: 14px;
        color: #64748b;
        line-height: 1.65;
        margin-bottom: 20px;
        flex-grow: 1;
    }

    .cdm-service-body a {
        font-size: 14px;
        font-weight: 700;
        color: #03228f;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s ease;
    }

    .cdm-service-body a:hover {
        color: #fb9a27;
        gap: 12px;
    }

    /* ══════════════════════════════════════════════
       9. TESTIMONIALS FROM DATABASE
       ══════════════════════════════════════════════ */
    .cdm-testimonials-section {
        background: linear-gradient(135deg, rgba(2, 20, 74, 0.96) 0%, rgba(3, 34, 143, 0.94) 100%),
                    url('https://images.unsplash.com/photo-1557804506-669a67965ba0?auto=format&fit=crop&w=1600&q=80') center/cover fixed;
        padding: 85px 20px;
        color: #ffffff;
    }

    .cdm-testimonial-container {
        max-width: 900px;
        margin: 0 auto;
        text-align: center;
    }

    .cdm-testimonial-slider {
        position: relative;
        min-height: 220px;
    }

    .cdm-testimonial-item {
        display: none;
        animation: cdmFadeIn 0.6s ease;
    }

    .cdm-testimonial-item.active {
        display: block;
    }

    .cdm-stars {
        color: #facc15;
        font-size: 18px;
        margin-bottom: 20px;
    }

    .cdm-quote-text {
        font-size: clamp(1.15rem, 2.5vw, 1.45rem);
        line-height: 1.65;
        font-style: italic;
        color: #ffffff;
        margin-bottom: 25px;
    }

    .cdm-author-name {
        font-size: 18px;
        font-weight: 700;
        color: #ffffff;
    }

    .cdm-author-company {
        color: #fb9a27;
        font-weight: 500;
        font-size: 15px;
        margin-left: 8px;
    }

    .cdm-testimonial-nav {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin-top: 30px;
    }

    .cdm-t-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.35);
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .cdm-t-dot.active {
        width: 28px;
        border-radius: 10px;
        background: #fb9a27;
    }

    @media (max-width: 1100px) {
        .cdm-clients-grid {
            grid-template-columns: repeat(4, 1fr);
        }
    }

    @media (max-width: 900px) {
        .cdm-about-grid,
        .cdm-partner-grid {
            grid-template-columns: 1fr;
            gap: 40px;
        }
        .cdm-counter-grid {
            grid-template-columns: 1fr;
            gap: 30px;
        }
        .cdm-counter-left {
            border-right: none;
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
            padding-right: 0;
            padding-bottom: 30px;
        }
        .cdm-services-grid {
            grid-template-columns: 1fr 1fr;
        }
    }

    @media (max-width: 600px) {
        .cdm-hero-slider {
            min-height: 540px;
        }
        .cdm-slide {
            padding: 80px 15px 140px;
            min-height: 540px;
        }
        .cdm-clients-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        .cdm-counter-right {
            grid-template-columns: repeat(2, 1fr);
            gap: 25px;
        }
        .cdm-services-grid {
            grid-template-columns: 1fr;
        }
        .cdm-why-grid {
            grid-template-columns: 1fr;
        }
        .cdm-cta-container {
            text-align: center;
            justify-content: center;
        }
    }
</style>

<!-- 1. HERO SLIDER BANNER (DYNAMIC SERVICES FROM DATABASE) -->
<section class="cdm-hero-slider">
    <?php
    $hero_bg_images = [
        'https://images.unsplash.com/photo-1611162617474-5b21e879e113?auto=format&fit=crop&w=1600&q=80',
        'https://images.unsplash.com/photo-1571786256017-aee7a0c009b6?auto=format&fit=crop&w=1600&q=80',
        'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=1600&q=80',
        'https://images.unsplash.com/photo-1542744094-3a31f272c490?auto=format&fit=crop&w=1600&q=80',
    ];

    // Build slides from active database services (up to 4 slides)
    $hero_slides = !empty($services) ? array_slice($services, 0, 4) : [];
    if (empty($hero_slides)) {
        $hero_slides = [
            ['service_name' => 'Social Media Marketing', 'short_description' => 'Best Social Media Marketing services in Pondicherry designed to engage, convert, and scale brand visibility.'],
            ['service_name' => 'SEO Service', 'short_description' => 'Boost your rankings and drive sustainable organic traffic with expert technical and local SEO strategies.'],
            ['service_name' => 'Google & Meta Ads', 'short_description' => 'We Provide Exclusive Google Ads & Meta PPC Management delivering high-intent conversions.'],
            ['service_name' => 'Content Marketing', 'short_description' => 'We deliver outstanding Content Marketing solutions that captivate your ideal customers.'],
        ];
    }

    foreach ($hero_slides as $h_idx => $hs):
        $words = explode(' ', $hs['service_name']);
        $first_word = $words[0] ?? '';
        $rest_words = count($words) > 1 ? implode(' ', array_slice($words, 1)) : 'Service';
        $bg_img = $hero_bg_images[$h_idx % count($hero_bg_images)];
    ?>
    <div class="cdm-slide <?= $h_idx === 0 ? 'active' : '' ?>" style="background-image: url('<?= htmlspecialchars($bg_img) ?>');">
        <div class="cdm-slide-overlay"></div>
        <div class="cdm-slide-content">
            <h1 class="cdm-slide-title">
                <?= htmlspecialchars($first_word) ?> <span class="stroke"><?= htmlspecialchars($rest_words) ?></span>
            </h1>
            <p class="cdm-slide-desc">
                <?= htmlspecialchars($hs['short_description'] ?? 'Best digital marketing services in Pondicherry designed to scale traffic, build authority, and convert visitors.') ?>
            </p>
            <div class="cdm-slide-actions">
                <a href="<?= $base_url ?>/contact?service=<?= urlencode($hs['service_name']) ?>" class="cdm-btn-learn">
                    <span>Learn More</span>
                    <i class="fas fa-arrow-right"></i>
                </a>
                <a href="#nextSection" class="cdm-scroll-down" aria-label="Scroll down">
                    <i class="fas fa-angle-down"></i>
                </a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <!-- Slider Dots -->
    <div class="cdm-slider-dots">
        <?php foreach ($hero_slides as $h_idx => $hs): ?>
        <span class="cdm-dot <?= $h_idx === 0 ? 'active' : '' ?>" onclick="jumpSlide(<?= $h_idx ?>)"></span>
        <?php endforeach; ?>
    </div>
</section>

<!-- 2. FLIP BOXES (DYNAMIC SERVICES FROM DATABASE) -->
<section class="cdm-flip-section" id="nextSection">
    <div class="cdm-flip-grid">
        <?php
        $service_icon_map = [
            'seo-optimization' => 'fa-magnifying-glass-chart',
            'google-meta-ads' => 'fa-bullhorn',
            'social-media-marketing' => 'fa-share-nodes',
            'website-app-development' => 'fa-laptop-code',
            'brand-identity-design' => 'fa-palette',
            'content-marketing' => 'fa-pen-nib',
        ];

        // Take up to 5 services for the flip card row
        $flip_services = !empty($services) ? array_slice($services, 0, 5) : [];
        if (empty($flip_services)) {
            $flip_services = array_slice($hero_slides, 0, 5);
        }

        foreach ($flip_services as $f_idx => $fs):
            $slug = $fs['slug'] ?? '';
            $f_icon = $service_icon_map[$slug] ?? null;

            if (!$f_icon) {
                $name_l = strtolower($fs['service_name'] ?? '');
                if (str_contains($name_l, 'seo')) {
                    $f_icon = 'fa-magnifying-glass-chart';
                } elseif (str_contains($name_l, 'ads') || str_contains($name_l, 'ppc') || str_contains($name_l, 'google')) {
                    $f_icon = 'fa-bullhorn';
                } elseif (str_contains($name_l, 'social') || str_contains($name_l, 'media')) {
                    $f_icon = 'fa-share-nodes';
                } elseif (str_contains($name_l, 'web') || str_contains($name_l, 'app') || str_contains($name_l, 'dev')) {
                    $f_icon = 'fa-laptop-code';
                } elseif (str_contains($name_l, 'brand') || str_contains($name_l, 'design') || str_contains($name_l, 'identity') || str_contains($name_l, 'logo')) {
                    $f_icon = 'fa-palette';
                } elseif (str_contains($name_l, 'content') || str_contains($name_l, 'copy')) {
                    $f_icon = 'fa-pen-nib';
                } else {
                    $f_icon = !empty($fs['icon']) ? $fs['icon'] : 'fa-chart-line';
                }
            }

            if (!str_starts_with($f_icon, 'fa-') && !str_starts_with($f_icon, 'fas ') && !str_starts_with($f_icon, 'fab ')) {
                $f_icon = 'fa-' . ltrim($f_icon, 'fa-');
            }

            $f_img = !empty($fs['image']) ? $fs['image'] : '';
            if (!empty($f_img) && !str_starts_with($f_img, 'http')) {
                $f_img = $base_url . '/' . ltrim($f_img, '/');
            }
        ?>
        <div class="cdm-flip-card cdm-reveal">
            <div class="cdm-flip-inner">
                <div class="cdm-flip-front">
                    <div class="cdm-flip-icon-wrap">
                        <i class="fas <?= htmlspecialchars($f_icon) ?>"></i>
                    </div>
                    <h4><?= htmlspecialchars($fs['service_name']) ?></h4>
                </div>
                <div class="cdm-flip-back" style="<?= !empty($f_img) ? "background: linear-gradient(135deg, rgba(3, 34, 143, 0.94) 0%, rgba(2, 20, 74, 0.96) 100%), url('".htmlspecialchars($f_img)."') center/cover;" : "" ?>">
                    <h4><?= htmlspecialchars($fs['service_name']) ?></h4>
                    <p><?= htmlspecialchars($fs['short_description'] ?? 'Expert solutions to scale your business growth and online visibility.') ?></p>
                    <a href="<?= !empty($fs['slug']) ? $base_url . '/service/' . htmlspecialchars($fs['slug']) : $base_url . '/contact?service=' . urlencode($fs['service_name']) ?>">
                        Read More <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- 3. ABOUT US SECTION (CONNECTED TO DATABASE SETTINGS) -->
<section class="cdm-about-section cdm-reveal">
    <div class="cdm-about-grid">
        <div class="cdm-about-images cdm-reveal-left">
            <div class="cdm-about-img1">
                <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&w=800&q=80" alt="About <?= htmlspecialchars($site_name) ?>">
            </div>
            <div class="cdm-about-img2">
                <img src="https://images.unsplash.com/photo-1531482615713-2afd69097998?auto=format&fit=crop&w=600&q=80" alt="Strategy Team">
            </div>
        </div>

        <div class="cdm-about-content cdm-reveal-right">
            <h2><span class="text-secondary">Best Digital Marketing</span> Company in Pondicherry</h2>
            <p>
                <strong><?= htmlspecialchars($site_name) ?></strong> is an expert digital marketing and custom web architecture firm based in Pondicherry, India. With proven expertise across high-growth industries, our top-rated team empowers businesses with tailored strategies that drive measurable, compounding revenue.
            </p>
            <p>
                Specializing in SEO services to boost organic visibility, PPC campaigns for Targeted Advertising, Social Media Marketing for audience engagement, Google Ads for High-Converting Leads, and Content Marketing to captivate and convert, we excel in creating growth-centric solutions.
            </p>
            <p>
                Explore the power of digital excellence with <?= htmlspecialchars($site_name) ?> — Pondicherry's leading digital partner. Let’s build your brand’s future together!
            </p>

            <div class="cdm-metrics-strip">
                <div class="cdm-metric-item">
                    <i class="fas fa-chart-line"></i>
                    <div>
                        <h4>Drive More Leads</h4>
                        <span style="font-size:13px; color:#64748b;">Qualified High-Intent Inquiries</span>
                    </div>
                </div>
                <div class="cdm-metric-item">
                    <i class="fas fa-gauge-high"></i>
                    <div>
                        <h4>Boost More Sales</h4>
                        <span style="font-size:13px; color:#64748b;">Maximized ROI & Conversions</span>
                    </div>
                </div>
            </div>

            <a href="<?= $base_url ?>/why-choose-us" class="cdm-btn-primary">
                <span>Read More</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>

<!-- 4. CLIENTS 3D FLIP LOGO GRID -->
<section class="cdm-clients-section" id="clientsSection">
    <div class="cdm-section-heading">
        <h2>Our <span class="text-secondary">Client's</span></h2>
        <div class="cdm-divider"></div>
    </div>

    <div class="cdm-clients-grid">
        <?php
        // Combine DB project clients with trusted partners
        $client_logos = [];
        if (!empty($projects)) {
            foreach ($projects as $proj) {
                if (!empty($proj['client_name'])) {
                    $client_logos[] = [
                        'name' => $proj['client_name'],
                        'icon' => 'fa-building-columns',
                        'category' => $proj['industry'] ?? 'Enterprise'
                    ];
                }
            }
        }

        $standard_clients = [
            ['name' => 'Advanced GroHair & GloSkin', 'icon' => 'fa-spa', 'category' => 'Wellness Clinic'],
            ['name' => 'Grand Serenaa Hotel & Resorts', 'icon' => 'fa-hotel', 'category' => 'Hospitality'],
            ['name' => 'Jeno Maran Builders Pvt. Ltd.', 'icon' => 'fa-building', 'category' => 'Construction'],
            ['name' => 'Kalaialayam', 'icon' => 'fa-palette', 'category' => 'Art & Culture'],
            ['name' => 'Aishwarya Grand', 'icon' => 'fa-utensils', 'category' => 'Luxury Dining'],
            ['name' => 'RV Boating', 'icon' => 'fa-ship', 'category' => 'Tourism'],
            ['name' => 'Aurowellness Castle', 'icon' => 'fa-heart-pulse', 'category' => 'Health Retreat'],
            ['name' => 'Yas Travels', 'icon' => 'fa-plane-departure', 'category' => 'Global Travel'],
            ['name' => 'SVM Sweets', 'icon' => 'fa-cake-candles', 'category' => 'Retail Sweets'],
            ['name' => 'Economic Construction', 'icon' => 'fa-trowel-bricks', 'category' => 'Real Estate'],
            ['name' => 'Appachi Chettinad', 'icon' => 'fa-bowl-rice', 'category' => 'Restaurant'],
            ['name' => 'Presidency School', 'icon' => 'fa-graduation-cap', 'category' => 'Education'],
            ['name' => 'MML Groups', 'icon' => 'fa-briefcase', 'category' => 'Corporate Enterprise'],
            ['name' => 'Green City Promoters', 'icon' => 'fa-tree-city', 'category' => 'Properties'],
            ['name' => 'Aakruthi Interiors', 'icon' => 'fa-couch', 'category' => 'Interior Design'],
            ['name' => 'Cloud 96 Technology', 'icon' => 'fa-cloud', 'category' => 'Software Solutions']
        ];

        // Merge up to 16 cards
        $final_clients = array_slice(array_merge($client_logos, $standard_clients), 0, 16);

        foreach ($final_clients as $idx => $client):
        ?>
        <div class="cdm-client-card" id="card_<?= $idx ?>">
            <div class="cdm-client-face front">
                <i class="fas <?= $client['icon'] ?> cdm-client-logo-icon"></i>
                <div class="cdm-client-name"><?= htmlspecialchars($client['name']) ?></div>
            </div>
            <div class="cdm-client-face back">
                <i class="fas <?= $client['icon'] ?> cdm-client-logo-icon" style="color: #fb9a27;"></i>
                <div class="cdm-client-name" style="color: #03228f;"><?= htmlspecialchars($client['category']) ?></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- 5. WHY CHOOSE US (DYNAMIC FROM DATABASE `why_choose_us` TABLE) -->
<section class="cdm-partner-section">
    <div class="cdm-partner-grid">
        <div>
            <h2 style="font-size: clamp(1.8rem, 3.5vw, 2.6rem); font-weight: 800; color: #03228f; line-height: 1.25; margin-bottom: 20px;">
                Your Trusted Digital Marketing Partner in Pondicherry
            </h2>
            <p style="font-size: 15px; color: #475569; line-height: 1.75; margin-bottom: 20px; text-align: justify;">
                At <?= htmlspecialchars($site_name) ?>, we pride ourselves on being the go-to agency in Pondicherry that businesses trust for consistent growth and measurable performance:
            </p>

            <div class="cdm-why-grid">
                <?php if (!empty($why_choose_us_items)): ?>
                    <?php foreach ($why_choose_us_items as $w): ?>
                    <div class="cdm-why-item">
                        <i class="fas <?= htmlspecialchars($w['icon'] ?? 'fa-check-circle') ?>"></i>
                        <h5><?= htmlspecialchars($w['question']) ?></h5>
                        <p><?= htmlspecialchars($w['answer']) ?></p>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="cdm-why-item">
                        <i class="fas fa-trophy"></i>
                        <h5>Proven Track Record</h5>
                        <p>Over 500+ successful digital initiatives with measurable ROI.</p>
                    </div>
                    <div class="cdm-why-item">
                        <i class="fas fa-users-gear"></i>
                        <h5>Certified Specialists</h5>
                        <p>Dedicated specialists in SEO, performance marketing & web dev.</p>
                    </div>
                    <div class="cdm-why-item">
                        <i class="fas fa-chart-line"></i>
                        <h5>Data-Driven Growth</h5>
                        <p>Every campaign decision backed by live analytics and ROI metrics.</p>
                    </div>
                    <div class="cdm-why-item">
                        <i class="fas fa-headset"></i>
                        <h5>Dedicated Support</h5>
                        <p>Direct access to account managers and strategy updates.</p>
                    </div>
                <?php endif; ?>
            </div>

            <a href="<?= $base_url ?>/contact" class="cdm-btn-primary">
                <span>Discuss Your Project</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>

        <div class="cdm-featured-card">
            <img src="https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&w=800&q=80" alt="Strategy Meeting">
            <h3>17+ Years of Industry Expertise</h3>
            <span>Pondicherry’s Most Trusted Digital Firm</span>
        </div>
    </div>
</section>

<!-- 6. CALL US PARALLAX BANNER (DYNAMIC PHONE) -->
<section class="cdm-cta-banner">
    <div class="cdm-cta-container">
        <div class="cdm-cta-text">
            <h3>Ready to Get Started? Let's Discuss your projects.</h3>
            <p>Pondicherry's Trusted Experts!</p>
        </div>
        <div>
            <a href="tel:<?= preg_replace('/[^0-9+]/', '', $contact_phone) ?>" class="cdm-btn-call">
                <i class="fas fa-phone-alt"></i>
                <span>Call Us Now</span>
            </a>
        </div>
    </div>
</section>

<!-- 7. INNOVATIVE STRATEGIES SECTION -->
<section style="padding: 85px 0;">
    <div class="cdm-partner-grid">
        <div style="order: 2;">
            <div class="cdm-featured-card">
                <img src="https://images.unsplash.com/photo-1551836022-d5d88e9218df?auto=format&fit=crop&w=800&q=80" alt="Data-Driven Marketing">
                <h3>Data-Driven Growth Architecture</h3>
                <span>Measurable Campaign Performance</span>
            </div>
        </div>

        <div style="order: 1;">
            <h2 style="font-size: clamp(1.8rem, 3.5vw, 2.6rem); font-weight: 800; color: #03228f; line-height: 1.25; margin-bottom: 20px;">
                Innovative Strategies for <span class="text-secondary">Real Results</span>
            </h2>
            <p style="font-size: 15px; color: #475569; line-height: 1.75; margin-bottom: 20px; text-align: justify;">
                At <?= htmlspecialchars($site_name) ?>, we don’t just follow the trends — we engineer them. Our team of specialists is dedicated to creating innovative digital marketing strategies that yield real, tangible results. From advanced SEO tactics to PPC campaigns that generate verified leads, our methods are built around data-driven insights.
            </p>
            <ul style="list-style: none; display: flex; flex-direction: column; gap: 14px; margin-bottom: 25px;">
                <li style="display: flex; align-items: center; gap: 10px; font-weight: 600; color: #03228f;">
                    <i class="fas fa-bullseye" style="color: #fb9a27;"></i> High-converting audience remarketing & retargeting
                </li>
                <li style="display: flex; align-items: center; gap: 10px; font-weight: 600; color: #03228f;">
                    <i class="fas fa-pen-nib" style="color: #fb9a27;"></i> Search-intent content optimization & semantic clustering
                </li>
                <li style="display: flex; align-items: center; gap: 10px; font-weight: 600; color: #03228f;">
                    <i class="fas fa-chart-pie" style="color: #fb9a27;"></i> Omni-channel social media engagement & community building
                </li>
            </ul>
            <p style="font-size: 15px; color: #475569; line-height: 1.75; margin-bottom: 25px;">
                Our innovative approach is all about delivering Growth and Long-Term Business success.
            </p>
            <a href="<?= $base_url ?>/why-choose-us" class="cdm-btn-primary">
                <span>Read More</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>

<!-- 8. PARALLAX STATS COUNTER (DYNAMIC FROM DATABASE COUNTS) -->
<section class="cdm-counter-section cdm-reveal">
    <div class="cdm-counter-grid">
        <div class="cdm-counter-left cdm-reveal-left">
            <div class="number" data-count="17+">0+</div>
            <h4>Years Of Experience</h4>
        </div>
        <div class="cdm-counter-right cdm-reveal-right">
            <div class="cdm-stat-box">
                <h3 data-count="<?= (int)$stat_clients ?: 156 ?>+">0+</h3>
                <p>Happy Clients</p>
            </div>
            <div class="cdm-stat-box">
                <h3 data-count="<?= (int)$stat_projects ?: 159 ?>+">0+</h3>
                <p>Finished Projects</p>
            </div>
            <div class="cdm-stat-box">
                <h3 data-count="110+">0+</h3>
                <p>Specialists & Staff</p>
            </div>
            <div class="cdm-stat-box">
                <h3 data-count="19">0</h3>
                <p>Awards Won</p>
            </div>
        </div>
    </div>
</section>

<!-- 9. SERVICES SHOWCASE (LIVE DATABASE `services` QUERY) -->
<section class="cdm-services-section">
    <div class="cdm-section-heading">
        <span style="color: #fb9a27; font-weight: 700; text-transform: uppercase; font-size: 13px; letter-spacing: 1px; display: block; margin-bottom: 8px;">
            // We Offer a Full Range of Digital Marketing Services! //
        </span>
        <h2>Our Core <span class="text-secondary">Services</span></h2>
        <div class="cdm-divider"></div>
    </div>

    <div class="cdm-services-grid">
        <?php
        $service_stock_images = [
            'https://images.unsplash.com/photo-1571786256017-aee7a0c009b6?auto=format&fit=crop&w=600&q=80',
            'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=600&q=80',
            'https://images.unsplash.com/photo-1611162617474-5b21e879e113?auto=format&fit=crop&w=600&q=80',
            'https://images.unsplash.com/photo-1551288049-bebda4e38f71?auto=format&fit=crop&w=600&q=80',
            'https://images.unsplash.com/photo-1542744094-3a31f272c490?auto=format&fit=crop&w=600&q=80',
            'https://images.unsplash.com/photo-1507238691740-187a5b1d37b8?auto=format&fit=crop&w=600&q=80',
        ];

        foreach ($services as $idx => $s):
            $img = !empty($s['image']) ? $s['image'] : ($service_stock_images[$idx % count($service_stock_images)]);
            if (!str_starts_with($img, 'http')) {
                $img = $base_url . '/' . ltrim($img, '/');
            }
        ?>
        <div class="cdm-service-card cdm-reveal cdm-hover-lift">
            <div class="cdm-service-thumb">
                <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($s['service_name']) ?>" loading="lazy" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=600&q=80';">
            </div>
            <div class="cdm-service-body">
                <h3><?= htmlspecialchars($s['service_name']) ?></h3>
                <p><?= htmlspecialchars($s['short_description'] ?? 'Tailored digital solutions engineered to scale traffic, build authority, and convert visitors into clients.') ?></p>
                <a href="<?= !empty($s['slug']) ? $base_url . '/service/' . htmlspecialchars($s['slug']) : $base_url . '/contact?service=' . urlencode($s['service_name']) ?>">
                    <span>Explore Details</span>
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- 10. TESTIMONIALS (LIVE DATABASE `reviews` & `testimonials` QUERY) -->
<section class="cdm-testimonials-section">
    <div class="cdm-section-heading">
        <span style="color: #ffffff; font-weight: 700; text-transform: uppercase; font-size: 13px; letter-spacing: 1px; display: block; margin-bottom: 8px;">
            Testimonials
        </span>
        <h2 style="color: #ffffff;">What About <span class="text-secondary">Our Client Says</span></h2>
        <div class="cdm-divider" style="background: #ffffff;"></div>
    </div>

    <div class="cdm-testimonial-container">
        <div class="cdm-testimonial-slider">
            <?php
            // Pull testimonials and approved reviews directly from DB
            $db_quotes = [];

            // Add reviews from DB
            foreach ($reviews as $rev) {
                $msg = !empty($rev['message']) ? $rev['message'] : (!empty($rev['review_text']) ? $rev['review_text'] : '');
                if ($msg) {
                    $db_quotes[] = [
                        'name' => $rev['name'],
                        'company' => !empty($rev['company']) ? $rev['company'] : (!empty($rev['designation']) ? $rev['designation'] : 'Enterprise Partner'),
                        'text' => $msg,
                        'stars' => (int)($rev['star_rating'] ?? 5)
                    ];
                }
            }

            // Add testimonials from DB
            foreach ($testimonials as $t) {
                if (!empty($t['message'])) {
                    $db_quotes[] = [
                        'name' => $t['name'],
                        'company' => !empty($t['designation']) ? $t['designation'] : 'Client Partner',
                        'text' => $t['message'],
                        'stars' => 5
                    ];
                }
            }

            // Fallback quotes if none exist in DB
            if (empty($db_quotes)) {
                $db_quotes = [
                    [
                        'name' => 'Feroz',
                        'company' => 'Volkswagen Pondicherry',
                        'text' => 'I would love to recommend this team wherever there is a scope for digital marketing. They keep client business goals at the top of their priority list.',
                        'stars' => 5
                    ],
                    [
                        'name' => 'James',
                        'company' => 'Capital Medical Services',
                        'text' => 'Really this is the best Digital Marketing Agency specialized in SEO, SMO, PPC, Web Architecture, and lead generation.',
                        'stars' => 5
                    ],
                    [
                        'name' => 'Thangamani',
                        'company' => 'JENO Maran Builders',
                        'text' => 'My experience has been very good with this company. All types of services were tailored to our real estate launch, delivering verified inquiries promptly.',
                        'stars' => 5
                    ]
                ];
            }

            foreach ($db_quotes as $k => $q):
                $rating_stars = max(1, min(5, (int)($q['stars'] ?? 5)));
            ?>
            <div class="cdm-testimonial-item <?= $k === 0 ? 'active' : '' ?>" id="testim_<?= $k ?>">
                <div class="cdm-stars">
                    <?php for ($s_i = 0; $s_i < $rating_stars; $s_i++): ?>
                    <i class="fas fa-star"></i>
                    <?php endfor; ?>
                </div>
                <p class="cdm-quote-text">“<?= htmlspecialchars($q['text']) ?>”</p>
                <div>
                    <span class="cdm-author-name"><?= htmlspecialchars($q['name']) ?>,</span>
                    <span class="cdm-author-company"><?= htmlspecialchars($q['company']) ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="cdm-testimonial-nav">
            <?php for ($i = 0; $i < count($db_quotes); $i++): ?>
            <span class="cdm-t-dot <?= $i === 0 ? 'active' : '' ?>" onclick="jumpTestimonial(<?= $i ?>)"></span>
            <?php endfor; ?>
        </div>
    </div>
</section>

<!-- HOMEPAGE INTERACTIVE SCRIPTS -->
<script>
// 1. Hero Slider Logic
let currentSlide = 0;
const slides = document.querySelectorAll('.cdm-slide');
const dots = document.querySelectorAll('.cdm-dot');

function showSlide(index) {
    if (slides.length === 0) return;
    slides.forEach((s, i) => {
        s.classList.toggle('active', i === index);
    });
    dots.forEach((d, i) => {
        d.classList.toggle('active', i === index);
    });
    currentSlide = index;
}

function nextSlide() {
    if (slides.length === 0) return;
    let next = (currentSlide + 1) % slides.length;
    showSlide(next);
}

function jumpSlide(index) {
    showSlide(index);
}

if (slides.length > 1) {
    setInterval(nextSlide, 6000);
}

// 2. Client Cards 3D Auto-Flip
function flipClientCards() {
    const cards = document.querySelectorAll('.cdm-client-card');
    cards.forEach(card => {
        card.classList.toggle('flip');
    });
}
setInterval(flipClientCards, 5000);

// 3. Testimonial Slider
let currentTestimonial = 0;
const testItems = document.querySelectorAll('.cdm-testimonial-item');
const testDots = document.querySelectorAll('.cdm-t-dot');

function showTestimonial(index) {
    if (testItems.length === 0) return;
    testItems.forEach((t, i) => {
        t.classList.toggle('active', i === index);
    });
    testDots.forEach((d, i) => {
        d.classList.toggle('active', i === index);
    });
    currentTestimonial = index;
}

function jumpTestimonial(index) {
    showTestimonial(index);
}

if (testItems.length > 1) {
    setInterval(() => {
        let nxt = (currentTestimonial + 1) % testItems.length;
        showTestimonial(nxt);
    }, 7000);
}
</script>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>