<?php 
/**
 * Client Reviews View - CherriDM Theme
 * 100% Dynamic with Database Integration
 */
$reviews = $reviews ?? [];
$site_name = !empty($settings['site_name']) ? $settings['site_name'] : ($site_name ?? 'Cloud Aspire');
$avg_rating_display = $avg_rating_display ?? '0.0';
$stat_clients = $stat_clients ?? 0;
$stat_satisfaction = $stat_satisfaction ?? '0%';
$stat_retention = $stat_retention ?? '0%';
$msg_status = $msg_status ?? '';
$msg_text = $msg_text ?? '';
$csrf_token = $csrf_token ?? '';

include __DIR__ . '/../../../includes/header.php'; 
require_once __DIR__ . '/../../../includes/components/page_hero.php';
?>

<style>
    /* ══════════════════════════════════════════════════
       REVIEWS PAGE STYLES (CherriDM Design System)
       ══════════════════════════════════════════════════ */
    .cdm-reviews-section {
        padding: 60px 0 80px;
        background-color: #f8faff;
        width: 100%;
        box-sizing: border-box;
    }

    .cdm-reviews-container {
        max-width: 1240px;
        margin: 0 auto;
        padding: 0 20px;
        width: 100%;
        box-sizing: border-box;
    }

    /* ══════════════════════════════════════════════════
       1. LIVE METRICS STRIP (Consistent height, width & center alignment)
       ══════════════════════════════════════════════════ */
    .cdm-reviews-metrics {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 24px;
        margin-bottom: 50px;
        align-items: stretch;
    }

    .cdm-metric-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 30px 20px;
        text-align: center;
        box-shadow: 0 4px 16px rgba(3, 34, 143, 0.05);
        transition: all 0.35s ease;
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 140px;
        height: 100%;
        box-sizing: border-box;
    }

    .cdm-metric-card:hover {
        transform: translateY(-5px);
        border-color: #fb9a27;
        box-shadow: 0 14px 35px rgba(3, 34, 143, 0.12);
    }

    .cdm-metric-card h3 {
        font-size: clamp(2.2rem, 3.5vw, 2.7rem);
        font-weight: 800;
        color: #03228f;
        letter-spacing: -0.02em;
        line-height: 1.1;
        margin: 0 0 8px;
        text-align: center;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .cdm-metric-card p {
        color: #64748b;
        font-size: 0.95rem;
        font-weight: 600;
        margin: 0;
        line-height: 1.35;
        text-align: center;
    }

    /* ══════════════════════════════════════════════════
       2. SECTION HEADER BAR
       ══════════════════════════════════════════════════ */
    .cdm-reviews-header-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 35px;
        flex-wrap: wrap;
        gap: 16px;
    }

    .cdm-reviews-header-title .eyebrow {
        color: #fb9a27;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 13px;
        letter-spacing: 1px;
        display: block;
        margin-bottom: 4px;
    }

    .cdm-reviews-header-title h2 {
        font-size: clamp(1.8rem, 3vw, 2.3rem);
        font-weight: 800;
        color: #03228f;
        margin: 0;
        line-height: 1.25;
    }

    .cdm-reviews-write-btn {
        padding: 12px 24px;
        font-size: 0.95rem;
        border-radius: 8px;
        text-decoration: none;
    }

    /* ══════════════════════════════════════════════════
       3. REVIEWS GRID & CARDS
       ══════════════════════════════════════════════════ */
    .cdm-reviews-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
        gap: 28px;
    }

    .cdm-review-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 34px 28px;
        box-shadow: 0 4px 18px rgba(3, 34, 143, 0.05);
        display: flex;
        flex-direction: column;
        position: relative;
        transition: all 0.35s ease;
        box-sizing: border-box;
    }

    .cdm-review-card:hover {
        transform: translateY(-6px);
        border-color: #fb9a27;
        box-shadow: 0 16px 40px rgba(3, 34, 143, 0.12);
    }

    .cdm-quote-badge {
        position: absolute;
        top: 24px;
        right: 24px;
        font-size: 2.2rem;
        color: #fb9a27;
        opacity: 0.18;
        pointer-events: none;
        transition: all 0.3s ease;
    }

    .cdm-review-card:hover .cdm-quote-badge {
        opacity: 0.45;
        transform: scale(1.15) rotate(-6deg);
    }

    .cdm-review-stars {
        color: #fb9a27;
        font-size: 1.15rem;
        margin-bottom: 16px;
        letter-spacing: 3px;
    }

    .cdm-review-text {
        color: #475569;
        font-size: 0.98rem;
        line-height: 1.75;
        margin-bottom: 24px;
        font-style: italic;
        flex-grow: 1;
    }

    .cdm-review-author {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-top: auto;
        padding-top: 18px;
        border-top: 1px solid #f1f5f9;
    }

    .cdm-avatar-circle {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: #03228f;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #ffffff;
        font-weight: 700;
        font-size: 0.9rem;
        flex-shrink: 0;
        box-shadow: 0 4px 12px rgba(3, 34, 143, 0.2);
    }

    .cdm-author-info h4 {
        margin: 0;
        font-size: 1.02rem;
        font-weight: 700;
        color: #03228f;
    }

    .cdm-author-role {
        font-size: 0.82rem;
        color: #64748b;
        font-weight: 500;
        margin-top: 2px;
    }

    .cdm-verified-tag {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 0.76rem;
        color: #10b981;
        font-weight: 600;
        margin-top: 3px;
    }

    /* ══════════════════════════════════════════════════
       4. REVIEW SUBMISSION FORM CARD
       ══════════════════════════════════════════════════ */
    .cdm-review-form-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 44px 40px;
        box-shadow: 0 10px 35px rgba(3, 34, 143, 0.08);
        position: relative;
        overflow: hidden;
        margin-top: 60px;
        box-sizing: border-box;
    }

    .cdm-review-form-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #03228f, #fb9a27, #03228f);
    }

    .cdm-interactive-stars {
        display: inline-flex;
        flex-direction: row-reverse;
        gap: 6px;
    }

    .cdm-interactive-stars input {
        display: none;
    }

    .cdm-interactive-stars label {
        font-size: 2.2rem;
        color: #cbd5e1;
        cursor: pointer;
        transition: transform 0.2s ease, color 0.2s ease;
        user-select: none;
    }

    .cdm-interactive-stars label:hover,
    .cdm-interactive-stars label:hover ~ label,
    .cdm-interactive-stars input:checked ~ label {
        color: #fb9a27;
        transform: scale(1.15);
    }

    .cdm-rating-tag {
        font-size: 0.88rem;
        font-weight: 700;
        color: #03228f;
        background: #e9eeff;
        padding: 6px 14px;
        border-radius: 20px;
        border: 1px solid rgba(3, 34, 143, 0.15);
    }

    .cdm-input-control {
        width: 100%;
        padding: 13px 16px;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        font-size: 0.95rem;
        color: #0f172a;
        background: #f8faff;
        transition: all 0.25s ease;
        outline: none;
        box-sizing: border-box;
    }

    .cdm-input-control:focus {
        background: #ffffff;
        border-color: #03228f;
        box-shadow: 0 0 0 3px rgba(3, 34, 143, 0.12);
    }

    /* ══════════════════════════════════════════════════
       5. RESPONSIVE BREAKPOINTS
       ══════════════════════════════════════════════════ */
    @media (max-width: 992px) {
        .cdm-reviews-metrics {
            grid-template-columns: repeat(2, 1fr);
            gap: 18px;
            margin-bottom: 40px;
        }
    }

    @media (max-width: 650px) {
        .cdm-reviews-section {
            padding: 45px 0 60px;
        }
        .cdm-reviews-container {
            padding: 0 16px;
        }
        .cdm-reviews-metrics {
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            margin-bottom: 30px;
        }
        .cdm-metric-card {
            padding: 20px 12px;
            min-height: 110px;
        }
        .cdm-metric-card h3 {
            font-size: 1.8rem;
            margin-bottom: 4px;
        }
        .cdm-metric-card p {
            font-size: 0.82rem;
        }
        .cdm-reviews-header-bar {
            flex-direction: column;
            align-items: flex-start;
            gap: 16px;
        }
        .cdm-reviews-write-btn {
            width: 100%;
            justify-content: center;
        }
        .cdm-reviews-grid {
            grid-template-columns: 1fr;
            gap: 20px;
        }
        .cdm-review-card {
            padding: 24px 20px;
        }
        .cdm-review-form-card {
            padding: 30px 18px;
            margin-top: 45px;
        }
        .cdm-form-grid {
            grid-template-columns: 1fr !important;
        }
    }
