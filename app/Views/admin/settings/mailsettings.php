<?php
require_once __DIR__ . '/../../../../admin/includes/header.php';
?>

<style>
.settings-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--border-radius); padding: 32px; box-shadow: var(--shadow-sm); margin-bottom: 24px; transition: all var(--transition); }
.settings-card h3 { font-size: 18px; color: var(--text-main); margin-bottom: 24px; display: flex; align-items: center; gap: 12px; font-weight: 700; }
.settings-card h3 i { color: var(--primary); font-size: 20px; }

.form-group { margin-bottom: 24px; }
.form-group label { display: block; margin-bottom: 8px; color: var(--text-main); font-weight: 600; font-size: 14px; }
.form-group input, .form-group select, .form-group textarea { 
    width: 100%; padding: 14px 18px; 
    border: 1px solid var(--border-color); border-radius: var(--btn-radius); 
    font-size: 14px; background: var(--bg-main); color: var(--text-main); 
    transition: all var(--transition); 
}
.form-group input:focus, .form-group select:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px color-mix(in srgb, var(--primary) 20%, transparent); }
.form-group .hint { font-size: 12px; color: var(--text-muted); margin-top: 6px; display: block; }

.row { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px; }
.grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; margin-bottom: 24px; }
@media (max-width: 992px) { .row, .grid-3 { grid-template-columns: 1fr; } }

.test-section { background: color-mix(in srgb, var(--primary) 5%, transparent); border-radius: var(--border-radius); padding: 32px; margin-top: 24px; border: 1px solid var(--border-color); }
.config-list { background: var(--bg-main); border-radius: var(--border-radius); padding: 24px; border: 1px solid var(--border-color); }
.config-item { display: flex; justify-content: space-between; align-items: center; padding: 16px 0; border-bottom: 1px solid var(--border-color); }
.config-item:last-child { border-bottom: none; padding-bottom: 0; }
.config-item:first-child { padding-top: 0; }
.config-item strong { color: var(--text-main); display: inline-block; min-width: 150px; }
.config-item span { color: var(--text-muted); font-size: 14px; }
.config-item .actions { display: flex; gap: 8px; }

.status-badge { padding: 6px 12px; border-radius: 8px; font-size: 11px; font-weight: 600; border: 1px solid var(--border-color); }
.status-configured { background: color-mix(in srgb, var(--success) 15%, transparent); color: var(--success); border-color: color-mix(in srgb, var(--success) 30%, transparent); }
.status-missing { background: color-mix(in srgb, var(--danger) 15%, transparent); color: var(--danger); border-color: color-mix(in srgb, var(--danger) 30%, transparent); }
</style>

<div class="saas-header">
    <h1 class="saas-header-title">
        <i class="fas fa-envelope" aria-hidden="true" style="color: var(--primary);"></i>
        Mail & SMTP Settings
    </h1>
    <p class="saas-header-subtitle">Configure outbound transactional email delivery, SMTP credentials, and test connectivity.</p>
</div>

<?php require_once __DIR__ . '/../../../../admin/includes/settings-nav.php'; ?>

<?php if ($message): ?>
    <div class="alert alert-<?= $message_type ?>">
        <i class="fas fa-<?= $message_type === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
        <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

