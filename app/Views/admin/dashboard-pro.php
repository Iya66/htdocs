<?php
/**
 * Professional Admin Dashboard
 * Theme: Card Grid with Animated GIF Icons & Green Action Buttons
 */

$total_uploads = $uploads_count ?? 0;
$icon_path = '/files/lcons/';

$call_not_answered_count = $call_not_answered_count ?? 0;
$call_back_later_count = $call_back_later_count ?? 0;
$already_has_website_count = $already_has_website_count ?? 0;
$no_website_count = $no_website_count ?? 0;
$website_improvement_count = $website_improvement_count ?? 0;
$interested_count = $interested_count ?? 0;
$followup_pending_count = $followup_pending_count ?? 0;
$meeting_scheduled_count = $meeting_scheduled_count ?? 0;
$proposal_sent_count = $proposal_sent_count ?? 0;
$converted_count = $converted_count ?? 0;
$not_interested_count = $not_interested_count ?? 0;
$overdue_followups_count = $overdue_followups_count ?? 0;
$upcoming_followups_count = $upcoming_followups_count ?? 0;
$conversion_rate = $conversion_rate ?? 0.0;


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

/* ── ADMIN WELCOME & PROFILE BANNER ── */
.dash-welcome-card {
    background: #FFFFFF;
    border-radius: 12px;
    padding: 20px 24px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    border: 1px solid #E2E8F0;
    border-left: 4px solid #2563EB;
}
.dash-welcome-left {
    display: flex;
    align-items: center;
    gap: 16px;
}
.dash-welcome-avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    overflow: hidden;
    background: #2563EB;
    color: #FFFFFF;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    font-weight: 700;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
    flex-shrink: 0;
    border: 2px solid #FFFFFF;
}
.dash-welcome-avatar-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
.dash-welcome-initial {
    line-height: 1;
}
.dash-welcome-greeting {
    font-size: 1.12rem;
    font-weight: 600;
    color: #0F172A;
    margin-bottom: 4px;
}
.dash-welcome-greeting strong {
    color: #2563EB;
}
.dash-welcome-meta {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
    font-size: 0.82rem;
    color: #64748B;
}
.dash-role-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: #EFF6FF;
    color: #2563EB;
    padding: 2px 10px;
    border-radius: 9999px;
    font-weight: 600;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}
.dash-welcome-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}
.dash-welcome-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 0.84rem;
    font-weight: 600;
    text-decoration: none;
    background: #F1F5F9;
    color: #334155;
    border: 1px solid #CBD5E1;
    transition: all 0.2s ease;
}
.dash-welcome-btn:hover {
    background: #E2E8F0;
    color: #0F172A;
}
.dash-welcome-btn-primary {
    background: #2563EB;
    color: #FFFFFF;
    border-color: #2563EB;
}
.dash-welcome-btn-primary:hover {
    background: #1D4ED8;
    color: #FFFFFF;
}
@media (max-width: 768px) {
    .dash-welcome-card {
        flex-direction: column;
        align-items: flex-start;
        padding: 16px;
    }
    .dash-welcome-actions {
        width: 100%;
    }
    .dash-welcome-btn {
        flex: 1;
        justify-content: center;
    }
}
</style>

