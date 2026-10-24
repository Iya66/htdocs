<?php
/**
 * Reusable Component: Parallax Stats Counter (Connected to Database Settings)
 * 
 * Uses dynamic database values from $settings:
 *  - stat_experience
 *  - stat_clients
 *  - stat_projects
 *  - stat_staff
 */

$stat_experience = (int)($settings['stat_experience'] ?? 5);
$stat_clients    = (int)($settings['stat_clients'] ?? 2);
$stat_projects   = (int)($settings['stat_projects'] ?? 1);
$stat_staff      = (int)($settings['stat_staff'] ?? 10);
?>

<style>
.cdm-counter-section-comp {
    background: linear-gradient(135deg, rgba(3, 34, 143, 0.95) 0%, rgba(2, 20, 74, 0.98) 100%),
                url('https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=1600&q=80') center/cover fixed;
    padding: 85px 20px;
    color: #ffffff;
    position: relative;
    overflow: hidden;
    width: 100%;
}

.cdm-counter-grid-comp {
    max-width: 1240px;
    width: 100%;
    margin: 0 auto;
    display: grid;
    grid-template-columns: 1fr 2fr;
    gap: 60px;
    align-items: center;
}

.cdm-counter-left-comp {
    text-align: center;
    border-right: 1px solid rgba(255, 255, 255, 0.15);
    padding-right: 40px;
}

.cdm-counter-left-comp .number {
    font-size: clamp(3.5rem, 6vw, 5.2rem);
    font-weight: 800;
    color: var(--secondary-color, #fb9a27);
    line-height: 1;
    margin-bottom: 12px;
}

.cdm-counter-left-comp h4 {
    font-size: 19px;
    letter-spacing: 2px;
    text-transform: uppercase;
    font-weight: 700;
    color: #ffffff;
}

.cdm-counter-right-comp {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 25px;
    text-align: center;
}

.cdm-stat-box-comp {
    padding: 15px;
}

.cdm-stat-box-comp h3 {
    font-size: clamp(2.2rem, 4vw, 3rem);
    font-weight: 800;
    color: #ffffff;
    margin-bottom: 8px;
    line-height: 1;
}

.cdm-stat-box-comp p {
    font-size: 15px;
    color: rgba(255, 255, 255, 0.85);
    font-weight: 600;
}

@media (max-width: 900px) {
    .cdm-counter-section-comp {
        background-attachment: scroll !important;
        padding: 55px 16px;
    }
    .cdm-counter-grid-comp {
        grid-template-columns: 1fr;
        gap: 30px;
    }
    .cdm-counter-left-comp {
        border-right: none;
        border-bottom: 1px solid rgba(255, 255, 255, 0.15);
        padding-right: 0;
        padding-bottom: 30px;
    }
    .cdm-counter-left-comp .number {
        font-size: clamp(3rem, 7vw, 4.2rem);
    }
    .cdm-counter-left-comp h4 {
        font-size: 16px;
    }
}

@media (max-width: 600px) {
    .cdm-counter-right-comp {
        grid-template-columns: 1fr;
        gap: 18px;
    }
    .cdm-stat-box-comp {
        padding: 10px;
        min-width: 0;
    }
    .cdm-stat-box-comp h3 {
        font-size: clamp(2rem, 6vw, 2.3rem);
    }
}
</style>

<section class="cdm-counter-section-comp">
    <div class="cdm-counter-grid-comp">
        <div class="cdm-counter-left-comp">
            <div class="number"><?= $stat_experience ?>+</div>
            <h4>YEARS OF EXPERIENCE</h4>
        </div>
        <div class="cdm-counter-right-comp">
            <div class="cdm-stat-box-comp">
                <h3><?= $stat_clients ?>+</h3>
                <p>Happy Clients</p>
            </div>
            <div class="cdm-stat-box-comp">
                <h3><?= $stat_projects ?>+</h3>
                <p>Finished Projects</p>
            </div>
            <div class="cdm-stat-box-comp">
                <h3><?= $stat_staff ?>+</h3>
                <p>Specialists &amp; Staff</p>
            </div>
        </div>
    </div>
</section>