<!-- Main SMTP Settings -->
<div class="settings-card">
    <h3><i class="fas fa-cog"></i> PHPMailer Configuration</h3>
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
        <div class="grid-3">
            <div class="form-group">
                <label>Admin Email *</label>
                <input type="email" name="admin_email" value="<?= htmlspecialchars(getSettingValue('mail_admin_email')) ?>" placeholder="admin@example.com" required>
                <span class="hint">Primary admin email for notifications</span>
            </div>
            <div class="form-group">
                <label>SMTP Email *</label>
                <input type="email" name="smtp_email" value="<?= htmlspecialchars(getSettingValue('mail_smtp_email')) ?>" placeholder="noreply@example.com" required>
                <span class="hint">Email used for sending</span>
            </div>
            <div class="form-group">
                <label>SMTP Password</label>
                <input type="password" name="smtp_password" value="" placeholder="Enter new password to update (leave blank to keep current)">
                <span class="hint">For Gmail, use App Password</span>
            </div>
        </div>
        <div class="row">
            <div class="form-group">
                <label>SMTP Server *</label>
                <input type="text" name="smtp_server" value="<?= htmlspecialchars(getSettingValue('mail_smtp_server')) ?>" placeholder="portel.cloudaspire.in" required>
                <span class="hint">e.g., portel.cloudaspire.in, smtp.gmail.com, smtp.hostinger.com</span>
            </div>
            <div class="form-group">
                <label>SMTP Port *</label>
                <input type="text" name="smtp_port" value="<?= htmlspecialchars(getSettingValue('mail_smtp_port', '587')) ?>" placeholder="587" required>
                <span class="hint">587 (TLS), 465 (SSL), 25 (Other)</span>
            </div>
        </div>
        <div class="row">
            <div class="form-group">
                <label>SMTP Protocol</label>
                <select name="smtp_protocol">
                    <option value="tls" <?= getSettingValue('mail_smtp_protocol', 'tls') === 'tls' ? 'selected' : '' ?>>TLS</option>
                    <option value="ssl" <?= getSettingValue('mail_smtp_protocol', 'tls') === 'ssl' ? 'selected' : '' ?>>SSL</option>
                    <option value="none" <?= getSettingValue('mail_smtp_protocol', 'tls') === 'none' ? 'selected' : '' ?>>None</option>
                </select>
                <span class="hint">TLS is recommended for most providers</span>
            </div>
            <div class="form-group">
                <label>From Name</label>
                <input type="text" name="from_name" value="<?= htmlspecialchars(getSettingValue('mail_from_name', 'CloudASPIRE')) ?>" placeholder="CloudASPIRE">
                <span class="hint">Name shown in emails</span>
            </div>
        </div>
        <div class="row">
            <div class="form-group" style="grid-column: span 2; display: flex; align-items: center; gap: 12px; margin-bottom: 24px;">
                <input type="checkbox" id="force_ipv4" name="force_ipv4" value="1" <?= getSettingValue('mail_force_ipv4', '0') === '1' ? 'checked' : '' ?> style="width: auto; height: auto; transform: scale(1.2); margin: 0; cursor: pointer;">
                <div>
                    <label for="force_ipv4" style="display: inline; font-weight: 700; cursor: pointer; color: var(--text-main);">Force IPv4 Connection</label>
                    <span class="hint" style="margin-top: 4px;">Resolves SMTP hostname to an IPv4 address before connecting. Recommended if you get "Cannot assign requested address" or "Error 99" on shared hosting.</span>
                </div>
            </div>
        </div>
        <button type="submit" name="save_mail_settings" class="btn btn-success">
            <i class="fas fa-save"></i> Save All Settings
        </button>
    </form>

    <!-- Current Configuration Status -->
    <div style="margin-top: 25px;">
        <h4 style="margin-bottom: 15px; color: #333;"><i class="fas fa-check-circle"></i> Configuration Status</h4>
        <div class="config-list">
            <div class="config-item">
                <div>
                    <strong>Admin Email:</strong>
                    <span><?= htmlspecialchars(getSettingValue('mail_admin_email') ?: 'Not configured') ?></span>
                </div>
                <span class="status-badge <?= getSettingValue('mail_admin_email') ? 'status-configured' : 'status-missing' ?>">
                    <?= getSettingValue('mail_admin_email') ? 'Configured' : 'Missing' ?>
                </span>
            </div>
            <div class="config-item">
                <div>
                    <strong>SMTP Email:</strong>
                    <span><?= htmlspecialchars(getSettingValue('mail_smtp_email') ?: 'Not configured') ?></span>
                </div>
                <span class="status-badge <?= getSettingValue('mail_smtp_email') ? 'status-configured' : 'status-missing' ?>">
                    <?= getSettingValue('mail_smtp_email') ? 'Configured' : 'Missing' ?>
                </span>
            </div>
            <div class="config-item">
                <div>
                    <strong>SMTP Password:</strong>
                    <span><?= getSettingValue('mail_smtp_password') ? '******** (encrypted)' : 'Not configured' ?></span>
                </div>
                <span class="status-badge <?= getSettingValue('mail_smtp_password') ? 'status-configured' : 'status-missing' ?>">
                    <?= getSettingValue('mail_smtp_password') ? 'Set' : 'Missing' ?>
                </span>
            </div>
            <div class="config-item">
                <div>
                    <strong>SMTP Server:</strong>
                    <span><?= htmlspecialchars(getSettingValue('mail_smtp_server') ?: 'Not configured') ?></span>
                </div>
                <span class="status-badge <?= getSettingValue('mail_smtp_server') ? 'status-configured' : 'status-missing' ?>">
                    <?= getSettingValue('mail_smtp_server') ? 'Configured' : 'Missing' ?>
                </span>
            </div>
            <div class="config-item">
                <div>
                    <strong>SMTP Port:</strong>
                    <span><?= htmlspecialchars(getSettingValue('mail_smtp_port', '587')) ?></span>
                </div>
                <span class="status-badge status-configured">Active</span>
            </div>
            <div class="config-item">
                <div>
                    <strong>SMTP Protocol:</strong>
                    <span><?= strtoupper(htmlspecialchars(getSettingValue('mail_smtp_protocol', 'tls'))) ?></span>
                </div>
                <span class="status-badge status-configured">Active</span>
            </div>
            <div class="config-item">
                <div>
                    <strong>Force IPv4:</strong>
                    <span><?= getSettingValue('mail_force_ipv4', '0') === '1' ? 'Enabled' : 'Disabled' ?></span>
                </div>
                <span class="status-badge <?= getSettingValue('mail_force_ipv4', '0') === '1' ? 'status-configured' : '' ?>" style="color: <?= getSettingValue('mail_force_ipv4', '0') === '1' ? 'var(--success)' : 'var(--text-muted)' ?>;">
                    <?= getSettingValue('mail_force_ipv4', '0') === '1' ? 'Active' : 'Inactive' ?>
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Test SMTP Connection -->
<div class="settings-card">
    <h3><i class="fas fa-wifi"></i> SMTP Connection Status</h3>
    <div class="test-section">
        <p style="margin-bottom: 15px; color: #666;">Click to test if your SMTP server is reachable and configured correctly.</p>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <button type="submit" name="test_smtp" class="btn" style="background: #17a2b8;">
                <i class="fas fa-plug"></i> Test SMTP Connection
            </button>
        </form>
    </div>
