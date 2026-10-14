<?php
require_once __DIR__ . '/../../../../admin/includes/header.php';
?>

<style>
/* Modern SaaS Settings Theme Extensions */
.saas-header {
    margin-bottom: 24px;
}
.saas-header-title {
    font-size: 24px;
    font-weight: 700;
    letter-spacing: -0.03em;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 12px;
}
.saas-header-subtitle {
    margin-top: 4px;
    font-size: 14px;
    color: var(--text-secondary);
}

.saas-badge-role {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    background: var(--primary-soft);
    color: var(--primary);
    border: 1px solid rgba(79, 70, 229, 0.2);
    border-radius: var(--radius-pill);
    font-size: 12px;
    font-weight: 600;
    text-transform: capitalize;
}

.saas-alert {
    padding: 14px 18px;
    border-radius: var(--radius-lg);
    margin-bottom: 24px;
    font-size: 14px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 12px;
    animation: fadeIn 0.2s ease-out;
}
.saas-alert-success {
    background: var(--success-soft);
    color: var(--success-text);
    border: 1px solid rgba(34, 197, 94, 0.25);
}
.saas-alert-error {
    background: var(--danger-soft);
    color: var(--danger-text);
    border: 1px solid rgba(239, 68, 68, 0.25);
}

.saas-card {
    background: var(--bg-surface);
    border: 1px solid var(--border-light);
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow-xs);
    margin-bottom: 24px;
    overflow: hidden;
    transition: all var(--duration-base) var(--ease-out);
}
.saas-card:hover {
    box-shadow: var(--shadow-sm);
    border-color: var(--border-medium);
}
.saas-card-header {
    padding: 20px 24px;
    border-bottom: 1px solid var(--border-light);
    background: var(--bg-surface-secondary);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
}
.saas-card-header-info h2 {
    font-size: 16px;
    font-weight: 600;
    color: var(--text-primary);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
    letter-spacing: -0.01em;
}
.saas-card-header-info h2 i {
    color: var(--primary);
    font-size: 16px;
}
.saas-card-header-info p {
    margin: 3px 0 0 0;
    font-size: 13px;
    color: var(--text-secondary);
}
.saas-card-body {
    padding: 24px;
}
.saas-card-footer {
    padding: 16px 24px;
    background: var(--bg-surface-secondary);
    border-top: 1px solid var(--border-light);
    display: flex;
    align-items: center;
    justify-content: flex-end;
}

/* User Header Banner */
.profile-hero {
    display: flex;
    align-items: center;
    gap: 20px;
    padding-bottom: 24px;
    border-bottom: 1px solid var(--border-light);
    margin-bottom: 24px;
}
.profile-hero-avatar {
    width: 64px;
    height: 64px;
    border-radius: var(--radius-xl);
    background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 26px;
    font-weight: 700;
    box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);
    flex-shrink: 0;
}
.profile-hero-details h3 {
    font-size: 18px;
    font-weight: 600;
    color: var(--text-primary);
    margin: 0 0 4px 0;
}
.profile-hero-details p {
    margin: 0;
    font-size: 13px;
    color: var(--text-secondary);
    display: flex;
    align-items: center;
    gap: 8px;
}

/* Form Controls */
.saas-form-group {
    margin-bottom: 20px;
}
.saas-label {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 8px;
    color: var(--text-primary);
    font-weight: 500;
    font-size: 13.5px;
}
.saas-label-text {
    display: flex;
    align-items: center;
    gap: 8px;
}
.saas-label-text i {
    color: var(--text-tertiary);
    font-size: 13px;
}
.saas-input-wrap {
    position: relative;
    display: flex;
    align-items: center;
}
.saas-input {
    width: 100%;
    padding: 11px 14px;
    border: 1px solid var(--border-light);
    border-radius: var(--radius-md);
    font-size: 14px;
    font-family: inherit;
    background: var(--bg-surface);
    color: var(--text-primary);
    transition: all 180ms ease-in-out;
}
.saas-input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px var(--primary-soft);
}
.saas-input:disabled {
    background: var(--bg-surface-secondary);
    color: var(--text-tertiary);
    cursor: not-allowed;
}

