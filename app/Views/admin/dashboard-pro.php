<?php
/**
 * Professional Admin Dashboard
 * Theme: Card Grid with Animated GIF Icons & Green Action Buttons
 */

$total_uploads = $uploads_count ?? 0;
$icon_path = '/files/lcons/';

// Pipeline percentages
$total_p = max(1, $leads_count);
$p_new = round(($lead_status_counts['new'] / $total_p) * 100);
$p_contacted = round(($lead_status_counts['contacted'] / $total_p) * 100);
$p_confirmed = round(($lead_status_counts['confirmed'] / $total_p) * 100);
$p_lose = round(($lead_status_counts['lose'] / $total_p) * 100);

// Dashboard stat cards — each maps to a GIF icon and a link
$dash_cards = [
    [
        'gif'   => 'leads.gif',
        'value' => number_format($leads_count),
        'label' => 'Total Leads',
        'link'  => $base_path . 'leads',
    ],
    [
        'gif'   => 'services.gif',
        'value' => number_format($services_count),
        'label' => 'Total Services',
        'link'  => $base_path . 'services',
    ],
    [
        'gif'   => 'new-leads.gif',
        'value' => number_format($new_leads),
        'label' => 'New Leads',
        'link'  => $base_path . 'leads',
    ],
    [
        'gif'   => 'reviews.gif',
        'value' => number_format($reviews_count),
        'label' => 'Total Reviews',
        'link'  => $base_path . 'reviews',
    ],
    [
        'gif'   => 'projects.gif',
        'value' => number_format((int)$projects_count),
        'label' => 'Client Projects',
        'link'  => $base_path . 'projects',
    ],
    [
        'gif'   => 'why-choose-us.gif',
        'value' => number_format($why_us_count),
        'label' => 'Why Choose Us',
        'link'  => $base_path . 'why-choose-us',
    ],
    [
        'gif'   => 'media-upload.gif',
        'value' => number_format($total_uploads),
        'label' => 'Media Uploads',
        'link'  => $base_path . 'files',
    ],
    [
        'gif'   => 'plans.gif',
        'value' => number_format($staff_count),
        'label' => 'Total Staff',
        'link'  => $base_path . 'settings/module',
    ],
];
?>

<style>
/* ═══════════════════════════════════════════════════════════════
   DASHBOARD THEME — CARD GRID WITH ANIMATED GIF ICONS
   ═══════════════════════════════════════════════════════════════ */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');

.dash-wrap {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    padding: 0;
}

/* ── STAT CARD GRID ── */
.dash-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-bottom: 28px;
}
@media (max-width: 1200px) {
    .dash-grid { grid-template-columns: repeat(3, 1fr); }
}
@media (max-width: 900px) {
    .dash-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 540px) {
    .dash-grid { grid-template-columns: 1fr; }
}

/* ── INDIVIDUAL STAT CARD ── */
.dash-stat-card {
    background: #FFFFFF;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
    overflow: hidden;
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    display: flex;
    flex-direction: column;
}
.dash-stat-card:hover {
    box-shadow: 0 8px 28px rgba(0, 0, 0, 0.1);
    transform: translateY(-4px);
}

/* Card body: icon left + number/label right */
.dash-stat-body {
    display: flex;
    align-items: center;
    padding: 22px 20px;
    gap: 16px;
    flex: 1;
}
.dash-stat-gif {
    width: 56px;
    height: 56px;
    object-fit: contain;
    flex-shrink: 0;
}
.dash-stat-info {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    flex: 1;
}
.dash-stat-value {
    font-size: 1.75rem;
    font-weight: 800;
    color: #1a1a2e;
    letter-spacing: -0.03em;
    line-height: 1.1;
}
.dash-stat-label {
    font-size: 0.82rem;
    font-weight: 600;
    color: #6B7280;
    margin-top: 4px;
    text-align: right;
}

