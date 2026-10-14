<?php
/**
 * @var string|null $message
 * @var string|null $message_type
 * @var array $backups
 * @var int $dbCount
 * @var int $fileCount
 * @var int $totalSize
 * @var string $csrf_token
 * @var string $frequency
 * @var string $type
 * @var string $sync_url
 * @var string|int|null $last_run
 * @var object|null $backupModel
 */

$message = $message ?? null;
$message_type = $message_type ?? null;
$backups = $backups ?? [];
$dbCount = $dbCount ?? 0;
$fileCount = $fileCount ?? 0;
$totalSize = $totalSize ?? 0;
$csrf_token = $csrf_token ?? '';
$frequency = $frequency ?? 'off';
$type = $type ?? 'database';
$sync_url = $sync_url ?? '';
$last_run = $last_run ?? null;

require_once __DIR__ . '/../../../admin/includes/header.php';
?>
<link rel="stylesheet" href="<?= $base_path ?>view/backup/backup.css">

<div class="backup-header">
    <h2><i class="fas fa-shield-alt"></i> Backup Manager</h2>
    <div class="header-actions">
        <button class="btn btn-primary" onclick="BK.openGenerateModal()"><i class="fas fa-plus-circle"></i> Generate Backup</button>
        <button class="btn btn-edit" onclick="BK.openUploadModal()"><i class="fas fa-upload"></i> Upload & Restore</button>
    </div>
</div>

<?php if ($message): ?>
    <div style="margin: 0 0 24px 0; padding: 16px; border-radius: var(--border-radius); display: flex; align-items: center; gap: 12px; font-size: 14px; background: <?= $message_type === 'success' ? 'color-mix(in srgb, var(--success) 10%, var(--bg-card))' : 'color-mix(in srgb, var(--danger) 10%, var(--bg-card))' ?>; border: 1px solid <?= $message_type === 'success' ? 'var(--success)' : 'var(--danger)' ?>; color: var(--text-main);">
        <i class="fas fa-<?= $message_type === 'success' ? 'check-circle' : 'exclamation-circle' ?>" style="color: <?= $message_type === 'success' ? 'var(--success)' : 'var(--danger)' ?>; font-size: 18px;"></i>
        <div><?= htmlspecialchars($message) ?></div>
    </div>
<?php endif; ?>

<!-- Stats -->
<div class="backup-stats">
    <div class="bstat">
        <div class="bstat-icon purple"><i class="fas fa-archive"></i></div>
        <div class="bstat-info"><h4 id="backupCount"><?= count($backups) ?></h4><p>Total Backups</p></div>
    </div>
    <div class="bstat">
        <div class="bstat-icon green"><i class="fas fa-database"></i></div>
        <div class="bstat-info"><h4><?= $dbCount ?></h4><p>Database Backups</p></div>
    </div>
    <div class="bstat">
        <div class="bstat-icon blue"><i class="fas fa-folder"></i></div>
        <div class="bstat-info"><h4><?= $fileCount ?></h4><p>File Backups</p></div>
    </div>
    <div class="bstat">
        <div class="bstat-icon orange"><i class="fas fa-hdd"></i></div>
        <div class="bstat-info"><h4 id="totalSize"><?= $backupModel->listBackups() ? round($totalSize/1024/1024, 1) . ' MB' : '0 B' ?></h4><p>Storage Used</p></div>
    </div>
</div>

<!-- Backup Policy & Cloud Sync -->
<div class="backup-card" style="margin-bottom: 24px; padding: 24px;">
    <h3 style="margin-bottom: 8px;"><i class="fas fa-clock"></i> Automated Policy & Cloud Sync</h3>
    <p style="color: var(--text-muted); font-size: 13px; margin-bottom: 20px;">Configure automatic database backups and synchronize archives automatically to remote cloud URLs.</p>
    
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 20px;">
            <div class="form-group-bk">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; font-size: 13px; color: var(--text-main);">Backup Frequency</label>
                <select name="backup_frequency" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: var(--btn-radius); background: var(--bg-main); color: var(--text-main); font-size: 13px;">
                    <option value="off" <?= $frequency === 'off' ? 'selected' : '' ?>>Disabled / Manual Only</option>
                    <option value="daily" <?= $frequency === 'daily' ? 'selected' : '' ?>>Daily</option>
                    <option value="weekly" <?= $frequency === 'weekly' ? 'selected' : '' ?>>Weekly</option>
                    <option value="monthly" <?= $frequency === 'monthly' ? 'selected' : '' ?>>Monthly</option>
                </select>
            </div>
            
            <div class="form-group-bk">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; font-size: 13px; color: var(--text-main);">Backup Type</label>
                <select name="backup_type" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: var(--btn-radius); background: var(--bg-main); color: var(--text-main); font-size: 13px;">
                    <option value="database" <?= $type === 'database' ? 'selected' : '' ?>>Database Only</option>
                    <option value="files" <?= $type === 'files' ? 'selected' : '' ?>>Files Only</option>
                    <option value="full" <?= $type === 'full' ? 'selected' : '' ?>>Full Backup</option>
                </select>
            </div>
            
            <div class="form-group-bk">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; font-size: 13px; color: var(--text-main);">Cloud Sync Webhook URL</label>
                <input type="text" name="backup_sync_url" value="<?= htmlspecialchars($sync_url) ?>" placeholder="https://api.dropbox.com/backup or custom endpoint" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: var(--btn-radius); background: var(--bg-main); color: var(--text-main); font-size: 13px;">
                <span style="font-size: 11px; color: var(--text-muted); display: block; margin-top: 4px;">Post backups automatically to this URL upon creation. Use mock/localhost to simulate.</span>
            </div>
        </div>
        
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
            <div style="font-size: 12px; color: var(--text-muted);">
                <i class="fas fa-calendar-check"></i> Last Scheduled Backup: 
                <strong style="color: var(--text-main);"><?= $last_run ? date('Y-m-d H:i:s', (int)$last_run) : 'Never' ?></strong>
            </div>
            <button type="submit" name="save_backup_settings" class="btn btn-primary" style="padding: 10px 20px; font-size: 13px;">
                <i class="fas fa-save"></i> Save Policies
            </button>
        </div>
    </form>
