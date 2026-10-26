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

$stat_projects = $stat_projects ?? (int)($settings['stat_projects'] ?? 1);
$stat_clients = $stat_clients ?? (int)($settings['stat_clients'] ?? 2);
$stat_services = $stat_services ?? count($services);
// Company milestones — editable from Admin > Settings
$stat_experience = (int)($settings['stat_experience'] ?? 5);
$stat_staff = (int)($settings['stat_staff'] ?? 10);

// SEO
$pageTitle = "Digital Marketing Agency in Pondicherry | Cloud Aspire Solutions";
$pageDesc = "Looking for a digital marketing agency in Pondicherry? Cloud Aspire Solutions offers SEO, social media marketing, Google Ads and Meta Ads to help businesses improve online visibility and generate leads.";

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
        will-change: opacity, transform;
        backface-visibility: hidden;
        -webkit-backface-visibility: hidden;
    }

    .cdm-slide.active {
        display: flex;
        animation: cdmFadeIn 0.8s cubic-bezier(0.16, 1, 0.3, 1);
    }

    @keyframes cdmFadeIn {
        from { opacity: 0; transform: translate3d(0, 0, 0) scale(1.02); }
        to { opacity: 1; transform: translate3d(0, 0, 0) scale(1); }
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
       2. COMPACT FEATURE CARDS (CLEAN OVERVIEW)
       ══════════════════════════════════════════════ */
    .cdm-flip-section {
        position: relative;
        z-index: 10;
        margin-top: -65px;
        margin-bottom: 50px;
    }

    .cdm-flip-grid {
        max-width: 1240px;
        margin: 0 auto;
        padding: 0 20px;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
    }

    .cdm-feat-card {
        background: #ffffff;
        border-radius: 10px;
        padding: 20px 18px 18px;
        box-shadow: 0 8px 25px rgba(3, 34, 143, 0.08);
        border: 1px solid #eef2f6;
        border-top: 3px solid #fb9a27;
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        height: 100%;
        min-height: 195px;
    }

    .cdm-feat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 14px 30px rgba(3, 34, 143, 0.14);
        border-top-color: #03228f;
    }

    .cdm-feat-icon-wrap {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        background: rgba(3, 34, 143, 0.07);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 12px;
        transition: all 0.25s ease;
    }

    .cdm-feat-icon-wrap i {
        font-size: 22px;
        color: #03228f;
        transition: all 0.25s ease;
    }

    .cdm-feat-card:hover .cdm-feat-icon-wrap {
        background: #fb9a27;
        transform: scale(1.05);
    }

    .cdm-feat-card:hover .cdm-feat-icon-wrap i {
        color: #ffffff;
    }

    .cdm-feat-card h4 {
        font-size: 15px;
        font-weight: 700;
        color: #1e293b;
        line-height: 1.3;
        margin-bottom: 6px;
    }

    .cdm-feat-card p {
        font-size: 12.5px;
        line-height: 1.5;
        color: #64748b;
        margin-bottom: 14px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        flex-grow: 1;
    }

    .cdm-feat-card a.cdm-feat-link {
        font-size: 12.5px;
        font-weight: 700;
        color: #03228f;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s ease;
        text-transform: capitalize;
    }

    .cdm-feat-card a.cdm-feat-link:hover {
        color: #fb9a27;
        gap: 9px;
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
        line-height: 1.7;
        margin-bottom: 16px;
        text-align: left;
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

    .cdm-innovative-section {
        padding: 85px 0;
        overflow: hidden;
    }

    .cdm-innovative-title {
        font-size: clamp(1.8rem, 3.5vw, 2.6rem);
        font-weight: 800;
        color: #03228f;
        line-height: 1.25;
        margin-bottom: 20px;
        word-break: break-word;
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
        gap: 14px;
        margin: 20px 0 25px;
    }

    .cdm-why-item {
        background: #f8faff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 12px 14px;
        display: flex;
        align-items: flex-start;
        gap: 12px;
        transition: all 0.25s ease;
    }

    .cdm-why-item:hover {
        border-color: #fb9a27;
        background: #ffffff;
        box-shadow: 0 4px 15px rgba(3, 34, 143, 0.08);
        transform: translateY(-2px);
    }

    .cdm-why-icon-box {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        background: rgba(251, 154, 39, 0.12);
        color: #fb9a27;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 16px;
    }

    .cdm-why-text {
        flex: 1;
        min-width: 0;
    }

    .cdm-why-text h5 {
        font-size: 14px;
        font-weight: 700;
        color: #03228f;
        margin-bottom: 2px;
        line-height: 1.3;
    }

    .cdm-why-text p {
        font-size: 12px;
        color: #64748b;
        line-height: 1.4;
        margin: 0;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
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
        font-size: 18px;
        font-weight: 800;
        padding: 14px 38px;
        border-radius: 40px;
        border: 2px solid #fb9a27;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        transition: all 0.3s ease;
        cursor: pointer;
        text-decoration: none;
        white-space: nowrap;
    }
    .cdm-btn-call i {
        font-size: 15px;
        line-height: 1;
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
    /* ══════════════════════════════════════════════
       7. PARALLAX STATS COUNTER
       ══════════════════════════════════════════════ */
    .cdm-counter-section {
        background: linear-gradient(135deg, rgba(3, 34, 143, 0.95) 0%, rgba(2, 20, 74, 0.98) 100%),
                    url('https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=1600&q=80') center/cover fixed;
        padding: 55px 20px;
        color: #ffffff;
    }

    .cdm-counter-grid {
        max-width: 1240px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: 1fr 2fr;
        gap: 45px;
        align-items: center;
    }
    .cdm-counter-left {
        text-align: center;
        border-right: 1px solid rgba(255, 255, 255, 0.15);
        padding-right: 30px;
    }

    .cdm-counter-left .number {
        font-size: 58px;
        font-weight: 800;
        color: #fb9a27;
        line-height: 1;
        margin-bottom: 6px;
    }

    .cdm-counter-left h4 {
        font-size: 16px;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        font-weight: 600;
    }

    .cdm-counter-right {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 18px;
        text-align: center;
    }

    .cdm-stat-box h3 {
        font-size: 34px;
        font-weight: 800;
        color: #ffffff;
        margin-bottom: 4px;
    }

    .cdm-stat-box p {
        font-size: 13px;
        color: rgba(255, 255, 255, 0.85);
        font-weight: 500;
    }

    /* ══════════════════════════════════════════════
       8. SERVICES FROM DATABASE
       ══════════════════════════════════════════════ */
    .cdm-services-section {
        padding: 60px 0;
        background: #f8faff;
    }

    .cdm-services-grid {
        max-width: 1240px;
        margin: 0 auto;
        padding: 0 20px;
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 22px;
    }

    .cdm-service-card {
        background: #ffffff;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 4px 18px rgba(3, 34, 143, 0.06);
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
        height: 100%;
        border: 1px solid #eef2f6;
        border-bottom: 3px solid transparent;
    }

    .cdm-service-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 30px rgba(3, 34, 143, 0.12);
        border-color: #fb9a27;
    }

    .cdm-service-thumb {
        height: 160px;
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
        transform: scale(1.06);
    }

    .cdm-service-badge {
        position: absolute;
        top: 12px;
        right: 12px;
        background: #fb9a27;
        color: #ffffff;
        font-size: 11px;
        font-weight: 700;
        padding: 3px 10px;
        border-radius: 14px;
        letter-spacing: 0.3px;
    }

    .cdm-service-body {
        padding: 18px 20px 20px;
        display: flex;
        flex-direction: column;
        flex-grow: 1;
        justify-content: space-between;
    }

    .cdm-service-body h3 {
        font-size: 18px;
        font-weight: 700;
        color: #03228f;
        margin-bottom: 8px;
        line-height: 1.3;
    }

    .cdm-service-body p {
        font-size: 13.5px;
        color: #64748b;
        line-height: 1.55;
        margin-bottom: 14px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        min-height: 42px;
    }

    .cdm-service-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: auto;
        padding-top: 12px;
        border-top: 1px solid #f1f5f9;
    }

    .cdm-service-body a.cdm-svc-link {
        font-size: 13.5px;
        font-weight: 700;
        color: #03228f;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s ease;
    }

    .cdm-service-body a.cdm-svc-link:hover {
        color: #fb9a27;
        gap: 9px;
    }

    .cdm-btn-svc-quote {
        background: rgba(251, 154, 39, 0.12);
        color: #e08316;
        border: none;
        padding: 6px 14px;
        border-radius: 4px;
        font-size: 12.5px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .cdm-btn-svc-quote:hover {
        background: #fb9a27;
        color: #ffffff;
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
        .cdm-cta-banner,
        .cdm-testimonials-section {
            background-attachment: scroll !important;
        }
        .cdm-about-grid,
        .cdm-partner-grid {
            grid-template-columns: 1fr;
            gap: 35px;
            padding: 0 16px;
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
            gap: 20px;
            padding: 0 16px;
        }
        .cdm-about-images {
            grid-template-columns: 1fr;
            gap: 12px;
        }
        .cdm-about-img1 {
            height: 280px;
        }
        .cdm-about-img2 {
            display: none;
        }
    }

    @media (max-width: 600px) {
        .cdm-hero-slider {
            min-height: 480px;
        }
        .cdm-slide {
            padding: 55px 16px 125px;
            min-height: 480px;
        }
        .cdm-slide-title {
            font-size: clamp(1.6rem, 6.5vw, 2.5rem);
        }
        .cdm-slide-desc {
            font-size: 14px;
            margin-bottom: 25px;
        }
        .cdm-slider-dots {
            bottom: 60px;
        }
        .cdm-flip-section {
            margin-top: -50px;
            margin-bottom: 45px;
        }
        .cdm-flip-grid {
            padding: 0 16px;
            gap: 14px;
        }
        .cdm-about-section {
            padding: 45px 0;
        }
        .cdm-about-img1 {
            height: 220px;
        }
        .cdm-about-content h2,
        .cdm-partner-grid h2,
        .cdm-innovative-title {
            font-size: clamp(1.35rem, 5.5vw, 1.85rem) !important;
            word-break: break-word;
        }
        .cdm-about-content p,
        .cdm-partner-grid p,
        .cdm-innovative-section p {
            text-align: left !important;
            font-size: 14px !important;
        }
        .cdm-innovative-section {
            padding: 45px 0 !important;
        }
        .cdm-metrics-strip {
            grid-template-columns: 1fr;
            gap: 14px;
            padding: 16px 0;
            margin: 20px 0;
        }
        .cdm-clients-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }
        .cdm-counter-right {
            grid-template-columns: 1fr;
            gap: 20px;
        }
        .cdm-services-grid {
            grid-template-columns: 1fr;
            padding: 0 16px;
        }
        .cdm-why-grid {
            grid-template-columns: 1fr;
        }
        .cdm-featured-card {
            padding: 12px;
        }
        .cdm-featured-card img {
            height: 210px;
        }
        .cdm-cta-container {
            text-align: center;
            justify-content: center;
            flex-direction: column;
            gap: 20px;
        }
        .cdm-btn-call {
            font-size: 16px;
            padding: 12px 28px;
            width: 100%;
            max-width: 260px;
            justify-content: center;
        }
    }

    /* ══════════════════════════════════════════════
       CORE PILLARS, PROCESS & CONTACT STYLING
       ══════════════════════════════════════════════ */
    .cdm-pillar-section {
        padding: 85px 0;
        background: #ffffff;
    }
    .cdm-pillar-section.cdm-alt-bg {
        background: #f8faff;
    }
    .cdm-pillar-grid {
        max-width: 1240px;
        margin: 0 auto;
        padding: 0 20px;
        display: grid;
        grid-template-columns: 1.15fr 0.85fr;
        gap: 60px;
        align-items: center;
    }
    .cdm-pillar-grid.cdm-reverse {
        grid-template-columns: 0.85fr 1.15fr;
    }
    .cdm-pillar-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 16px;
        background: rgba(3, 34, 143, 0.08);
        color: #03228f;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        margin-bottom: 15px;
    }
    .cdm-pillar-content h2 {
        font-size: clamp(1.85rem, 3.6vw, 2.6rem);
        font-weight: 800;
        color: #03228f;
        line-height: 1.22;
        margin-bottom: 18px;
    }
    .cdm-pillar-lead {
        font-size: 15.5px;
        line-height: 1.75;
        color: #475569;
        margin-bottom: 25px;
    }
    .cdm-pillar-features {
        display: flex;
        flex-direction: column;
        gap: 16px;
        margin-bottom: 30px;
    }
    .cdm-pfeat-item {
        display: flex;
        align-items: flex-start;
        gap: 16px;
        background: #ffffff;
        padding: 16px 18px;
        border-radius: 10px;
        border: 1px solid #eef2f6;
        transition: all 0.25s ease;
        box-shadow: 0 2px 10px rgba(3, 34, 143, 0.03);
    }
    .cdm-alt-bg .cdm-pfeat-item {
        background: #ffffff;
    }
    .cdm-pillar-section:not(.cdm-alt-bg) .cdm-pfeat-item {
        background: #f8faff;
    }
    .cdm-pfeat-item:hover {
        border-color: #fb9a27;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(3, 34, 143, 0.08);
    }
    .cdm-pfeat-icon {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        background: rgba(251, 154, 39, 0.12);
        color: #e08316;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }
    .cdm-pfeat-text h4 {
        font-size: 15px;
        font-weight: 700;
        color: #03228f;
        margin-bottom: 4px;
        line-height: 1.3;
    }
    .cdm-pfeat-text p {
        font-size: 13.5px;
        color: #64748b;
        margin: 0;
        line-height: 1.5;
    }
    .cdm-pillar-card {
        background: #ffffff;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 15px 40px rgba(3, 34, 143, 0.1);
        border: 1px solid #e2e8f0;
        text-align: center;
        position: relative;
    }
    .cdm-pillar-card img {
        width: 100%;
        height: 320px;
        object-fit: cover;
        border-radius: 10px;
        margin-bottom: 20px;
    }
    .cdm-pillar-stat-badge {
        position: absolute;
        bottom: 40px;
        right: 40px;
        background: linear-gradient(135deg, #03228f 0%, #02144a 100%);
        color: #ffffff;
        padding: 12px 20px;
        border-radius: 10px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        display: flex;
        align-items: center;
        gap: 12px;
        text-align: left;
    }
    .cdm-pillar-stat-badge .num {
        font-size: 24px;
        font-weight: 800;
        color: #fb9a27;
        line-height: 1;
    }
    .cdm-pillar-stat-badge .lbl {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #cbd5e1;
        line-height: 1.3;
    }

    /* Small Business Marketing Section */
    .cdm-smallbiz-section {
        padding: 85px 0;
        background: linear-gradient(135deg, #02144a 0%, #03228f 100%);
        color: #ffffff;
    }
    .cdm-smallbiz-card {
        max-width: 1240px;
        margin: 0 auto;
        padding: 0 20px;
    }
    .cdm-smallbiz-header {
        text-align: center;
        max-width: 800px;
        margin: 0 auto 45px;
    }
    .cdm-smallbiz-pill {
        display: inline-block;
        padding: 6px 18px;
        background: rgba(251, 154, 39, 0.2);
        border: 1px solid #fb9a27;
        color: #fb9a27;
        border-radius: 30px;
        font-size: 12.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 15px;
    }
    .cdm-smallbiz-header h2 {
        font-size: clamp(2rem, 3.8vw, 2.7rem);
        font-weight: 800;
        color: #ffffff;
        margin-bottom: 16px;
    }
    .cdm-smallbiz-header p {
        font-size: 16px;
        color: #cbd5e1;
        line-height: 1.7;
    }
    .cdm-smallbiz-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 24px;
        margin-bottom: 40px;
    }
    .cdm-sb-col {
        background: rgba(255, 255, 255, 0.06);
        border: 1px solid rgba(255, 255, 255, 0.12);
        border-radius: 12px;
        padding: 30px 24px;
        transition: all 0.3s ease;
        text-align: left;
    }
    .cdm-sb-col:hover {
        background: rgba(255, 255, 255, 0.1);
        border-color: #fb9a27;
        transform: translateY(-4px);
    }
    .cdm-sb-icon {
        width: 52px;
        height: 52px;
        border-radius: 12px;
        background: rgba(251, 154, 39, 0.2);
        color: #fb9a27;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        margin-bottom: 18px;
    }
    .cdm-sb-col h3 {
        font-size: 18px;
        font-weight: 700;
        color: #ffffff;
        margin-bottom: 10px;
    }
    .cdm-sb-col p {
        font-size: 14px;
        color: #cbd5e1;
        line-height: 1.6;
        margin: 0;
    }
    .cdm-smallbiz-action {
        text-align: center;
    }

    /* Process Section */
    .cdm-process-section {
        padding: 85px 0;
        background: #f8faff;
    }
    .cdm-process-grid {
        max-width: 1240px;
        margin: 0 auto;
        padding: 0 20px;
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 24px;
    }
    .cdm-process-step {
        background: #ffffff;
        border-radius: 12px;
        padding: 32px 22px 26px;
        border: 1px solid #eef2f6;
        box-shadow: 0 4px 15px rgba(3, 34, 143, 0.04);
        position: relative;
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
    }
    .cdm-process-step:hover {
        transform: translateY(-4px);
        border-color: #fb9a27;
        box-shadow: 0 12px 30px rgba(3, 34, 143, 0.1);
    }
    .cdm-step-num {
        font-size: 26px;
        font-weight: 900;
        color: #fb9a27;
        margin-bottom: 12px;
        line-height: 1;
        font-family: monospace;
    }
    .cdm-step-icon {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        background: rgba(3, 34, 143, 0.07);
        color: #03228f;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        margin-bottom: 16px;
    }
    .cdm-process-step h3 {
        font-size: 17px;
        font-weight: 700;
        color: #03228f;
        margin-bottom: 8px;
    }
    .cdm-process-step p {
        font-size: 13.5px;
        color: #64748b;
        line-height: 1.55;
        margin: 0;
    }

    /* Contact Section on Homepage */
    .cdm-home-contact-section {
        padding: 85px 0;
        background: #ffffff;
        border-top: 1px solid #e2e8f0;
    }
    .cdm-home-contact-wrapper {
        max-width: 1240px;
        margin: 0 auto;
        padding: 0 20px;
        display: grid;
        grid-template-columns: 1fr 1.05fr;
        gap: 60px;
        align-items: start;
    }
    .cdm-contact-pill {
        display: inline-block;
        padding: 6px 16px;
        background: rgba(251, 154, 39, 0.12);
        color: #e08316;
        border-radius: 20px;
        font-size: 12.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        margin-bottom: 12px;
    }
    .cdm-home-contact-info h2 {
        font-size: clamp(2rem, 3.8vw, 2.7rem);
        font-weight: 800;
        color: #03228f;
        line-height: 1.22;
        margin-bottom: 16px;
    }
    .cdm-home-contact-info p {
        font-size: 15.5px;
        color: #475569;
        line-height: 1.7;
        margin-bottom: 25px;
    }
    .cdm-contact-details-list {
        display: flex;
        flex-direction: column;
        gap: 16px;
        margin-bottom: 30px;
    }
    .cdm-cdetail-item {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        font-size: 14.5px;
        color: #334155;
    }
    .cdm-cdetail-item i {
        color: #fb9a27;
        font-size: 18px;
        margin-top: 3px;
        flex-shrink: 0;
    }
    .cdm-cdetail-item strong {
        display: block;
        color: #03228f;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 2px;
    }
    .cdm-cdetail-item a {
        color: #03228f;
        font-weight: 600;
        text-decoration: none;
    }
    .cdm-cdetail-item a:hover {
        color: #fb9a27;
    }
    .cdm-quick-channels {
        display: flex;
        gap: 14px;
        flex-wrap: wrap;
    }
    .cdm-btn-wa {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #25d366;
        color: #ffffff;
        font-weight: 700;
        font-size: 14.5px;
        padding: 12px 24px;
        border-radius: 6px;
        text-decoration: none;
        transition: all 0.25s ease;
    }
    .cdm-btn-wa:hover {
        background: #1eb956;
        transform: translateY(-2px);
        color: #ffffff;
    }
    .cdm-btn-call-direct {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #03228f;
        color: #ffffff;
        font-weight: 700;
        font-size: 14.5px;
        padding: 12px 24px;
        border-radius: 6px;
        text-decoration: none;
        transition: all 0.25s ease;
    }
    .cdm-btn-call-direct:hover {
        background: #fb9a27;
        transform: translateY(-2px);
        color: #ffffff;
    }
    .cdm-home-contact-form-box {
        background: #f8faff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 36px 32px;
        box-shadow: 0 10px 30px rgba(3, 34, 143, 0.05);
    }
    .cdm-form-header {
        margin-bottom: 22px;
    }
    .cdm-form-header h3 {
        font-size: 20px;
        font-weight: 800;
        color: #03228f;
        margin-bottom: 6px;
    }
    .cdm-form-header p {
        font-size: 13.5px;
        color: #64748b;
        margin: 0;
    }
    .cdm-form-group {
        margin-bottom: 16px;
    }
    .cdm-form-group label {
        display: block;
        font-size: 13px;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 6px;
    }
    .cdm-form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }
    .cdm-input {
        width: 100%;
        padding: 12px 14px;
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        background: #ffffff;
        font-size: 14px;
        font-family: inherit;
        color: #1e293b;
        box-sizing: border-box;
        transition: all 0.2s ease;
    }
    .cdm-input:focus {
        outline: none;
        border-color: #03228f;
        box-shadow: 0 0 0 3px rgba(3, 34, 143, 0.12);
    }
    .cdm-btn-submit-lead {
        width: 100%;
        padding: 14px 24px;
        background: #fb9a27;
        color: #ffffff;
        border: none;
        border-radius: 8px;
        font-size: 15px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.25s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        box-shadow: 0 4px 15px rgba(251, 154, 39, 0.3);
    }
    .cdm-btn-submit-lead:hover {
        background: #e08316;
        transform: translateY(-2px);
    }
    .cdm-form-status {
        margin-top: 14px;
        padding: 12px 14px;
        border-radius: 6px;
        font-size: 13.5px;
        font-weight: 600;
        text-align: center;
    }
    .cdm-form-status.success {
        background: #dcfce7;
        color: #15803d;
        border: 1px solid #86efac;
    }
    .cdm-form-status.error {
        background: #fee2e2;
        color: #b91c1c;
        border: 1px solid #fca5a5;
    }

    @media (max-width: 991px) {
        .cdm-pillar-grid, .cdm-pillar-grid.cdm-reverse {
            grid-template-columns: 1fr;
            gap: 35px;
        }
        .cdm-reverse .cdm-pillar-visual {
            order: 2;
        }
        .cdm-smallbiz-grid {
            grid-template-columns: 1fr;
            gap: 18px;
        }
        .cdm-process-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 18px;
        }
        .cdm-home-contact-wrapper {
            grid-template-columns: 1fr;
            gap: 40px;
        }
    }
    @media (max-width: 600px) {
        .cdm-process-grid {
            grid-template-columns: 1fr;
        }
        .cdm-form-row {
            grid-template-columns: 1fr;
            gap: 0;
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
            <?php if ($h_idx === 0): ?>
            <h1 class="cdm-slide-title">
                Digital Marketing Agency <span class="stroke">in Pondicherry</span>
            </h1>
            <p class="cdm-slide-desc">
                Looking for a digital marketing agency in Pondicherry? Cloud Aspire Solutions offers SEO, social media marketing, Google Ads and Meta Ads to help businesses improve online visibility and generate leads.
            </p>
            <?php else: ?>
            <div class="cdm-slide-title" role="heading" aria-level="2">
                <?= htmlspecialchars($first_word) ?> <span class="stroke"><?= htmlspecialchars($rest_words) ?></span>
            </div>
            <p class="cdm-slide-desc">
                <?= htmlspecialchars($hs['short_description'] ?? 'Best digital marketing services in Pondicherry designed to scale traffic, build authority, and convert visitors.') ?>
            </p>
            <?php endif; ?>
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
        <div class="cdm-feat-card cdm-reveal">
            <div>
                <div class="cdm-feat-icon-wrap">
                    <i class="fas <?= htmlspecialchars($f_icon) ?>"></i>
                </div>
                <h4><?= htmlspecialchars($fs['service_name']) ?></h4>
                <p><?= htmlspecialchars($fs['short_description'] ?? 'Expert solutions to scale your business growth and online visibility.') ?></p>
            </div>
            <a href="<?= !empty($fs['slug']) ? $base_url . '/service/' . htmlspecialchars($fs['slug']) : $base_url . '/contact?service=' . urlencode($fs['service_name']) ?>" class="cdm-feat-link">
                <span>Explore Service</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- 3. ABOUT US SECTION (CONNECTED TO DATABASE SETTINGS) -->
<?php
$about_title = !empty($settings['about_title']) ? $settings['about_title'] : 'Best Digital Marketing Company in Pondicherry';
$about_highlight = !empty($settings['about_highlight']) ? $settings['about_highlight'] : 'Best Digital Marketing';
$about_p1 = !empty($settings['about_p1']) ? $settings['about_p1'] : ($site_name . ' is an expert digital marketing and custom web architecture firm based in Pondicherry, India. With proven expertise across high-growth industries, our top-rated team empowers businesses with tailored strategies that drive measurable, compounding revenue.');
$about_p2 = !empty($settings['about_p2']) ? $settings['about_p2'] : 'Specializing in SEO services to boost organic visibility, PPC campaigns for Targeted Advertising, Social Media Marketing for audience engagement, Google Ads for High-Converting Leads, and Content Marketing to captivate and convert, we excel in creating growth-centric solutions.';
$about_p3 = !empty($settings['about_p3']) ? $settings['about_p3'] : ('Explore the power of digital excellence with ' . $site_name . ' — Pondicherry\'s leading digital partner. Let’s build your brand’s future together!');
$about_metric1_title = !empty($settings['about_metric1_title']) ? $settings['about_metric1_title'] : 'Drive More Leads';
$about_metric1_sub = !empty($settings['about_metric1_sub']) ? $settings['about_metric1_sub'] : 'Qualified High-Intent Inquiries';
$about_metric2_title = !empty($settings['about_metric2_title']) ? $settings['about_metric2_title'] : 'Boost More Sales';
$about_metric2_sub = !empty($settings['about_metric2_sub']) ? $settings['about_metric2_sub'] : 'Maximized ROI & Conversions';
$about_exp_title = !empty($settings['about_exp_title']) ? $settings['about_exp_title'] : '5+ Years of Industry Expertise';
$about_exp_sub = !empty($settings['about_exp_sub']) ? $settings['about_exp_sub'] : 'Pondicherry’s Most Trusted Digital Firm';

$about_rendered_title = htmlspecialchars($about_title);
if (!empty($about_highlight)) {
    $about_rendered_title = preg_replace('/(' . preg_quote($about_highlight, '/') . ')/i', '<span class="text-secondary">$1</span>', $about_rendered_title);
}
?>
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
            <h2><?= $about_rendered_title ?></h2>
            <p><?= htmlspecialchars($about_p1) ?></p>
            <?php if (!empty($about_p2)): ?>
            <p><?= htmlspecialchars($about_p2) ?></p>
            <?php endif; ?>
            <?php if (!empty($about_p3)): ?>
            <p><?= htmlspecialchars($about_p3) ?></p>
            <?php endif; ?>

            <div class="cdm-metrics-strip">
                <div class="cdm-metric-item">
                    <i class="fas fa-chart-line"></i>
                    <div>
                        <h4><?= htmlspecialchars($about_metric1_title) ?></h4>
                        <span style="font-size:13px; color:#64748b;"><?= htmlspecialchars($about_metric1_sub) ?></span>
                    </div>
                </div>
                <div class="cdm-metric-item">
                    <i class="fas fa-gauge-high"></i>
                    <div>
                        <h4><?= htmlspecialchars($about_metric2_title) ?></h4>
                        <span style="font-size:13px; color:#64748b;"><?= htmlspecialchars($about_metric2_sub) ?></span>
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

<!-- 4. REUSABLE CLIENTS 3D FLIP LOGO GRID (FROM DATABASE) -->
<?php include __DIR__ . '/../../../includes/components/clients_grid.php'; ?>

<!-- 5. WHY CHOOSE US (DYNAMIC FROM DATABASE `why_choose_us` TABLE) -->
<section class="cdm-partner-section">
    <div class="cdm-partner-grid">
        <div>
            <h2 style="font-size: clamp(1.8rem, 3.5vw, 2.6rem); font-weight: 800; color: #03228f; line-height: 1.25; margin-bottom: 20px;">
                Your Trusted Digital Marketing Partner in Pondicherry
            </h2>
            <p style="font-size: 15px; color: #475569; line-height: 1.7; margin-bottom: 20px; text-align: left;">
                At <?= htmlspecialchars($site_name) ?>, we pride ourselves on being the go-to agency in Pondicherry that businesses trust for consistent growth and measurable performance:
            </p>

            <div class="cdm-why-grid">
                <?php if (!empty($why_choose_us_items)): ?>
                    <?php foreach ($why_choose_us_items as $w): ?>
                    <div class="cdm-why-item">
                        <div class="cdm-why-icon-box">
                            <i class="fas <?= htmlspecialchars($w['icon'] ?? 'fa-check-circle') ?>"></i>
                        </div>
                        <div class="cdm-why-text">
                            <h5><?= htmlspecialchars($w['question']) ?></h5>
                            <p><?= htmlspecialchars($w['answer']) ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="cdm-why-item">
                        <div class="cdm-why-icon-box"><i class="fas fa-trophy"></i></div>
                        <div class="cdm-why-text">
                            <h5>Proven Track Record</h5>
                            <p>Over <?= (int)$stat_projects ?>+ successful digital initiatives with measurable ROI.</p>
                        </div>
                    </div>
                    <div class="cdm-why-item">
                        <div class="cdm-why-icon-box"><i class="fas fa-users-gear"></i></div>
                        <div class="cdm-why-text">
                            <h5>Certified Specialists</h5>
                            <p>Dedicated specialists in SEO, performance marketing & web dev.</p>
                        </div>
                    </div>
                    <div class="cdm-why-item">
                        <div class="cdm-why-icon-box"><i class="fas fa-chart-line"></i></div>
                        <div class="cdm-why-text">
                            <h5>Data-Driven Growth</h5>
                            <p>Every campaign decision backed by live analytics and ROI metrics.</p>
                        </div>
                    </div>
                    <div class="cdm-why-item">
                        <div class="cdm-why-icon-box"><i class="fas fa-headset"></i></div>
                        <div class="cdm-why-text">
                            <h5>Dedicated Support</h5>
                            <p>Direct access to account managers and strategy updates.</p>
                        </div>
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
            <h3><?= htmlspecialchars($about_exp_title) ?></h3>
            <span><?= htmlspecialchars($about_exp_sub) ?></span>
        </div>
    </div>
</section>

<!-- 6. REUSABLE CALL US PARALLAX BANNER (CONNECTED TO DATABASE SETTINGS) -->
<?php include __DIR__ . '/../../../includes/components/cta_banner.php'; ?>

<!-- 7. INNOVATIVE STRATEGIES SECTION (CONNECTED TO DATABASE SETTINGS) -->
<?php
$innovative_title = !empty($settings['innovative_title']) ? $settings['innovative_title'] : 'Innovative Strategies for Real Results';
$innovative_highlight = !empty($settings['innovative_highlight']) ? $settings['innovative_highlight'] : 'Real Results';
$innovative_desc = !empty($settings['innovative_desc']) ? $settings['innovative_desc'] : ('At ' . $site_name . ', we don’t just follow the trends — we engineer them. Our team of specialists is dedicated to creating innovative digital marketing strategies that yield real, tangible results.');
$innovative_b1 = !empty($settings['innovative_b1']) ? $settings['innovative_b1'] : 'High-converting audience remarketing & retargeting';
$innovative_b2 = !empty($settings['innovative_b2']) ? $settings['innovative_b2'] : 'Search-intent content optimization & semantic clustering';
$innovative_b3 = !empty($settings['innovative_b3']) ? $settings['innovative_b3'] : 'Omni-channel social media engagement & community building';
$innovative_footer = !empty($settings['innovative_footer']) ? $settings['innovative_footer'] : 'Our innovative approach is all about delivering Growth and Long-Term Business success.';

$innovative_rendered_title = htmlspecialchars($innovative_title);
if (!empty($innovative_highlight)) {
    $innovative_rendered_title = preg_replace('/(' . preg_quote($innovative_highlight, '/') . ')/i', '<span class="text-secondary">$1</span>', $innovative_rendered_title);
}
?>
<section class="cdm-innovative-section">
    <div class="cdm-partner-grid">
        <div style="order: 2;">
            <div class="cdm-featured-card">
                <img src="https://images.unsplash.com/photo-1551836022-d5d88e9218df?auto=format&fit=crop&w=800&q=80" alt="Data-Driven Marketing">
                <h3>Data-Driven Growth Architecture</h3>
                <span>Measurable Campaign Performance</span>
            </div>
        </div>

        <div style="order: 1;">
            <h2 class="cdm-innovative-title">
                <?= $innovative_rendered_title ?>
            </h2>
            <p style="font-size: 15px; color: #475569; line-height: 1.75; margin-bottom: 20px;">
                <?= htmlspecialchars($innovative_desc) ?>
            </p>
            <ul style="list-style: none; display: flex; flex-direction: column; gap: 14px; margin-bottom: 25px; padding: 0;">
                <li style="display: flex; align-items: flex-start; gap: 12px; font-weight: 600; color: #03228f; line-height: 1.45;">
                    <i class="fas fa-bullseye" style="color: #fb9a27; margin-top: 3px; flex-shrink: 0;"></i>
                    <span style="flex: 1; min-width: 0;"><?= htmlspecialchars($innovative_b1) ?></span>
                </li>
                <li style="display: flex; align-items: flex-start; gap: 12px; font-weight: 600; color: #03228f; line-height: 1.45;">
                    <i class="fas fa-pen-nib" style="color: #fb9a27; margin-top: 3px; flex-shrink: 0;"></i>
                    <span style="flex: 1; min-width: 0;"><?= htmlspecialchars($innovative_b2) ?></span>
                </li>
                <li style="display: flex; align-items: flex-start; gap: 12px; font-weight: 600; color: #03228f; line-height: 1.45;">
                    <i class="fas fa-chart-pie" style="color: #fb9a27; margin-top: 3px; flex-shrink: 0;"></i>
                    <span style="flex: 1; min-width: 0;"><?= htmlspecialchars($innovative_b3) ?></span>
                </li>
            </ul>
            <?php if (!empty($innovative_footer)): ?>
            <p style="font-size: 15px; color: #475569; line-height: 1.75; margin-bottom: 25px;">
                <?= htmlspecialchars($innovative_footer) ?>
            </p>
            <?php endif; ?>
            <a href="<?= $base_url ?>/why-choose-us" class="cdm-btn-primary">
                <span>Read More</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>

<!-- 8. REUSABLE PARALLAX STATS COUNTER (FROM DATABASE SETTINGS) -->
<?php include __DIR__ . '/../../../includes/components/stats_counter.php'; ?>

<!-- 9. SERVICES SHOWCASE (LIVE DATABASE `services` QUERY) -->
<section class="cdm-services-section" id="servicesSection">
    <div class="cdm-section-heading">
        <span style="color: #fb9a27; font-weight: 700; text-transform: uppercase; font-size: 13px; letter-spacing: 1px; display: block; margin-bottom: 8px;">
            // End-to-End Growth Solutions //
        </span>
        <h2>Digital Marketing Services <span class="text-secondary">We Offer</span></h2>
        <div class="cdm-divider"></div>
        <p style="max-width: 760px; margin: 15px auto 0; color: #475569; font-size: 15px; line-height: 1.6;">
            We engineer data-driven digital marketing campaigns specifically tailored to help Pondicherry businesses dominate local search, amplify social reach, and maximize customer acquisition.
        </p>
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
                <div>
                    <h3><?= htmlspecialchars($s['service_name']) ?></h3>
                    <p><?= htmlspecialchars($s['short_description'] ?? 'Tailored digital solutions engineered to scale traffic, build authority, and convert visitors into clients.') ?></p>
                </div>
                <div class="cdm-service-actions">
                    <a href="<?= !empty($s['slug']) ? $base_url . '/service/' . htmlspecialchars($s['slug']) : $base_url . '/contact?service=' . urlencode($s['service_name']) ?>" class="cdm-svc-link">
                        <span>Details</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                    <button type="button" class="cdm-btn-svc-quote" onclick="openContactPopup('<?= addslashes(htmlspecialchars($s['service_name'])) ?>')">
                        <span>Get Quote</span>
                        <i class="fas fa-paper-plane" style="font-size: 10px;"></i>
                    </button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- PILLAR 1: SEARCH ENGINE OPTIMIZATION (SEO) -->
<section class="cdm-pillar-section" id="seo-section">
    <div class="cdm-pillar-grid">
        <div class="cdm-pillar-content cdm-reveal-left">
            <div class="cdm-pillar-badge"><i class="fas fa-magnifying-glass-chart"></i> Organic Visibility &amp; Top Rankings</div>
            <h2>Search Engine Optimization (SEO)</h2>
            <p class="cdm-pillar-lead">
                Drive sustainable, high-intent organic customer traffic with tailored SEO strategies in Pondicherry. We optimize your digital presence for local Google Map packs, national searches, and AI-driven answer engines to turn searchers into paying clients.
            </p>
            <div class="cdm-pillar-features">
                <div class="cdm-pfeat-item">
                    <div class="cdm-pfeat-icon"><i class="fas fa-map-location-dot"></i></div>
                    <div class="cdm-pfeat-text">
                        <h4>Local SEO &amp; Google Business Profile</h4>
                        <p>Dominate the Google Local 3-Pack in Pondicherry, driving direct phone calls, walk-ins, and local service appointments.</p>
                    </div>
                </div>
                <div class="cdm-pfeat-item">
                    <div class="cdm-pfeat-icon"><i class="fas fa-bolt"></i></div>
                    <div class="cdm-pfeat-text">
                        <h4>On-Page &amp; Technical Architecture</h4>
                        <p>Speed optimization, Core Web Vitals, rich schema markup, and keyword clustering for fast, top-tier indexation.</p>
                    </div>
                </div>
                <div class="cdm-pfeat-item">
                    <div class="cdm-pfeat-icon"><i class="fas fa-link"></i></div>
                    <div class="cdm-pfeat-text">
                        <h4>Authority Link Building &amp; Content PR</h4>
                        <p>Earn authoritative contextual backlinks that cement your position as the most trusted brand in your market.</p>
                    </div>
                </div>
            </div>
            <div style="display: flex; gap: 16px; flex-wrap: wrap; align-items: center;">
                <a href="<?= $base_url ?>/contact?service=SEO+Optimization" class="cdm-btn-primary">
                    <span>Get Free SEO Audit</span>
                    <i class="fas fa-arrow-right"></i>
                </a>
                <a href="tel:<?= $contact_phone ?>" style="color: #03228f; font-weight: 700; text-decoration: none; font-size: 14.5px; display: inline-flex; align-items: center; gap: 8px;">
                    <i class="fas fa-phone-volume" style="color: #fb9a27;"></i> Speak With SEO Expert
                </a>
            </div>
        </div>
        <div class="cdm-pillar-visual cdm-reveal-right">
            <div class="cdm-pillar-card">
                <img src="https://images.unsplash.com/photo-1571786256017-aee7a0c009b6?auto=format&fit=crop&w=800&q=80" alt="Search Engine Optimization in Pondicherry" loading="lazy">
                <div class="cdm-pillar-stat-badge">
                    <div class="num">#1</div>
                    <div class="lbl">Page-One Google<br>Rankings</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- PILLAR 2: SOCIAL MEDIA MARKETING -->
<section class="cdm-pillar-section cdm-alt-bg" id="social-media-section">
    <div class="cdm-pillar-grid cdm-reverse">
        <div class="cdm-pillar-visual cdm-reveal-left">
            <div class="cdm-pillar-card">
                <img src="https://images.unsplash.com/photo-1611162617474-5b21e879e113?auto=format&fit=crop&w=800&q=80" alt="Social Media Marketing in Pondicherry" loading="lazy">
                <div class="cdm-pillar-stat-badge" style="left: 40px; right: auto;">
                    <div class="num">10x</div>
                    <div class="lbl">Audience Engagement<br>&amp; Brand Reach</div>
                </div>
            </div>
        </div>
        <div class="cdm-pillar-content cdm-reveal-right">
            <div class="cdm-pillar-badge"><i class="fas fa-share-nodes"></i> Audience Growth &amp; Viral Engagement</div>
            <h2>Social Media Marketing</h2>
            <p class="cdm-pillar-lead">
                Transform casual scrollers into passionate brand advocates. Our creative social media marketing strategies combine scroll-stopping visual design, viral Instagram reels, and targeted B2B LinkedIn campaigns to build unstoppable brand momentum.
            </p>
            <div class="cdm-pillar-features">
                <div class="cdm-pfeat-item">
                    <div class="cdm-pfeat-icon"><i class="fab fa-instagram"></i></div>
                    <div class="cdm-pfeat-text">
                        <h4>Instagram &amp; Facebook Mastery</h4>
                        <p>High-production short-form video reels, aesthetic carousel posts, and daily stories that capture consumer attention.</p>
                    </div>
                </div>
                <div class="cdm-pfeat-item">
                    <div class="cdm-pfeat-icon"><i class="fab fa-linkedin-in"></i></div>
                    <div class="cdm-pfeat-text">
                        <h4>B2B Social &amp; Corporate Branding</h4>
                        <p>Position your leadership and generate enterprise connections through data-backed B2B content strategies.</p>
                    </div>
                </div>
                <div class="cdm-pfeat-item">
                    <div class="cdm-pfeat-icon"><i class="fas fa-comments"></i></div>
                    <div class="cdm-pfeat-text">
                        <h4>Community Management &amp; Reputation</h4>
                        <p>Real-time comment engagement, message responses, and positive brand sentiment that turns followers into buyers.</p>
                    </div>
                </div>
            </div>
            <a href="<?= $base_url ?>/contact?service=Social+Media+Marketing" class="cdm-btn-primary">
                <span>Scale Your Social Presence</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>

<!-- PILLAR 3: GOOGLE ADS AND META ADS -->
<section class="cdm-pillar-section" id="paid-ads-section">
    <div class="cdm-pillar-grid">
        <div class="cdm-pillar-content cdm-reveal-left">
            <div class="cdm-pillar-badge"><i class="fas fa-bullhorn"></i> High-Intent Performance Advertising</div>
            <h2>Google Ads and Meta Ads</h2>
            <p class="cdm-pillar-lead">
                Stop guessing and start converting with precision paid advertising. We design, manage, and continuously optimize high-ROI PPC campaigns across Google Search, Display, YouTube, Facebook, and Instagram to deliver predictable revenue and lower acquisition costs.
            </p>
            <div class="cdm-pillar-features">
                <div class="cdm-pfeat-item">
                    <div class="cdm-pfeat-icon"><i class="fab fa-google"></i></div>
                    <div class="cdm-pfeat-text">
                        <h4>Google Search &amp; Intent-Based Ads</h4>
                        <p>Target buyers at the exact moment they search for your services with compelling ad copy and negative keyword guards.</p>
                    </div>
                </div>
                <div class="cdm-pfeat-item">
                    <div class="cdm-pfeat-icon"><i class="fab fa-meta"></i></div>
                    <div class="cdm-pfeat-text">
                        <h4>Meta (Facebook &amp; Instagram) Funnels</h4>
                        <p>Hyper-targeted demographic, behavioral, and lookalike audience campaigns that deliver instant qualified leads.</p>
                    </div>
                </div>
                <div class="cdm-pfeat-item">
                    <div class="cdm-pfeat-icon"><i class="fas fa-rotate"></i></div>
                    <div class="cdm-pfeat-text">
                        <h4>Dynamic Retargeting &amp; ROAS Optimization</h4>
                        <p>Re-engage website drop-offs and past visitors with tailored conversion offers to multiply your final checkout rate.</p>
                    </div>
                </div>
            </div>
            <a href="<?= $base_url ?>/contact?service=Google+%26+Meta+Ads" class="cdm-btn-primary">
                <span>Launch High-Converting Ads</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        <div class="cdm-pillar-visual cdm-reveal-right">
            <div class="cdm-pillar-card">
                <img src="https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=800&q=80" alt="Google Ads and Meta Ads in Pondicherry" loading="lazy">
                <div class="cdm-pillar-stat-badge">
                    <div class="num">4.8x</div>
                    <div class="lbl">Average Client<br>ROAS Multiplier</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- PILLAR 4: DIGITAL MARKETING FOR SMALL BUSINESSES -->
<section class="cdm-smallbiz-section" id="small-business-section">
    <div class="cdm-smallbiz-card">
        <div class="cdm-smallbiz-header">
            <span class="cdm-smallbiz-pill">Empowering Local Entrepreneurs</span>
            <h2>Digital Marketing for Small Businesses</h2>
            <p>
                We believe premium digital marketing should be accessible to growing enterprises. Whether you run a retail shop, boutique, clinic, restaurant, or professional firm in Pondicherry, our small business marketing packages deliver impactful, scalable results without burning your budget.
            </p>
        </div>
        <div class="cdm-smallbiz-grid">
            <div class="cdm-sb-col">
                <div class="cdm-sb-icon"><i class="fas fa-phone-volume"></i></div>
                <h3>Direct Phone &amp; WhatsApp Inquiries</h3>
                <p>Engineered to generate immediate calls and WhatsApp chats from customers actively searching for your service locally.</p>
            </div>
            <div class="cdm-sb-col">
                <div class="cdm-sb-icon"><i class="fas fa-hand-holding-dollar"></i></div>
                <h3>Cost-Effective, Transparent Pricing</h3>
                <p>Every rupee of your ad spend is accounted for. Zero hidden fees and scalable plans that grow alongside your revenue.</p>
            </div>
            <div class="cdm-sb-col">
                <div class="cdm-sb-icon"><i class="fas fa-chart-line"></i></div>
                <h3>Clear, Actionable Reporting</h3>
                <p>Simple dashboards and weekly reports tracking real customer inquiries, store visits, and revenue growth.</p>
            </div>
        </div>
        <div class="cdm-smallbiz-action">
            <a href="<?= $base_url ?>/contact?service=Small+Business+Package" class="cdm-btn-primary" style="background:#fb9a27; color:#ffffff;">
                <span>Get Small Business Marketing Strategy</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>

<!-- PILLAR 5: OUR DIGITAL MARKETING PROCESS -->
<section class="cdm-process-section" id="process-section">
    <div class="container" style="max-width: 1240px; margin: 0 auto; padding: 0 20px;">
        <div class="cdm-section-heading">
            <span style="color: #fb9a27; font-weight: 700; text-transform: uppercase; font-size: 13px; letter-spacing: 1px; display: block; margin-bottom: 8px;">
                // Proven Execution Framework //
            </span>
            <h2>Our Digital Marketing <span class="text-secondary">Process</span></h2>
            <div class="cdm-divider"></div>
            <p style="max-width: 760px; margin: 15px auto 0; color: #475569; font-size: 15.5px; line-height: 1.6;">
                Our systematic 4-stage process guarantees total transparency, rigorous testing, and compounding commercial growth.
            </p>
        </div>
        <div class="cdm-process-grid">
            <div class="cdm-process-step">
                <div class="cdm-step-num">01</div>
                <div class="cdm-step-icon"><i class="fas fa-magnifying-glass"></i></div>
                <h3>Discovery &amp; Market Audit</h3>
                <p>We analyze your business model, customer persona, competitor strengths, and search opportunities across Pondicherry and target regions.</p>
            </div>
            <div class="cdm-process-step">
                <div class="cdm-step-num">02</div>
                <div class="cdm-step-icon"><i class="fas fa-compass-drafting"></i></div>
                <h3>Custom Growth Strategy</h3>
                <p>We craft a targeted marketing blueprint combining high-intent SEO keywords, ad creatives, conversion funnels, and budget allocations.</p>
            </div>
            <div class="cdm-process-step">
                <div class="cdm-step-num">03</div>
                <div class="cdm-step-icon"><i class="fas fa-rocket"></i></div>
                <h3>Multi-Channel Launch</h3>
                <p>Our certified specialists launch targeted campaigns across Google, Meta, and Search with conversion tracking configured.</p>
            </div>
            <div class="cdm-process-step">
                <div class="cdm-step-num">04</div>
                <div class="cdm-step-icon"><i class="fas fa-chart-line"></i></div>
                <h3>Analyze, Optimize &amp; Scale</h3>
                <p>We run rigorous A/B tests on ad creatives and landing pages to lower cost-per-lead and aggressively scale your winning channels.</p>
            </div>
        </div>
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

<!-- 11. REUSABLE FREQUENTLY ASKED QUESTIONS SECTION (CONNECTED TO DATABASE `faqs`) -->
<?php include __DIR__ . '/../../../includes/components/faq_section.php'; ?>

<!-- 12. CONTACT CLOUD ASPIRE SOLUTIONS SECTION -->
<section class="cdm-home-contact-section" id="contactSection">
    <div class="cdm-home-contact-wrapper">
        <div class="cdm-home-contact-info cdm-reveal-left">
            <span class="cdm-contact-pill">Get In Touch Today</span>
            <h2>Contact <span class="text-secondary">Cloud Aspire Solutions</span></h2>
            <p>
                Ready to accelerate your business with Pondicherry's premier digital marketing agency? Reach out to our strategy consultants today for a free discovery session and personalized revenue proposal.
            </p>
            <div class="cdm-contact-details-list">
                <div class="cdm-cdetail-item">
                    <i class="fas fa-location-dot"></i>
                    <div>
                        <strong>Office Location:</strong>
                        <span><?= htmlspecialchars($contact_address) ?></span>
                    </div>
                </div>
                <div class="cdm-cdetail-item">
                    <i class="fas fa-phone"></i>
                    <div>
                        <strong>Phone Number:</strong>
                        <a href="tel:<?= $contact_phone ?>">+91 <?= htmlspecialchars($contact_phone) ?></a>
                    </div>
                </div>
                <div class="cdm-cdetail-item">
                    <i class="fas fa-envelope"></i>
                    <div>
                        <strong>Email Address:</strong>
                        <a href="mailto:<?= htmlspecialchars($contact_email) ?>"><?= htmlspecialchars($contact_email) ?></a>
                    </div>
                </div>
                <div class="cdm-cdetail-item">
                    <i class="fas fa-clock"></i>
                    <div>
                        <strong>Business Hours:</strong>
                        <span><?= htmlspecialchars($contact_hours) ?></span>
                    </div>
                </div>
            </div>
            <div class="cdm-quick-channels">
                <?php
                $clean_wa = preg_replace('/[^0-9]/', '', $contact_whatsapp);
                if (strlen($clean_wa) === 10) { $clean_wa = '91' . $clean_wa; }
                ?>
                <a href="https://wa.me/<?= $clean_wa ?>?text=<?= urlencode('Hi Cloud Aspire, I want to inquire about digital marketing services.') ?>" target="_blank" class="cdm-btn-wa">
                    <i class="fab fa-whatsapp"></i> Chat on WhatsApp
                </a>
                <a href="tel:<?= $contact_phone ?>" class="cdm-btn-call-direct">
                    <i class="fas fa-phone-volume"></i> Call Directly
                </a>
            </div>
        </div>
        <div class="cdm-home-contact-form-box cdm-reveal-right">
            <div class="cdm-form-header">
                <h3>Request a Free Growth Consultation</h3>
                <p>Fill out the form below and our strategy team will reach out within 24 hours.</p>
            </div>
            <form id="homeLeadForm" onsubmit="submitHomeLead(event)">
                <div class="cdm-form-group">
                    <label for="hl_name">Your Name *</label>
                    <input type="text" id="hl_name" name="name" required placeholder="e.g. John Doe" class="cdm-input">
                </div>
                <div class="cdm-form-row">
                    <div class="cdm-form-group">
                        <label for="hl_phone">Phone Number *</label>
                        <input type="tel" id="hl_phone" name="phone" required placeholder="Phone / WhatsApp" class="cdm-input">
                    </div>
                    <div class="cdm-form-group">
                        <label for="hl_email">Email Address *</label>
                        <input type="email" id="hl_email" name="email" required placeholder="name@example.com" class="cdm-input">
                    </div>
                </div>
                <div class="cdm-form-group">
                    <label for="hl_service">Service Needed</label>
                    <select id="hl_service" name="service" class="cdm-input">
                        <option value="Search Engine Optimization (SEO)">Search Engine Optimization (SEO)</option>
                        <option value="Social Media Marketing">Social Media Marketing</option>
                        <option value="Google Ads &amp; Meta Ads">Google Ads and Meta Ads</option>
                        <option value="Digital Marketing for Small Businesses">Digital Marketing for Small Businesses</option>
                        <option value="Website &amp; App Development">Website &amp; App Development</option>
                        <option value="Brand Identity &amp; Design">Brand Identity &amp; Design</option>
                    </select>
                </div>
                <div class="cdm-form-group">
                    <label for="hl_message">Your Project Details / Goals</label>
                    <textarea id="hl_message" name="message" rows="3" placeholder="Tell us about your business goals and requirements..." class="cdm-input"></textarea>
                </div>
                <!-- Anti-spam Honeypot -->
                <div style="display:none !important;" aria-hidden="true">
                    <input type="text" name="website_hp" tabindex="-1" autocomplete="off">
                </div>
                <button type="submit" id="hl_submit_btn" class="cdm-btn-submit-lead">
                    <span>Send Consultation Request</span>
                    <i class="fas fa-paper-plane"></i>
                </button>
                <div id="hl_status_msg" class="cdm-form-status" style="display:none;"></div>
            </form>
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
if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    let clientFlipInterval = setInterval(flipClientCards, 5000);
    const clientGrid = document.querySelector('.cdm-clients-grid');
    if (clientGrid) {
        clientGrid.addEventListener('mouseenter', () => clearInterval(clientFlipInterval));
        clientGrid.addEventListener('mouseleave', () => {
            clearInterval(clientFlipInterval);
            clientFlipInterval = setInterval(flipClientCards, 5000);
        });
    }
}

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

// 4. Homepage Direct Lead Capture AJAX Handler
function submitHomeLead(e) {
    e.preventDefault();
    const form = document.getElementById('homeLeadForm');
    const btn = document.getElementById('hl_submit_btn');
    const msgBox = document.getElementById('hl_status_msg');
    const formData = new FormData(form);

    btn.disabled = true;
    btn.innerHTML = '<span>Sending Request...</span> <i class="fas fa-spinner fa-spin"></i>';
    msgBox.style.display = 'none';
    msgBox.className = 'cdm-form-status';

    fetch('<?= $base_url ?>/api/lead-capture.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<span>Send Consultation Request</span> <i class="fas fa-paper-plane"></i>';
        msgBox.style.display = 'block';
        if (data.status === 'success') {
            msgBox.className = 'cdm-form-status success';
            msgBox.textContent = data.message || 'Thank you! Your request has been received. Our team will contact you shortly.';
            form.reset();
        } else {
            msgBox.className = 'cdm-form-status error';
            msgBox.textContent = data.message || 'Something went wrong. Please try again or call us directly.';
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = '<span>Send Consultation Request</span> <i class="fas fa-paper-plane"></i>';
        msgBox.style.display = 'block';
        msgBox.className = 'cdm-form-status error';
        msgBox.textContent = 'Network error. Please try again or reach out on WhatsApp.';
    });
}
</script>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>