/* Green action button bar */
.dash-stat-btn {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: linear-gradient(135deg, #7BC74D, #5CB338);
    padding: 10px 16px;
    text-decoration: none;
    color: #FFFFFF;
    font-size: 0.82rem;
    font-weight: 600;
    transition: all 0.2s ease;
}
.dash-stat-btn:hover {
    background: linear-gradient(135deg, #5CB338, #4A9E2F);
    color: #FFFFFF;
    padding-left: 20px;
}
.dash-stat-btn-icon {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.25);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    transition: transform 0.2s ease;
}
.dash-stat-btn:hover .dash-stat-btn-icon {
    transform: translateX(3px);
    background: rgba(255, 255, 255, 0.35);
}

/* ── CHART / PIPELINE GRID ── */
.dash-data-grid {
    display: grid;
    grid-template-columns: 1.5fr 1fr;
    gap: 20px;
    margin-bottom: 24px;
}
@media (max-width: 1024px) {
    .dash-data-grid { grid-template-columns: 1fr; }
}

/* ── PANEL CARDS ── */
.dash-panel {
    background: #FFFFFF;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
    transition: box-shadow 0.2s ease;
}
.dash-panel:hover {
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
}
.dash-panel-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
    padding-bottom: 14px;
    border-bottom: 2px solid #F0F0F0;
}
.dash-panel-title {
    font-size: 0.95rem;
    font-weight: 700;
    color: #1a1a2e;
    display: flex;
    align-items: center;
    gap: 10px;
}
.dash-panel-title-gif {
    width: 28px;
    height: 28px;
    object-fit: contain;
}
.dash-panel-link {
    font-size: 0.78rem;
    font-weight: 600;
    color: #5CB338;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 12px;
    border-radius: 6px;
    border: 1px solid #5CB338;
    transition: all 0.15s ease;
}
.dash-panel-link:hover {
    background: #5CB338;
    color: #FFFFFF;
}

/* Chart container */
.dash-chart-wrap {
    height: 250px;
    position: relative;
}

/* ── PIPELINE ROWS ── */
.dash-pipe-row {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 0;
    border-bottom: 1px solid #F5F5F5;
}
.dash-pipe-row:last-child {
    border-bottom: none;
}
.dash-pipe-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    flex-shrink: 0;
    box-shadow: 0 0 0 3px rgba(0,0,0,0.04);
}
.dash-pipe-info {
    flex: 1;
}
.dash-pipe-name {
    font-size: 0.84rem;
    font-weight: 600;
    color: #1a1a2e;
    margin-bottom: 6px;
}
.dash-pipe-track {
    height: 8px;
    background: #F0F0F0;
    border-radius: 9999px;
    overflow: hidden;
}
.dash-pipe-fill {
    height: 100%;
    border-radius: 9999px;
    transition: width 1s cubic-bezier(0.16, 1, 0.3, 1);
}
.dash-pipe-val {
    font-size: 0.84rem;
    font-weight: 700;
    color: #374151;
    flex-shrink: 0;
    min-width: 54px;
    text-align: right;
}
.dash-pipe-pct {
    color: #9CA3AF;
    font-weight: 500;
    font-size: 0.76rem;
}

/* ── ENTRANCE ANIMATIONS ── */
@keyframes cardSlideUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
.anim-card {
    animation: cardSlideUp 0.45s cubic-bezier(0.16, 1, 0.3, 1) both;
}
.ad1 { animation-delay: 0.04s; }
.ad2 { animation-delay: 0.08s; }
.ad3 { animation-delay: 0.12s; }
.ad4 { animation-delay: 0.16s; }
.ad5 { animation-delay: 0.20s; }
.ad6 { animation-delay: 0.24s; }
.ad7 { animation-delay: 0.28s; }
.ad8 { animation-delay: 0.32s; }
.ad9 { animation-delay: 0.38s; }
</style>