</div>

<!-- Backup History -->
<div class="backup-card">
    <h3><i class="fas fa-history"></i> Backup History</h3>
    <div class="table-responsive">
        <table class="backup-table">
            <thead><tr><th>File</th><th>Type</th><th>Size</th><th>Created</th><th>Actions</th></tr></thead>
            <tbody id="backupList">
                <?php if (empty($backups)): ?>
                <tr><td colspan="5"><div class="empty-state"><i class="fas fa-box-open"></i><p>No backups found. Generate your first backup!</p></div></td></tr>
                <?php else: foreach ($backups as $b): ?>
                <tr>
                    <td><div class="file-icon"><i class="fas fa-file-archive"></i><div><div class="fname"><?= htmlspecialchars($b['filename']) ?></div><div class="fmeta"><?= $b['created_ago'] ?></div></div></div></td>
                    <td><span class="type-badge <?= $b['type'] ?>"><?= $b['type'] ?></span></td>
                    <td><?= $b['size_formatted'] ?></td>
                    <td><?= $b['created'] ?></td>
                    <td><div class="td-actions">
                        <a href="<?= $base_path ?>controller/backup/BackupController.php?action=download&file=<?= urlencode($b['filename']) ?>" class="btn btn-sm btn-primary" title="Download"><i class="fas fa-download"></i></a>
                        <button class="btn btn-sm btn-edit" onclick="BK.confirmRestore('<?= htmlspecialchars($b['filename'], ENT_QUOTES) ?>')" title="Restore"><i class="fas fa-undo"></i></button>
                        <button class="btn btn-sm btn-delete" onclick="BK.confirmDelete('<?= htmlspecialchars($b['filename'], ENT_QUOTES) ?>')" title="Delete"><i class="fas fa-trash"></i></button>
                    </div></td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ═══════ GENERATE MODAL ═══════ -->
<div class="bk-overlay" id="generateModal">
<div class="bk-modal">
    <button class="bk-close" onclick="BK.closeModal('generateModal')"><i class="fas fa-times"></i></button>
    <div class="step-indicator"><span class="step-dot active"></span><span class="step-dot"></span><span class="step-dot"></span></div>

    <!-- Step 1: Config -->
    <div id="gen-step-1">
        <h3><i class="fas fa-cog"></i> Configure Backup</h3>
        <div class="form-group-bk">
            <label>Backup Type</label>
            <div class="radio-group">
                <div class="radio-opt"><input type="radio" name="backup_type" value="full" id="typeFull" checked><label for="typeFull"><i class="fas fa-layer-group"></i>Full Backup</label></div>
                <div class="radio-opt"><input type="radio" name="backup_type" value="database" id="typeDb"><label for="typeDb"><i class="fas fa-database"></i>Database</label></div>
                <div class="radio-opt"><input type="radio" name="backup_type" value="files" id="typeFiles"><label for="typeFiles"><i class="fas fa-folder"></i>Files</label></div>
            </div>
        </div>
        <div class="form-group-bk">
            <label>Custom Name</label>
            <input type="text" id="customName" value="backup" placeholder="e.g. company_backup">
        </div>
        <button class="btn btn-primary btn-block" id="sendOtpBtn" onclick="BK.sendOtp()"><i class="fas fa-paper-plane"></i> Send OTP & Continue</button>
    </div>

    <!-- Step 2: OTP -->
    <div id="gen-step-2" style="display:none;">
        <h3><i class="fas fa-lock"></i> Verify Identity</h3>
        <p style="text-align:center;color:var(--text-muted);font-size:14px;margin-bottom:8px;">Enter the 6-digit code sent to your email</p>
        <div class="otp-inputs">
            <input type="text" maxlength="1" inputmode="numeric">
            <input type="text" maxlength="1" inputmode="numeric">
            <input type="text" maxlength="1" inputmode="numeric">
            <input type="text" maxlength="1" inputmode="numeric">
            <input type="text" maxlength="1" inputmode="numeric">
            <input type="text" maxlength="1" inputmode="numeric">
        </div>
        <div class="otp-timer">Resend available in <strong id="resendTimer">60s</strong></div>
        <div style="display:flex;gap:12px;">
            <button class="btn btn-block" id="resendBtn" disabled onclick="BK.resendOtp()" style="flex:1;background:var(--bg-main);color:var(--text-main);"><i class="fas fa-redo"></i> Resend</button>
            <button class="btn btn-primary btn-block" id="verifyOtpBtn" onclick="BK.verifyOtp()" style="flex:2;"><i class="fas fa-check"></i> Verify OTP</button>
        </div>
    </div>

    <!-- Step 3: Generate -->
    <div id="gen-step-3" style="display:none;">
        <h3><i class="fas fa-check-circle" style="color:var(--success);"></i> Ready to Generate</h3>
        <p style="color:var(--text-muted);font-size:14px;margin-bottom:20px;">Identity verified. Click below to create your backup.</p>
        <div id="genProgress" style="display:none;"><div class="progress-bar-bk"><div class="fill"></div></div><p style="text-align:center;font-size:13px;color:var(--text-muted);">Generating backup, please wait...</p></div>
        <button class="btn btn-success-bk btn-block" id="generateBtn" onclick="BK.generateBackup()"><i class="fas fa-download"></i> Generate Backup</button>
    </div>