.saas-grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}
@media (max-width: 768px) {
    .saas-grid-2 { grid-template-columns: 1fr; }
    .profile-hero { flex-direction: column; text-align: center; }
    .profile-hero-details p { justify-content: center; }
}

/* Password Hint Box */
.password-requirements {
    background: var(--bg-surface-secondary);
    border: 1px solid var(--border-light);
    border-radius: var(--radius-md);
    padding: 14px 16px;
    margin-top: 16px;
}
.password-requirements-title {
    font-size: 12px;
    font-weight: 600;
    color: var(--text-secondary);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 8px;
}
.password-requirements-list {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
    list-style: none;
    margin: 0;
    padding: 0;
}
@media (max-width: 600px) {
    .password-requirements-list { grid-template-columns: 1fr; }
}
.password-requirements-list li {
    font-size: 12.5px;
    color: var(--text-secondary);
    display: flex;
    align-items: center;
    gap: 6px;
}
.password-requirements-list li i {
    color: var(--success);
    font-size: 11px;
}

/* Info Tiles */
.info-tiles-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 16px;
}
.info-tile {
    background: var(--bg-surface-secondary);
    border: 1px solid var(--border-light);
    border-radius: var(--radius-lg);
    padding: 16px 20px;
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.info-tile-label {
    font-size: 12px;
    color: var(--text-secondary);
    text-transform: uppercase;
    letter-spacing: 0.04em;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 6px;
}
.info-tile-value {
    font-size: 15px;
    font-weight: 600;
    color: var(--text-primary);
}
</style>

<div class="saas-header">
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="saas-header-title">
                <i class="fas fa-user-cog" aria-hidden="true" style="color: var(--primary);"></i>
                Admin Profile Settings
            </h1>
            <p class="saas-header-subtitle">Manage your personal account credentials, communication address, and security policies.</p>
        </div>
        <div>
            <span class="saas-badge-role">
                <i class="fas fa-shield-alt" aria-hidden="true"></i>
                <?= htmlspecialchars(ucfirst($admin['role'] ?? 'Administrator')) ?>
            </span>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../../../admin/includes/settings-nav.php'; ?>

<?php if (!empty($message)): ?>
    <div class="saas-alert saas-alert-<?= $message_type === 'success' ? 'success' : 'error' ?>" role="alert">
        <i class="fas fa-<?= $message_type === 'success' ? 'check-circle' : 'exclamation-circle' ?>" aria-hidden="true"></i>
        <span><?= htmlspecialchars($message) ?></span>
    </div>
<?php endif; ?>

<!-- 1. Profile Settings Card -->
<div class="saas-card">
    <div class="saas-card-header">
        <div class="saas-card-header-info">
            <h2><i class="fas fa-id-card" aria-hidden="true"></i> Profile Details</h2>
            <p>Update your public username and verified contact email.</p>
        </div>
    </div>
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
        <input type="hidden" name="update_profile" value="1">
        <div class="saas-card-body">
            <div class="profile-hero">
                <div class="profile-hero-avatar" aria-hidden="true">
                    <?= strtoupper(substr($admin['username'] ?? 'A', 0, 1)) ?>
                </div>
                <div class="profile-hero-details">
                    <h3><?= htmlspecialchars($admin['username'] ?? 'Admin') ?></h3>
                    <p><i class="fas fa-envelope" aria-hidden="true"></i> <?= htmlspecialchars($admin['email'] ?? 'No email configured') ?></p>
                </div>
            </div>

            <div class="saas-grid-2">
                <div class="saas-form-group">
                    <label class="saas-label" for="profile_username">
                        <span class="saas-label-text"><i class="fas fa-user" aria-hidden="true"></i> Username</span>
                    </label>
                    <input type="text" id="profile_username" name="username" class="saas-input" value="<?= htmlspecialchars($admin['username']) ?>" placeholder="e.g. administrator" required>
                </div>
                <div class="saas-form-group">
                    <label class="saas-label" for="profile_email">
                        <span class="saas-label-text"><i class="fas fa-envelope" aria-hidden="true"></i> Email Address</span>
                    </label>
                    <input type="email" id="profile_email" name="email" class="saas-input" value="<?= htmlspecialchars($admin['email']) ?>" placeholder="admin@domain.com" required>
                </div>
            </div>
        </div>
        <div class="saas-card-footer">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save" aria-hidden="true"></i> Save Profile Changes
            </button>
        </div>
    </form>
</div>

<!-- 2. Security & Password Card -->
<div class="saas-card">
    <div class="saas-card-header">
        <div class="saas-card-header-info">
            <h2><i class="fas fa-shield-halved" aria-hidden="true"></i> Password & Authentication</h2>
            <p>Ensure your account remains safe using a strong, unique password.</p>
        </div>
    </div>
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
        <input type="hidden" name="change_password" value="1">
        <div class="saas-card-body">
            <div class="saas-form-group">
                <label class="saas-label" for="current_password">
                    <span class="saas-label-text"><i class="fas fa-key" aria-hidden="true"></i> Current Password</span>
                </label>
                <input type="password" id="current_password" name="current_password" class="saas-input" placeholder="••••••••••••" required autocomplete="current-password">
            </div>
            
            <div class="saas-grid-2">
                <div class="saas-form-group">
                    <label class="saas-label" for="new_password">
                        <span class="saas-label-text"><i class="fas fa-lock" aria-hidden="true"></i> New Password</span>
                    </label>
                    <input type="password" id="new_password" name="new_password" class="saas-input" placeholder="••••••••••••" required autocomplete="new-password">
                </div>
                <div class="saas-form-group">
                    <label class="saas-label" for="confirm_password">
                        <span class="saas-label-text"><i class="fas fa-lock" aria-hidden="true"></i> Confirm New Password</span>
                    </label>
                    <input type="password" id="confirm_password" name="confirm_password" class="saas-input" placeholder="••••••••••••" required autocomplete="new-password">
                </div>
            </div>

            <div class="password-requirements">
                <div class="password-requirements-title">Password Security Policy</div>
                <ul class="password-requirements-list">
                    <li><i class="fas fa-check-circle" aria-hidden="true"></i> Minimum 8 characters long</li>
                    <li><i class="fas fa-check-circle" aria-hidden="true"></i> At least 1 uppercase & 1 lowercase letter</li>
                    <li><i class="fas fa-check-circle" aria-hidden="true"></i> At least 1 numerical digit (0-9)</li>
                    <li><i class="fas fa-check-circle" aria-hidden="true"></i> At least 1 special character (!@#$%^&*)</li>
                </ul>
            </div>
        </div>
        <div class="saas-card-footer">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-key" aria-hidden="true"></i> Update Password
            </button>
        </div>
    </form>
</div>

<!-- 3. Account Metadata -->
<div class="saas-card">
    <div class="saas-card-header">
        <div class="saas-card-header-info">
            <h2><i class="fas fa-circle-info" aria-hidden="true"></i> Account Information</h2>
            <p>System attributes and persistent identity parameters.</p>
        </div>
    </div>
    <div class="saas-card-body">
        <div class="info-tiles-grid">
            <div class="info-tile">
                <div class="info-tile-label"><i class="fas fa-hashtag" aria-hidden="true"></i> Account ID</div>
                <div class="info-tile-value">#<?= htmlspecialchars($admin['id']) ?></div>
            </div>
            <div class="info-tile">
                <div class="info-tile-label"><i class="fas fa-user-shield" aria-hidden="true"></i> Privilege Level</div>
                <div class="info-tile-value"><?= ucfirst(htmlspecialchars($admin['role'] ?? 'admin')) ?></div>
            </div>
            <div class="info-tile">
                <div class="info-tile-label"><i class="fas fa-calendar-check" aria-hidden="true"></i> Member Since</div>
                <div class="info-tile-value"><?= date('M d, Y', strtotime($admin['created_at'])) ?></div>
            </div>
            <div class="info-tile">
                <div class="info-tile-label"><i class="fas fa-clock" aria-hidden="true"></i> Account Status</div>
                <div class="info-tile-value" style="color: var(--success);"><i class="fas fa-circle" style="font-size: 8px; vertical-align: middle; margin-right: 4px;"></i> Active</div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../../../admin/includes/footer.php'; ?>