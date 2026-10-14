<?php
require_once __DIR__ . '/../../../admin/includes/header.php';
?>

<style>
.star-rating { display: flex; gap: 4px; flex-direction: row-reverse; justify-content: flex-end; }
.star-rating input { display: none; }
.star-rating label { font-size: 28px; color: var(--border-color); cursor: pointer; transition: all var(--transition); }
.star-rating label:hover, .star-rating label:hover ~ label, .star-rating input:checked ~ label { color: #f59e0b; transform: scale(1.1); }
.stars { color: #f59e0b; font-size: 16px; letter-spacing: 2px; }
.review-msg { max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: var(--text-muted); font-size: 13px; }
.reviewer-name { font-weight: 600; color: var(--text-main); font-size: 14px; }
.status-badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 100px; font-size: 12px; font-weight: 600; text-transform: capitalize; }
.status-approved { background: rgba(16, 185, 129, 0.15); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.3); }
.status-pending { background: rgba(245, 158, 11, 0.15); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.3); }
.status-rejected { background: rgba(239, 68, 68, 0.15); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.3); }

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
  min-width: 160px;
  padding: 6px 12px;
  font-size: 13px;
}
.bulk-bar .btn {
  white-space: nowrap;
}
.review-checkbox {
  width: 16px;
  height: 16px;
  accent-color: var(--primary, #6366f1);
  cursor: pointer;
}
</style>

<div class="saas-page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-star" style="color: #f59e0b;"></i> Reviews Management</h1>
        <p class="page-subtitle">Moderate customer reviews, approve pending feedback, and manage testimonials shown on the website.</p>
    </div>
    <div class="header-actions">
        <button class="btn btn-primary" onclick="openModal()"><i class="fas fa-plus"></i> Add Review</button>
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
      <option value="approved">Approved (Live)</option>
      <option value="pending">Pending</option>
      <option value="rejected">Rejected</option>
    </select>
    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-sync-alt"></i> Apply</button>
  </form>
  <div class="bulk-bar-divider"></div>
  <form method="POST" id="bulkDeleteForm" onsubmit="return confirm('Permanently delete selected reviews?')" style="display:flex; align-items:center;">
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
                    <th style="width: 36px;"><input type="checkbox" class="review-checkbox" id="selectAllCb" onclick="toggleSelectAll(this)"></th>
                    <th style="width: 50px;">ID</th>
                    <th>Reviewer</th>
                    <th>Rating</th>
                    <th>Status</th>
                    <th>Message</th>
                    <th>Date</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reviews as $review): 
                    $rStatus = $review['status'] ?? 'approved';
                ?>
                <tr>
                    <td><input type="checkbox" class="review-checkbox row-cb" value="<?= $review['id'] ?>" onclick="updateBulkBar()"></td>
                    <td style="color: var(--text-muted);">#<?= $review['id'] ?></td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 36px; height: 36px; border-radius: 50%; background: color-mix(in srgb, var(--primary) 15%, transparent); display: flex; align-items: center; justify-content: center; color: var(--primary); font-weight: 600; font-size: 14px;">
                                <?= strtoupper(substr($review['name'] ?? '?', 0, 1)) ?>
                            </div>
                            <div>
                                <div class="reviewer-name"><?= htmlspecialchars($review['name']) ?></div>
                                <?php if (!empty($review['company']) || !empty($review['designation'])): ?>
                                    <div style="font-size: 11px; color: var(--text-muted);"><?= htmlspecialchars(trim(($review['designation'] ?? '') . ' ' . ($review['company'] ?? ''))) ?></div>
                                <?php endif; ?>
                                <?php if (!empty($review['email'])): ?>
                                    <div style="font-size: 12px; color: var(--text-muted);"><a href="mailto:<?= htmlspecialchars($review['email']) ?>" style="color: inherit; text-decoration: none;"><?= htmlspecialchars($review['email']) ?></a></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td><span class="stars"><?= str_repeat('★', $review['star_rating']) . str_repeat('☆', 5 - $review['star_rating']) ?></span></td>
                    <td>
                        <span class="status-badge status-<?= htmlspecialchars($rStatus) ?>">
                            <i class="fas fa-<?= $rStatus === 'approved' ? 'check' : ($rStatus === 'pending' ? 'clock' : 'ban') ?>"></i>
                            <?= htmlspecialchars($rStatus) ?>
                        </span>
                    </td>
                    <td class="review-msg" title="<?= htmlspecialchars($review['message']) ?>"><?= htmlspecialchars($review['message']) ?></td>
                    <td style="color: var(--text-muted); font-size: 13px;"><?= date('M d, Y', strtotime($review['created_at'])) ?></td>
                    <td style="text-align: right;">
                        <div style="display: flex; gap: 6px; justify-content: flex-end; align-items: center;">
                            <?php if ($rStatus !== 'approved'): ?>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                <input type="hidden" name="action" value="approve">
                                <input type="hidden" name="id" value="<?= $review['id'] ?>">
                                <button type="submit" class="btn btn-sm" style="background: rgba(16, 185, 129, 0.15); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.3);" title="Approve Review">
                                    <i class="fas fa-check"></i>
                                </button>
                            </form>
                            <?php endif; ?>

                            <?php if ($rStatus !== 'rejected'): ?>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                <input type="hidden" name="action" value="reject">
                                <input type="hidden" name="id" value="<?= $review['id'] ?>">
                                <button type="submit" class="btn btn-sm" style="background: rgba(245, 158, 11, 0.15); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.3);" title="Reject Review">
                                    <i class="fas fa-ban"></i>
                                </button>
                            </form>
                            <?php endif; ?>

                            <button class="btn btn-sm" onclick="editReview(<?= htmlspecialchars(json_encode($review)) ?>)" style="background: var(--bg-main); color: var(--text-main); border: 1px solid var(--border-color);" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this review permanently?')">
                                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $review['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-delete" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                
                <?php if (empty($reviews)): ?>
                <tr>
                    <td colspan="8" style="text-align:center; padding:80px 20px; background: var(--bg-card); border-bottom: none;">
                        <div style="width: 64px; height: 64px; background: var(--bg-main); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                            <i class="fas fa-star" style="font-size: 24px; color: var(--text-muted);"></i>
                        </div>
                        <h3 style="font-size: 18px; color: var(--text-main); margin-bottom: 8px;">No reviews found</h3>
                        <p style="color: var(--text-muted); font-size: 15px; margin-bottom: 24px;">Start adding customer testimonials to build trust.</p>
                        <button class="btn btn-primary" onclick="openModal()"><i class="fas fa-plus"></i> Add First Review</button>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal" id="reviewModal">
    <div class="modal-content" style="max-width: 540px;">
        <div class="modal-header">
            <h3 class="modal-title"><i class="fas fa-star"></i> <span id="modalTitle">Add New Review</span></h3>
            <button type="button" class="modal-close" onclick="closeModal()" aria-label="Close modal">&times;</button>
        </div>
        <form method="POST">
            <div class="modal-body">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <input type="hidden" name="action" id="formAction" value="create">
                <input type="hidden" name="id" id="reviewId">
                <div class="form-group">
                    <label for="reviewName"><i class="fas fa-user"></i> Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="reviewName" class="form-control" placeholder="e.g. Rahul Verma" required>
                </div>
                <div class="form-group">
                    <label for="reviewEmail"><i class="fas fa-envelope"></i> Email Address</label>
                    <input type="email" name="email" id="reviewEmail" class="form-control" placeholder="e.g. rahul@example.com">
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div class="form-group">
                        <label for="reviewCompany"><i class="fas fa-building"></i> Company</label>
                        <input type="text" name="company" id="reviewCompany" class="form-control" placeholder="e.g. Nexus Tech">
                    </div>
                    <div class="form-group">
                        <label for="reviewStatus"><i class="fas fa-shield-alt"></i> Moderation Status</label>
                        <select name="status" id="reviewStatus" class="form-control">
                            <option value="approved">Approved</option>
                            <option value="pending">Pending</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-star"></i> Star Rating <span class="text-danger">*</span></label>
                    <div class="star-rating">
                        <input type="radio" name="star_rating" id="star5" value="5"><label for="star5">★</label>
                        <input type="radio" name="star_rating" id="star4" value="4"><label for="star4">★</label>
                        <input type="radio" name="star_rating" id="star3" value="3"><label for="star3">★</label>
                        <input type="radio" name="star_rating" id="star2" value="2"><label for="star2">★</label>
                        <input type="radio" name="star_rating" id="star1" value="1"><label for="star1">★</label>
                    </div>
                </div>
                <div class="form-group">
                    <label for="reviewMessage"><i class="fas fa-comment"></i> Review Message <span class="text-danger">*</span></label>
                    <textarea name="message" id="reviewMessage" class="form-control" rows="4" placeholder="Client review comments..." required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal()"><i class="fas fa-times"></i> Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Review</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal() { 
    document.getElementById('modalTitle').textContent = 'Add New Review'; 
    document.getElementById('formAction').value = 'create'; 
    document.getElementById('reviewId').value = ''; 
    document.getElementById('reviewName').value = ''; 
    document.getElementById('reviewEmail').value = ''; 
    document.getElementById('reviewCompany').value = '';
    document.getElementById('reviewStatus').value = 'approved';
    document.getElementById('reviewMessage').value = ''; 
    document.querySelectorAll('.star-rating input').forEach(i => i.checked = false); 
    document.getElementById('reviewModal').classList.add('active'); 
}
function editReview(r) { 
    document.getElementById('modalTitle').textContent = 'Edit Review'; 
    document.getElementById('formAction').value = 'update'; 
    document.getElementById('reviewId').value = r.id; 
    document.getElementById('reviewName').value = r.name; 
    document.getElementById('reviewEmail').value = r.email || ''; 
    document.getElementById('reviewCompany').value = r.company || '';
    document.getElementById('reviewStatus').value = r.status || 'approved';
    document.getElementById('reviewMessage').value = r.message; 
    const ratingInput = document.querySelector(`input[name="star_rating"][value="${r.star_rating}"]`);
    if (ratingInput) ratingInput.checked = true; 
    document.getElementById('reviewModal').classList.add('active'); 
}
function closeModal() { document.getElementById('reviewModal').classList.remove('active'); }
window.onclick = function(e) { if (e.target === document.getElementById('reviewModal')) closeModal(); }

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
  if (ids.length === 0) { e.preventDefault(); alert('No reviews selected.'); return; }
  document.getElementById('bulkStatusIds').innerHTML = '';
  ids.forEach(id => {
    const inp = document.createElement('input');
    inp.type = 'hidden'; inp.name = 'bulk_ids[]'; inp.value = id;
    document.getElementById('bulkStatusIds').appendChild(inp);
  });
});

document.getElementById('bulkDeleteForm').addEventListener('submit', function(e) {
  const ids = getSelectedIds();
  if (ids.length === 0) { e.preventDefault(); alert('No reviews selected.'); return; }
  document.getElementById('bulkDeleteIds').innerHTML = '';
  ids.forEach(id => {
    const inp = document.createElement('input');
    inp.type = 'hidden'; inp.name = 'bulk_ids[]'; inp.value = id;
    document.getElementById('bulkDeleteIds').appendChild(inp);
  });
});
</script>

<?php require_once __DIR__ . '/../../../admin/includes/footer.php'; ?>