</style>

<?php
render_page_hero([
    'title' => 'Client',
    'highlight' => 'Reviews',
    'subtitle' => 'Real feedback and verified ratings from businesses we have empowered to grow online',
    'breadcrumb_active' => 'Reviews',
    'base_url' => $base_url
]);
?>

<section class="cdm-reviews-section">
    <div class="cdm-reviews-container">
        <!-- ══════════════ 1. LIVE METRICS STRIP ══════════════ -->
        <div class="cdm-reviews-metrics">
            <div class="cdm-metric-card cdm-reveal">
                <h3 class="counter-num" data-target="<?= (float)$avg_rating_display ?>" data-decimals="1" data-suffix="/5"><?= $avg_rating_display ?>/5</h3>
                <p>Average Rating</p>
            </div>
            <div class="cdm-metric-card cdm-reveal">
                <h3 class="counter-num" data-target="<?= (int)$stat_clients ?>" data-decimals="0" data-suffix="+"><?= $stat_clients ?>+</h3>
                <p>Happy Clients</p>
            </div>
            <div class="cdm-metric-card cdm-reveal">
                <h3 class="counter-num" data-target="<?= (int)$satisfaction_percentage ?>" data-decimals="0" data-suffix="%"><?= $stat_satisfaction ?></h3>
                <p>Satisfaction Rate</p>
            </div>
            <div class="cdm-metric-card cdm-reveal">
                <h3 class="counter-num" data-target="<?= (int)$retention_percentage ?>" data-decimals="0" data-suffix="%"><?= $stat_retention ?></h3>
                <p>Positive Feedback</p>
            </div>
        </div>

        <!-- ══════════════ 2. SECTION HEADER BAR ══════════════ -->
        <div class="cdm-reviews-header-bar">
            <div class="cdm-reviews-header-title">
                <span class="eyebrow">// Client Feedback //</span>
                <h2>Verified Client <span class="text-secondary">Ratings &amp; Reviews</span></h2>
            </div>
            <a href="#write-review" class="cdm-btn-quote cdm-reviews-write-btn">
                <i class="fas fa-pen-nib"></i> <span>Write a Review</span>
            </a>
        </div>

        <!-- ══════════════ 3. REVIEWS GRID ══════════════ -->
        <div class="cdm-reviews-grid" id="reviewsGrid">
            <?php 
            $theme_colors = ['#03228f', '#fb9a27', '#0a1945', '#1e40af', '#0284c7'];
            if (!empty($reviews)): 
            ?>
                <?php foreach($reviews as $k => $r): 
                    $stars = (int)($r['star_rating'] ?? 5);
                    $reviewer_name = $r['name'] ?? 'Client';
                    $role_parts = array_filter([$r['designation'] ?? '', $r['company'] ?? '']);
                    $role_text = implode(' • ', $role_parts);
                    $bg_color = $theme_colors[$k % count($theme_colors)];
                ?>
                <div class="cdm-review-card cdm-reveal cdm-hover-lift" id="review-<?= (int)$r['id'] ?>">
                    <i class="fas fa-quote-right cdm-quote-badge"></i>
                    <div class="cdm-review-stars"><?= str_repeat('★', $stars) . str_repeat('☆', max(0, 5 - $stars)) ?></div>
                    <p class="cdm-review-text">"<?= htmlspecialchars($r['message'] ?? ($r['review_text'] ?? '')) ?>"</p>
                    <div class="cdm-review-author">
                        <div class="cdm-avatar-circle" style="background: <?= $bg_color ?>;">
                            <?= strtoupper(substr(trim($reviewer_name), 0, 2)) ?>
                        </div>
                        <div class="cdm-author-info">
                            <h4><?= htmlspecialchars($reviewer_name) ?></h4>
                            <?php if (!empty($role_text)): ?>
                                <div class="cdm-author-role"><?= htmlspecialchars($role_text) ?></div>
                            <?php endif; ?>
                            <div class="cdm-verified-tag">
                                <i class="fas fa-check-circle"></i> Verified Client &bull; <?= !empty($r['created_at']) ? date('M Y', strtotime($r['created_at'])) : 'Recent' ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div id="noReviewsPlaceholder" style="grid-column: 1/-1; text-align: center; padding: 70px 20px; background: #ffffff; border-radius: 14px; border: 1px dashed #cbd5e1;">
                    <div style="width: 70px; height: 70px; border-radius: 50%; background: rgba(3, 34, 143, 0.08); color: #03228f; display: inline-flex; align-items: center; justify-content: center; font-size: 1.8rem; margin-bottom: 20px;">
                        <i class="fas fa-star"></i>
                    </div>
                    <h3 style="font-size: 1.4rem; color: #03228f; margin-bottom: 8px;">No Reviews Listed Yet</h3>
                    <p style="color: #64748b; max-width: 460px; margin: 0 auto 24px;">Be the first to share your experience with <?= htmlspecialchars($site_name) ?>!</p>
                    <a href="#write-review" class="cdm-btn-quote" style="display: inline-flex;">
                        <span>Leave First Review</span>
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- ══════════════ 4. USER-SIDE REVIEW FORM ══════════════ -->
        <div class="cdm-review-form-card" id="write-review">
            <div style="text-align: center; max-width: 620px; margin: 0 auto 36px;">
                <span style="color: #fb9a27; font-weight: 700; text-transform: uppercase; font-size: 12px; letter-spacing: 1px; display: inline-block; margin-bottom: 6px;">
                    // Voice Your Experience //
                </span>
                <h2 style="font-size: clamp(1.8rem, 3vw, 2.3rem); font-weight: 800; color: #03228f; margin-bottom: 8px;">
                    Rate &amp; Review Our <span class="text-secondary">Services</span>
                </h2>
                <div style="width: 50px; height: 3px; background: #fb9a27; margin: 0 auto 12px; border-radius: 2px;"></div>
                <p style="color: #64748b; font-size: 0.95rem; margin: 0;">
                    Your feedback helps us continuously elevate our service standards and empowers fellow businesses.
                </p>
            </div>

            <?php if ($msg_status): ?>
                <div style="padding: 16px 20px; border-radius: 10px; margin-bottom: 24px; display: flex; align-items: center; gap: 12px; <?= $msg_status === 'success' ? 'background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534;' : 'background: #fef2f2; border: 1px solid #fecaca; color: #991b1b;' ?>" id="serverAlert">
                    <i class="fas fa-<?= $msg_status === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
                    <div><?= $msg_text ?></div>
                </div>
            <?php endif; ?>

            <div id="ajaxAlertContainer"></div>

            <div id="reviewFormWrapper">
                <form action="<?= $base_url ?>/reviews#write-review" method="POST" id="userReviewForm">
                    <input type="hidden" name="action" value="submit_review">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    
                    <div class="cdm-form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <!-- Star Rating Picker (Full Width) -->
                        <div style="grid-column: 1 / -1; margin-bottom: 4px;">
                            <label style="display: block; font-size: 0.9rem; font-weight: 700; color: #03228f; margin-bottom: 8px;">
                                Your Overall Rating <span style="color: #ef4444;">*</span>
                            </label>
                            <div style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap;">
                                <div class="cdm-interactive-stars">
                                    <input type="radio" name="star_rating" id="userStar5" value="5" checked>
                                    <label for="userStar5" title="5 Stars - Excellent">★</label>
                                    
                                    <input type="radio" name="star_rating" id="userStar4" value="4">
                                    <label for="userStar4" title="4 Stars - Very Good">★</label>
                                    
                                    <input type="radio" name="star_rating" id="userStar3" value="3">
                                    <label for="userStar3" title="3 Stars - Good">★</label>
                                    
                                    <input type="radio" name="star_rating" id="userStar2" value="2">
                                    <label for="userStar2" title="2 Stars - Fair">★</label>
                                    
                                    <input type="radio" name="star_rating" id="userStar1" value="1">
                                    <label for="userStar1" title="1 Star - Poor">★</label>
                                </div>
                                <span class="cdm-rating-tag" id="ratingSummaryLabel">★ 5 Stars - Excellent!</span>
                            </div>
                        </div>

                        <!-- Name -->
                        <div>
                            <label style="display: block; font-size: 0.9rem; font-weight: 700; color: #03228f; margin-bottom: 8px;">
                                Full Name <span style="color: #ef4444;">*</span>
                            </label>
                            <input type="text" name="name" id="reviewerName" class="cdm-input-control" placeholder="e.g. Rahul Sharma" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                        </div>

                        <!-- Email -->
                        <div>
                            <label style="display: block; font-size: 0.9rem; font-weight: 700; color: #03228f; margin-bottom: 8px;">
                                Email Address <span style="color: #ef4444;">*</span>
                            </label>
                            <input type="email" name="email" id="reviewerEmail" class="cdm-input-control" placeholder="e.g. rahul@example.com" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                            <div style="font-size: 0.78rem; color: #64748b; margin-top: 4px;">
                                <i class="fas fa-lock"></i> Confirmation receipt will be sent. Never published publicly.
                            </div>
                        </div>

                        <!-- Company / Business (Full Width) -->
                        <div style="grid-column: 1 / -1;">
                            <label style="display: block; font-size: 0.9rem; font-weight: 700; color: #03228f; margin-bottom: 8px;">
                                Business / Organization Name <span style="color: #94a3b8; font-weight: normal;">(Optional)</span>
                            </label>
                            <input type="text" name="company" id="reviewerCompany" class="cdm-input-control" placeholder="e.g. Cafe Heritage Pondy" value="<?= htmlspecialchars($_POST['company'] ?? '') ?>">
                        </div>

                        <!-- Review Message (Full Width) -->
                        <div style="grid-column: 1 / -1;">
                            <label style="display: block; font-size: 0.9rem; font-weight: 700; color: #03228f; margin-bottom: 8px;">
                                Your Review &amp; Experience <span style="color: #ef4444;">*</span>
                            </label>
                            <textarea name="message" id="reviewerMessage" class="cdm-input-control" rows="4" placeholder="Tell us about the campaigns executed, quality of work, team collaboration, and overall results achieved..." required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                        </div>

                        <!-- Submit Button (Full Width) -->
                        <div style="grid-column: 1 / -1; margin-top: 8px;">
                            <button type="submit" class="cdm-btn-quote" id="submitReviewBtn" style="width: 100%; justify-content: center; padding: 15px;">
                                <i class="fas fa-paper-plane"></i>
                                <span>Post the Review</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const starLabels = {
        '5': '★ 5 Stars - Excellent!',
        '4': '★ 4 Stars - Very Good',
        '3': '★ 3 Stars - Good',
        '2': '★ 2 Stars - Fair',
        '1': '★ 1 Star - Poor'
    };

    const ratingSummaryLabel = document.getElementById('ratingSummaryLabel');
    const starRadios = document.querySelectorAll('input[name="star_rating"]');
    
    starRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.checked && starLabels[this.value]) {
                ratingSummaryLabel.textContent = starLabels[this.value];
            }
        });
    });

    // AJAX Form Handling
    const form = document.getElementById('userReviewForm');
    const submitBtn = document.getElementById('submitReviewBtn');
    const alertContainer = document.getElementById('ajaxAlertContainer');
    const formWrapper = document.getElementById('reviewFormWrapper');
    const reviewsGrid = document.getElementById('reviewsGrid');
    const noReviewsPlaceholder = document.getElementById('noReviewsPlaceholder');

    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const name = document.getElementById('reviewerName').value.trim();
            const email = document.getElementById('reviewerEmail').value.trim();
            const message = document.getElementById('reviewerMessage').value.trim();

            if (!name || !email || !message) {
                showAlert('Please fill in all required fields.', 'error');
                return;
            }

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> <span>Posting your review...</span>';
            alertContainer.innerHTML = '';

            const formData = new FormData(form);
            formData.append('ajax', '1');

            fetch('<?= $base_url ?>/reviews', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> <span>Post the Review</span>';

                if (data.success) {
                    formWrapper.innerHTML = `
                        <div style="text-align: center; padding: 40px 20px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px;">
                            <div style="width: 64px; height: 64px; background: #dcfce7; color: #16a34a; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 2rem; margin-bottom: 16px;">
                                <i class="fas fa-check"></i>
                            </div>
                            <h3 style="color: #166534; font-size: 1.4rem; font-weight: 800; margin-bottom: 8px;">Review Submitted Successfully!</h3>
                            <p style="color: #334155; max-width: 520px; margin: 0 auto 16px; font-size: 0.95rem; line-height: 1.6;">
                                ${escapeHtml(data.message)}
                            </p>
                            <p style="color: #64748b; font-size: 0.85rem; margin-bottom: 20px;">
                                A thank-you note and receipt have been dispatched to <strong>${escapeHtml(email)}</strong>.
                            </p>
                            <button type="button" class="cdm-btn-quote" onclick="location.reload()" style="font-size: 0.9rem;">
                                <i class="fas fa-sync-alt"></i> Refresh Page
                            </button>
                        </div>
                    `;

                    if (data.review) {
                        if (noReviewsPlaceholder) {
                            noReviewsPlaceholder.remove();
                        }
                        const r = data.review;
                        const starStr = '★'.repeat(r.star_rating) + '☆'.repeat(Math.max(0, 5 - r.star_rating));
                        const initials = (r.name.trim().substring(0, 2) || 'CL').toUpperCase();
                        const roleSubtitle = (r.designation || r.company) 
                            ? `<div class="cdm-author-role">${escapeHtml([r.designation, r.company].filter(Boolean).join(' • '))}</div>` 
                            : '';

                        const newCard = document.createElement('div');
                        newCard.className = 'cdm-review-card';
                        newCard.style.borderColor = '#fb9a27';
                        newCard.innerHTML = `
                            <i class="fas fa-quote-right cdm-quote-badge"></i>
                            <div class="cdm-review-stars">${starStr}</div>
                            <p class="cdm-review-text">"${escapeHtml(r.message)}"</p>
                            <div class="cdm-review-author">
                                <div class="cdm-avatar-circle" style="background: #fb9a27;">
                                    ${initials}
                                </div>
                                <div class="cdm-author-info">
                                    <h4>${escapeHtml(r.name)} <span style="font-size: 0.72rem; background: #dcfce7; color: #166534; padding: 2px 6px; border-radius: 4px; margin-left: 6px;">Just Now</span></h4>
                                    ${roleSubtitle}
                                    <div class="cdm-verified-tag"><i class="fas fa-check-circle"></i> Verified Client &bull; ${r.date || 'Recent'}</div>
                                </div>
                            </div>
                        `;
                        reviewsGrid.prepend(newCard);
                        newCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                } else {
                    showAlert(data.message || 'Unable to post review. Please try again.', 'error');
                }
            })
            .catch(err => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> <span>Post the Review</span>';
                showAlert('Network error. Please try submitting again.', 'error');
            });
        });
    }

    function showAlert(text, type) {
        alertContainer.innerHTML = `
            <div style="padding: 14px 18px; border-radius: 10px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; ${type === 'success' ? 'background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534;' : 'background: #fef2f2; border: 1px solid #fecaca; color: #991b1b;'}">
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                <div>${escapeHtml(text)}</div>
            </div>
        `;
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // Number Count-Up Animation
    const counterElements = document.querySelectorAll('.counter-num');
    const countObserver = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                const el = entry.target;
                if (!el.dataset.counted) {
                    el.dataset.counted = 'true';
                    const target = parseFloat(el.getAttribute('data-target')) || 0;
                    const decimals = parseInt(el.getAttribute('data-decimals'), 10) || 0;
                    const suffix = el.getAttribute('data-suffix') || '';
                    const duration = 1400;
                    let startTime = null;

                    function animateCount(timestamp) {
                        if (!startTime) startTime = timestamp;
                        const progress = Math.min((timestamp - startTime) / duration, 1);
                        const ease = progress === 1 ? 1 : 1 - Math.pow(2, -10 * progress);
                        const currentVal = (ease * target).toFixed(decimals);
                        el.textContent = currentVal + suffix;
                        if (progress < 1) {
                            requestAnimationFrame(animateCount);
                        } else {
                            el.textContent = (target).toFixed(decimals) + suffix;
                        }
                    }

                    requestAnimationFrame(animateCount);
                }
                countObserver.unobserve(el);
            }
        });
    }, { threshold: 0.25 });

    counterElements.forEach((el) => countObserver.observe(el));
});
</script>

<?php include __DIR__ . '/../../../includes/components/cta_banner.php'; ?>
<?php include __DIR__ . '/../../../includes/footer.php'; ?>
