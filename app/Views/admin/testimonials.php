<?php
require_once __DIR__ . '/../../../admin/includes/header.php';

/** @var array $testimonials */
/** @var string $csrf_token */
/** @var string $message */
/** @var string $message_type */

$testimonials = $testimonials ?? [];
$csrf_token = $csrf_token ?? '';
$message = $message ?? '';
$message_type = $message_type ?? '';
?>

<style>
.testimonial-msg { max-width: 320px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: var(--text-muted); font-size: 13px; }
.person-name { font-weight: 600; color: var(--text-main); font-size: 14px; }
.person-designation { font-size: 12px; color: var(--text-muted); margin-top: 2px; }
.avatar-img { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 1px solid var(--border-color); }
.avatar-placeholder { width: 40px; height: 40px; border-radius: 50%; background: color-mix(in srgb, var(--primary) 15%, transparent); display: flex; align-items: center; justify-content: center; color: var(--primary); font-weight: 600; font-size: 15px; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
@media (max-width: 992px) { .form-row { grid-template-columns: 1fr; gap: 16px; } }

/* ── BULK ACTION BAR ── */
.bulk-bar {
  display: none;
  position: sticky;
  top: 0;
  z-index: 50;
  background: var(--bg-card, #FFFFFF);
  border: 1px solid var(--primary, #6366f1);
  border-radius: 12px;
  padding: 12px 20px;
  margin-bottom: 16px;
  align-items: center;
  gap: 12px;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
  animation: slideDown 0.2s ease;
}
.bulk-bar.active {
  display: flex;
}
@keyframes slideDown {
  from { opacity: 0; transform: translateY(-8px); }
  to { opacity: 1; transform: translateY(0); }
}
.bulk-bar-count {
  font-size: 14px;
  font-weight: 600;
  color: var(--primary, #6366f1);
  white-space: nowrap;
}
.bulk-bar-divider {
  width: 1px;
  height: 24px;
  background: var(--border-color, #e2e8f0);
}
.bulk-bar .form-control {
  min-width: 150px;
  padding: 6px 12px;
  font-size: 13px;
  margin-bottom: 0;
}
.bulk-bar .btn {
  white-space: nowrap;
}
.testimonial-checkbox {
  width: 16px;
  height: 16px;
  accent-color: var(--primary, #6366f1);
  cursor: pointer;
}
</style>

<div class="saas-page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-quote-left" style="color: var(--primary);"></i> Testimonials Management</h1>
        <p class="page-subtitle">Publish and manage client success testimonials and verified quotes.</p>
    </div>
    <div class="header-actions">
        <button class="btn btn-primary" onclick="openModal()"><i class="fas fa-plus"></i> Add Testimonial</button>
    </div>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?= $message_type ?>">
        <i class="fas fa-<?= $message_type === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i> 
        <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

<!-- Bulk Action Bar -->
<div class="bulk-bar" id="bulkBar">
  <span class="bulk-bar-count"><span id="bulkCount">0</span> selected</span>
  <div class="bulk-bar-divider"></div>
  <form method="POST" id="bulkStatusForm" style="display:flex; align-items:center; gap: 8px;">
    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
    <input type="hidden" name="action" value="bulk_update_status">
    <div id="bulkStatusIds"></div>
    <select name="status" class="form-control" required style="margin-bottom: 0;">
      <option value="" disabled selected>Change Status To…</option>
      <option value="active">Active (Published)</option>
      <option value="inactive">Inactive (Hidden)</option>
    </select>
    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-sync-alt"></i> Apply</button>
  </form>
  <div class="bulk-bar-divider"></div>
  <form method="POST" id="bulkDeleteForm" onsubmit="return confirm('Permanently delete selected testimonials?')" style="display:flex; align-items:center;">
    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
    <input type="hidden" name="action" value="bulk_delete">
    <div id="bulkDeleteIds"></div>
    <button type="submit" class="btn btn-sm" style="background: #ef4444; color: #fff; border: 1px solid #dc2626;"><i class="fas fa-trash"></i> Delete Selected</button>
  </form>
  <div style="margin-left: auto;">
    <button type="button" class="btn btn-secondary btn-sm" onclick="clearSelection()"><i class="fas fa-times"></i> Clear</button>
  </div>
</div>

<div class="content-card">
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th style="width: 36px;"><input type="checkbox" class="testimonial-checkbox" id="selectAllCb" onclick="toggleSelectAll(this)"></th>
                    <th style="width: 60px;">ID</th>
                    <th>Person</th>
                    <th>Message</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($testimonials as $t): ?>
                <tr>
                    <td><input type="checkbox" class="testimonial-checkbox row-cb" value="<?= $t['id'] ?>" onclick="updateBulkBar()"></td>
                    <td style="color: var(--text-muted);">#<?= $t['id'] ?></td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <?php 
                            $admin_avatar = '';
                            if (!empty($t['image'])) {
                                $admin_avatar = (str_starts_with($t['image'], 'http://') || str_starts_with($t['image'], 'https://') || str_starts_with($t['image'], '/')) ? $t['image'] : '../' . $t['image'];
                            }
                            ?>
                            <?php if ($admin_avatar): ?>
                                <img src="<?= htmlspecialchars($admin_avatar) ?>" alt="Avatar" class="avatar-img">
                            <?php else: ?>
                                <div class="avatar-placeholder"><?= strtoupper(substr($t['name'], 0, 1)) ?></div>
                            <?php endif; ?>
                            <div>
                                <div class="person-name"><?= htmlspecialchars($t['name']) ?></div>
                                <div class="person-designation"><?= htmlspecialchars($t['designation']) ?></div>
                            </div>
                        </div>
                    </td>
                    <td class="testimonial-msg" title="<?= htmlspecialchars($t['message']) ?>"><?= htmlspecialchars($t['message']) ?></td>
                    <td><span class="status status-<?= $t['status'] ?>"><?= ucfirst($t['status']) ?></span></td>
                    <td style="color: var(--text-muted); font-size: 13px;"><?= date('M d, Y', strtotime($t['created_at'])) ?></td>
                    <td style="text-align: right;">
                        <div style="display: flex; gap: 8px; justify-content: flex-end;">
                            <button class="btn btn-sm" onclick="editTestimonial(<?= htmlspecialchars(json_encode($t)) ?>)" style="background: var(--bg-main); color: var(--text-main); border: 1px solid var(--border-color);" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                <input type="hidden" name="action" value="toggle">
                                <input type="hidden" name="id" value="<?= $t['id'] ?>">
                                <button type="submit" class="btn btn-sm" style="background: var(--bg-main); color: var(--text-main); border: 1px solid var(--border-color);" title="Toggle Visibility">
                                    <i class="fas fa-<?= $t['status'] === 'active' ? 'eye-slash' : 'eye' ?>"></i>
                                </button>
                            </form>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this testimonial permanently?')">
                                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $t['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-delete" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                
                <?php if (empty($testimonials)): ?>
                <tr>
                    <td colspan="7" style="text-align:center; padding:80px 20px; background: var(--bg-card); border-bottom: none;">
                        <div style="width: 64px; height: 64px; background: var(--bg-main); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                            <i class="fas fa-quote-left" style="font-size: 24px; color: var(--text-muted);"></i>
                        </div>
                        <h3 style="font-size: 18px; color: var(--text-main); margin-bottom: 8px;">No testimonials found</h3>
                        <p style="color: var(--text-muted); font-size: 15px; margin-bottom: 24px;">Add customer testimonials to build trust.</p>
                        <button class="btn btn-primary" onclick="openModal()"><i class="fas fa-plus"></i> Add First Testimonial</button>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal" id="testModal">
    <div class="modal-content" style="max-width: 650px;">
        <div class="modal-header">
            <h3 class="modal-title"><i class="fas fa-quote-left"></i> <span id="modalTitle">Add New Testimonial</span></h3>
            <button type="button" class="modal-close" onclick="closeModal()" aria-label="Close modal">&times;</button>
        </div>
        <form method="POST" enctype="multipart/form-data">
            <div class="modal-body">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <input type="hidden" name="action" id="formAction" value="create">
                <input type="hidden" name="id" id="testId">
                <input type="hidden" name="existing_image" id="testExistingImage">

                <div class="form-row">
                    <div class="form-group">
                        <label for="testName"><i class="fas fa-user"></i> Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="testName" class="form-control" placeholder="e.g. Ananya Sharma" required>
                    </div>
                    <div class="form-group">
                        <label for="testDesignation"><i class="fas fa-briefcase"></i> Designation / Role</label>
                        <input type="text" name="designation" id="testDesignation" class="form-control" placeholder="e.g. Head of Marketing, D2C Apparel">
                    </div>
                </div>

                <div class="form-group">
                    <label for="testMessage"><i class="fas fa-comment"></i> Message <span class="text-danger">*</span></label>
                    <textarea name="message" id="testMessage" class="form-control" rows="4" placeholder="Client feedback or testimonial statement..." required></textarea>
                </div>

                <!-- Avatar Upload & Quick Selection Section -->
                <div class="form-group" style="background: var(--bg-main); border: 1px solid var(--border-color); border-radius: 12px; padding: 16px; margin-bottom: 20px;">
                    <label style="font-weight: 600; font-size: 13px; color: var(--text-main); margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                        <i class="fas fa-camera" style="color: var(--primary);"></i> Client Photo / Avatar
                    </label>

                    <div style="display: flex; gap: 20px; align-items: center; margin-bottom: 14px;">
                        <!-- Avatar Preview -->
                        <div style="position: relative; width: 68px; height: 68px; flex-shrink: 0;">
                            <img id="testAvatarPreview" src="" alt="Avatar Preview" style="width: 68px; height: 68px; border-radius: 50%; object-fit: cover; border: 2px solid var(--primary); display: none; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                            <div id="testAvatarPlaceholder" style="width: 68px; height: 68px; border-radius: 50%; background: color-mix(in srgb, var(--primary) 15%, transparent); display: flex; align-items: center; justify-content: center; color: var(--primary); font-size: 24px; border: 2px dashed var(--border-color);">
                                <i class="fas fa-user"></i>
                            </div>
                        </div>

                        <!-- Upload Trigger & Info -->
                        <div style="flex: 1;">
                            <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                                <button type="button" class="btn btn-sm" style="background: var(--bg-card); border: 1px solid var(--border-color); color: var(--text-main);" onclick="document.getElementById('testImageFile').click()">
                                    <i class="fas fa-upload" style="color: var(--primary);"></i> Upload from Device
                                </button>
                                <button type="button" class="btn btn-sm" style="background: var(--primary-soft, rgba(91,53,232,0.08)); border: 1px solid var(--primary); color: var(--primary); font-weight: 600;" onclick="openMediaPickerForTestimonial()">
                                    <i class="fas fa-images"></i> Browse Media Library
                                </button>
                                <button type="button" id="testAvatarClearBtn" class="btn btn-sm" style="background: transparent; color: var(--danger); border: none; display: none;" onclick="clearTestimonialAvatar()">
                                    <i class="fas fa-times"></i> Remove
                                </button>
                            </div>
                            <input type="file" name="image_file" id="testImageFile" accept=".jpg,.jpeg,.png,.gif,.webp" style="display: none;" onchange="previewTestimonialAvatar(this)">
                            <div style="font-size: 11px; color: var(--text-muted); margin-top: 6px;">Supported: JPG, PNG, WEBP, GIF (Max 5MB)</div>
                        </div>
                    </div>

                    <?php if (!empty($sample_avatars)): ?>
                    <!-- Quick Select Preset Avatars -->
                    <div style="margin-top: 12px; border-top: 1px dashed var(--border-color); padding-top: 10px;">
                        <div style="font-size: 11px; font-weight: 600; color: var(--text-muted); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;">Or select from existing avatars:</div>
                        <div style="display: flex; gap: 10px; flex-wrap: wrap;" id="avatarPresetsList">
                            <?php foreach ($sample_avatars as $sa): ?>
                            <div class="avatar-preset-chip" style="cursor: pointer; position: relative; border-radius: 50%; padding: 2px; border: 2px solid transparent; transition: all 0.2s ease;" onclick="selectAvatarPreset('<?= htmlspecialchars($sa) ?>', this)" title="<?= htmlspecialchars(basename($sa)) ?>">
                                <img src="<?= (str_starts_with($sa, 'http') ? $sa : '../' . $sa) ?>" alt="Avatar" style="width: 36px; height: 36px; border-radius: 50%; object-fit: cover; display: block;">
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Direct URL input -->
                    <div style="margin-top: 12px;">
                        <input type="text" name="image" id="testImage" class="form-control" style="font-size: 12px; padding: 6px 12px;" placeholder="Or paste image URL (https://... or uploads/testimonials/...)" oninput="previewTestimonialUrl(this.value)">
                    </div>
                </div>

                <div class="form-group">
                    <label for="testStatus"><i class="fas fa-toggle-on"></i> Status</label>
                    <select name="status" id="testStatus" class="form-control">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal()"><i class="fas fa-times"></i> Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Testimonial</button>
            </div>
        </form>
    </div>
</div>

<script>
function previewTestimonialAvatar(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = document.getElementById('testAvatarPreview');
            const placeholder = document.getElementById('testAvatarPlaceholder');
            const clearBtn = document.getElementById('testAvatarClearBtn');
            img.src = e.target.result;
            img.style.display = 'block';
            placeholder.style.display = 'none';
            clearBtn.style.display = 'inline-flex';
            document.querySelectorAll('.avatar-preset-chip').forEach(c => c.style.borderColor = 'transparent');
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function previewTestimonialUrl(url) {
    const img = document.getElementById('testAvatarPreview');
    const placeholder = document.getElementById('testAvatarPlaceholder');
    const clearBtn = document.getElementById('testAvatarClearBtn');
    if (url && url.trim()) {
        let finalSrc = url.trim();
        if (!finalSrc.startsWith('http://') && !finalSrc.startsWith('https://') && !finalSrc.startsWith('/')) {
            finalSrc = '../' + finalSrc;
        }
        img.src = finalSrc;
        img.style.display = 'block';
        placeholder.style.display = 'none';
        clearBtn.style.display = 'inline-flex';
    } else if (!document.getElementById('testImageFile').value) {
        img.style.display = 'none';
        placeholder.style.display = 'flex';
        clearBtn.style.display = 'none';
    }
}

function selectAvatarPreset(path, el) {
    document.getElementById('testImageFile').value = '';
    document.getElementById('testImage').value = path;
    document.getElementById('testExistingImage').value = path;
    previewTestimonialUrl(path);
    document.querySelectorAll('.avatar-preset-chip').forEach(c => c.style.borderColor = 'transparent');
    if (el) el.style.borderColor = 'var(--primary)';
}

function clearTestimonialAvatar() {
    document.getElementById('testImageFile').value = '';
    document.getElementById('testImage').value = '';
    document.getElementById('testExistingImage').value = '';
    const img = document.getElementById('testAvatarPreview');
    img.src = '';
    img.style.display = 'none';
    document.getElementById('testAvatarPlaceholder').style.display = 'flex';
    document.getElementById('testAvatarClearBtn').style.display = 'none';
    document.querySelectorAll('.avatar-preset-chip').forEach(c => c.style.borderColor = 'transparent');
}

function openModal() {
    document.getElementById('modalTitle').textContent = 'Add New Testimonial';
    document.getElementById('formAction').value = 'create';
    document.getElementById('testId').value = '';
    document.getElementById('testName').value = '';
    document.getElementById('testDesignation').value = '';
    document.getElementById('testMessage').value = '';
    document.getElementById('testStatus').value = 'active';
    clearTestimonialAvatar();
    document.getElementById('testModal').classList.add('active');
}

function editTestimonial(t) {
    document.getElementById('modalTitle').textContent = 'Edit Testimonial';
    document.getElementById('formAction').value = 'update';
    document.getElementById('testId').value = t.id;
    document.getElementById('testName').value = t.name;
    document.getElementById('testDesignation').value = t.designation || '';
    document.getElementById('testMessage').value = t.message;
    document.getElementById('testImageFile').value = '';
    document.getElementById('testExistingImage').value = t.image || '';
    document.getElementById('testImage').value = t.image || '';
    document.getElementById('testStatus').value = t.status;
    if (t.image) {
        previewTestimonialUrl(t.image);
        // Highlight preset chip if matched
        document.querySelectorAll('.avatar-preset-chip').forEach(c => {
            if (c.getAttribute('title') === t.image.split('/').pop()) {
                c.style.borderColor = 'var(--primary)';
            } else {
                c.style.borderColor = 'transparent';
            }
        });
    } else {
        clearTestimonialAvatar();
    }
    document.getElementById('testModal').classList.add('active');
}

function closeModal() {
    document.getElementById('testModal').classList.remove('active');
}
window.onclick = function(e) {
    if (e.target === document.getElementById('testModal')) closeModal();
}

function openMediaPickerForTestimonial() {
    openUniversalMediaPicker({
        folder: 'testimonials',
        targetInput: '#testImage',
        targetPreview: '#testAvatarPreview',
        onSelect: function(file) {
            document.getElementById('testImageFile').value = '';
            document.getElementById('testExistingImage').value = file.relative_path;
            document.getElementById('testAvatarPlaceholder').style.display = 'none';
            document.getElementById('testAvatarClearBtn').style.display = 'inline-flex';
            document.querySelectorAll('.avatar-preset-chip').forEach(c => c.style.borderColor = 'transparent');
        }
    });
}

// ── BULK ACTION FUNCTIONS ──
function getSelectedIds() {
  return Array.from(document.querySelectorAll('.row-cb:checked')).map(cb => cb.value);
}

function updateBulkBar() {
  const ids = getSelectedIds();
  const bar = document.getElementById('bulkBar');
  const count = document.getElementById('bulkCount');
  const selectAll = document.getElementById('selectAllCb');
  const allCbs = document.querySelectorAll('.row-cb');

  count.textContent = ids.length;

  if (ids.length > 0) {
    bar.classList.add('active');
  } else {
    bar.classList.remove('active');
  }

  // Update select-all state
  if (allCbs.length > 0 && ids.length === allCbs.length) {
    selectAll.checked = true;
    selectAll.indeterminate = false;
  } else if (ids.length > 0) {
    selectAll.checked = false;
    selectAll.indeterminate = true;
  } else {
    selectAll.checked = false;
    selectAll.indeterminate = false;
  }
}

function toggleSelectAll(masterCb) {
  document.querySelectorAll('.row-cb').forEach(cb => {
    cb.checked = masterCb.checked;
  });
  updateBulkBar();
}

function clearSelection() {
  document.querySelectorAll('.row-cb').forEach(cb => cb.checked = false);
  const selectAll = document.getElementById('selectAllCb');
  if (selectAll) {
    selectAll.checked = false;
    selectAll.indeterminate = false;
  }
  updateBulkBar();
}

// Inject selected IDs into bulk forms before submission
document.getElementById('bulkStatusForm').addEventListener('submit', function(e) {
  const ids = getSelectedIds();
  if (ids.length === 0) { e.preventDefault(); alert('No testimonials selected.'); return; }
  document.getElementById('bulkStatusIds').innerHTML = '';
  ids.forEach(id => {
    const inp = document.createElement('input');
    inp.type = 'hidden'; inp.name = 'bulk_ids[]'; inp.value = id;
    document.getElementById('bulkStatusIds').appendChild(inp);
  });
});

document.getElementById('bulkDeleteForm').addEventListener('submit', function(e) {
  const ids = getSelectedIds();
  if (ids.length === 0) { e.preventDefault(); alert('No testimonials selected.'); return; }
  document.getElementById('bulkDeleteIds').innerHTML = '';
  ids.forEach(id => {
    const inp = document.createElement('input');
    inp.type = 'hidden'; inp.name = 'bulk_ids[]'; inp.value = id;
    document.getElementById('bulkDeleteIds').appendChild(inp);
  });
});
</script>

<?php require_once __DIR__ . '/../../../admin/includes/media-picker-modal.php'; ?>
<?php require_once __DIR__ . '/../../../admin/includes/footer.php'; ?>
