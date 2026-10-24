<?php
require_once __DIR__ . '/../../../admin/includes/header.php';
$csrf_token = generateCSRFToken();
?>

<style>
.profile-container { max-width: 850px; margin: 0 auto; }
.profile-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--border-radius); padding: 32px; box-shadow: var(--shadow-sm); margin-bottom: 24px; }
.profile-card h3 { font-size: 18px; color: var(--text-main); margin-bottom: 24px; display: flex; align-items: center; gap: 12px; border-bottom: 1px solid var(--border-color); padding-bottom: 16px; }
.profile-card h3 i { color: var(--primary); }

/* Avatar Management Section */
.avatar-section { text-align: center; margin-bottom: 32px; }
.avatar-preview-wrapper { display: inline-block; position: relative; }
.avatar-preview { 
    width: 128px; height: 128px; border-radius: 50%; 
    border: 4px solid var(--bg-main, #FFFFFF); margin: 0 auto 12px; 
    overflow: hidden; display: flex; align-items: center; justify-content: center; 
    background: color-mix(in srgb, var(--primary, #2563EB) 15%, transparent); 
    box-shadow: 0 4px 18px rgba(37, 99, 235, 0.16); 
    position: relative;
}
.avatar-preview img { width: 100%; height: 100%; object-fit: cover; display: block; border-radius: 50%; }
.avatar-preview .initials { color: var(--primary, #2563EB); font-size: 46px; font-weight: 700; line-height: 1; }

.avatar-loading-overlay {
    position: absolute; inset: 0; background: rgba(15, 23, 42, 0.7);
    color: #FFFFFF; display: flex; flex-direction: column; align-items: center; justify-content: center;
    border-radius: 50%; z-index: 10; font-size: 24px;
}

.avatar-badge-status {
    margin-bottom: 14px; font-size: 13px; font-weight: 600;
}
.avatar-actions-bar {
    display: flex; gap: 10px; justify-content: center; align-items: center;
    margin-bottom: 12px; flex-wrap: wrap;
}
.avatar-guidelines {
    font-size: 12px; color: var(--text-muted, #64748B); max-width: 480px; margin: 0 auto;
}

/* Instant Preview Box */
.avatar-preview-box {
    max-width: 420px; margin: 16px auto; background: #F8FAFC;
    border: 1px solid #CBD5E1; border-radius: 12px; padding: 16px; text-align: left;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}
.preview-box-header {
    font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em;
    color: #475569; margin-bottom: 10px; display: flex; align-items: center; gap: 6px;
}
.preview-box-body {
    display: flex; gap: 14px; align-items: center; margin-bottom: 14px;
}
.preview-thumbnail-img {
    width: 60px; height: 60px; border-radius: 50%; object-fit: cover;
    border: 2px solid #2563EB; flex-shrink: 0;
}
.preview-box-info { flex: 1; min-width: 0; }
.preview-filename { font-size: 13px; font-weight: 600; color: #1E293B; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.preview-filesize { font-size: 12px; color: #64748B; margin-top: 2px; }
.preview-box-actions { display: flex; gap: 8px; justify-content: flex-end; }

/* Custom Modal */
.custom-modal-backdrop {
    position: fixed; inset: 0; background: rgba(15, 23, 42, 0.55);
    z-index: 9999; display: flex; align-items: center; justify-content: center;
    padding: 16px; backdrop-filter: blur(2px);
}
.custom-modal-dialog {
    background: #FFFFFF; border-radius: 16px; width: 100%; max-width: 440px;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2); overflow: hidden;
    animation: modalFadeIn 0.2s ease-out;
}
@keyframes modalFadeIn {
    from { opacity: 0; transform: scale(0.95); }
    to { opacity: 1; transform: scale(1); }
}
.custom-modal-header {
    background: #FEF2F2; border-bottom: 1px solid #FEE2E2; padding: 18px 24px;
    display: flex; align-items: center; justify-content: space-between;
}
.custom-modal-header h4 {
    margin: 0; font-size: 16px; font-weight: 700; color: #991B1B;
    display: flex; align-items: center; gap: 8px;
}
.custom-modal-body {
    padding: 24px; font-size: 14px; color: #334155; line-height: 1.5;
}
.custom-modal-footer {
    background: #F8FAFC; border-top: 1px solid #E2E8F0; padding: 16px 24px;
    display: flex; justify-content: flex-end; gap: 10px;
}

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

        <?php
        $user_avatar_url = getAdminAvatarUrl($current_user['avatar'] ?? '', '');
        $has_custom_avatar = !empty($user_avatar_url);
        ?>

        <div class="avatar-section">
            <div class="avatar-preview-wrapper">
                <div class="avatar-preview" id="mainAvatarPreview">
                    <?php if ($has_custom_avatar): ?>
                        <img src="<?= htmlspecialchars($user_avatar_url, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($current_user['name'] ?? 'Admin', ENT_QUOTES, 'UTF-8') ?>" id="currentAvatarImg">
                    <?php else: ?>
                        <span class="initials" id="currentAvatarInitials"><?= strtoupper(substr($current_user['username'] ?? 'A', 0, 1)) ?></span>
                    <?php endif; ?>
                    <div class="avatar-loading-overlay" id="avatarLoadingOverlay" style="display:none;">
                        <i class="fas fa-spinner fa-spin"></i>
                    </div>
                </div>

                <div class="avatar-badge-status" id="avatarBadgeStatus">
                    <?= $has_custom_avatar ? '<span style="color:var(--success, #16A34A);"><i class="fas fa-check-circle"></i> Custom Profile Photo</span>' : '<span style="color:var(--text-muted, #64748B);"><i class="fas fa-user"></i> Default Avatar</span>' ?>
                </div>

                <!-- Action Controls -->
                <div class="avatar-actions-bar">
                    <button type="button" class="btn btn-sm" id="btnSelectNewPhoto" style="background:#2563EB; color:#fff; padding: 8px 16px; border-radius: 8px; font-weight:600;" onclick="triggerPhotoSelection()">
                        <i class="fas fa-camera"></i> Change Photo
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger" id="btnOpenRemoveModal" style="padding: 8px 16px; border-radius: 8px; font-weight:600; <?= !$has_custom_avatar ? 'display:none;' : '' ?>" onclick="openRemovePhotoModal()">
                        <i class="fas fa-trash-alt"></i> Remove Photo
                    </button>
                </div>

                <!-- Instant preview panel (appears upon selecting a file) -->
                <div class="avatar-preview-box" id="avatarPreviewBox" style="display:none;">
                    <div class="preview-box-header">
                        <i class="fas fa-image"></i> <span>Selected Photo Preview</span>
                    </div>
                    <div class="preview-box-body">
                        <img id="clientPreviewThumbnail" src="" alt="Preview" class="preview-thumbnail-img">
                        <div class="preview-box-info">
                            <div class="preview-filename" id="previewFileName">filename.jpg</div>
                            <div class="preview-filesize" id="previewFileSize">0 KB</div>
                            <div style="font-size: 11px; color: #16A34A; margin-top: 3px;"><i class="fas fa-check"></i> Valid format ready for upload</div>
                        </div>
                    </div>
                    <div class="preview-box-actions">
                        <button type="button" class="btn btn-sm btn-success" id="btnUploadPreviewNow" onclick="uploadSelectedPhoto()" style="padding: 6px 14px; font-size: 13px;">
                            <i class="fas fa-upload"></i> Upload & Save
                        </button>
                        <button type="button" class="btn btn-sm btn-secondary" onclick="cancelPhotoPreview()" style="padding: 6px 14px; font-size: 13px;">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                    </div>
                </div>

                <div class="avatar-guidelines">
                    <i class="fas fa-info-circle"></i> Supported formats: <strong>JPG, JPEG, PNG, WebP</strong> • Max upload size: <strong>2 MB</strong>
                </div>

                <div id="avatarAjaxAlert" style="display:none; max-width: 480px; margin: 14px auto 0;"></div>
            </div>
        </div>

        <form method="POST" enctype="multipart/form-data" id="profileEditForm">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>" id="profileCsrfToken">
            <input type="hidden" name="avatar" id="avatarHiddenField" value="<?= htmlspecialchars($current_user['avatar'] ?? '') ?>">
            <input type="file" id="profilePhotoFileInput" name="avatar_file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" style="display:none;" onchange="onPhotoSelected(this)">

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

            <button type="submit" name="update_profile" class="btn btn-success" style="padding: 14px 32px;">
                <i class="fas fa-save"></i> Save Profile Details
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
                        <span class="hint">Minimum 8 characters with upper, lower, number, and symbol</span>
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

<!-- Confirmation Modal for Removing Profile Photo -->
<div class="custom-modal-backdrop" id="confirmRemovePhotoModal" style="display:none;" onclick="if(event.target===this) closeRemovePhotoModal()">
    <div class="custom-modal-dialog" role="dialog" aria-labelledby="removeModalTitle" aria-modal="true">
        <div class="custom-modal-header">
            <h4 id="removeModalTitle"><i class="fas fa-exclamation-triangle"></i> Remove Profile Photo</h4>
            <button type="button" onclick="closeRemovePhotoModal()" style="background:none; border:none; font-size:22px; color:#991B1B; cursor:pointer;" aria-label="Close">&times;</button>
        </div>
        <div class="custom-modal-body">
            <p style="margin:0 0 10px 0; font-weight: 500;">Are you sure you want to remove your profile photo?</p>
            <p style="margin:0; font-size:13px; color:#64748B;">This action will immediately delete your uploaded photo from the server and restore your default avatar.</p>
        </div>
        <div class="custom-modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeRemovePhotoModal()" id="btnCancelRemove">Keep Photo</button>
            <button type="button" class="btn btn-danger" id="btnConfirmPhotoRemoval" onclick="confirmPhotoRemoval()">
                <i class="fas fa-trash-alt"></i> Yes, Remove Photo
            </button>
        </div>
    </div>
</div>

<script>
const CSRF_TOKEN = <?= json_encode($csrf_token) ?>;
const MAX_UPLOAD_BYTES = 2 * 1024 * 1024; // 2 MB
const DEFAULT_INITIAL = <?= json_encode(strtoupper(substr($current_user['username'] ?? 'A', 0, 1))) ?>;
let pendingFile = null;

function triggerPhotoSelection() {
    document.getElementById('profilePhotoFileInput').click();
}

function onPhotoSelected(input) {
    clearAvatarAlert();
    if (!input.files || !input.files[0]) {
        return;
    }

    const file = input.files[0];

    // 1. Validate file size (max 2 MB)
    if (file.size > MAX_UPLOAD_BYTES) {
        showAvatarAlert('File exceeds maximum upload size (max 2 MB). Selected size: ' + (file.size / (1024 * 1024)).toFixed(2) + ' MB.', 'danger');
        input.value = '';
        return;
    }

    // 2. Validate file type
    const validTypes = ['image/jpeg', 'image/png', 'image/webp'];
    const ext = file.name.split('.').pop().toLowerCase();
    const validExts = ['jpg', 'jpeg', 'png', 'webp'];

    if (!validTypes.includes(file.type) && !validExts.includes(ext)) {
        showAvatarAlert('Invalid image format. Only JPG, JPEG, PNG, and WebP are allowed.', 'danger');
        input.value = '';
        return;
    }

    pendingFile = file;

    // 3. Client-side image preview via FileReader
    const reader = new FileReader();
    reader.onload = function(e) {
        document.getElementById('clientPreviewThumbnail').src = e.target.result;
        document.getElementById('previewFileName').textContent = file.name;
        document.getElementById('previewFileSize').textContent = formatBytes(file.size);
        document.getElementById('avatarPreviewBox').style.display = 'block';
    };
    reader.readAsDataURL(file);
}

function cancelPhotoPreview() {
    pendingFile = null;
    document.getElementById('profilePhotoFileInput').value = '';
    document.getElementById('avatarPreviewBox').style.display = 'none';
    clearAvatarAlert();
}

function uploadSelectedPhoto() {
    if (!pendingFile) {
        showAvatarAlert('Please select an image file first.', 'warning');
        return;
    }

    const overlay = document.getElementById('avatarLoadingOverlay');
    const uploadBtn = document.getElementById('btnUploadPreviewNow');
    overlay.style.display = 'flex';
    uploadBtn.disabled = true;
    uploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';

    const formData = new FormData();
    formData.append('action', 'upload');
    formData.append('csrf_token', CSRF_TOKEN);
    formData.append('avatar', pendingFile);
    formData.append('avatar_file', pendingFile);

    fetch('api/profile-photo.php?action=upload', {
        method: 'POST',
        headers: {
            'X-CSRF-Token': CSRF_TOKEN
        },
        body: formData
    })
    .then(response => response.json().then(data => ({ status: response.status, body: data })))
    .then(({ status, body }) => {
        overlay.style.display = 'none';
        uploadBtn.disabled = false;
        uploadBtn.innerHTML = '<i class="fas fa-upload"></i> Upload & Save';

        if (status === 200 && body.success) {
            // Success: update main avatar preview
            const newUrl = body.avatar_url + (body.avatar_url.includes('?') ? '&' : '?') + 't=' + Date.now();
            updateAvatarElements(newUrl, true);
            document.getElementById('avatarHiddenField').value = body.avatar_path || '';
            cancelPhotoPreview();
            showAvatarAlert(body.message || 'Profile photo updated successfully!', 'success');
        } else {
            showAvatarAlert(body.message || 'Failed to upload profile photo.', 'danger');
        }
    })
    .catch(err => {
        overlay.style.display = 'none';
        uploadBtn.disabled = false;
        uploadBtn.innerHTML = '<i class="fas fa-upload"></i> Upload & Save';
        showAvatarAlert('Network or server error occurred during upload. Please try again.', 'danger');
    });
}

function openRemovePhotoModal() {
    clearAvatarAlert();
    document.getElementById('confirmRemovePhotoModal').style.display = 'flex';
}

function closeRemovePhotoModal() {
    document.getElementById('confirmRemovePhotoModal').style.display = 'none';
}

function confirmPhotoRemoval() {
    const confirmBtn = document.getElementById('btnConfirmPhotoRemoval');
    const cancelBtn = document.getElementById('btnCancelRemove');
    confirmBtn.disabled = true;
    cancelBtn.disabled = true;
    confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Removing...';

    const formData = new FormData();
    formData.append('action', 'remove');
    formData.append('csrf_token', CSRF_TOKEN);

    fetch('api/profile-photo.php?action=remove', {
        method: 'POST',
        headers: {
            'X-CSRF-Token': CSRF_TOKEN
        },
        body: formData
    })
    .then(response => response.json().then(data => ({ status: response.status, body: data })))
    .then(({ status, body }) => {
        confirmBtn.disabled = false;
        cancelBtn.disabled = false;
        confirmBtn.innerHTML = '<i class="fas fa-trash-alt"></i> Yes, Remove Photo';
        closeRemovePhotoModal();

        if (status === 200 && body.success) {
            updateAvatarElements(null, false, body.default_initial || DEFAULT_INITIAL);
            document.getElementById('avatarHiddenField').value = '';
            showAvatarAlert(body.message || 'Profile photo removed successfully.', 'success');
        } else {
            showAvatarAlert(body.message || 'Failed to remove profile photo.', 'danger');
        }
    })
    .catch(err => {
        confirmBtn.disabled = false;
        cancelBtn.disabled = false;
        confirmBtn.innerHTML = '<i class="fas fa-trash-alt"></i> Yes, Remove Photo';
        closeRemovePhotoModal();
        showAvatarAlert('Network error occurred while removing photo. Please try again.', 'danger');
    });
}

function updateAvatarElements(newUrl, isCustom, initialChar) {
    const previewContainer = document.getElementById('mainAvatarPreview');
    const badgeStatus = document.getElementById('avatarBadgeStatus');
    const removeBtn = document.getElementById('btnOpenRemoveModal');
    const headerAvatar = document.getElementById('headerProfileAvatar');

    if (isCustom && newUrl) {
        // Main page avatar
        previewContainer.innerHTML = '<img src="' + escapeHtml(newUrl) + '" alt="Avatar" id="currentAvatarImg">' +
            '<div class="avatar-loading-overlay" id="avatarLoadingOverlay" style="display:none;"><i class="fas fa-spinner fa-spin"></i></div>';
        badgeStatus.innerHTML = '<span style="color:var(--success, #16A34A);"><i class="fas fa-check-circle"></i> Custom Profile Photo</span>';
        removeBtn.style.display = 'inline-flex';

        // Synchronize topbar header avatar in real-time
        if (headerAvatar) {
            headerAvatar.innerHTML = '<img src="' + escapeHtml(newUrl) + '" alt="Avatar" class="profile-avatar-img">';
        }
    } else {
        const init = initialChar || DEFAULT_INITIAL;
        previewContainer.innerHTML = '<span class="initials" id="currentAvatarInitials">' + escapeHtml(init) + '</span>' +
            '<div class="avatar-loading-overlay" id="avatarLoadingOverlay" style="display:none;"><i class="fas fa-spinner fa-spin"></i></div>';
        badgeStatus.innerHTML = '<span style="color:var(--text-muted, #64748B);"><i class="fas fa-user"></i> Default Avatar</span>';
        removeBtn.style.display = 'none';

        // Synchronize topbar header avatar in real-time
        if (headerAvatar) {
            headerAvatar.innerHTML = '<span class="profile-avatar-initial">' + escapeHtml(init) + '</span>';
        }
    }
}

function showAvatarAlert(msg, type) {
    const box = document.getElementById('avatarAjaxAlert');
    const alertClass = type === 'success' ? 'alert-success' : (type === 'warning' ? 'alert-warning' : 'alert-error');
    const icon = type === 'success' ? 'fa-check-circle' : (type === 'warning' ? 'fa-exclamation-triangle' : 'fa-exclamation-circle');
    box.className = 'alert ' + alertClass;
    box.innerHTML = '<i class="fas ' + icon + '"></i> ' + escapeHtml(msg);
    box.style.display = 'block';
}

function clearAvatarAlert() {
    const box = document.getElementById('avatarAjaxAlert');
    if (box) {
        box.style.display = 'none';
        box.innerHTML = '';
    }
}

function formatBytes(bytes) {
    if (bytes >= 1048576) {
        return (bytes / 1048576).toFixed(2) + ' MB';
    }
    return Math.round(bytes / 1024) + ' KB';
}

function escapeHtml(str) {
    return String(str).replace(/[&<>"']/g, function(s) {
        return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[s];
    });
}

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeRemovePhotoModal();
    }
});
</script>

<?php require_once __DIR__ . '/../../../admin/includes/footer.php'; ?>