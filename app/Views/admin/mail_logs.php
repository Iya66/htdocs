<?php
/**
 * Email Sent History & Outbound Delivery View
 * Modern SaaS UI with Live Email Preview Modal, Filtering, and Queue Management
 */
include __DIR__ . '/../../../admin/includes/header.php';
?>

<style>
.email-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}

.stat-metric-card {
    background: var(--bg-surface, #ffffff);
    border: 1px solid var(--border-light, rgba(0,0,0,0.08));
    border-radius: var(--radius-xl, 14px);
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 16px;
    box-shadow: var(--shadow-xs, 0 1px 3px rgba(0,0,0,0.04));
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.stat-metric-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md, 0 8px 16px rgba(0,0,0,0.06));
}

.stat-metric-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
}

.stat-metric-content h4 {
    font-size: 13px;
    font-weight: 600;
    color: var(--text-secondary, #64748b);
    margin: 0 0 4px 0;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.stat-metric-content .val {
    font-size: 22px;
    font-weight: 700;
    color: var(--text-primary, #0f172a);
    line-height: 1.2;
}

.stat-metric-content .sub {
    font-size: 12px;
    color: var(--text-muted, #94a3b8);
    margin-top: 2px;
}

.filter-panel {
    background: var(--bg-surface, #ffffff);
    border: 1px solid var(--border-light, rgba(0,0,0,0.08));
    border-radius: var(--radius-xl, 14px);
    padding: 18px 20px;
    margin-bottom: 24px;
}

.filter-form-grid {
    display: grid;
    grid-template-columns: 2fr 1.2fr 1fr 1fr auto;
    gap: 12px;
    align-items: flex-end;
}

@media (max-width: 992px) {
    .filter-form-grid {
        grid-template-columns: 1fr 1fr;
    }
}
@media (max-width: 576px) {
    .filter-form-grid {
        grid-template-columns: 1fr;
    }
}

.status-badge-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}
.status-sent {
    background: rgba(16, 185, 129, 0.12);
    color: #10b981;
    border: 1px solid rgba(16, 185, 129, 0.25);
}
.status-failed {
    background: rgba(239, 68, 68, 0.12);
    color: #ef4444;
    border: 1px solid rgba(239, 68, 68, 0.25);
}
.status-pending {
    background: rgba(245, 158, 11, 0.12);
    color: #f59e0b;
    border: 1px solid rgba(245, 158, 11, 0.25);
}

.table-card {
    background: var(--bg-surface, #ffffff);
    border: 1px solid var(--border-light, rgba(0,0,0,0.08));
    border-radius: var(--radius-xl, 14px);
    overflow: hidden;
    box-shadow: var(--shadow-xs, 0 1px 3px rgba(0,0,0,0.04));
}

.email-table {
    width: 100%;
    border-collapse: collapse;
    text-align: left;
}

.email-table th {
    background: var(--bg-main, #f8fafc);
    padding: 14px 16px;
    font-size: 12.5px;
    font-weight: 700;
    color: var(--text-secondary, #64748b);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    border-bottom: 1px solid var(--border-light, rgba(0,0,0,0.08));
}

.email-table td {
    padding: 14px 16px;
    font-size: 13.5px;
    border-bottom: 1px solid var(--border-light, rgba(0,0,0,0.06));
    vertical-align: middle;
}

.email-table tr:hover td {
    background: var(--bg-hover, #f8fafc);
}

.recipient-cell {
    font-weight: 600;
    color: var(--text-primary, #0f172a);
    display: flex;
    align-items: center;
    gap: 8px;
}

.subject-cell {
    color: var(--text-secondary, #334155);
    max-width: 320px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    display: block;
}

.btn-action-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    border: 1px solid var(--border-light, rgba(0,0,0,0.1));
    background: #ffffff;
    color: var(--text-secondary, #475569);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    cursor: pointer;
    transition: all 0.15s ease;
    text-decoration: none;
}

.btn-action-icon:hover {
    border-color: var(--primary, #4f46e5);
    color: var(--primary, #4f46e5);
    background: rgba(79, 70, 229, 0.06);
}

.btn-action-icon.btn-danger-icon:hover {
    border-color: #ef4444;
    color: #ef4444;
    background: rgba(239, 68, 68, 0.06);
}

/* Modal Overlay */
.email-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(15, 23, 42, 0.65);
    backdrop-filter: blur(4px);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    padding: 20px;
}

.email-modal-card {
    background: #ffffff;
    border-radius: 16px;
    width: 100%;
    max-width: 820px;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    overflow: hidden;
    animation: modalSlideUp 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes modalSlideUp {
    from { opacity: 0; transform: translateY(20px) scale(0.98); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}

.modal-header {
    padding: 18px 24px;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #f8fafc;
}

.modal-body {
    padding: 24px;
    overflow-y: auto;
    flex-grow: 1;
}

.modal-footer {
    padding: 14px 24px;
    border-top: 1px solid #e2e8f0;
    display: flex;
    justify-content: flex-end;
    background: #f8fafc;
    gap: 10px;
}

.preview-iframe {
    width: 100%;
    height: 480px;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    background: #ffffff;
}
</style>

<div class="admin-page-container">
    <!-- Breadcrumb & Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
        <div>
            <h1 style="font-size: 24px; font-weight: 800; color: var(--text-primary, #0f172a); margin: 0 0 6px 0;">
                <i class="fas fa-envelope-open-text" style="color: var(--primary, #4f46e5); margin-right: 8px;"></i> Email Sent History
            </h1>
            <p style="color: var(--text-secondary, #64748b); font-size: 14px; margin: 0;">
                Track, inspect, and verify all automated system emails, lead alerts, reviews, and client notifications.
            </p>
        </div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <!-- Process Queue -->
            <form method="POST" style="margin: 0;">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <input type="hidden" name="action" value="process_queue">
                <button type="submit" class="btn" style="background: #10b981; color: #fff; padding: 9px 16px; font-size: 13px; font-weight: 600; border-radius: 8px; border: none; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                    <i class="fas fa-play"></i> Process Queue
                </button>
            </form>

            <!-- Export CSV -->
            <a href="mail_logs.php?action=export_csv&csrf_token=<?= $csrf_token ?>" class="btn" style="background: var(--bg-surface, #fff); border: 1px solid var(--border-light, rgba(0,0,0,0.12)); color: var(--text-primary, #1e293b); padding: 9px 16px; font-size: 13px; font-weight: 600; border-radius: 8px; text-decoration: none; display: flex; align-items: center; gap: 6px;">
                <i class="fas fa-file-csv" style="color: #10b981;"></i> Export CSV
            </a>

            <!-- Clean Old Logs -->
            <form method="POST" style="margin: 0;" onsubmit="return confirm('Purge email logs older than 30 days?');">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <input type="hidden" name="action" value="clear_old_logs">
                <button type="submit" class="btn" style="background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.2); color: #ef4444; padding: 9px 14px; font-size: 13px; font-weight: 600; border-radius: 8px; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                    <i class="fas fa-trash-alt"></i> Purge &gt; 30 Days
                </button>
            </form>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if (!empty($message)): ?>
    <div class="alert alert-<?= $message_type === 'success' ? 'success' : ($message_type === 'warning' ? 'warning' : 'danger') ?>" style="margin-bottom: 20px; padding: 12px 18px; border-radius: 10px; display: flex; align-items: center; gap: 10px; font-size: 14px; font-weight: 500;">
        <i class="fas fa-<?= $message_type === 'success' ? 'check-circle' : 'exclamation-triangle' ?>"></i>
        <div><?= htmlspecialchars($message) ?></div>
    </div>
    <?php endif; ?>

    <!-- KPI Metric Cards -->
    <div class="email-stats-grid">
        <div class="stat-metric-card">
            <div class="stat-metric-icon" style="background: rgba(79, 70, 229, 0.1); color: #4f46e5;">
                <i class="fas fa-paper-plane"></i>
            </div>
            <div class="stat-metric-content">
                <h4>Total Sent</h4>
                <div class="val"><?= number_format($stats['total_sent']) ?></div>
                <div class="sub">Success Rate: <strong style="color: #10b981;"><?= $stats['success_rate'] ?></strong></div>
            </div>
        </div>

        <div class="stat-metric-card">
            <div class="stat-metric-icon" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
                <i class="fas fa-calendar-day"></i>
            </div>
            <div class="stat-metric-content">
                <h4>Sent Today</h4>
                <div class="val"><?= number_format($stats['sent_today']) ?></div>
                <div class="sub">Past 7 Days: <?= number_format($stats['sent_week']) ?></div>
            </div>
        </div>

        <div class="stat-metric-card">
            <div class="stat-metric-icon" style="background: rgba(239, 68, 68, 0.1); color: #ef4444;">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <div class="stat-metric-content">
                <h4>Delivery Failures</h4>
                <div class="val"><?= number_format($stats['total_failed']) ?></div>
                <div class="sub"><?= $stats['total_failed'] === 0 ? 'All delivered cleanly' : 'Check error trace' ?></div>
            </div>
        </div>

        <div class="stat-metric-card">
            <div class="stat-metric-icon" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;">
                <i class="fas fa-bolt"></i>
            </div>
            <div class="stat-metric-content">
                <h4>Queue In-Flight</h4>
                <div class="val"><?= number_format($stats['queue_pending']) ?></div>
                <div class="sub">Background worker active</div>
            </div>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="filter-panel">
        <form method="GET" action="mail_logs.php" class="filter-form-grid">
            <div class="form-group" style="margin: 0;">
                <label style="font-size: 12px; font-weight: 600; color: var(--text-secondary); margin-bottom: 4px; display: block;">Search Recipient or Subject</label>
                <input type="text" name="search" value="<?= htmlspecialchars($filter_search) ?>" placeholder="e.g. client@gmail.com, Lead Alert..." style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-light, #e2e8f0); border-radius: 8px; font-size: 13.5px;">
            </div>

            <div class="form-group" style="margin: 0;">
                <label style="font-size: 12px; font-weight: 600; color: var(--text-secondary); margin-bottom: 4px; display: block;">Status</label>
                <select name="status" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-light, #e2e8f0); border-radius: 8px; font-size: 13.5px; background: #fff;">
                    <option value="all" <?= $filter_status === 'all' ? 'selected' : '' ?>>All Statuses</option>
                    <option value="sent" <?= $filter_status === 'sent' ? 'selected' : '' ?>>Delivered / Sent</option>
                    <option value="failed" <?= $filter_status === 'failed' ? 'selected' : '' ?>>Failed</option>
                </select>
            </div>

            <div class="form-group" style="margin: 0;">
                <label style="font-size: 12px; font-weight: 600; color: var(--text-secondary); margin-bottom: 4px; display: block;">From</label>
                <input type="date" name="from" value="<?= htmlspecialchars($filter_from) ?>" style="width: 100%; padding: 7px 10px; border: 1px solid var(--border-light, #e2e8f0); border-radius: 8px; font-size: 13px;">
            </div>

            <div class="form-group" style="margin: 0;">
                <label style="font-size: 12px; font-weight: 600; color: var(--text-secondary); margin-bottom: 4px; display: block;">To</label>
                <input type="date" name="to" value="<?= htmlspecialchars($filter_to) ?>" style="width: 100%; padding: 7px 10px; border: 1px solid var(--border-light, #e2e8f0); border-radius: 8px; font-size: 13px;">
            </div>

            <div style="display: flex; gap: 8px;">
                <button type="submit" class="btn btn-primary" style="padding: 8px 16px; font-size: 13.5px; border-radius: 8px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fas fa-filter"></i> Filter
                </button>
                <?php if (!empty($filter_search) || $filter_status !== 'all' || !empty($filter_from) || !empty($filter_to)): ?>
                <a href="mail_logs.php" class="btn" style="background: var(--bg-hover, #f1f5f9); border: 1px solid var(--border-light, #e2e8f0); padding: 8px 12px; font-size: 13px; border-radius: 8px; text-decoration: none; color: var(--text-secondary);" title="Reset Filters">
                    <i class="fas fa-times"></i>
                </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Email Logs Table -->
    <div class="table-card">
        <div class="table-responsive">
            <table class="email-table">
                <thead>
                    <tr>
                        <th style="width: 60px;">#ID</th>
                        <th>Recipient</th>
                        <th>Subject</th>
                        <th style="width: 110px;">Status</th>
                        <th>Error Details</th>
                        <th style="width: 150px;">Sent Timestamp</th>
                        <th style="width: 110px; text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($logs)): ?>
                        <?php foreach ($logs as $log): 
                            $st = strtolower($log['status']);
                            $isSent = ($st === 'sent');
                        ?>
                        <tr>
                            <td style="color: var(--text-muted); font-weight: 600; font-size: 12px;">
                                #<?= $log['id'] ?>
                            </td>
                            <td>
                                <div class="recipient-cell">
                                    <i class="fas fa-envelope" style="color: var(--primary); font-size: 12px;"></i>
                                    <span><?= htmlspecialchars($log['recipient']) ?></span>
                                </div>
                            </td>
                            <td>
                                <span class="subject-cell" title="<?= htmlspecialchars($log['subject']) ?>">
                                    <?= htmlspecialchars($log['subject']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="status-badge-pill status-<?= $isSent ? 'sent' : ($st === 'pending' ? 'pending' : 'failed') ?>">
                                    <i class="fas fa-<?= $isSent ? 'check' : ($st === 'pending' ? 'clock' : 'times') ?>"></i>
                                    <?= ucfirst($st) ?>
                                </span>
                            </td>
                            <td>
                                <?php if (!empty($log['error_message'])): ?>
                                    <span style="color: #ef4444; font-size: 12px; font-family: monospace; display: block; max-width: 260px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?= htmlspecialchars($log['error_message']) ?>">
                                        <?= htmlspecialchars($log['error_message']) ?>
                                    </span>
                                <?php else: ?>
                                    <span style="color: var(--text-muted); font-size: 12px;">—</span>
                                <?php endif; ?>
                            </td>
                            <td style="color: var(--text-secondary); font-size: 12.5px; white-space: nowrap;">
                                <?= date('M d, Y • h:i A', strtotime($log['sent_at'])) ?>
                            </td>
                            <td style="text-align: center;">
                                <div style="display: inline-flex; gap: 6px;">
                                    <!-- View Details Button -->
                                    <button type="button" class="btn-action-icon" onclick="openEmailPreview(<?= (int)$log['id'] ?>)" title="View Email Preview">
                                        <i class="fas fa-eye"></i>
                                    </button>

                                    <!-- Resend Button -->
                                    <form method="POST" style="margin: 0; display: inline;" onsubmit="return confirm('Resend this email to <?= htmlspecialchars($log['recipient']) ?>?');">
                                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                        <input type="hidden" name="action" value="resend_email">
                                        <input type="hidden" name="log_id" value="<?= (int)$log['id'] ?>">
                                        <button type="submit" class="btn-action-icon" title="Resend Email">
                                            <i class="fas fa-redo-alt"></i>
                                        </button>
                                    </form>

                                    <!-- Delete Log Button -->
                                    <form method="POST" style="margin: 0; display: inline;" onsubmit="return confirm('Delete this email log entry?');">
                                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                        <input type="hidden" name="action" value="delete_log">
                                        <input type="hidden" name="log_id" value="<?= (int)$log['id'] ?>">
                                        <button type="submit" class="btn-action-icon btn-danger-icon" title="Delete Log">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 48px 20px; color: var(--text-muted);">
                                <i class="fas fa-inbox" style="font-size: 36px; color: #cbd5e1; margin-bottom: 12px; display: block;"></i>
                                <div style="font-size: 15px; font-weight: 600; color: var(--text-primary);">No email history records found</div>
                                <div style="font-size: 13px; margin-top: 4px;">Try adjusting your search criteria or date filters.</div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination Bar -->
        <?php if ($total_pages > 1): ?>
        <div style="padding: 16px 20px; border-top: 1px solid var(--border-light, rgba(0,0,0,0.06)); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <div style="font-size: 13px; color: var(--text-secondary);">
                Showing <strong><?= min($total_logs, $offset + 1) ?></strong> to <strong><?= min($total_logs, $offset + $per_page) ?></strong> of <strong><?= $total_logs ?></strong> email logs
            </div>
            <div style="display: flex; gap: 6px;">
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?>&status=<?= urlencode($filter_status) ?>&search=<?= urlencode($filter_search) ?>&from=<?= urlencode($filter_from) ?>&to=<?= urlencode($filter_to) ?>" class="btn-action-icon" style="width: auto; padding: 0 12px; font-size: 12px; font-weight: 600;">
                        <i class="fas fa-chevron-left"></i> Prev
                    </a>
                <?php endif; ?>

                <?php 
                $startPage = max(1, $page - 2);
                $endPage = min($total_pages, $page + 2);
                for ($p = $startPage; $p <= $endPage; $p++): 
                ?>
                    <a href="?page=<?= $p ?>&status=<?= urlencode($filter_status) ?>&search=<?= urlencode($filter_search) ?>&from=<?= urlencode($filter_from) ?>&to=<?= urlencode($filter_to) ?>" class="btn-action-icon" style="width: 32px; font-weight: 600; <?= $p === $page ? 'background: var(--primary); color: #fff; border-color: var(--primary);' : '' ?>">
                        <?= $p ?>
                    </a>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?= $page + 1 ?>&status=<?= urlencode($filter_status) ?>&search=<?= urlencode($filter_search) ?>&from=<?= urlencode($filter_from) ?>&to=<?= urlencode($filter_to) ?>" class="btn-action-icon" style="width: auto; padding: 0 12px; font-size: 12px; font-weight: 600;">
                        Next <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- ============================================================== -->
<!-- EMAIL PREVIEW MODAL -->
<!-- ============================================================== -->
<div class="email-modal-overlay" id="emailPreviewModal" onclick="closeEmailPreview(event)">
    <div class="email-modal-card" onclick="event.stopPropagation()">
        <div class="modal-header">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(79, 70, 229, 0.1); color: #4f46e5; display: flex; align-items: center; justify-content: center; font-size: 16px;">
                    <i class="fas fa-envelope-open-text"></i>
                </div>
                <div>
                    <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: #0f172a;" id="modalSubject">Email Subject</h3>
                    <div style="font-size: 12px; color: #64748b; margin-top: 2px;" id="modalRecipient">To: recipient@example.com</div>
                </div>
            </div>
            <button type="button" onclick="closeEmailModal()" style="background: none; border: none; font-size: 18px; color: #94a3b8; cursor: pointer; padding: 4px 8px; border-radius: 6px;">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="modal-body">
            <div style="display: flex; justify-content: space-between; align-items: center; background: #f8fafc; padding: 12px 16px; border-radius: 10px; margin-bottom: 16px; border: 1px solid #e2e8f0; font-size: 13px; flex-wrap: wrap; gap: 8px;">
                <div><span style="color: #64748b;">Status:</span> <strong id="modalStatus">-</strong></div>
                <div><span style="color: #64748b;">Timestamp:</span> <strong id="modalSentAt">-</strong></div>
                <div id="modalErrorWrap" style="display: none; width: 100%; color: #ef4444; font-size: 12px; font-family: monospace; padding-top: 6px; border-top: 1px dashed #cbd5e1;"></div>
            </div>

            <!-- HTML Email Preview Frame -->
            <label style="font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 6px; display: block;">Rendered Email Content</label>
            <iframe id="emailPreviewFrame" class="preview-iframe" src="about:blank"></iframe>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn" onclick="closeEmailModal()" style="background: #e2e8f0; color: #334155; font-weight: 600; padding: 8px 16px; border-radius: 8px; border: none; cursor: pointer;">
                Close
            </button>
        </div>
    </div>
</div>

<script>
function openEmailPreview(logId) {
    const modal = document.getElementById('emailPreviewModal');
    const iframe = document.getElementById('emailPreviewFrame');
    const subjectElem = document.getElementById('modalSubject');
    const recipientElem = document.getElementById('modalRecipient');
    const statusElem = document.getElementById('modalStatus');
    const sentAtElem = document.getElementById('modalSentAt');
    const errorWrap = document.getElementById('modalErrorWrap');

    subjectElem.textContent = 'Loading email details...';
    recipientElem.textContent = 'Fetching...';
    statusElem.textContent = '—';
    sentAtElem.textContent = '—';
    errorWrap.style.display = 'none';
    iframe.srcdoc = '<p style="font-family:sans-serif;padding:20px;color:#64748b;">Loading rendered message...</p>';

    modal.style.display = 'flex';

    fetch(`api/email_preview.php?id=${logId}&type=log`)
        .then(res => res.json())
        .then(res => {
            if (res.success && res.data) {
                const d = res.data;
                subjectElem.textContent = d.subject || 'No Subject';
                recipientElem.textContent = 'To: ' + d.recipient;
                statusElem.innerHTML = `<span class="status-badge-pill status-${d.status === 'sent' ? 'sent' : 'failed'}">${d.status.toUpperCase()}</span>`;
                sentAtElem.textContent = d.sent_at || 'Recent';

                if (d.error_message) {
                    errorWrap.style.display = 'block';
                    errorWrap.textContent = 'Error: ' + d.error_message;
                }

                if (d.body_html) {
                    iframe.srcdoc = d.body_html;
                } else {
                    iframe.srcdoc = `
                        <div style="font-family:sans-serif;padding:30px;text-align:center;color:#64748b;">
                            <h3 style="color:#0f172a;margin-bottom:8px;">${escapeHtml(d.subject)}</h3>
                            <p style="margin:0;">Dispatched to: <strong>${escapeHtml(d.recipient)}</strong> on ${d.sent_at}</p>
                            <p style="margin-top:12px;font-size:12px;color:#94a3b8;">(Message body was sent directly or purged from queue cache)</p>
                        </div>
                    `;
                }
            } else {
                iframe.srcdoc = `<p style="color:#ef4444;padding:20px;">Failed to load email: ${escapeHtml(res.error || 'Unknown error')}</p>`;
            }
        })
        .catch(err => {
            iframe.srcdoc = '<p style="color:#ef4444;padding:20px;">Network error loading email preview.</p>';
        });
}

function closeEmailModal() {
    document.getElementById('emailPreviewModal').style.display = 'none';
}

function closeEmailPreview(e) {
    if (e.target.id === 'emailPreviewModal') {
        closeEmailModal();
    }
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

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeEmailModal();
    }
});
</script>

<?php include __DIR__ . '/../../../admin/includes/footer.php'; ?>