</div>
</div>

<!-- ═══════ DELETE MODAL ═══════ -->
<div class="bk-overlay" id="deleteModal">
<div class="bk-modal" style="max-width:400px;">
    <button class="bk-close" onclick="BK.closeModal('deleteModal')"><i class="fas fa-times"></i></button>
    <h3><i class="fas fa-trash" style="color:var(--danger);"></i> Delete Backup</h3>
    <div class="confirm-box"><p><i class="fas fa-exclamation-triangle"></i> This will permanently delete <strong id="deleteFileName"></strong></p></div>
    <input type="hidden" id="deleteFileInput">
    <div style="display:flex;gap:12px;margin-top:16px;">
        <button class="btn btn-block" onclick="BK.closeModal('deleteModal')" style="flex:1;background:var(--bg-main);color:var(--text-main);">Cancel</button>
        <button class="btn btn-delete btn-block" id="deleteBtn" onclick="BK.deleteBackup()" style="flex:1;"><i class="fas fa-trash"></i> Delete</button>
    </div>
</div>
</div>

<!-- ═══════ RESTORE MODAL ═══════ -->
<div class="bk-overlay" id="restoreModal">
<div class="bk-modal" style="max-width:440px;">
    <button class="bk-close" onclick="BK.closeModal('restoreModal')"><i class="fas fa-times"></i></button>
    <h3><i class="fas fa-undo" style="color:var(--warning);"></i> Restore Backup</h3>
    <div class="confirm-box"><p><i class="fas fa-exclamation-triangle"></i> Restoring <strong id="restoreFileName"></strong> will overwrite current data. This cannot be undone!</p></div>
    <input type="hidden" id="restoreFileInput">
    <div style="display:flex;gap:12px;margin-top:16px;">
        <button class="btn btn-block" onclick="BK.closeModal('restoreModal')" style="flex:1;background:var(--bg-main);color:var(--text-main);">Cancel</button>
        <button class="btn btn-edit btn-block" id="restoreBtn" onclick="BK.restoreBackup()" style="flex:1;"><i class="fas fa-undo"></i> Restore Now</button>
    </div>
</div>
</div>

<!-- ═══════ UPLOAD MODAL ═══════ -->
<div class="bk-overlay" id="uploadModal">
<div class="bk-modal" style="max-width:440px;">
    <button class="bk-close" onclick="BK.closeModal('uploadModal')"><i class="fas fa-times"></i></button>
    <h3><i class="fas fa-upload"></i> Upload & Restore</h3>
    <p style="color:var(--text-muted);font-size:14px;margin-bottom:20px;">Upload a .zip or .sql backup file to restore.</p>
    <div class="form-group-bk"><label>Backup File</label><input type="file" id="uploadFile" accept=".zip,.sql"></div>
    <div class="confirm-box"><p><i class="fas fa-info-circle"></i> This will overwrite current data. Ensure you have a recent backup.</p></div>
    <button class="btn btn-primary btn-block" id="uploadBtn" onclick="BK.uploadRestore()"><i class="fas fa-upload"></i> Upload & Restore</button>
</div>
</div>

<script src="<?= $base_path ?>view/backup/backup.js"></script>
<script>BK.init('<?= $csrf_token ?>', '<?= $base_path ?>controller/backup/BackupController.php');</script>

<?php require_once __DIR__ . '/../../../admin/includes/footer.php'; ?>