<div class="dash-wrap">

    <!-- ═══ 1. STAT CARDS GRID (4 x 2) ═══ -->
    <div class="dash-grid">
        <?php foreach ($dash_cards as $i => $card): ?>
        <div class="dash-stat-card anim-card ad<?= $i + 1 ?>">
            <div class="dash-stat-body">
                <img src="<?= $icon_path . htmlspecialchars($card['gif']) ?>" alt="<?= htmlspecialchars($card['label']) ?>" class="dash-stat-gif">
                <div class="dash-stat-info">
                    <div class="dash-stat-value"><?= $card['value'] ?></div>
                    <div class="dash-stat-label"><?= htmlspecialchars($card['label']) ?></div>
                </div>
            </div>
            <a href="<?= htmlspecialchars($card['link']) ?>" class="dash-stat-btn">
                <span>See in Detail</span>
                <span class="dash-stat-btn-icon"><i class="fas fa-arrow-right"></i></span>
            </a>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- ═══ 2. CHART + PIPELINE ═══ -->
    <div class="dash-data-grid anim-card ad9">

        <!-- Left: 7-Day Inquiry Velocity Chart -->
        <div class="dash-panel">
            <div class="dash-panel-head">
                <div class="dash-panel-title">
                    <img src="<?= $icon_path ?>new-leads.gif" alt="" class="dash-panel-title-gif">
                    <span>7-Day Inquiry Velocity</span>
                </div>
                <a href="<?= $base_path ?>reports" class="dash-panel-link">
                    Full Report <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            <div class="dash-chart-wrap">
                <canvas id="leadsVelocityChart"></canvas>
            </div>
        </div>

        <!-- Right: Pipeline Breakdown -->
        <div class="dash-panel">
            <div class="dash-panel-head">
                <div class="dash-panel-title">
                    <img src="<?= $icon_path ?>plans.gif" alt="" class="dash-panel-title-gif">
                    <span>Lead Pipeline</span>
                </div>
                <span style="font-size: 11px; font-weight: 700; color: #FFFFFF; background: #5CB338; padding: 4px 12px; border-radius: 6px;"><?= $leads_count ?> Total</span>
            </div>

            <div class="dash-pipe-row">
                <div class="dash-pipe-dot" style="background: #F59E0B;"></div>
                <div class="dash-pipe-info">
                    <div class="dash-pipe-name">New Inquiries</div>
                    <div class="dash-pipe-track">
                        <div class="dash-pipe-fill" style="width: <?= $p_new ?>%; background: linear-gradient(90deg, #F59E0B, #FBBF24);"></div>
                    </div>
                </div>
                <div class="dash-pipe-val"><?= $lead_status_counts['new'] ?> <span class="dash-pipe-pct">(<?= $p_new ?>%)</span></div>
            </div>

            <div class="dash-pipe-row">
                <div class="dash-pipe-dot" style="background: #3B82F6;"></div>
                <div class="dash-pipe-info">
                    <div class="dash-pipe-name">In Discussion</div>
                    <div class="dash-pipe-track">
                        <div class="dash-pipe-fill" style="width: <?= $p_contacted ?>%; background: linear-gradient(90deg, #3B82F6, #60A5FA);"></div>
                    </div>
                </div>
                <div class="dash-pipe-val"><?= $lead_status_counts['contacted'] ?> <span class="dash-pipe-pct">(<?= $p_contacted ?>%)</span></div>
            </div>

            <div class="dash-pipe-row">
                <div class="dash-pipe-dot" style="background: #10B981;"></div>
                <div class="dash-pipe-info">
                    <div class="dash-pipe-name">Won / Closed</div>
                    <div class="dash-pipe-track">
                        <div class="dash-pipe-fill" style="width: <?= $p_confirmed ?>%; background: linear-gradient(90deg, #10B981, #34D399);"></div>
                    </div>
                </div>
                <div class="dash-pipe-val"><?= $lead_status_counts['confirmed'] ?> <span class="dash-pipe-pct">(<?= $p_confirmed ?>%)</span></div>
            </div>

            <div class="dash-pipe-row">
                <div class="dash-pipe-dot" style="background: #EF4444;"></div>
                <div class="dash-pipe-info">
                    <div class="dash-pipe-name">Lost</div>
                    <div class="dash-pipe-track">
                        <div class="dash-pipe-fill" style="width: <?= $p_lose ?>%; background: linear-gradient(90deg, #EF4444, #F87171);"></div>
                    </div>
                </div>
                <div class="dash-pipe-val"><?= $lead_status_counts['lose'] ?> <span class="dash-pipe-pct">(<?= $p_lose ?>%)</span></div>
            </div>
        </div>
    </div>

</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const ctx = document.getElementById('leadsVelocityChart');
    if (!ctx) return;

    const labels = <?= json_encode($chart_dates) ?>;
    const data = <?= json_encode($chart_counts) ?>;

    const gradient = ctx.getContext('2d').createLinearGradient(0, 0, 0, 230);
    gradient.addColorStop(0, 'rgba(92, 179, 56, 0.18)');
    gradient.addColorStop(1, 'rgba(92, 179, 56, 0.01)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Inquiries',
                data: data,
                borderColor: '#5CB338',
                backgroundColor: gradient,
                borderWidth: 2.5,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#5CB338',
                pointBorderColor: '#FFFFFF',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 7,
                pointHoverBackgroundColor: '#4A9E2F',
                pointHoverBorderWidth: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1a1a2e',
                    titleColor: '#FFFFFF',
                    bodyColor: '#FFFFFF',
                    padding: 12,
                    cornerRadius: 8,
                    titleFont: { size: 12, weight: '700' },
                    bodyFont: { size: 12 },
                    displayColors: false,
                    callbacks: {
                        title: (items) => items[0].label,
                        label: (item) => item.raw + ' inquiries'
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: {
                        color: '#9CA3AF',
                        font: { size: 11, weight: '600', family: 'Inter' }
                    },
                    border: { display: false }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        color: '#9CA3AF',
                        font: { size: 11, weight: '600', family: 'Inter' },
                        precision: 0,
                        stepSize: 1
                    },
                    grid: { color: '#F0F0F0', drawBorder: false },
                    border: { display: false }
                }
            }
        }
    });
});
</script>
