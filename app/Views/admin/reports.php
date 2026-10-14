<?php
require_once __DIR__ . '/../../../admin/includes/header.php';
?>



<div class="saas-page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-chart-line" style="color: var(--primary);"></i> Activity & System Reports</h1>
        <p class="page-subtitle">Analyze administrative actions, daily throughput, and automated email reporting.</p>
    </div>
</div>

<?php if ($message): ?>
<div class="alert alert-<?= $message_type ?>"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<!-- Stats -->
<div class="stats-grid">
    <div class="stat-card">
        <h3><?= $today_count ?></h3>
        <p>Today's Actions</p>
    </div>
    <div class="stat-card">
        <h3><?= $week_count ?></h3>
        <p>This Week</p>
    </div>
    <div class="stat-card">
        <h3><?= $month_count ?></h3>
        <p>This Month</p>
    </div>
    <div class="stat-card">
        <h3><?= $total_count ?></h3>
        <p>Total Records</p>
    </div>
</div>

<div class="tab-nav">
    <button class="tab-btn active" onclick="showTab('activity')">Activity Log</button>
    <button class="tab-btn" onclick="showTab('email-report')">Email Report</button>
    <button class="tab-btn" onclick="showTab('settings')">Email Settings</button>
</div>

<!-- Activity Log Tab -->
<div id="activity-tab" class="tab-content active">
    <div class="filter-section">
        <div class="filter-row">
            <div class="form-group">
                <label>Search</label>
                <input type="text" id="searchInput" placeholder="Search by admin, action, or details..." onkeyup="filterTable()">
            </div>
            <div class="form-group">
                <label>Filter by Action</label>
                <select id="actionFilter" onchange="filterTable()">
                    <option value="">All Actions</option>
                    <?php foreach ($unique_actions as $action): ?>
                    <option value="<?= htmlspecialchars($action['action']) ?>"><?= htmlspecialchars($action['action']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Filter by Date</label>
                <select id="dateFilter" onchange="filterTable()">
                    <option value="">All Time</option>
                    <option value="today">Today</option>
                    <option value="week">This Week</option>
                    <option value="month">This Month</option>
                </select>
            </div>
        </div>
    </div>

    <div class="settings-card">
        <h3><i class="fas fa-list-alt"></i> Recent Activity</h3>
        <div style="overflow-x: auto;">
            <table id="activityTable">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>Admin Name</th>
                        <th>Username</th>
                        <th>Action</th>
                        <th>Details</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($audit_logs)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 30px; color: #666;">No activity recorded yet.</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($audit_logs as $log): ?>
                    <tr data-action="<?= htmlspecialchars($log['action']) ?>" data-date="<?= date('Y-m-d', strtotime($log['created_at'])) ?>">
                        <td><?= date('Y-m-d H:i', strtotime($log['created_at'])) ?></td>
                        <td><?= htmlspecialchars($log['admin_name'] ?? '<em>System</em>') ?></td>
                        <td><?= htmlspecialchars($log['admin_username'] ?? '-') ?></td>
                        <td><span class="action-badge <?= strtolower(substr($log['action'], 0, 6)) === 'create' ? 'create' : (strtolower(substr($log['action'], 0, 6)) === 'update' ? 'update' : (strtolower(substr($log['action'], 0, 6)) === 'delete' ? 'delete' : (strtolower(substr($log['action'], 0, 4)) === 'login' ? 'login' : 'toggle'))) ?>"><?= htmlspecialchars($log['action']) ?></span></td>
                        <td><?= htmlspecialchars($log['details'] ?? '-') ?></td>
                        <td><code style="font-size: 11px;"><?= htmlspecialchars($log['ip_address'] ?? '-') ?></code></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Email Report Tab -->
<div id="email-report-tab" class="tab-content">
    <div class="settings-card">
        <h3><i class="fas fa-envelope"></i> Send Activity Report via Email</h3>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

            <div class="form-group">
                <label>Report Period</label>
                <select name="report_range">
                    <option value="all">All Time</option>
                    <option value="today">Today Only</option>
                    <option value="week">Last 7 Days</option>
                    <option value="month">Last 30 Days</option>
                </select>
            </div>

            <div class="form-group">
                <label>Recipient Email *</label>
                <input type="email" name="admin_email" required placeholder="admin@example.com" value="<?= htmlspecialchars($_SESSION['admin_email'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label>Preview (Last 5 Actions)</label>
                <div class="email-preview">
                    <?php
                    $preview = $conn->query("SELECT al.*, a.name as admin_name FROM admin_activities al LEFT JOIN admin a ON al.user_id = a.id ORDER BY al.created_at DESC LIMIT 5")->fetchAll();
                    foreach ($preview as $p):
                    ?>
                    <div style="padding: 8px 0; border-bottom: 1px solid #eee;">
                        <strong><?= date('Y-m-d H:i', strtotime($p['created_at'])) ?></strong> -
                        <?= htmlspecialchars($p['admin_name'] ?? 'System') ?> -
                        <span style="color: #667eea;"><?= htmlspecialchars($p['action']) ?></span>
                        <?php if ($p['details']): ?> - <?= htmlspecialchars($p['details']) ?><?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                    <?php if (empty($preview)): ?>
                    <p style="color: #666;">No activity to preview.</p>
                    <?php endif; ?>
                </div>
            </div>

            <button type="submit" name="send_report_email" class="btn btn-success">
                <i class="fas fa-paper-plane"></i> Send Report Email
            </button>
        </form>
    </div>
</div>

<!-- Email Settings Tab -->
<div id="settings-tab" class="tab-content">
    <div class="settings-card">
        <h3><i class="fas fa-cog"></i> Email Notification Settings</h3>
        <p style="color: #666; margin-bottom: 20px;">Configure automatic email notifications for site changes.</p>

        <?php
        // Get current email notification settings
        $notif_settings = $conn->query("SELECT setting_key, setting_value FROM settings WHERE setting_key LIKE 'notif_%'")->fetchAll(PDO::FETCH_ASSOC);
        $notif = [];
        foreach ($notif_settings as $s) {
            $notif[$s['setting_key']] = $s['setting_value'];
        }
        ?>

        <form method="POST" action="settings/save-notifications.php">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;">
                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 10px;">
                        <input type="checkbox" name="notif_staff" value="1" <?= ($notif['notif_staff'] ?? '') ? 'checked' : '' ?>>
                        Staff Changes (Create/Update/Delete)
                    </label>
                </div>
                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 10px;">
                        <input type="checkbox" name="notif_settings" value="1" <?= ($notif['notif_settings'] ?? '') ? 'checked' : '' ?>>
                        Settings Changes
                    </label>
                </div>
                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 10px;">
                        <input type="checkbox" name="notif_content" value="1" <?= ($notif['notif_content'] ?? '') ? 'checked' : '' ?>>
                        Content Changes (Leads, Reviews, etc.)
                    </label>
                </div>
                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 10px;">
                        <input type="checkbox" name="notif_login" value="1" <?= ($notif['notif_login'] ?? '') ? 'checked' : '' ?>>
                        Login Activity
                    </label>
                </div>
            </div>

            <div class="form-group" style="margin-top: 20px;">
                <label>Notification Email</label>
                <input type="email" name="notif_email" placeholder="notify@example.com" value="<?= htmlspecialchars($notif['notif_email'] ?? '') ?>">
                <small style="color: #666; display: block; margin-top: 5px;">Leave empty to use admin email from mail settings.</small>
            </div>

            <button type="submit" class="btn">
                <i class="fas fa-save"></i> Save Notification Settings
            </button>
        </form>
    </div>
</div>

<script>
function showTab(tabName) {
    document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    
    const tabEl = document.getElementById(tabName + '-tab');
    if (tabEl) {
        tabEl.classList.add('active');
    }
    
    const btns = document.querySelectorAll('.tab-btn');
    btns.forEach(btn => {
        const onclickAttr = btn.getAttribute('onclick') || '';
        if (onclickAttr.includes("'" + tabName + "'") || onclickAttr.includes('"' + tabName + '"')) {
            btn.classList.add('active');
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const tab = urlParams.get('tab');
    if (tab) {
        showTab(tab);
    }
});

function filterTable() {
    const search = document.getElementById('searchInput').value.toLowerCase();
    const action = document.getElementById('actionFilter').value;
    const date = document.getElementById('dateFilter').value;
    const today = new Date().toISOString().split('T')[0];
    const weekAgo = new Date(Date.now() - 7 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
    const monthAgo = new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];

    document.querySelectorAll('#activityTable tbody tr').forEach(row => {
        const text = row.textContent.toLowerCase();
        const rowAction = row.getAttribute('data-action');
        const rowDate = row.getAttribute('data-date');

        let show = text.includes(search);
        if (action && rowAction !== action) show = false;
        if (date === 'today' && rowDate !== today) show = false;
        if (date === 'week' && rowDate < weekAgo) show = false;
        if (date === 'month' && rowDate < monthAgo) show = false;

        row.style.display = show ? '' : 'none';
    });
}
</script>

<?php require_once __DIR__ . '/../../../admin/includes/footer.php'; ?>