<?php
require_once __DIR__ . '/../../../admin/includes/header.php';
?>



<div class="saas-page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-satellite-dish"></i> Activity Log</h1>
        <p class="page-subtitle">Inspect user actions, session security, IP tracking, and system events.</p>
    </div>
    <div class="header-actions">
        <a href="export-activity" class="btn btn-primary">
            <i class="fas fa-download"></i> Export Logs
        </a>
    </div>
</div>

<!-- Stats Cards -->
<div class="stat-pills">
    <div class="stat-pill">
        <div class="icon blue"><i class="fas fa-sign-in-alt"></i></div>
        <div class="info">
            <strong><?= number_format($stats['logins_today']) ?></strong>
            <span>Logins Today</span>
        </div>
    </div>
    <div class="stat-pill">
        <div class="icon red"><i class="fas fa-exclamation-triangle"></i></div>
        <div class="info">
            <strong><?= number_format($stats['failed_logins_today']) ?></strong>
            <span>Failed Logins</span>
        </div>
    </div>
    <div class="stat-pill">
        <div class="icon orange"><i class="fas fa-history"></i></div>
        <div class="info">
            <strong><?= number_format($stats['activities_week']) ?></strong>
            <span>This Week</span>
        </div>
    </div>
    <div class="stat-pill">
        <div class="icon green"><i class="fas fa-desktop"></i></div>
        <div class="info">
            <strong><?= number_format($stats['active_sessions']) ?></strong>
            <span>Active Sessions</span>
        </div>
    </div>
    <?php if ($stats['unresolved_alerts'] > 0): ?>
    <div class="stat-pill" style="border-color: rgba(239, 68, 68, 0.3);">
        <div class="icon red"><i class="fas fa-bell"></i></div>
        <div class="info">
            <strong><?= number_format($stats['unresolved_alerts']) ?></strong>
            <span>Security Alerts</span>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Filters -->
<form method="GET" class="activity-filters">
    <div class="filter-group">
        <label>Action Type</label>
        <select name="action">
            <option value="">All Actions</option>
            <?php foreach ($unique_actions as $a): ?>
                <option value="<?= htmlspecialchars($a['action']) ?>" <?= $filter_action === $a['action'] ? 'selected' : '' ?>><?= htmlspecialchars($a['action']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="filter-group">
        <label>User</label>
        <select name="user">
            <option value="">All Users</option>
            <?php foreach ($unique_users as $u): ?>
                <option value="<?= htmlspecialchars($u['name']) ?>" <?= $filter_user === $u['name'] ? 'selected' : '' ?>><?= htmlspecialchars($u['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="filter-group">
        <label>IP Address</label>
        <input type="text" name="ip" value="<?= htmlspecialchars($filter_ip) ?>" placeholder="Search IP...">
    </div>
    <div class="filter-group">
        <label>From Date</label>
        <input type="date" name="from" value="<?= htmlspecialchars($date_from) ?>" class="form-control">
    </div>
    <div class="filter-group">
        <label>To Date</label>
        <input type="date" name="to" value="<?= htmlspecialchars($date_to) ?>" class="form-control">
    </div>
    <div class="filter-group" style="flex: 0 0 auto;">
        <label>&nbsp;</label>
        <button type="submit" class="btn btn-primary" style="height: 42px;">
            <i class="fas fa-search"></i> Filter
        </button>
    </div>
    <div class="filter-group" style="flex: 0 0 auto;">
        <label>&nbsp;</label>
        <a href="activity" class="btn btn-edit" style="height: 42px;">
            <i class="fas fa-times"></i> Clear
        </a>
    </div>
</form>

<!-- Activity Table -->
<div class="content-card" style="padding: 0; overflow: hidden;">
    <?php if (empty($activities)): ?>
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
            <p>No activities found</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="activity-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Action</th>
                        <th>Details</th>
                        <th>IP Address</th>
                        <th>Device</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($activities as $activity): ?>
                        <?php
                        $badgeClass = 'badge-info';
                        if (stripos($activity['action'], 'LOGIN_SUCCESS') !== false) $badgeClass = 'badge-success';
                        if (stripos($activity['action'], 'LOGIN_FAILED') !== false) $badgeClass = 'badge-danger';
                        if (stripos($activity['action'], 'DELETE') !== false) $badgeClass = 'badge-warning';
                        if (stripos($activity['action'], 'CREATE') !== false) $badgeClass = 'badge-info';
                        if (stripos($activity['action'], 'UPDATE') !== false) $badgeClass = 'badge-info';
                        ?>
                        <tr>
                            <td>
                                <div class="user-cell">
                                    <div class="user-avatar"><?= strtoupper(substr($activity['user_name'] ?? 'S', 0, 1)) ?></div>
                                    <div>
                                        <strong style="color: var(--saas-text-main); font-size: 14px;"><?= htmlspecialchars($activity['user_name'] ?? 'System') ?></strong>
                                        <div style="font-size: 12px; color: var(--saas-text-muted); margin-top:2px;"><?= htmlspecialchars($activity['user_email'] ?? '') ?></div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="activity-badge <?= $badgeClass ?>"><?= htmlspecialchars(str_replace('_', ' ', $activity['action'])) ?></span></td>
                            <td style="max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?= htmlspecialchars($activity['details']) ?>"><?= htmlspecialchars($activity['details']) ?></td>
                            <td class="ip-cell"><?= htmlspecialchars($activity['ip_address'] ?? '-') ?></td>
                            <td style="font-size: 12px; color: var(--text-secondary);"><?= htmlspecialchars($activity['device_info'] ?? '-') ?></td>
                            <td class="time-cell"><?= date('M d, Y<br>H:i', strtotime($activity['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Pagination -->
<?php if ($total_pages > 1): ?>
<div class="pagination">
    <?php if ($page > 1): ?>
        <a href="?page=<?= $page - 1 ?>&action=<?= urlencode($filter_action) ?>&user=<?= urlencode($filter_user) ?>&ip=<?= urlencode($filter_ip) ?>&from=<?= urlencode($date_from) ?>&to=<?= urlencode($date_to) ?>"><i class="fas fa-chevron-left"></i></a>
    <?php endif; ?>

    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
        <?php if ($i == $page): ?>
            <span class="active"><?= $i ?></span>
        <?php else: ?>
            <a href="?page=<?= $i ?>&action=<?= urlencode($filter_action) ?>&user=<?= urlencode($filter_user) ?>&ip=<?= urlencode($filter_ip) ?>&from=<?= urlencode($date_from) ?>&to=<?= urlencode($date_to) ?>"><?= $i ?></a>
        <?php endif; ?>
    <?php endfor; ?>

    <?php if ($page < $total_pages): ?>
        <a href="?page=<?= $page + 1 ?>&action=<?= urlencode($filter_action) ?>&user=<?= urlencode($filter_user) ?>&ip=<?= urlencode($filter_ip) ?>&from=<?= urlencode($date_from) ?>&to=<?= urlencode($date_to) ?>"><i class="fas fa-chevron-right"></i></a>
    <?php endif; ?>
</div>
<?php endif; ?>

<p style="text-align: center; color: var(--saas-text-muted); margin-top: 24px; font-size: 13px; font-weight: 500;">
    Showing <?= number_format($offset + 1) ?> - <?= number_format(min($offset + $per_page, $total_activities)) ?> of <?= number_format($total_activities) ?> activities
</p>

<?php require_once __DIR__ . '/../../../admin/includes/footer.php'; ?>