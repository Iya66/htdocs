<?php
/**
 * Reusable Component: CTA Banner (Connected to Database Settings)
 * 
 * Uses dynamic database values from $settings:
 *  - cta_title
 *  - cta_subtitle
 *  - cta_btn_text
 *  - contact_phone
 */

$cta_title    = !empty($settings['cta_title']) ? $settings['cta_title'] : 'Ready to Get Started? Let\'s Discuss your projects.';
$cta_subtitle = !empty($settings['cta_subtitle']) ? $settings['cta_subtitle'] : 'Pondicherry\'s Trusted Experts!';
$cta_btn_text = !empty($settings['cta_btn_text']) ? $settings['cta_btn_text'] : 'Call Us Now';
$contact_phone = !empty($settings['contact_phone']) ? $settings['contact_phone'] : '8098830937';
$clean_phone  = preg_replace('/[^0-9+]/', '', $contact_phone);
?>

<style>
.cdm-cta-banner-component {
    background: linear-gradient(135deg, rgba(3, 34, 143, 0.95) 0%, rgba(2, 20, 74, 0.98) 100%),
                url('https://images.unsplash.com/photo-1557804506-669a67965ba0?auto=format&fit=crop&w=1600&q=80') center/cover fixed;
    padding: 70px 20px;
    color: #ffffff;
    position: relative;
    overflow: hidden;
    width: 100%;
    box-sizing: border-box;
}

.cdm-cta-container-comp {
    max-width: 1240px;
    width: 100%;
    margin: 0 auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 30px;
    box-sizing: border-box;
}

.cdm-cta-text-comp {
    max-width: 820px;
    width: 100%;
    min-width: 0;
}

.cdm-cta-text-comp h3 {
    font-size: clamp(1.4rem, 4vw, 2.3rem);
    font-weight: 800;
    margin-bottom: 8px;
    color: #ffffff;
    line-height: 1.25;
    word-break: break-word;
    overflow-wrap: break-word;
}

.cdm-cta-text-comp p {
    font-size: 16px;
    color: var(--secondary-color, #fb9a27);
    font-weight: 600;
    word-break: break-word;
}

.cdm-btn-call-comp {
    background: var(--secondary-color, #fb9a27);
    color: #ffffff;
    padding: 15px 34px;
    border-radius: 30px;
    font-weight: 700;
    font-size: 16px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    box-shadow: 0 8px 25px rgba(251, 154, 39, 0.35);
    white-space: nowrap;
    line-height: 1.2;
}

.cdm-btn-call-comp i {
    font-size: 14px;
    line-height: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.25s ease;
}

.cdm-btn-call-comp:hover {
    background: #ffffff;
    color: var(--secondary-color, #fb9a27);
    transform: translateY(-3px);
    box-shadow: 0 12px 30px rgba(251, 154, 39, 0.5);
}

.cdm-btn-call-comp:hover i {
    transform: rotate(15deg) scale(1.1);
}

@media (max-width: 768px) {
    .cdm-cta-banner-component {
        background-attachment: scroll !important;
        padding: 42px 16px;
    }
    .cdm-cta-container-comp {
        text-align: center;
        justify-content: center;
        flex-direction: column;
        gap: 18px;
        padding: 0;
    }
    .cdm-cta-text-comp {
        width: 100%;
        max-width: 100%;
    }
    .cdm-cta-text-comp h3 {
        font-size: clamp(1.2rem, 5vw, 1.6rem);
        line-height: 1.3;
    }
    .cdm-cta-text-comp p {
        font-size: 14px;
    }
    .cdm-btn-call-comp {
        padding: 12px 28px;
        font-size: 14.5px;
        width: auto;
        min-width: 200px;
        max-width: 100%;
        justify-content: center;
    }
}
</style>

<section class="cdm-cta-banner-component">
    <div class="cdm-cta-container-comp">
        <div class="cdm-cta-text-comp">
            <h3><?= htmlspecialchars($cta_title) ?></h3>
            <p><?= htmlspecialchars($cta_subtitle) ?></p>
        </div>
        <div>
            <a href="tel:<?= htmlspecialchars($clean_phone) ?>" class="cdm-btn-call-comp">
                <span><?= htmlspecialchars($cta_btn_text) ?></span>
                <i class="fas fa-phone-alt" aria-hidden="true"></i>
            </a>
        </div>
    </div>
</section>
