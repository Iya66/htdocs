<?php
require_once __DIR__ . '/../../../../admin/includes/header.php';
?>

<style>
.alert { padding: 16px 20px; border-radius: var(--border-radius); margin-bottom: 24px; border: 1px solid transparent; font-size: 14px; }
.alert-success { background: color-mix(in srgb, var(--success) 10%, transparent); color: var(--success); border-color: color-mix(in srgb, var(--success) 20%, transparent); }
.alert-error { background: color-mix(in srgb, var(--danger) 10%, transparent); color: var(--danger); border-color: color-mix(in srgb, var(--danger) 20%, transparent); }

.card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--border-radius); padding: 32px; box-shadow: var(--shadow-sm); margin-bottom: 24px; transition: all var(--transition); }
.card h3 { font-size: 18px; color: var(--text-main); margin-bottom: 24px; display: flex; align-items: center; gap: 12px; font-weight: 700; }
.card h3 i { color: var(--primary); font-size: 20px; }
.card h3 .badge { background: color-mix(in srgb, var(--primary) 15%, transparent); color: var(--primary); padding: 4px 12px; border-radius: 20px; font-size: 12px; margin-left: auto; border: 1px solid color-mix(in srgb, var(--primary) 30%, transparent); font-weight: 600; }

.form-group { margin-bottom: 20px; }
.form-group label { display: block; margin-bottom: 8px; color: var(--text-main); font-weight: 600; font-size: 14px; }
.form-group input, .form-group select, .form-group textarea { 
    width: 100%; padding: 14px 18px; 
    border: 1px solid var(--border-color); border-radius: var(--btn-radius); 
    font-size: 14px; background: var(--bg-main); color: var(--text-main); 
    transition: all var(--transition); 
}
.form-group input:focus, .form-group select:focus, .form-group textarea:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px color-mix(in srgb, var(--primary) 20%, transparent); }
.form-group textarea { resize: vertical; min-height: 80px; }
.form-group .hint { font-size: 12px; color: var(--text-muted); margin-top: 6px; display: block; }

.grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
.grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
@media (max-width: 992px) { .grid-2, .grid-3 { grid-template-columns: 1fr; } }

.toggle-switch { position: relative; width: 50px; height: 26px; }
.toggle-switch input { opacity: 0; width: 0; height: 0; }
.toggle-slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background: var(--border-color); transition: .4s; border-radius: 26px; }
.toggle-slider:before { position: absolute; content: ""; height: 20px; width: 20px; left: 3px; bottom: 3px; background: #fff; transition: .4s; border-radius: 50%; box-shadow: var(--shadow-sm); }
input:checked + .toggle-slider { background: var(--primary); }
input:checked + .toggle-slider:before { transform: translateX(24px); }

table { width: 100%; border-collapse: separate; border-spacing: 0; border: 1px solid var(--border-color); border-radius: var(--border-radius); overflow: hidden; }
th { background: var(--bg-main); color: var(--text-muted); padding: 16px; text-align: left; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 1px solid var(--border-color); }
td { padding: 16px; border-bottom: 1px solid var(--border-color); font-size: 14px; color: var(--text-main); }
tr:last-child td { border-bottom: none; }
tr:hover td { background: color-mix(in srgb, var(--primary) 5%, transparent); }

.module-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--border-radius); padding: 24px; margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center; transition: all var(--transition); }
.module-card:hover { border-color: var(--primary); box-shadow: var(--shadow-sm); transform: translateY(-2px); }
.module-info h4 { color: var(--text-main); margin-bottom: 8px; font-size: 16px; font-weight: 600; }
.module-info p { color: var(--text-muted); font-size: 13px; margin: 0; display: flex; align-items: center; gap: 8px; }
.module-info .type-badge { display: inline-block; padding: 4px 12px; background: color-mix(in srgb, var(--primary) 15%, transparent); color: var(--primary); border-radius: 20px; font-size: 11px; font-weight: 600; border: 1px solid color-mix(in srgb, var(--primary) 30%, transparent); }
.module-actions { display: flex; gap: 12px; align-items: center; }

.status-active { background: color-mix(in srgb, var(--success) 15%, transparent); color: var(--success); padding: 6px 12px; border-radius: 20px; font-size: 11px; font-weight: 600; border: 1px solid color-mix(in srgb, var(--success) 30%, transparent); }
.status-inactive { background: color-mix(in srgb, var(--danger) 15%, transparent); color: var(--danger); padding: 6px 12px; border-radius: 20px; font-size: 11px; font-weight: 600; border: 1px solid color-mix(in srgb, var(--danger) 30%, transparent); }