<div class="dash-wrap">

    <!-- ═══ ADMIN WELCOME & PROFILE BANNER ═══ -->
    <div class="dash-welcome-card anim-card">
        <div class="dash-welcome-left">
            <div class="dash-welcome-avatar">
                <?php if (!empty($admin_avatar_url)): ?>
                    <img src="<?= htmlspecialchars($admin_avatar_url, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($admin_name ?? 'Admin', ENT_QUOTES, 'UTF-8') ?>" class="dash-welcome-avatar-img" onerror="this.onerror=null;this.parentElement.innerHTML='<span class=\'dash-welcome-initial\'><?= htmlspecialchars($admin_initial ?? 'A', ENT_QUOTES, 'UTF-8') ?></span>';">
                <?php else: ?>
                    <span class="dash-welcome-initial"><?= htmlspecialchars($admin_initial ?? 'A', ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
            </div>
            <div class="dash-welcome-text">
                <div class="dash-welcome-greeting">Welcome back, <strong><?= htmlspecialchars($admin_name ?? 'Administrator', ENT_QUOTES, 'UTF-8') ?></strong>!</div>
                <div class="dash-welcome-meta">
                    <span class="dash-role-pill"><i class="fas fa-shield-alt"></i> <?= htmlspecialchars(ucfirst((string)($admin_role ?? 'admin')), ENT_QUOTES, 'UTF-8') ?></span>
                    <span><i class="far fa-calendar-alt"></i> <?= date('l, F j, Y') ?></span>
                </div>
            </div>
        </div>
        <div class="dash-welcome-actions">
            <a href="<?= $base_path ?>accounts.php" class="dash-welcome-btn">
                <i class="fas fa-camera"></i> Change Photo
            </a>
            <a href="<?= $base_path ?>accounts.php" class="dash-welcome-btn dash-welcome-btn-primary">
                <i class="fas fa-user-edit"></i> Edit Profile
            </a>
        </div>
    </div>

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

<?php
// Pipeline rows are generated from app/lead_statuses.php so the dashboard
// always agrees with the Leads CRM. Percentages are relative to the grouped
// total, which keeps the bars comparable even as stages are added.
$group_counts = $lead_group_counts ?? [];
$group_total = max(1, array_sum(array_column($group_counts, 'count')));
?>
<?php foreach ($group_counts as $group): ?>
            <div class="dash-pipe-row">
                <div class="dash-pipe-dot" style="background: <?= htmlspecialchars($group['color'], ENT_QUOTES, 'UTF-8') ?>;"></div>
                <div class="dash-pipe-info">
                    <div class="dash-pipe-name"><?= htmlspecialchars($group['label'], ENT_QUOTES, 'UTF-8') ?></div>
                    <div class="dash-pipe-track">
                        <div class="dash-pipe-fill" style="width: <?= round(($group['count'] / $group_total) * 100) ?>%; background: linear-gradient(90deg, <?= htmlspecialchars($group['color'], ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars($group['color'], ENT_QUOTES, 'UTF-8') ?>cc);"></div>
                    </div>
                </div>
                <div class="dash-pipe-val"><?= number_format($group['count']) ?> <span class="dash-pipe-pct">(<?= round(($group['count'] / $group_total) * 100) ?>%)</span></div>
            </div>
<?php endforeach; ?>
            <div style="margin-top: var(--space-4); padding-top: var(--space-3); border-top: 1px solid var(--border-light);">
              <a href="<?= $base_path ?>leads" style="font-size: 11px; font-weight: 600; color: var(--primary); text-decoration: none;">
                Manage all <?= count(leadStatusKeys()) ?> pipeline stages <i class="fas fa-arrow-right"></i>
              </a>
            </div>
        </div>
    </div>

    <!-- ═══ 3. LEAD STAGES & FOLLOW-UP PERFORMANCE (SECTION 8) ═══ -->
    <div class="dash-panel anim-card" style="margin-top: 24px; padding: 24px;">
        <div class="dash-panel-head" style="flex-wrap: wrap; gap: 12px; margin-bottom: 16px;">
            <div class="dash-panel-title">
                <i class="fas fa-layer-group" style="color: var(--primary, #3B82F6); font-size: 18px;"></i>
                <span>12-Stage Lead Performance & Follow-up Tracking</span>
            </div>
            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                <span style="font-size: 12px; font-weight: 700; color: #10B981; background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.25); padding: 5px 12px; border-radius: 9999px;">
                    <i class="fas fa-chart-line"></i> Conversion Rate: <?= $conversion_rate ?>%
                </span>
                <?php if ($overdue_followups_count > 0): ?>
                <a href="<?= $base_path ?>leads?stage=overdue" style="font-size: 12px; font-weight: 700; color: #EF4444; background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.25); padding: 5px 12px; border-radius: 9999px; text-decoration: none;">
                    <i class="fas fa-exclamation-triangle"></i> <?= $overdue_followups_count ?> Overdue Follow-up<?= $overdue_followups_count > 1 ? 's' : '' ?>
                </a>
                <?php endif; ?>
                <?php if ($upcoming_followups_count > 0): ?>
                <a href="<?= $base_path ?>leads?stage=upcoming" style="font-size: 12px; font-weight: 600; color: #3B82F6; background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.25); padding: 5px 12px; border-radius: 9999px; text-decoration: none;">
                    <i class="fas fa-calendar-alt"></i> <?= $upcoming_followups_count ?> Upcoming
                </a>
                <?php endif; ?>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(170px, 1fr)); gap: 12px; margin-top: 14px;">
            <?php
            $stageCards = [
                ['label' => 'New Leads', 'key' => 'new_lead', 'count' => $new_leads, 'icon' => 'fa-inbox', 'color' => '#3B82F6'],
                ['label' => 'Call Not Answered', 'key' => 'call_not_answered', 'count' => $call_not_answered_count, 'icon' => 'fa-phone-slash', 'color' => '#64748B'],
                ['label' => 'Call Back Later', 'key' => 'call_back_later', 'count' => $call_back_later_count, 'icon' => 'fa-clock', 'color' => '#F59E0B'],
                ['label' => 'Already Has Website', 'key' => 'already_has_website', 'count' => $already_has_website_count, 'icon' => 'fa-globe', 'color' => '#06B6D4'],
                ['label' => 'No Website', 'key' => 'no_website', 'count' => $no_website_count, 'icon' => 'fa-laptop-code', 'color' => '#14B8A6'],
                ['label' => 'Improvement Needed', 'key' => 'website_improvement', 'count' => $website_improvement_count, 'icon' => 'fa-wrench', 'color' => '#6366F1'],
                ['label' => 'Interested', 'key' => 'interested', 'count' => $interested_count, 'icon' => 'fa-thumbs-up', 'color' => '#8B5CF6'],
                ['label' => 'Follow-up Pending', 'key' => 'followup_pending', 'count' => $followup_pending_count, 'icon' => 'fa-bell', 'color' => '#F97316'],
                ['label' => 'Meeting Scheduled', 'key' => 'meeting_scheduled', 'count' => $meeting_scheduled_count, 'icon' => 'fa-calendar-check', 'color' => '#0EA5E9'],
                ['label' => 'Proposal Sent', 'key' => 'proposal_sent', 'count' => $proposal_sent_count, 'icon' => 'fa-file-invoice-dollar', 'color' => '#D946EF'],
                ['label' => 'Converted', 'key' => 'converted', 'count' => $converted_count, 'icon' => 'fa-circle-check', 'color' => '#10B981'],
                ['label' => 'Not Interested', 'key' => 'not_interested', 'count' => $not_interested_count, 'icon' => 'fa-circle-xmark', 'color' => '#EF4444'],
            ];
            foreach ($stageCards as $sc):
            ?>
            <a href="<?= $base_path ?>leads?stage=<?= $sc['key'] ?>" style="display: flex; align-items: center; justify-content: space-between; padding: 12px 14px; background: #FAFBFD; border: 1px solid #EEF2F6; border-radius: 8px; text-decoration: none; transition: all 0.2s ease;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i class="fas <?= $sc['icon'] ?>" style="color: <?= $sc['color'] ?>; font-size: 14px;"></i>
                    <span style="font-size: 11px; font-weight: 600; color: #475569;"><?= htmlspecialchars($sc['label']) ?></span>
                </div>
                <span style="font-size: 13px; font-weight: 700; color: #0F172A;"><?= number_format($sc['count']) ?></span>
            </a>
            <?php endforeach; ?>
        </div>
        <div style="font-size: 11px; color: #94A3B8; margin-top: 14px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
            <span><i class="fas fa-info-circle"></i> Conversion Rate = (Converted Leads / Total Leads) &times; 100</span>
            <a href="<?= $base_path ?>leads" style="color: var(--primary, #3B82F6); font-weight: 600; text-decoration: none;">View CRM Pipeline &rarr;</a>
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
