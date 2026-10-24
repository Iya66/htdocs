<?php
/**
 * Reusable Component: Frequently Asked Questions (FAQ) Section
 * 
 * 100% Dynamically Connected to Database (`faqs` table) & Admin Panel
 * Supports both:
 *  1. Direct include: include __DIR__ . '/includes/components/faq_section.php';
 *  2. Function call: render_faq_section([ 'limit' => 6, 'title' => ..., ... ]);
 */

if (!function_exists('render_faq_section')) {
    function render_faq_section($params = []) {
        global $conn, $site_name, $faqs;

        $limit = $params['limit'] ?? 8;
        $section_bg = $params['bg_color'] ?? '#f8faff';
        $tagline = $params['tagline'] ?? '// Frequently Asked Questions //';
        $title_main = $params['title_main'] ?? 'Frequently Asked';
        $title_highlight = $params['title_highlight'] ?? 'Questions';
        $subtitle = $params['subtitle'] ?? 'Find quick answers to common questions about our digital marketing, SEO, PPC, and growth strategies.';
        $service_id = $params['service_id'] ?? null;

        // Fetch FAQs from DB if not already provided
        $faq_list = $params['faqs'] ?? ($faqs ?? []);
        if (empty($faq_list) && isset($conn)) {
            try {
                if ($service_id !== null) {
                    $stmt = $conn->prepare("SELECT * FROM faqs WHERE status = 'active' AND (service_id = :sid OR service_id IS NULL) ORDER BY COALESCE(sort_order, 0) ASC, id ASC LIMIT 50");
                    $stmt->bindValue(':sid', (int)$service_id, PDO::PARAM_INT);
                    $stmt->execute();
                    $faq_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } else {
                    $stmt = $conn->prepare("SELECT * FROM faqs WHERE status = 'active' ORDER BY COALESCE(sort_order, 0) ASC, id ASC LIMIT 50");
                    $stmt->execute();
                    $faq_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
                }
            } catch (Exception $e) {
                $faq_list = [];
            }
        }

        // Deduplicate questions to ensure diverse, high-value FAQ entries
        if (!empty($faq_list)) {
            $unique_faqs = [];
            $seen_questions = [];
            foreach ($faq_list as $f) {
                $norm = strtolower(preg_replace('/[^a-z0-9]/', '', $f['question'] ?? ''));
                if (!empty($norm) && !in_array($norm, $seen_questions)) {
                    $seen_questions[] = $norm;
                    $unique_faqs[] = $f;
                }
            }
            $faq_list = array_slice($unique_faqs, 0, $limit);
        }

        // High-converting defaults if DB is empty
        if (empty($faq_list)) {
            $company = !empty($site_name) ? $site_name : 'Cloud Aspire';
            $faq_list = [
                [
                    'question' => "How quickly can we see results from digital marketing campaigns?",
                    'answer' => "Paid advertising campaigns on Google and Meta start driving high-intent inquiries within 48 to 72 hours of launch. Organic SEO momentum typically begins showing compounding impressions and ranking growth within 30 to 60 days."
                ],
                [
                    'question' => "Do you offer custom tailored packages for specific business verticals?",
                    'answer' => "Yes! Over 70% of our partners receive tailored growth roadmaps engineered around their specific industry, customer acquisition target (CAC), and aggressive scalability goals."
                ],
                [
                    'question' => "What makes {$company} the top digital marketing company in Pondicherry?",
                    'answer' => "Unlike traditional agencies that prioritize vanity metrics like clicks and impressions, we focus strictly on bottom-line revenue, qualified leads, and measurable Return On Ad Spend (ROAS)."
                ],
                [
                    'question' => "How transparent is your campaign tracking and reporting?",
                    'answer' => "You receive 24/7 access to live analytics dashboards, bi-weekly performance reviews, and dedicated account manager communication with zero hidden charges or ambiguous metrics."
                ],
                [
                    'question' => "Can you help upgrade or rebuild our existing website or web app?",
                    'answer' => "Absolutely. Our web engineering specialists build ultra-fast, mobile-first, conversion-optimized websites and web applications with seamless tracking integrations and modern security."
                ]
            ];
        }
        ?>

        <style>
        .cdm-faq-component {
            padding: 85px 0;
            background-color: <?= htmlspecialchars($section_bg) ?>;
            position: relative;
            overflow: hidden;
            width: 100%;
        }

        .cdm-faq-component .container {
            max-width: 920px;
            margin: 0 auto;
            padding: 0 20px;
            box-sizing: border-box;
        }

        .cdm-faq-header-wrap {
            text-align: center;
            margin-bottom: 50px;
        }

        .cdm-faq-tagline {
            color: var(--secondary-color, #fb9a27);
            font-weight: 700;
            text-transform: uppercase;
            font-size: 13px;
            letter-spacing: 1.5px;
            display: inline-block;
            margin-bottom: 10px;
        }

        .cdm-faq-title {
            font-size: clamp(2rem, 3.8vw, 2.75rem);
            font-weight: 800;
            color: var(--primary-color, #03228f);
            margin-bottom: 12px;
            line-height: 1.25;
            word-break: break-word;
        }

        .cdm-faq-divider {
            width: 60px;
            height: 4px;
            background: var(--secondary-color, #fb9a27);
            margin: 0 auto 16px;
            border-radius: 2px;
        }

        .cdm-faq-desc {
            font-size: 15px;
            color: var(--text-muted, #64748b);
            max-width: 650px;
            margin: 0 auto;
            line-height: 1.65;
        }

        .cdm-faq-accordion {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .cdm-faq-card {
            background: #ffffff;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 0 2px 10px rgba(3, 34, 143, 0.03);
            border-left: 4px solid transparent;
        }

        .cdm-faq-card:hover {
            border-color: #cbd5e1;
            box-shadow: 0 8px 25px rgba(3, 34, 143, 0.07);
        }

        .cdm-faq-card.active {
            border-color: #03228f;
            border-left: 4px solid var(--secondary-color, #fb9a27);
            box-shadow: 0 12px 30px rgba(3, 34, 143, 0.1);
        }

        .cdm-faq-btn {
            width: 100%;
            background: transparent;
            border: none;
            padding: 22px 26px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            text-align: left;
            cursor: pointer;
            font-family: inherit;
            color: var(--primary-color, #03228f);
            transition: background-color 0.2s ease;
            -webkit-tap-highlight-color: transparent;
        }

        .cdm-faq-btn:hover {
            background-color: rgba(248, 250, 255, 0.6);
        }

        .cdm-faq-q-text {
            font-size: 17px;
            font-weight: 700;
            line-height: 1.45;
            color: #03228f;
            flex: 1;
            min-width: 0;
            word-break: break-word;
            overflow-wrap: break-word;
        }

        .cdm-faq-card.active .cdm-faq-q-text {
            color: #021a6e;
        }

        .cdm-faq-icon-badge {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #e9eeff;
            color: #03228f;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            flex-shrink: 0;
            transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .cdm-faq-card.active .cdm-faq-icon-badge {
            background: var(--secondary-color, #fb9a27);
            color: #ffffff;
            transform: rotate(180deg);
        }

        .cdm-faq-answer {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.35s cubic-bezier(0.16, 1, 0.3, 1), padding 0.35s ease;
            padding: 0 26px;
            font-size: 15px;
            color: #475569;
            line-height: 1.75;
            word-break: break-word;
            overflow-wrap: break-word;
        }

        .cdm-faq-card.active .cdm-faq-answer {
            max-height: 500px;
            padding: 0 26px 24px;
        }

        .cdm-faq-bottom-cta {
            text-align: center;
            margin-top: 45px;
            padding: 24px;
            background: #ffffff;
            border-radius: 10px;
            border: 1px dashed #cbd5e1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 18px;
            flex-wrap: wrap;
        }

        .cdm-faq-bottom-cta p {
            font-size: 15px;
            color: #334155;
            font-weight: 600;
            margin: 0;
        }

        .cdm-faq-cta-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--secondary-color, #fb9a27);
            font-weight: 700;
            text-decoration: none;
            transition: gap 0.2s ease;
        }

        .cdm-faq-cta-link:hover {
            color: #03228f;
            gap: 12px;
        }

        @media (max-width: 768px) {
            .cdm-faq-component {
                padding: 55px 0;
            }
            .cdm-faq-component .container {
                padding: 0 16px;
            }
            .cdm-faq-header-wrap {
                margin-bottom: 35px;
            }
            .cdm-faq-title {
                font-size: clamp(1.6rem, 6vw, 2.1rem);
            }
            .cdm-faq-btn {
                padding: 16px 18px;
                gap: 12px;
            }
            .cdm-faq-q-text {
                font-size: 15px;
            }
            .cdm-faq-icon-badge {
                width: 30px;
                height: 30px;
                font-size: 12px;
            }
            .cdm-faq-answer {
                font-size: 14px;
                line-height: 1.65;
                padding: 0 18px;
            }
            .cdm-faq-card.active .cdm-faq-answer {
                padding: 0 18px 20px;
            }
            .cdm-faq-bottom-cta {
                padding: 18px 16px;
                flex-direction: column;
                gap: 10px;
            }
        }
        </style>

        <section class="cdm-faq-component" id="faqSection">
            <div class="container">
                <div class="cdm-faq-header-wrap">
                    <span class="cdm-faq-tagline"><?= htmlspecialchars($tagline) ?></span>
                    <h2 class="cdm-faq-title">
                        <?= htmlspecialchars($title_main) ?> <span class="text-secondary"><?= htmlspecialchars($title_highlight) ?></span>
                    </h2>
                    <div class="cdm-faq-divider"></div>
                    <?php if (!empty($subtitle)): ?>
                    <p class="cdm-faq-desc"><?= htmlspecialchars($subtitle) ?></p>
                    <?php endif; ?>
                </div>

                <div class="cdm-faq-accordion" role="region" aria-label="Frequently Asked Questions">
                    <?php foreach ($faq_list as $idx => $item): 
                        $is_first = ($idx === 0);
                        $q_text = $item['question'] ?? '';
                        $a_text = $item['answer'] ?? '';
                    ?>
                    <div class="cdm-faq-card <?= $is_first ? 'active' : '' ?>" id="faqCard_<?= $idx ?>">
                        <button type="button" 
                                class="cdm-faq-btn" 
                                onclick="toggleFaqCard(this)" 
                                aria-expanded="<?= $is_first ? 'true' : 'false' ?>"
                                aria-controls="faqAns_<?= $idx ?>">
                            <span class="cdm-faq-q-text"><?= htmlspecialchars($q_text) ?></span>
                            <span class="cdm-faq-icon-badge">
                                <i class="fas fa-chevron-down"></i>
                            </span>
                        </button>
                        <div class="cdm-faq-answer" id="faqAns_<?= $idx ?>" role="region">
                            <p style="margin: 0;"><?= nl2br(htmlspecialchars($a_text)) ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="cdm-faq-bottom-cta">
                    <p>Have a question that's not answered here?</p>
                    <a href="<?= isset($base_url) ? $base_url : '' ?>/contact" class="cdm-faq-cta-link">
                        <span>Get in Touch with Our Team</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </section>

        <!-- Google Schema.org FAQPage Structured Data -->
        <script type="application/ld+json">
        {
          "@context": "https://schema.org",
          "@type": "FAQPage",
          "mainEntity": [
            <?php 
            $schema_entities = [];
            foreach ($faq_list as $f) {
                $schema_entities[] = json_encode([
                    '@type' => 'Question',
                    'name' => $f['question'] ?? '',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => strip_tags($f['answer'] ?? '')
                    ]
                ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            }
            echo implode(",\n            ", $schema_entities);
            ?>
          ]
        }
        </script>

        <script>
        if (typeof toggleFaqCard === 'undefined') {
            function toggleFaqCard(btn) {
                const card = btn.closest('.cdm-faq-card');
                const isAlreadyActive = card.classList.contains('active');
                
                // Close other accordion cards in this accordion
                const parentAccordion = card.closest('.cdm-faq-accordion');
                if (parentAccordion) {
                    parentAccordion.querySelectorAll('.cdm-faq-card').forEach(c => {
                        if (c !== card) {
                            c.classList.remove('active');
                            const b = c.querySelector('.cdm-faq-btn');
                            if (b) b.setAttribute('aria-expanded', 'false');
                        }
                    });
                }

                // Toggle target card
                if (isAlreadyActive) {
                    card.classList.remove('active');
                    btn.setAttribute('aria-expanded', 'false');
                } else {
                    card.classList.add('active');
                    btn.setAttribute('aria-expanded', 'true');
                }
            }
        }
        </script>
        <?php
    }
}

// Auto-render when included
render_faq_section($faq_params ?? []);
unset($faq_params);