</div>

<!-- Test Email Section -->
<div class="settings-card">
    <h3><i class="fas fa-paper-plane"></i> Send Test Email</h3>
    <form method="POST" class="test-section">
        <div class="row">
            <div class="form-group">
                <label>Recipient Email</label>
                <input type="email" name="test_email" value="" placeholder="test@example.com" required>
                <span class="hint">Enter email address to receive test message</span>
            </div>
        </div>
        <button type="submit" name="send_test_email" class="btn">
            <i class="fas fa-paper-plane"></i> Send Test Email
        </button>
    </form>
</div>

<?php
$queue_stats = ['pending' => 0, 'processing' => 0, 'sent' => 0, 'failed' => 0, 'total' => 0];
$queued_emails = [];
$mail_logs = [];
try {
    $queue_stats = getMailQueueStats($conn);
    $queued_emails = $conn->query("SELECT * FROM mail_queue ORDER BY created_at DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
    $mail_logs = $conn->query("SELECT * FROM mail_logs ORDER BY sent_at DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}
?>

<!-- High-Speed Async Mail Queue Status -->
<div class="settings-card" style="border-left: 4px solid var(--primary);">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 20px;">
        <h3 style="margin: 0;"><i class="fas fa-bolt" style="color: #f59e0b;"></i> Fast Asynchronous Email Queue</h3>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <form method="POST" style="margin: 0;">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <button type="submit" name="process_mail_queue" class="btn" style="background: #10b981; color: #fff; padding: 8px 16px; font-size: 13px; font-weight: 600; border-radius: 8px; border: none; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                    <i class="fas fa-play"></i> Process Queue Now
                </button>
            </form>
            <?php if ($queue_stats['failed'] > 0): ?>
            <form method="POST" style="margin: 0;">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <button type="submit" name="retry_failed_queue" class="btn" style="background: #f59e0b; color: #fff; padding: 8px 16px; font-size: 13px; font-weight: 600; border-radius: 8px; border: none; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                    <i class="fas fa-redo"></i> Retry Failed (<?= $queue_stats['failed'] ?>)
                </button>
            </form>
            <?php endif; ?>
            <form method="POST" style="margin: 0;" onsubmit="return confirm('Clear completed and old queue records?');">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <button type="submit" name="clear_mail_queue" class="btn" style="background: var(--bg-main); color: var(--text-muted); padding: 8px 16px; font-size: 13px; font-weight: 600; border-radius: 8px; border: 1px solid var(--border-color); cursor: pointer; display: flex; align-items: center; gap: 6px;">
                    <i class="fas fa-trash-alt"></i> Clear Old
                </button>
            </form>
        </div>
    </div>
    <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 20px;">
        All admin notifications and form inquiries are queued in &lt; 2ms and delivered asynchronously in the background so your admin panel responds instantly.
    </p>
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px;">
        <div style="background: var(--bg-main); border: 1px solid var(--border-color); padding: 16px; border-radius: 10px; text-align: center;">
            <div style="font-size: 24px; font-weight: 700; color: #f59e0b;"><?= $queue_stats['pending'] ?></div>
            <div style="font-size: 12px; color: var(--text-muted); font-weight: 600; text-transform: uppercase; margin-top: 4px;">Pending Delivery</div>
        </div>
        <div style="background: var(--bg-main); border: 1px solid var(--border-color); padding: 16px; border-radius: 10px; text-align: center;">
            <div style="font-size: 24px; font-weight: 700; color: #3b82f6;"><?= $queue_stats['processing'] ?></div>
            <div style="font-size: 12px; color: var(--text-muted); font-weight: 600; text-transform: uppercase; margin-top: 4px;">In Flight / Worker</div>
        </div>
        <div style="background: var(--bg-main); border: 1px solid var(--border-color); padding: 16px; border-radius: 10px; text-align: center;">
            <div style="font-size: 24px; font-weight: 700; color: #10b981;"><?= $queue_stats['sent'] ?></div>
            <div style="font-size: 12px; color: var(--text-muted); font-weight: 600; text-transform: uppercase; margin-top: 4px;">Delivered (Sent)</div>
        </div>
        <div style="background: var(--bg-main); border: 1px solid var(--border-color); padding: 16px; border-radius: 10px; text-align: center;">
            <div style="font-size: 24px; font-weight: 700; color: <?= $queue_stats['failed'] > 0 ? '#ef4444' : 'var(--text-muted)' ?>;"><?= $queue_stats['failed'] ?></div>
            <div style="font-size: 12px; color: var(--text-muted); font-weight: 600; text-transform: uppercase; margin-top: 4px;">Failed Retries</div>
        </div>
    </div>

    <!-- Recent Queue Items Table -->
    <h4 style="font-size: 14px; font-weight: 700; color: var(--text-main); margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
        <i class="fas fa-layer-group" style="color: var(--primary);"></i> Recent Queue Dispatch Items
    </h4>
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="border-bottom: 2px solid var(--border-color); color: var(--text-main); font-weight: 600; font-size: 13px;">
                    <th style="padding: 10px 8px;">ID</th>
                    <th style="padding: 10px 8px;">Recipient</th>
                    <th style="padding: 10px 8px;">Subject</th>
                    <th style="padding: 10px 8px;">Status</th>
                    <th style="padding: 10px 8px;">Attempts</th>
                    <th style="padding: 10px 8px;">Created At</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($queued_emails as $q): 
                    $badgeBg = match($q['status']) {
                        'sent' => 'color-mix(in srgb, var(--success) 15%, transparent)',
                        'processing' => 'color-mix(in srgb, #3b82f6 15%, transparent)',
                        'failed' => 'color-mix(in srgb, var(--danger) 15%, transparent)',
                        default => 'color-mix(in srgb, #f59e0b 15%, transparent)'
                    };
                    $badgeColor = match($q['status']) {
                        'sent' => 'var(--success)',
                        'processing' => '#3b82f6',
                        'failed' => 'var(--danger)',
                        default => '#f59e0b'
                    };
                ?>
                <tr style="border-bottom: 1px solid var(--border-color); font-size: 13px;">
                    <td style="padding: 10px 8px; color: var(--text-muted); font-family: monospace;">#<?= $q['id'] ?></td>
                    <td style="padding: 10px 8px; color: var(--text-main); font-weight: 500;"><?= htmlspecialchars($q['recipient']) ?></td>
                    <td style="padding: 10px 8px; color: var(--text-muted);"><?= htmlspecialchars($q['subject']) ?></td>
                    <td style="padding: 10px 8px;">
                        <span style="padding: 3px 8px; border-radius: 6px; font-size: 11px; font-weight: 600; background: <?= $badgeBg ?>; color: <?= $badgeColor ?>;">
                            <?= ucfirst($q['status']) ?>
                        </span>
                    </td>
                    <td style="padding: 10px 8px; color: var(--text-muted);"><?= $q['attempts'] ?>/<?= $q['max_attempts'] ?></td>
                    <td style="padding: 10px 8px; color: var(--text-muted);"><?= date('M d, H:i:s', strtotime($q['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($queued_emails)): ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 20px; color: var(--text-muted); font-size: 13px;">No items currently in queue.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Outbound Email Delivery Logs -->
<div class="settings-card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; flex-wrap: wrap; gap: 10px;">
        <h3 style="margin: 0;"><i class="fas fa-list-check"></i> Outbound Delivery Logs</h3>
        <a href="../mail_logs.php" class="btn btn-primary" style="padding: 7px 14px; font-size: 12.5px; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
            <i class="fas fa-envelope-open-text"></i> View Full Email Sent History &rarr;
        </a>
    </div>
    <p style="margin-bottom: 15px; color: var(--text-muted); font-size: 14px;">Recent system email transactions and connection results.</p>
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="border-bottom: 2px solid var(--border-color); color: var(--text-main); font-weight: 600; font-size: 13px;">
                    <th style="padding: 12px 8px;">Recipient</th>
                    <th style="padding: 12px 8px;">Subject</th>
                    <th style="padding: 12px 8px;">Status</th>
                    <th style="padding: 12px 8px;">Error Message</th>
                    <th style="padding: 12px 8px;">Sent At</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($mail_logs as $log): ?>
                <tr style="border-bottom: 1px solid var(--border-color); font-size: 13px;">
                    <td style="padding: 12px 8px; color: var(--text-main); font-weight: 500;"><?= htmlspecialchars($log['recipient']) ?></td>
                    <td style="padding: 12px 8px; color: var(--text-muted);"><?= htmlspecialchars($log['subject']) ?></td>
                    <td style="padding: 12px 8px;">
                        <span style="padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: 600; display: inline-block; background: <?= $log['status'] === 'sent' ? 'color-mix(in srgb, var(--success) 15%, transparent)' : 'color-mix(in srgb, var(--danger) 15%, transparent)' ?>; color: <?= $log['status'] === 'sent' ? 'var(--success)' : 'var(--danger)' ?>;">
                            <?= ucfirst($log['status']) ?>
                        </span>
                    </td>
                    <td style="padding: 12px 8px; color: var(--danger); font-size: 12px; max-width: 250px; overflow-wrap: break-word;"><?= htmlspecialchars($log['error_message'] ?? 'None') ?></td>
                    <td style="padding: 12px 8px; color: var(--text-muted);"><?= date('M d, Y H:i', strtotime($log['sent_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
                
                <?php if (empty($mail_logs)): ?>
                <tr>
                    <td colspan="5" style="text-align: center; padding: 30px; color: var(--text-muted); font-size: 13px;">No outgoing email logs found.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Common SMTP Providers Guide -->
<div class="settings-card" style="background: #fff3e0;">
    <h3 style="color: #f57c00;"><i class="fas fa-lightbulb"></i> SMTP Provider Setup Guide</h3>
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;">
        <div style="background: #fff; padding: 20px; border-radius: 8px; border: 2px solid #6366f1;">
            <h4 style="color: #6366f1; margin-bottom: 10px;">NinzaHost / CloudASPIRE <span style="font-size:11px;background:#6366f1;color:#fff;padding:2px 8px;border-radius:4px;margin-left:6px;">Current</span></h4>
            <ul style="color: #666; font-size: 13px; line-height: 1.8; padding-left: 20px;">
                <li>SMTP Server: <strong>portel.cloudaspire.in</strong></li>
                <li>Port: <strong>465</strong> (SSL/SMTPS)</li>
                <li>Protocol: <strong>SSL</strong></li>
                <li>Username: noreply@portel.cloudaspire.in</li>
                <li><em>Do NOT use mail.portel.cloudaspire.in</em></li>
            </ul>
        </div>
        <div style="background: #fff; padding: 20px; border-radius: 8px;">
            <h4 style="color: #333; margin-bottom: 10px;">Gmail / Google Workspace</h4>
            <ul style="color: #666; font-size: 13px; line-height: 1.8; padding-left: 20px;">
                <li>SMTP Server: smtp.gmail.com</li>
                <li>Port: 587 (TLS) or 465 (SSL)</li>
                <li>Use App Password instead of regular password</li>
                <li>Enable 2FA to generate App Password</li>
            </ul>
        </div>
        <div style="background: #fff; padding: 20px; border-radius: 8px;">
            <h4 style="color: #333; margin-bottom: 10px;">Hostinger / cPanel</h4>
            <ul style="color: #666; font-size: 13px; line-height: 1.8; padding-left: 20px;">
                <li>SMTP Server: smtp.hostinger.com</li>
                <li>Port: 465 (SSL)</li>
                <li>Use your hosting email credentials</li>
                <li>Ensure SMTP is enabled in cPanel</li>
            </ul>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../../../admin/includes/footer.php'; ?>