.tab-nav { display: flex; gap: 12px; margin-bottom: 32px; flex-wrap: wrap; }
.tab-btn { 
    padding: 12px 24px; background: var(--bg-card); 
    border: 1px solid var(--border-color); border-radius: var(--btn-radius); 
    cursor: pointer; font-size: 14px; font-weight: 600; color: var(--text-muted); 
    transition: all var(--transition); 
}
.tab-btn:hover, .tab-btn.active { background: var(--primary); color: #fff; border-color: var(--primary); }
.tab-content { display: none; }
.tab-content.active { display: block; animation: fadeIn var(--transition) ease forwards; }

.google-config { background: color-mix(in srgb, var(--primary) 5%, transparent); border-radius: var(--border-radius); padding: 32px; border: 1px solid var(--border-color); }
.google-logo { width: 64px; height: 64px; background: #fff; border-radius: 16px; display: flex; align-items: center; justify-content: center; margin-bottom: 0; box-shadow: var(--shadow-sm); border: 1px solid var(--border-color); }

.email-avatar { width: 40px; height: 40px; background: color-mix(in srgb, var(--primary) 15%, transparent); color: var(--primary); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 16px; border: 1px solid color-mix(in srgb, var(--primary) 30%, transparent); }
.role-badge { display: inline-block; padding: 4px 10px; background: color-mix(in srgb, var(--info) 15%, transparent); color: var(--info); border-radius: 12px; font-size: 11px; font-weight: 600; border: 1px solid color-mix(in srgb, var(--info) 30%, transparent); }
</style>

<div class="saas-header">
    <h1 class="saas-header-title">
        <i class="fas fa-cubes" aria-hidden="true" style="color: var(--primary);"></i>
        Module & Auth Settings
    </h1>
    <p class="saas-header-subtitle">Toggle core platform features, single sign-on providers, and access control.</p>
</div>

<?php require_once __DIR__ . '/../../../../admin/includes/settings-nav.php'; ?>

<?php if ($message): ?>
    <div class="alert alert-<?= $message_type ?>"><i class="fas fa-<?= $message_type === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i> <?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<div class="tab-nav">
    <button class="tab-btn active" onclick="showTab('modules')"><i class="fas fa-cubes"></i> Modules</button>
    <button class="tab-btn" onclick="showTab('google-login')"><i class="fab fa-google"></i> Google Login</button>
    <button class="tab-btn" onclick="showTab('registered-emails')"><i class="fas fa-envelope"></i> Registered Emails</button>
</div>

<!-- Modules Tab -->
<div id="modules" class="tab-content active">
    <?php if ($edit_module): ?>
    <div class="card">
        <h3>
            <i class="fas fa-edit"></i> Edit Module
        </h3>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <input type="hidden" name="module_id" value="<?= $edit_module['id'] ?>">
            <div class="grid-3">
                <div class="form-group">
                    <label>Module Name *</label>
                    <input type="text" name="module_name" value="<?= htmlspecialchars($edit_module['module_name'] ?? '') ?>" required placeholder="e.g., Leads Module">
                </div>
                <div class="form-group">
                    <label>Module Key *</label>
                    <input type="text" name="module_key" value="<?= htmlspecialchars($edit_module['module_key'] ?? '') ?>" required placeholder="e.g., leads_module" pattern="[a-z_]+">
                    <span class="hint">Lowercase letters and underscores only</span>
                </div>
                <div class="form-group">
                    <label>Type</label>
                    <select name="module_type">
                        <option value="feature" <?= ($edit_module['module_type'] ?? '') === 'feature' ? 'selected' : '' ?>>Feature</option>
                        <option value="integration" <?= ($edit_module['module_type'] ?? '') === 'integration' ? 'selected' : '' ?>>Integration</option>
                        <option value="utility" <?= ($edit_module['module_type'] ?? '') === 'utility' ? 'selected' : '' ?>>Utility</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="module_description" placeholder="Module description..."><?= htmlspecialchars($edit_module['description'] ?? ($edit_module['module_description'] ?? '')) ?></textarea>
            </div>
            <button type="submit" name="save_module" class="btn btn-primary">
                <i class="fas fa-save"></i> Update Module
            </button>
            <a href="module" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php endif; ?>

    <div class="card">
        <h3><i class="fas fa-list"></i> All Modules</h3>
        <?php if (empty($modules)): ?>
            <p style="color: #666; text-align: center; padding: 30px;">No modules found. Add your first module above.</p>
        <?php else: ?>
            <?php foreach ($modules as $module): ?>
                <div class="module-card">
                    <div class="module-info">
                        <span class="type-badge"><?= ucfirst($module['module_type']) ?></span>
                        <h4><?= htmlspecialchars($module['module_name']) ?></h4>
                        <p><?= htmlspecialchars($module['module_key']) ?> - <?= htmlspecialchars($module['description'] ?? ($module['module_description'] ?? 'No description')) ?></p>
                    </div>
                    <div class="module-actions">
                        <span class="<?= $module['is_active'] ? 'status-active' : 'status-inactive' ?>">
                            <?= $module['is_active'] ? 'Active' : 'Inactive' ?>
                        </span>
                        <a href="?edit_module=<?= $module['id'] ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                            <input type="hidden" name="module_id" value="<?= $module['id'] ?>">
                            <button type="submit" name="toggle_module" class="btn btn-sm btn-primary" title="Toggle Status">
                                <i class="fas fa-toggle-on"></i>
                            </button>
                        </form>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this module?')">
                            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                            <input type="hidden" name="module_id" value="<?= $module['id'] ?>">
                            <button type="submit" name="delete_module" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Google Login Tab -->
<div id="google-login" class="tab-content">
    <div class="google-config">
        <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 25px;">
            <div class="google-logo">
                <i class="fab fa-google" style="font-size: 30px; color: #667eea;"></i>
            </div>
            <div>
                <h3 style="margin: 0; color: #333;">Google OAuth Login</h3>
                <p style="margin: 5px 0 0; color: #666; font-size: 13px;">Configure Google OAuth for admin login. Only registered emails can access.</p>
            </div>
            <div style="margin-left: auto;">
                <label class="toggle-switch">
                    <input type="checkbox" name="google_login_enabled" value="1" <?= $google_login_enabled == '1' ? 'checked' : '' ?>>
                    <span class="toggle-slider"></span>
                </label>
                <span style="margin-left: 10px; font-weight: 600;"><?= $google_login_enabled == '1' ? 'Enabled' : 'Disabled' ?></span>
            </div>
        </div>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <div class="grid-2">
                <div class="form-group">
                    <label>Client Key *</label>
                    <input type="text" name="google_client_key" value="<?= htmlspecialchars($google_client_key) ?>" placeholder="Your Google OAuth Client Key">
                    <span class="hint">Get from Google Cloud Console → APIs & Services → Credentials</span>
                </div>
                <div class="form-group">
                    <label>Secret Key *</label>
                    <input type="password" name="google_secret_key" value="<?= htmlspecialchars($google_secret_key) ?>" placeholder="Your Google OAuth Secret Key">
                    <span class="hint">Keep this secret - do not share</span>
                </div>
            </div>
            <div class="form-group">
                <label>Redirect URI</label>
                <input type="url" name="google_redirect_uri" value="<?= htmlspecialchars($google_redirect_uri) ?>" placeholder="https://yourdomain.com/admin/auth-google.php">
                <span class="hint">Add this URL in Google Cloud Console → Authorized redirect URIs</span>
            </div>
            <div class="form-group">
                <label>Allowed Domains (optional)</label>
                <input type="text" name="allowed_domains" value="<?= htmlspecialchars($google_allowed_domains) ?>" placeholder="gmail.com, company.com">
                <span class="hint">Comma-separated domains. Leave empty to allow all domains with registered emails.</span>
            </div>
            <button type="submit" name="save_google_login" class="btn btn-primary"><i class="fas fa-save"></i> Save Google Login Settings</button>
        </form>
    </div>

    <div class="card" style="margin-top: 25px;">
        <h3><i class="fas fa-info-circle"></i> Setup Instructions</h3>
        <ol style="color: #666; line-height: 1.8; padding-left: 20px;">
            <li>Go to <a href="https://console.cloud.google.com/" target="_blank" style="color: #667eea;">Google Cloud Console</a></li>
            <li>Create a new project or select existing one</li>
            <li>Navigate to APIs & Services → Credentials</li>
            <li>Create OAuth 2.0 Client ID (Web application)</li>
            <li>Add authorized redirect URI: <code style="background: #f0f0f0; padding: 2px 8px; border-radius: 4px;"><?= htmlspecialchars($google_redirect_uri) ?></code></li>
            <li>Copy Client ID and Client Secret here</li>
            <li>Enable the Google+ API for your project</li>
            <li>Register emails that are allowed to login in "Registered Emails" tab</li>
        </ol>
    </div>
</div>

<!-- Registered Emails Tab -->
<div id="registered-emails" class="tab-content">
    <div class="card">
        <h3>
            <i class="fas fa-user-plus"></i> <?= $edit_email ? 'Edit Email' : 'Add Registered Email' ?>
            <span class="badge"><?= count($registered_emails) ?> Emails</span>
        </h3>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <?php if ($edit_email): ?>
                <input type="hidden" name="email_id" value="<?= $edit_email['id'] ?>">
            <?php endif; ?>
            <div class="grid-3">
                <div class="form-group">
                    <label>Email Address *</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($edit_email['email'] ?? '') ?>" required placeholder="user@company.com">
                </div>
                <div class="form-group">
                    <label>Name</label>
                    <input type="text" name="email_name" value="<?= htmlspecialchars($edit_email['name'] ?? '') ?>" placeholder="User Name">
                </div>
                <div class="form-group">
                    <label>Role</label>
                    <select name="email_role">
                        <option value="user" <?= ($edit_email['role'] ?? '') === 'user' ? 'selected' : '' ?>>User</option>
                        <option value="admin" <?= ($edit_email['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
                        <option value="manager" <?= ($edit_email['role'] ?? '') === 'manager' ? 'selected' : '' ?>>Manager</option>
                        <option value="viewer" <?= ($edit_email['role'] ?? '') === 'viewer' ? 'selected' : '' ?>>Viewer</option>
                    </select>
                </div>
            </div>
            <div style="display: flex; gap: 10px;">
                <button type="submit" name="<?= $edit_email ? 'update_email' : 'add_email' ?>" class="btn btn-success">
                    <i class="fas fa-<?= $edit_email ? 'save' : 'plus' ?>"></i> <?= $edit_email ? 'Update Email' : 'Add Email' ?>
                </button>
                <?php if ($edit_email): ?>
                    <a href="module.php#registered-emails" class="btn btn-warning"><i class="fas fa-times"></i> Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="card">
        <h3><i class="fas fa-envelope"></i> Registered Emails List</h3>
        <?php if (empty($registered_emails)): ?>
            <p style="color: #666; text-align: center; padding: 30px;">No registered emails found. Add emails above to allow login.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Added</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($registered_emails as $email): ?>
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div class="email-avatar" style="width: 35px; height: 35px; font-size: 14px;">
                                        <?= strtoupper(substr($email['name'] ?: $email['email'], 0, 1)) ?>
                                    </div>
                                    <span><?= htmlspecialchars($email['name'] ?: '-') ?></span>
                                </div>
                            </td>
                            <td><i class="fas fa-envelope" style="color: #667eea; margin-right: 5px;"></i> <?= htmlspecialchars($email['email']) ?></td>
                            <td><span class="role-badge"><?= ucfirst($email['role']) ?></span></td>
                            <td><span class="<?= $email['is_active'] ? 'status-active' : 'status-inactive' ?>"><?= $email['is_active'] ? 'Active' : 'Inactive' ?></span></td>
                            <td><?= date('M d, Y', strtotime($email['created_at'])) ?></td>
                            <td>
                                <div style="display: flex; gap: 5px;">
                                    <a href="?edit_email=<?= $email['id'] ?>#registered-emails" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                        <input type="hidden" name="email_id" value="<?= $email['id'] ?>">
                                        <button type="submit" name="toggle_email_status" class="btn btn-sm btn-primary" title="Toggle Status">
                                            <i class="fas fa-toggle-on"></i>
                                        </button>
                                    </form>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Remove this email? User will not be able to login.')">
                                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                        <input type="hidden" name="email_id" value="<?= $email['id'] ?>">
                                        <button type="submit" name="delete_email" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <div class="card" style="background: #fff3e0;">
        <h3 style="color: #f57c00;"><i class="fas fa-exclamation-triangle"></i> Important Notes</h3>
        <ul style="color: #666; line-height: 1.8; padding-left: 20px;">
            <li>Only registered emails can log in to the admin panel</li>
            <li>If Google Login is enabled, users must authenticate with their registered email</li>
            <li>Deactivate an email to temporarily block login without removing it</li>
            <li>Admin role has full access, other roles may have limited permissions</li>
        </ul>
    </div>
</div>



<script>
function showTab(tabId) {
    document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    document.getElementById(tabId).classList.add('active');
    event.target.classList.add('active');
}

// Handle hash for direct tab access
if (window.location.hash === '#registered-emails') {
    showTab('registered-emails');
}
</script>

<?php require_once __DIR__ . '/../../../../admin/includes/footer.php'; ?>