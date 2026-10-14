<?php
require_once __DIR__ . '/../../../admin/includes/header.php';
$csrf_token = generateCSRFToken();
?>

<style>
.profile-container { max-width: 850px; margin: 0 auto; }
.profile-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--border-radius); padding: 32px; box-shadow: var(--shadow-sm); margin-bottom: 24px; }
.profile-card h3 { font-size: 18px; color: var(--text-main); margin-bottom: 24px; display: flex; align-items: center; gap: 12px; border-bottom: 1px solid var(--border-color); padding-bottom: 16px; }
.profile-card h3 i { color: var(--primary); }

.avatar-section { text-align: center; margin-bottom: 32px; }
.avatar-preview { 
    width: 120px; height: 120px; border-radius: 50%; 
    border: 4px solid var(--bg-main); margin: 0 auto 16px; 
    overflow: hidden; display: flex; align-items: center; justify-content: center; 
    background: color-mix(in srgb, var(--primary) 15%, transparent); 
    box-shadow: var(--shadow-md); 
}
.avatar-preview img { width: 100%; height: 100%; object-fit: cover; }
.avatar-preview .initials { color: var(--primary); font-size: 44px; font-weight: 700; }

.form-group { margin-bottom: 20px; }
.form-group label { display: block; margin-bottom: 8px; color: var(--text-main); font-weight: 600; font-size: 14px; }
.form-group label i { color: var(--primary); margin-right: 8px; }
.form-group input { 
    width: 100%; padding: 14px 18px; 
    border: 1px solid var(--border-color); border-radius: var(--btn-radius); 
    font-size: 14px; background: var(--bg-main); color: var(--text-main); transition: all var(--transition); 
}
.form-group input:focus { outline: none; border-color: var(--primary); }
.form-group .hint { font-size: 12px; color: var(--text-muted); margin-top: 6px; display: block; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }

.password-section { background: var(--bg-main); padding: 28px; border-radius: var(--border-radius); margin-top: 32px; border: 1px solid var(--border-color); }
.password-section h4 { color: var(--warning); margin-bottom: 20px; display: flex; align-items: center; gap: 10px; font-size: 16px; }

.info-box { background: var(--bg-card); padding: 24px; border-radius: var(--border-radius); margin-bottom: 24px; border: 1px solid var(--border-color); display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; }
.info-box-item { display: flex; flex-direction: column; gap: 4px; }
.info-box-item strong { color: var(--text-muted); font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; }
.info-box-item span { color: var(--text-main); font-size: 15px; font-weight: 500; }

.role-badge { display: inline-block; padding: 6px 16px; background: color-mix(in srgb, var(--primary) 15%, transparent); color: var(--primary); border-radius: 20px; font-size: 12px; font-weight: 600; text-transform: uppercase; }
.security-card { background: var(--bg-main); padding: 28px; border-radius: var(--border-radius); border: 1px dashed var(--success); }
.security-card h3 { color: var(--success) !important; border-bottom-color: var(--success); }
.security-card h3 i { color: var(--success); }
.security-card ul { color: var(--text-muted); line-height: 2; padding-left: 20px; font-size: 14px; }
.security-card ul li::marker { color: var(--success); }
@media(max-width: 768px) { .form-row { grid-template-columns: 1fr; } }
</style>

<div class="saas-page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-user-circle" style="color: var(--primary);"></i> My Account Profile</h1>
        <p class="page-subtitle">Update personal credentials, WhatsApp notifications number, avatar, and password.</p>
    </div>
</div>

