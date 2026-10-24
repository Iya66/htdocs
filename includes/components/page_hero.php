<?php
/**
 * Reusable Component: Inner Page Hero Banner
 * 
 * Supports both:
 *  1. Direct include with variables ($hero_title, $hero_highlight, $hero_subtitle, $hero_breadcrumbs)
 *  2. Function call: render_page_hero([ 'title' => ..., 'highlight' => ..., 'subtitle' => ..., ... ])
 */

if (!function_exists('render_page_hero')) {
    function render_page_hero($params = []) {
        global $base_url, $pageTitle, $pageDesc;

        $hero_title       = $params['title'] ?? ($pageTitle ?? 'Cloud Aspire');
        $hero_highlight   = $params['highlight'] ?? '';
        $hero_subtitle    = $params['subtitle'] ?? ($pageDesc ?? '');
        $hero_bg_image    = $params['bg_image'] ?? 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=1600&q=80';
        $breadcrumb_act   = $params['breadcrumb_active'] ?? $hero_title;
        $b_url            = $params['base_url'] ?? ($base_url ?? '');
        $hero_breadcrumbs = $params['breadcrumbs'] ?? [
            ['label' => 'Home', 'url' => $b_url . '/'],
            ['label' => $breadcrumb_act]
        ];

        // Format highlight
        $rendered_title = htmlspecialchars($hero_title);
        if (!empty($hero_highlight)) {
            $hl_escaped = preg_quote($hero_highlight, '/');
            if (preg_match('/' . $hl_escaped . '/i', $rendered_title)) {
                $rendered_title = preg_replace('/(' . $hl_escaped . ')/i', '<span class="stroke">$1</span>', $rendered_title);
            } else {
                $rendered_title .= ' <span class="stroke">' . htmlspecialchars($hero_highlight) . '</span>';
            }
        }
        ?>
        <style>
        .cdm-page-hero {
            background: linear-gradient(135deg, rgba(2, 20, 74, 0.95) 0%, rgba(3, 34, 143, 0.92) 100%),
                        url('<?= htmlspecialchars($hero_bg_image) ?>') center/cover;
            padding: 95px 20px 85px;
            text-align: center;
            color: #ffffff;
            position: relative;
            overflow: hidden;
        }
        .cdm-page-hero h1 {
            font-size: clamp(2.2rem, 4.5vw, 3.5rem);
            font-weight: 800;
            color: #ffffff;
            margin-bottom: 14px;
            letter-spacing: -0.5px;
            line-height: 1.2;
        }
        .cdm-page-hero h1 span.stroke {
            color: var(--secondary-color, #fb9a27);
            text-shadow: 0 4px 20px rgba(251, 154, 39, 0.35);
        }
        .cdm-page-hero p {
            font-size: 16px;
            color: rgba(255, 255, 255, 0.9);
            max-width: 720px;
            margin: 0 auto 24px;
            line-height: 1.6;
        }
        .cdm-breadcrumb-capsule {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(8px);
            padding: 8px 24px;
            border-radius: 30px;
            font-size: 13.5px;
            font-weight: 600;
            border: 1px solid rgba(255, 255, 255, 0.15);
        }
        .cdm-breadcrumb-capsule a {
            color: #ffffff;
            text-decoration: none;
            transition: color 0.2s ease;
        }
        .cdm-breadcrumb-capsule a:hover {
            color: var(--secondary-color, #fb9a27);
        }
        .cdm-breadcrumb-capsule span.sep {
            color: rgba(255, 255, 255, 0.5);
            font-size: 11px;
        }
        .cdm-breadcrumb-capsule span.active {
            color: var(--secondary-color, #fb9a27);
        }

        @media (max-width: 600px) {
            .cdm-page-hero {
                padding: 65px 16px 55px;
            }
            .cdm-page-hero h1 {
                font-size: clamp(1.75rem, 6.5vw, 2.3rem);
                line-height: 1.25;
            }
            .cdm-page-hero p {
                font-size: 14px;
                margin-bottom: 18px;
            }
            .cdm-breadcrumb-capsule {
                padding: 6px 16px;
                font-size: 12px;
                flex-wrap: wrap;
                justify-content: center;
            }
        }
        </style>

        <section class="cdm-page-hero">
            <div class="container" style="max-width: 1240px; margin: 0 auto;">
                <h1><?= $rendered_title ?></h1>
                <?php if (!empty($hero_subtitle)): ?>
                    <p><?= htmlspecialchars($hero_subtitle) ?></p>
                <?php endif; ?>
                <?php if (!empty($hero_breadcrumbs)): ?>
                    <div class="cdm-breadcrumb-capsule">
                        <?php 
                        $b_count = count($hero_breadcrumbs);
                        foreach ($hero_breadcrumbs as $b_idx => $crumb): 
                            $is_last = ($b_idx === $b_count - 1);
                        ?>
                            <?php if (!empty($crumb['url']) && !$is_last): ?>
                                <a href="<?= htmlspecialchars($crumb['url']) ?>"><?= htmlspecialchars($crumb['label']) ?></a>
                            <?php else: ?>
                                <span class="active"><?= htmlspecialchars($crumb['label']) ?></span>
                            <?php endif; ?>
                            <?php if (!$is_last): ?>
                                <span class="sep"><i class="fas fa-chevron-right"></i></span>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
        <?php
    }
}

// Auto-render if included directly with set hero variables
if (isset($hero_title) || isset($hero_subtitle) || isset($hero_breadcrumbs)) {
    render_page_hero([
        'title'             => $hero_title ?? null,
        'highlight'         => $hero_highlight ?? '',
        'subtitle'          => $hero_subtitle ?? null,
        'breadcrumbs'       => $hero_breadcrumbs ?? null,
        'bg_image'          => $hero_bg_image ?? null
    ]);
    // Clear so repeated includes don't re-render accidentally
    unset($hero_title, $hero_highlight, $hero_subtitle, $hero_breadcrumbs, $hero_bg_image);
}
