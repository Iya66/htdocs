<?php
require_once __DIR__ . '/../../../admin/includes/header.php';
?>

<style>
.settings-card { 
    background: var(--bg-card); 
    border: 1px solid var(--border-color); border-radius: var(--border-radius); 
    padding: 32px; box-shadow: var(--shadow-sm); margin-bottom: 24px; 
}
.settings-card h3 { font-size: 18px; color: var(--text-main); margin-bottom: 24px; display: flex; align-items: center; gap: 12px; }
.settings-card h3 i { color: var(--primary); }
.settings-card h3 .badge { background: color-mix(in srgb, var(--primary) 15%, transparent); color: var(--primary); padding: 4px 12px; border-radius: 12px; font-size: 13px; margin-left: auto; }

.form-group { margin-bottom: 20px; }
.form-group label { display: block; margin-bottom: 8px; color: var(--text-main); font-weight: 600; font-size: 14px; }
.form-group label i { color: var(--primary); margin-right: 8px; }
.form-group input, .form-group select { 
    width: 100%; padding: 14px 18px; 
    border: 1px solid var(--border-color); border-radius: var(--btn-radius); 
    font-size: 14px; background: var(--bg-main); color: var(--text-main); transition: all var(--transition); 
}
.form-group input:focus, .form-group select:focus { outline: none; border-color: var(--primary); }
.form-group .hint { font-size: 12px; color: var(--text-muted); margin-top: 6px; display: block; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
.form-row-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
@media (max-width: 992px) { .form-row, .form-row-3 { grid-template-columns: 1fr; gap: 16px; } }

.checkbox-group { display: flex; align-items: center; gap: 12px; }
.checkbox-group input[type="checkbox"] { width: 18px; height: 18px; accent-color: var(--primary); }

.staff-avatar { 
    width: 44px; height: 44px; border-radius: 12px; 
    background: color-mix(in srgb, var(--primary) 15%, transparent); 
    display: flex; align-items: center; justify-content: center; 
    color: var(--primary); font-weight: 700; font-size: 16px; 
    border: 1px solid color-mix(in srgb, var(--primary) 30%, transparent);
}

.status-active { background: color-mix(in srgb, var(--success) 15%, transparent); color: var(--success); padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
.status-inactive { background: color-mix(in srgb, var(--danger) 15%, transparent); color: var(--danger); padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
.role-badge { display: inline-block; padding: 6px 12px; background: var(--bg-main); border: 1px solid var(--border-color); color: var(--text-main); border-radius: 8px; font-size: 12px; font-weight: 600; }

.action-btns { display: flex; gap: 8px; flex-wrap: wrap; }
.edit-form { background: var(--bg-card); padding: 32px; border-radius: var(--border-radius); margin-bottom: 24px; border: 1px dashed var(--primary); }

.tab-nav { display: flex; gap: 12px; margin-bottom: 32px; flex-wrap: wrap; }
.tab-btn { 
    padding: 12px 24px; background: var(--bg-card); 
    border: 1px solid var(--border-color); border-radius: var(--btn-radius); 
    cursor: pointer; font-size: 14px; font-weight: 600; color: var(--text-muted); 
    transition: all var(--transition); 
}
.tab-btn:hover, .tab-btn.active { 
    background: var(--primary); color: #fff; border-color: var(--primary); 
}
.tab-content { display: none; }
.tab-content.active { display: block; }

.audit-table { font-size: 13px; }
.audit-ip { font-family: monospace; color: var(--text-muted); }
.audit-time { color: var(--text-muted); white-space: nowrap; }

.security-badge { 
    display: inline-flex; align-items: center; gap: 8px; 
    background: color-mix(in srgb, var(--success) 15%, transparent); color: var(--success); 
    padding: 8px 16px; border-radius: 20px; font-size: 13px; font-weight: 600;
}

.permission-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
.permission-card { background: var(--bg-main); border: 1px solid var(--border-color); border-radius: var(--border-radius); padding: 24px; }
.permission-card h4 { margin-bottom: 16px; display: flex; align-items: center; gap: 8px; color: var(--text-main); }
.permission-card ul { color: var(--text-muted); font-size: 13px; line-height: 1.8; padding-left: 20px; }
.permission-card ul li::marker { color: var(--primary); }
@media(max-width: 768px) { .form-row, .form-row-3, .permission-grid { grid-template-columns: 1fr; } }
</style>

<div class="saas-page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-users-cog" style="color: var(--primary);"></i> Staff Management</h1>
        <p class="page-subtitle">Manage administrative team members, roles, permissions, and security audit logs.</p>
    </div>
    <div class="header-actions">
        <div class="security-badge"><i class="fas fa-shield-alt"></i> CSRF Protected</div>
    </div>
</div>

<?php if ($message): ?>
<div class="alert alert-<?= $message_type ?>">
    <i class="fas fa-<?= $message_type === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
    <?= htmlspecialchars($message) ?>
</div>
<?php endif; ?>

<div class="tab-nav">
    <button class="tab-btn active" onclick="showTab('staff-list')"><i class="fas fa-list"></i> Staff List</button>
    <button class="tab-btn" onclick="showTab('add-staff')"><i class="fas fa-user-plus"></i> Add Staff</button>
    <button class="tab-btn" onclick="showTab('audit-log')"><i class="fas fa-history"></i> Audit Log</button>
</div>

<div id="staff-list" class="tab-content active">
    <?php if ($edit_staff): ?>
    <div class="edit-form">
        <h3><i class="fas fa-edit"></i> Edit Staff: <?= htmlspecialchars($edit_staff['name']) ?></h3>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <input type="hidden" name="staff_id" value="<?= $edit_staff['id'] ?>">
            <div class="form-row">
                <div class="form-group">
                    <label><i class="fas fa-user"></i> Full Name *</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($edit_staff['name']) ?>" required class="form-control">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-at"></i> Username *</label>
                    <input type="text" name="username" value="<?= htmlspecialchars($edit_staff['username']) ?>" required class="form-control">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label><i class="fas fa-envelope"></i> Email *</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($edit_staff['email']) ?>" required>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-key"></i> Custom Permissions</label>
                    <?php 
                        $perms = json_decode($edit_staff['permissions'] ?? '[]', true) ?: []; 
                        $modules = ['leads' => 'Manage Leads', 'reviews' => 'Manage Reviews', 'projects' => 'Manage Projects', 'testimonials' => 'Manage Testimonials', 'files' => 'Manage Files', 'settings' => 'System Settings'];
                    ?>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 8px;">
                        <?php foreach($modules as $key => $label): ?>
                        <div class="checkbox-group">
                            <input type="checkbox" name="permissions[]" value="<?= $key ?>" id="edit_perm_<?= $key ?>" <?= in_array($key, $perms) ? 'checked' : '' ?>>
                            <label for="edit_perm_<?= $key ?>" style="margin-bottom:0; font-weight:500; font-size:13px;"><?= $label ?></label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <div class="checkbox-group">
                    <input type="checkbox" name="is_active" id="is_active_edit" <?= $edit_staff['is_active'] ? 'checked' : '' ?>>
                    <label for="is_active_edit">Active Account</label>
                </div>
            </div>
            <div style="display: flex; gap: 12px;">
                <button type="submit" name="update_staff" class="btn btn-success"><i class="fas fa-save"></i> Update Staff</button>
                <a href="staff" class="btn btn-edit"><i class="fas fa-times"></i> Cancel</a>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <?php if ($password_staff): ?>
    <div class="edit-form">
        <h3><i class="fas fa-key"></i> Change Password: <?= htmlspecialchars($password_staff['name']) ?></h3>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <input type="hidden" name="staff_id" value="<?= $password_staff['id'] ?>">
            <div class="form-row">
                <div class="form-group">
                    <label><i class="fas fa-lock"></i> New Password *</label>
                    <input type="password" name="new_password" required placeholder="Enter new password">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-lock"></i> Confirm Password *</label>
                    <input type="password" name="confirm_password" required placeholder="Confirm new password">
                </div>
            </div>
            <div style="display: flex; gap: 12px;">
                <button type="submit" name="change_password" class="btn btn-success"><i class="fas fa-key"></i> Change Password</button>
                <a href="staff" class="btn btn-edit"><i class="fas fa-times"></i> Cancel</a>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <div class="settings-card">
        <h3><i class="fas fa-users"></i> All Staff Members <span class="badge"><?= count($staff_list) ?></span></h3>
        <?php if (empty($staff_list)): ?>
            <p style="color: var(--text-muted); text-align: center; padding: 50px;">
                <i class="fas fa-users" style="font-size: 48px; opacity: 0.3; display: block; margin-bottom: 16px;"></i>
                No staff members found. Add your first staff member.
            </p>
        <?php else: ?>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Staff</th><th>Email</th><th>Permissions</th><th>Status</th><th>Joined</th><th>Last Login</th><th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($staff_list as $staff): ?>
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 14px;">
                                    <div class="staff-avatar"><?= strtoupper(substr($staff['username'], 0, 1)) ?></div>
                                    <div>
                                        <strong style="color: var(--text-primary);"><?= htmlspecialchars($staff['name']) ?></strong><br>
                                        <span style="color: var(--text-muted); font-size: 12px;">@<?= htmlspecialchars($staff['username']) ?></span>
                                    </div>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($staff['email']) ?></td>
                            <td>
                                <?php 
                                $p = json_decode($staff['permissions'] ?? '[]', true) ?: []; 
                                if(empty($p)) echo '<span style="color:var(--text-muted);font-size:12px;">None</span>';
                                else {
                                    echo '<div style="display:flex; flex-wrap:wrap; gap:4px;">';
                                    foreach($p as $m) echo '<span class="role-badge" style="padding:3px 6px; font-size:10px;">'.ucfirst($m).'</span>';
                                    echo '</div>';
                                }
                                ?>
                            </td>
                            <td><span class="<?= $staff['is_active'] ? 'status-active' : 'status-inactive' ?>"><?= $staff['is_active'] ? 'Active' : 'Inactive' ?></span></td>
                            <td><?= date('M d, Y', strtotime($staff['created_at'])) ?></td>
                            <td><?= $staff['last_login'] ? date('M d, Y H:i', strtotime($staff['last_login'])) : '<span style="color:var(--text-muted);">Never</span>' ?></td>
                            <td>
                                <div class="action-btns">
                                    <a href="?edit=<?= $staff['id'] ?>" class="btn btn-sm btn-edit" title="Edit"><i class="fas fa-edit"></i></a>
                                    <a href="?password=<?= $staff['id'] ?>" class="btn btn-sm" style="background: linear-gradient(135deg, #f59e0b, #d97706);" title="Change Password"><i class="fas fa-key"></i></a>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                        <input type="hidden" name="staff_id" value="<?= $staff['id'] ?>">
                                        <button type="submit" name="toggle_status" class="btn btn-sm btn-primary" title="Toggle Status">
                                            <i class="fas fa-toggle-on"></i>
                                        </button>
                                    </form>
                                    <?php if ($staff['id'] != $_SESSION['admin_id']): ?>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this staff member?')">
                                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                        <input type="hidden" name="staff_id" value="<?= $staff['id'] ?>">
                                        <button type="submit" name="delete_staff" class="btn btn-sm btn-delete" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<div id="add-staff" class="tab-content">
    <div class="settings-card">
        <h3><i class="fas fa-user-plus"></i> Create New Staff Member</h3>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <div class="form-row">
                <div class="form-group">
                    <label><i class="fas fa-user"></i> Full Name *</label>
                    <input type="text" name="name" required placeholder="Enter full name">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-at"></i> Username *</label>
                    <input type="text" name="username" required placeholder="Enter username">
                    <span class="hint">Unique username for login</span>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label><i class="fas fa-envelope"></i> Email *</label>
                    <input type="email" name="email" required placeholder="Enter email address">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-lock"></i> Password *</label>
                    <input type="password" name="password" required placeholder="Enter password" minlength="6">
                    <span class="hint">Minimum 6 characters</span>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label><i class="fas fa-key"></i> Custom Permissions</label>
                    <?php 
                        $modules = ['leads' => 'Manage Leads', 'reviews' => 'Manage Reviews', 'projects' => 'Manage Projects', 'testimonials' => 'Manage Testimonials', 'files' => 'Manage Files', 'settings' => 'System Settings'];
                    ?>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 8px;">
                        <?php foreach($modules as $key => $label): ?>
                        <div class="checkbox-group">
                            <input type="checkbox" name="permissions[]" value="<?= $key ?>" id="new_perm_<?= $key ?>">
                            <label for="new_perm_<?= $key ?>" style="margin-bottom:0; font-weight:500; font-size:13px;"><?= $label ?></label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="form-group">
                    <label>&nbsp;</label>
                    <div class="checkbox-group" style="margin-top: 8px;">
                        <input type="checkbox" name="is_active" id="is_active_new" checked>
                        <label for="is_active_new">Active Account</label>
                    </div>
                </div>
            </div>
            <button type="submit" name="create_staff" class="btn btn-success btn-block"><i class="fas fa-plus"></i> Create Staff Member</button>
        </form>
    </div>

    <div class="settings-card">
        <h4><i class="fas fa-info-circle"></i> About Custom Permissions</h4>
        <p style="color: var(--text-muted); font-size: 13px; margin-top: 10px;">Select exactly which modules this staff member can access. If they try to access an unassigned module, they will be blocked from viewing it.</p>
    </div>
</div>

<div id="audit-log" class="tab-content">
    <div class="settings-card">
        <h3><i class="fas fa-history"></i> Audit Log (Last 50 Actions)</h3>
        <?php if (empty($admin_activities)): ?>
            <p style="color: var(--text-muted); text-align: center; padding: 50px;">
                <i class="fas fa-history" style="font-size: 48px; opacity: 0.3; display: block; margin-bottom: 16px;"></i>
                No audit logs found.
            </p>
        <?php else: ?>
            <table class="audit-table">
                <thead>
                    <tr>
                        <th>#</th><th>Admin</th><th>Action</th><th>Details</th><th>IP Address</th><th>Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($admin_activities as $i => $log): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= htmlspecialchars($log['admin_name'] ?? 'System') ?></td>
                        <td><span class="role-badge"><?= str_replace('_', ' ', $log['action']) ?></span></td>
                        <td><?= htmlspecialchars($log['details']) ?></td>
                        <td class="audit-ip"><?= htmlspecialchars($log['ip_address'] ?: '-') ?></td>
                        <td class="audit-time"><?= date('M d, H:i', strtotime($log['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<script>
function showTab(tabId) {
    document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    document.getElementById(tabId).classList.add('active');
    event.target.classList.add('active');
}
</script>

<?php require_once __DIR__ . '/../../../admin/includes/footer.php'; ?>