<div class="profile-container">
    <?php if ($message): ?>
    <div class="alert alert-<?= $message_type ?>">
        <i class="fas fa-<?= $message_type === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
        <?= htmlspecialchars($message) ?>
    </div>
    <?php endif; ?>

    <div class="info-box">
        <div class="info-box-item">
            <strong>Role</strong>
            <span><span class="role-badge"><?= ucfirst($current_user['role']) ?></span></span>
        </div>
        <div class="info-box-item">
            <strong>Status</strong>
            <span><?= $current_user['is_active'] ? '<span style="color:var(--success);">● Active</span>' : '<span style="color:var(--danger);">● Inactive</span>' ?></span>
        </div>
        <div class="info-box-item">
            <strong>Last Login</strong>
            <span><?= $current_user['last_login'] ? date('M d, Y H:i', strtotime($current_user['last_login'])) : 'First login' ?></span>
        </div>
        <div class="info-box-item">
            <strong>Member Since</strong>
            <span><?= date('M d, Y', strtotime($current_user['created_at'])) ?></span>
        </div>
    </div>

    <div class="profile-card">
        <h3><i class="fas fa-user-edit"></i> Edit My Profile</h3>

        <div class="avatar-section">
            <div class="avatar-preview">
                <?php if (!empty($current_user['avatar'])): ?>
                    <img src="<?= htmlspecialchars($current_user['avatar']) ?>" alt="Avatar">
                <?php else: ?>
                    <span class="initials"><?= strtoupper(substr($current_user['username'], 0, 1)) ?></span>
                <?php endif; ?>
            </div>
            <p style="color: var(--text-muted); font-size: 13px;">Your profile picture</p>
        </div>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <div class="form-row">
                <div class="form-group">
                    <label><i class="fas fa-user"></i> Full Name *</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($current_user['name']) ?>" required placeholder="Enter your full name" class="form-control">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-at"></i> Username *</label>
                    <input type="text" name="username" value="<?= htmlspecialchars($current_user['username']) ?>" required placeholder="Enter username" class="form-control">
                    <span class="hint">Letters, numbers, and underscores only</span>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label><i class="fas fa-envelope"></i> Email Address *</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($current_user['email']) ?>" required placeholder="Enter email" class="form-control">
                </div>
                <div class="form-group">
                    <label><i class="fab fa-whatsapp"></i> WhatsApp Number</label>
                    <input type="text" name="wa_number" value="<?= htmlspecialchars($current_user['wa_number'] ?? '') ?>" placeholder="+91 9876543210">
                </div>
            </div>

            <div class="form-group">
                <label><i class="fas fa-image"></i> Avatar URL</label>
                <input type="url" name="avatar" value="<?= htmlspecialchars($current_user['avatar'] ?? '') ?>" placeholder="https://example.com/avatar.jpg">
                <span class="hint">Enter URL of your profile picture (recommended: square image, min 200x200px)</span>
            </div>

            <button type="submit" name="update_profile" class="btn btn-success" style="padding: 14px 32px;">
                <i class="fas fa-save"></i> Save Profile
            </button>

            <div class="password-section">
                <h4><i class="fas fa-lock"></i> Change Password</h4>
                <div class="form-group">
                    <label><i class="fas fa-key"></i> Current Password</label>
                    <input type="password" name="current_password" placeholder="Enter current password" class="form-control">
                    <span class="hint">Required only if changing your password</span>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-lock"></i> New Password</label>
                        <input type="password" name="new_password" placeholder="Enter new password" class="form-control">
                        <span class="hint">Minimum 6 characters</span>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-lock"></i> Confirm New Password</label>
                        <input type="password" name="confirm_password" placeholder="Confirm new password">
                    </div>
                </div>
                <button type="submit" name="update_profile" class="btn" style="background: linear-gradient(135deg, #f59e0b, #d97706); padding: 14px 28px;">
                    <i class="fas fa-key"></i> Update Password
                </button>
            </div>
        </form>
    </div>

    <div class="profile-card security-card">
        <h3><i class="fas fa-shield-alt"></i> Security Tips</h3>
        <ul>
            <li>Use a strong password with at least 8 characters including letters, numbers, and symbols</li>
            <li>Never share your password with anyone</li>
            <li>Change your password periodically for better security</li>
            <li>Keep your email and WhatsApp number updated for account recovery</li>
            <li>Your password is encrypted using Bcrypt - industry standard security</li>
        </ul>
    </div>
</div>

<?php require_once __DIR__ . '/../../../admin/includes/footer.php'; ?>