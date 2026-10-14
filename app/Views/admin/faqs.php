<?php
require_once __DIR__ . '/../../../admin/includes/header.php';

/** @var array $faqs */
/** @var int $total_faqs */
/** @var int $active_faqs */
/** @var int $inactive_faqs */
/** @var string $csrf_token */
/** @var string $message */
/** @var string $message_type */

$faqs = $faqs ?? [];
$total_faqs = $total_faqs ?? 0;
$active_faqs = $active_faqs ?? 0;
$inactive_faqs = $inactive_faqs ?? 0;
$csrf_token = $csrf_token ?? '';
$message = $message ?? '';
$message_type = $message_type ?? '';
?>

<style>
/* Stat Cards */
.faq-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}
.faq-stat-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius);
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 16px;
    transition: all var(--transition);
}
.faq-stat-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-sm);
}
.faq-stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
}
.faq-stat-val {
    font-size: 24px;
    font-weight: 700;
    color: var(--text-main);
    line-height: 1.2;
}
.faq-stat-label {
    font-size: 13px;
    color: var(--text-muted);
    margin-top: 2px;
}

/* Controls & Filter Bar */
.faq-controls-bar {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius);
    padding: 16px 20px;
    margin-bottom: 20px;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
}
.faq-search-box {
    position: relative;
    flex: 1;
    min-width: 260px;
    max-width: 420px;
}
.faq-search-box i {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-muted);
    font-size: 14px;
}
.faq-search-input {
    width: 100%;
    padding: 10px 14px 10px 38px;
    background: var(--bg-main);
    border: 1px solid var(--border-color);
    border-radius: var(--btn-radius);
    color: var(--text-main);
    font-size: 14px;
    outline: none;
    transition: all var(--transition);
}
.faq-search-input:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px color-mix(in srgb, var(--primary) 15%, transparent);
}
.filter-tabs {
    display: flex;
    gap: 6px;
    background: var(--bg-main);
    padding: 4px;
    border-radius: var(--btn-radius);
    border: 1px solid var(--border-color);
}
.filter-tab {
    padding: 6px 14px;
    font-size: 13px;
    font-weight: 500;
    border-radius: calc(var(--btn-radius) - 2px);
    cursor: pointer;
    color: var(--text-muted);
    transition: all var(--transition);
    border: none;
    background: transparent;
}
.filter-tab.active {
    background: var(--bg-card);
    color: var(--primary);
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    font-weight: 600;
}
.bulk-bar {
    position: fixed;
    bottom: 0;
    left: 260px;
    right: 0;
    background: var(--bg-card);
    border-top: 1px solid var(--border-color);
    padding: 16px 32px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 -4px 12px rgba(0,0,0,0.05);
    transform: translateY(100%);
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    z-index: 100;
}
.bulk-bar.visible {
    transform: translateY(0);
}
.bulk-actions {
    display: flex;
    gap: 12px;
    align-items: center;
}
.item-checkbox {
    margin-right: 12px;
    display: flex;
    align-items: center;
}
.item-checkbox input[type="checkbox"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
    accent-color: var(--primary);
}

/* FAQ Accordion List Cards */
.faq-list-container {
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.faq-admin-item {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius);
    transition: all var(--transition);
    overflow: hidden;
}
.faq-admin-item:hover {
    border-color: color-mix(in srgb, var(--primary) 40%, transparent);
    box-shadow: var(--shadow-sm);
}
.faq-admin-item.status-inactive {
    opacity: 0.72;
    background: color-mix(in srgb, var(--bg-card) 95%, var(--text-muted) 5%);
}
.faq-admin-header {
    padding: 18px 22px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    cursor: pointer;
    user-select: none;
}
.faq-header-left {
    display: flex;
    align-items: center;
    gap: 14px;
    flex: 1;
    min-width: 0;
}
.faq-order-badge {
    background: color-mix(in srgb, var(--primary) 12%, transparent);
    color: var(--primary);
    font-weight: 700;
    font-size: 12px;
    padding: 4px 10px;
    border-radius: 8px;
    flex-shrink: 0;
}
.faq-question-title {
    font-size: 15px;
    font-weight: 600;
    color: var(--text-main);
    line-height: 1.4;
    word-break: break-word;
}
.faq-header-right {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-shrink: 0;
}
.faq-status-pill {
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}
.faq-status-pill.active {
    background: color-mix(in srgb, var(--success, #10b981) 15%, transparent);
    color: var(--success, #10b981);
    border: 1px solid color-mix(in srgb, var(--success, #10b981) 30%, transparent);
}
.faq-status-pill.inactive {
    background: color-mix(in srgb, var(--text-muted) 15%, transparent);
    color: var(--text-muted);
    border: 1px solid color-mix(in srgb, var(--text-muted) 30%, transparent);
}
.faq-expand-icon {
    color: var(--text-muted);
    font-size: 14px;
    transition: transform var(--transition);
}
.faq-admin-item.open .faq-expand-icon {
    transform: rotate(180deg);
    color: var(--primary);
}

/* FAQ Expandable Body */
.faq-admin-body {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border-top: 1px solid transparent;
}
.faq-admin-item.open .faq-admin-body {
    max-height: 500px;
    border-top-color: var(--border-color);
}
.faq-body-inner {
    padding: 20px 22px;
    background: color-mix(in srgb, var(--bg-main) 60%, var(--bg-card) 40%);
}
.faq-answer-text {
    font-size: 14px;
    line-height: 1.7;
    color: var(--text-muted);
    white-space: pre-line;
    margin-bottom: 18px;
}
.faq-admin-actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 16px;
    border-top: 1px solid var(--border-color);
    flex-wrap: wrap;
    gap: 10px;
}
.faq-meta-info {
    font-size: 12px;
    color: var(--text-muted);
}
.faq-action-buttons {
    display: flex;
    gap: 8px;
}

/* Empty State */
.faq-empty-state {
    text-align: center;
    padding: 60px 20px;
    background: var(--bg-card);
    border: 1px dashed var(--border-color);
    border-radius: var(--border-radius);
    color: var(--text-muted);
}
.faq-empty-icon {
    font-size: 54px;
    color: var(--primary);
    opacity: 0.35;
    margin-bottom: 16px;
}

/* Modal Enhancements */
.modal-form-group {
    margin-bottom: 18px;
}
.modal-form-group label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: var(--text-main);
    margin-bottom: 6px;
}
.modal-form-group input,
.modal-form-group textarea,
.modal-form-group select {
    width: 100%;
    padding: 10px 14px;
    background: var(--bg-main);
    border: 1px solid var(--border-color);
    border-radius: var(--btn-radius);
    color: var(--text-main);
    font-size: 14px;
    outline: none;
    font-family: inherit;
    transition: all var(--transition);
}
.modal-form-group textarea {
    min-height: 120px;
    resize: vertical;
    line-height: 1.6;
}
.modal-form-group input:focus,
.modal-form-group textarea:focus,
.modal-form-group select:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px color-mix(in srgb, var(--primary) 15%, transparent);
}
.form-grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}
@media (max-width: 600px) {
    .form-grid-2 { grid-template-columns: 1fr; }
}
.char-count {
    text-align: right;
    font-size: 11px;
    color: var(--text-muted);
    margin-top: 4px;
}
</style>

<div class="saas-page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-question-circle" style="color: var(--primary);"></i> Frequently Asked Questions (FAQ)</h1>
        <p class="page-subtitle">Manage, create, and organize frequently asked questions shown on the website and services pages.</p>
    </div>
    <div class="header-actions" style="display: flex; gap: 10px;">
        <?php if ($total_faqs === 0): ?>
        <form method="POST" style="display:inline;">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <input type="hidden" name="action" value="seed_defaults">
            <button type="submit" class="btn" style="background: var(--bg-card); color: var(--text-main); border: 1px solid var(--border-color);">
                <i class="fas fa-magic"></i> Load Starter FAQs
            </button>
        </form>
        <?php endif; ?>
        <button class="btn btn-primary" onclick="openFaqModal()"><i class="fas fa-plus"></i> Add FAQ</button>
    </div>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?= $message_type === 'success' ? 'success' : 'error' ?>" style="margin-bottom: 20px;">
        <i class="fas fa-<?= $message_type === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
        <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

<!-- Quick Stats -->
<div class="faq-stats-grid">
    <div class="faq-stat-card">
        <div class="faq-stat-icon" style="background: color-mix(in srgb, var(--primary) 12%, transparent); color: var(--primary);">
            <i class="fas fa-comments-question-check"></i>
        </div>
        <div>
            <div class="faq-stat-val"><?= $total_faqs ?></div>
            <div class="faq-stat-label">Total Questions</div>
        </div>
    </div>
    <div class="faq-stat-card">
        <div class="faq-stat-icon" style="background: color-mix(in srgb, var(--success, #10b981) 12%, transparent); color: var(--success, #10b981);">
            <i class="fas fa-check-circle"></i>
        </div>
        <div>
            <div class="faq-stat-val"><?= $active_faqs ?></div>
            <div class="faq-stat-label">Published / Active</div>
        </div>
    </div>
    <div class="faq-stat-card">
        <div class="faq-stat-icon" style="background: color-mix(in srgb, var(--text-muted) 12%, transparent); color: var(--text-muted);">
            <i class="fas fa-eye-slash"></i>
        </div>
        <div>
            <div class="faq-stat-val"><?= $inactive_faqs ?></div>
            <div class="faq-stat-label">Hidden / Draft</div>
        </div>
    </div>
</div>

<!-- Controls Bar -->
<div class="faq-controls-bar">
    <div class="faq-search-box">
        <i class="fas fa-search"></i>
        <input type="text" id="faqSearch" class="faq-search-input" placeholder="Search questions or answers..." oninput="filterFaqs()">
    </div>
    <div class="filter-tabs">
        <button class="filter-tab active" data-filter="all" onclick="setStatusFilter('all', this)">All (<?= $total_faqs ?>)</button>
        <button class="filter-tab" data-filter="active" onclick="setStatusFilter('active', this)">Active (<?= $active_faqs ?>)</button>
        <button class="filter-tab" data-filter="inactive" onclick="setStatusFilter('inactive', this)">Inactive (<?= $inactive_faqs ?>)</button>
    </div>
</div>

<!-- FAQ Accordion List -->
<form id="bulkForm" method="POST">
    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
    <input type="hidden" name="action" id="bulkAction" value="">

    <div style="margin-bottom: 16px; display: flex; align-items: center; gap: 12px; padding: 0 4px;">
        <input type="checkbox" id="selectAll" style="width: 16px; height: 16px; cursor: pointer; accent-color: var(--primary);">
        <label for="selectAll" style="font-size: 14px; font-weight: 600; color: var(--text-muted); cursor: pointer;">Select All</label>
    </div>

    <div class="faq-list-container" id="faqList">
        <?php if (empty($faqs)): ?>
            <div class="faq-empty-state">
                <i class="fas fa-question-circle faq-empty-icon"></i>
                <h3 style="color: var(--text-main); margin-bottom: 8px;">No FAQs Found</h3>
                <p style="margin-bottom: 20px;">You haven't created any FAQs yet. Click below to add your first question or load starter FAQs.</p>
                <div style="display: flex; justify-content: center; gap: 12px;">
                    <button type="button" class="btn btn-primary" onclick="openFaqModal()"><i class="fas fa-plus"></i> Create First FAQ</button>
                    <button type="button" class="btn" onclick="document.getElementById('seedForm').submit();" style="background: var(--bg-main); border: 1px solid var(--border-color); color: var(--text-main);">
                        <i class="fas fa-magic"></i> Load Starter FAQs
                    </button>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($faqs as $index => $f): ?>
                <div class="faq-admin-item <?= $index === 0 ? 'open' : '' ?> status-<?= htmlspecialchars($f['status']) ?>" 
                     data-status="<?= htmlspecialchars($f['status']) ?>"
                     data-question="<?= htmlspecialchars(strtolower($f['question'])) ?>"
                     data-answer="<?= htmlspecialchars(strtolower($f['answer'])) ?>">
                    
                    <div class="faq-admin-header" onclick="toggleFaqAccordion(this.parentElement)">
                        <div class="faq-header-left">
                            <div class="item-checkbox" onclick="event.stopPropagation();">
                                <input type="checkbox" name="item_ids[]" value="<?= $f['id'] ?>" class="item-cb">
                            </div>
                            <div class="faq-order-badge" title="Sort Order">#<?= htmlspecialchars($f['sort_order'] ?? ($index + 1)) ?></div>
                            <div class="faq-question-title"><?= htmlspecialchars($f['question']) ?></div>
                        </div>
                        <div class="faq-header-right">
                            <span class="faq-status-pill <?= $f['status'] === 'active' ? 'active' : 'inactive' ?>">
                                <?= ucfirst($f['status']) ?>
                            </span>
                            <i class="fas fa-chevron-down faq-expand-icon"></i>
                        </div>
                    </div>

                    <div class="faq-admin-body">
                        <div class="faq-body-inner">
                            <div class="faq-answer-text"><?= htmlspecialchars($f['answer']) ?></div>
                            <div class="faq-admin-actions">
                                <div class="faq-meta-info">
                                    <i class="fas fa-clock"></i> Last updated: <?= !empty($f['updated_at']) ? date('M d, Y', strtotime($f['updated_at'])) : date('M d, Y', strtotime($f['created_at'])) ?>
                                </div>
                                <div class="faq-action-buttons">
                                    <button type="button" class="btn btn-sm" onclick="editFaq(<?= htmlspecialchars(json_encode($f)) ?>)" style="background: var(--bg-card); border: 1px solid var(--border-color); color: var(--text-main);">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    
                                    <button type="button" class="btn btn-sm" onclick="document.getElementById('toggleForm<?= $f['id'] ?>').submit();" style="background: var(--bg-card); border: 1px solid var(--border-color); color: var(--text-main);" title="Toggle Visibility">
                                        <i class="fas fa-<?= $f['status'] === 'active' ? 'eye-slash' : 'eye' ?>"></i> <?= $f['status'] === 'active' ? 'Hide' : 'Publish' ?>
                                    </button>

                                    <button type="button" class="btn btn-sm btn-delete" title="Delete FAQ" onclick="if(confirm('Are you sure you want to delete this FAQ permanently?')) document.getElementById('deleteForm<?= $f['id'] ?>').submit();">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Bulk Action Bar -->
    <div class="bulk-bar" id="bulkBar">
        <div style="font-weight: 600; color: var(--text-main);">
            <span id="selectedCount">0</span> items selected
        </div>
        <div class="bulk-actions">
            <select name="bulk_status" id="bulkStatus" class="form-control" style="width: auto; padding: 8px 12px; height: 38px;">
                <option value="">Change Status...</option>
                <option value="active">Set Active</option>
                <option value="inactive">Set Inactive</option>
            </select>
            <button type="button" class="btn btn-primary" onclick="submitBulk('bulk_update_status')" style="height: 38px; padding: 0 16px;">Update</button>
            <div style="width: 1px; height: 24px; background: var(--border-color); margin: 0 8px;"></div>
            <button type="button" class="btn btn-delete" onclick="if(confirm('Permanently delete selected items?')) submitBulk('bulk_delete')" style="height: 38px; padding: 0 16px;"><i class="fas fa-trash"></i> Delete Selected</button>
        </div>
    </div>
</form>

<?php if (empty($faqs)): ?>
<form id="seedForm" method="POST" style="display:none;"><input type="hidden" name="csrf_token" value="<?= $csrf_token ?>"><input type="hidden" name="action" value="seed_defaults"></form>
<?php endif; ?>
<?php foreach ($faqs as $f): ?>
<form id="toggleForm<?= $f['id'] ?>" method="POST" style="display:none;"><input type="hidden" name="csrf_token" value="<?= $csrf_token ?>"><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= $f['id'] ?>"></form>
<form id="deleteForm<?= $f['id'] ?>" method="POST" style="display:none;"><input type="hidden" name="csrf_token" value="<?= $csrf_token ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $f['id'] ?>"></form>
<?php endforeach; ?>

<!-- Modal for Create / Edit FAQ -->
<div class="modal" id="faqModal">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <h3 class="modal-title"><i class="fas fa-question-circle"></i> <span id="modalFaqTitle">Add FAQ</span></h3>
            <button type="button" class="modal-close" onclick="closeFaqModal()" aria-label="Close modal">&times;</button>
        </div>
        
        <form method="POST" id="faqForm">
            <div class="modal-body">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <input type="hidden" name="action" id="faqFormAction" value="create">
                <input type="hidden" name="id" id="faqId" value="">

                <div class="form-group">
                    <label for="faqQuestion"><i class="fas fa-heading"></i> Question <span class="text-danger">*</span></label>
                    <input type="text" name="question" id="faqQuestion" class="form-control" placeholder="e.g. How quickly can we expect to see tangible results?" required autocomplete="off">
                </div>

                <div class="form-group">
                    <label for="faqAnswer"><i class="fas fa-align-left"></i> Answer <span class="text-danger">*</span></label>
                    <textarea name="answer" id="faqAnswer" class="form-control" rows="4" placeholder="Provide a detailed, clear and customer-friendly explanation..." required oninput="updateCharCount()"></textarea>
                    <div class="char-count" id="answerCharCount">0 characters</div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="faqOrder"><i class="fas fa-sort-numeric-down"></i> Sort Order</label>
                        <input type="number" name="sort_order" id="faqOrder" class="form-control" value="1" min="0" step="1">
                    </div>

                    <div class="form-group">
                        <label for="faqStatus"><i class="fas fa-toggle-on"></i> Status</label>
                        <select name="status" id="faqStatus" class="form-control">
                            <option value="active">Active (Visible)</option>
                            <option value="inactive">Inactive (Draft)</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeFaqModal()"><i class="fas fa-times"></i> Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save FAQ</button>
            </div>
        </form>
    </div>
</div>

<script>
let currentStatusFilter = 'all';

function toggleFaqAccordion(element) {
    const wasOpen = element.classList.contains('open');
    // Optional: close other accordions or allow multi-expand
    element.classList.toggle('open');
}

function openFaqModal() {
    document.getElementById('modalFaqTitle').textContent = 'Add FAQ';
    document.getElementById('faqFormAction').value = 'create';
    document.getElementById('faqId').value = '';
    document.getElementById('faqQuestion').value = '';
    document.getElementById('faqAnswer').value = '';
    document.getElementById('faqOrder').value = <?= $total_faqs + 1 ?>;
    document.getElementById('faqStatus').value = 'active';
    updateCharCount();
    document.getElementById('faqModal').classList.add('active');
    setTimeout(() => document.getElementById('faqQuestion').focus(), 150);
}

function editFaq(f) {
    document.getElementById('modalFaqTitle').textContent = 'Edit FAQ';
    document.getElementById('faqFormAction').value = 'update';
    document.getElementById('faqId').value = f.id;
    document.getElementById('faqQuestion').value = f.question || '';
    document.getElementById('faqAnswer').value = f.answer || '';
    document.getElementById('faqOrder').value = f.sort_order ?? 0;
    document.getElementById('faqStatus').value = f.status || 'active';
    updateCharCount();
    document.getElementById('faqModal').classList.add('active');
    setTimeout(() => document.getElementById('faqQuestion').focus(), 150);
}

function closeFaqModal() {
    document.getElementById('faqModal').classList.remove('active');
}

function updateCharCount() {
    const val = document.getElementById('faqAnswer').value;
    document.getElementById('answerCharCount').textContent = val.length + ' characters';
}

function setStatusFilter(status, btn) {
    currentStatusFilter = status;
    document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
    btn.classList.add('active');
    filterFaqs();
}

function filterFaqs() {
    const query = (document.getElementById('faqSearch').value || '').trim().toLowerCase();
    const items = document.querySelectorAll('.faq-admin-item');
    
    items.forEach(item => {
        const itemStatus = item.getAttribute('data-status') || '';
        const q = item.getAttribute('data-question') || '';
        const a = item.getAttribute('data-answer') || '';

        const matchesStatus = (currentStatusFilter === 'all') || (itemStatus === currentStatusFilter);
        const matchesQuery = (query === '') || (q.includes(query) || a.includes(query));

        if (matchesStatus && matchesQuery) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
}

// Close modal on click outside or Escape key
window.addEventListener('click', function(e) {
    const modal = document.getElementById('faqModal');
    if (e.target === modal) {
        closeFaqModal();
    }
});

window.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeFaqModal();
    }
});

// Bulk Actions Logic
const selectAll = document.getElementById('selectAll');
const itemCbs = document.querySelectorAll('.item-cb');
const bulkBar = document.getElementById('bulkBar');
const selectedCount = document.getElementById('selectedCount');

function updateBulkBar() {
    const count = document.querySelectorAll('.item-cb:checked').length;
    selectedCount.textContent = count;
    if (count > 0) {
        bulkBar.classList.add('visible');
    } else {
        bulkBar.classList.remove('visible');
        if(selectAll) selectAll.checked = false;
    }
}

if(selectAll) {
    selectAll.addEventListener('change', (e) => {
        itemCbs.forEach(cb => {
            if(cb.closest('.faq-admin-item').style.display !== 'none') {
                cb.checked = e.target.checked;
            }
        });
        updateBulkBar();
    });
}

itemCbs.forEach(cb => {
    cb.addEventListener('change', () => {
        const visibleCbs = Array.from(itemCbs).filter(c => c.closest('.faq-admin-item').style.display !== 'none');
        const allChecked = visibleCbs.filter(c => c.checked).length === visibleCbs.length && visibleCbs.length > 0;
        if(selectAll) selectAll.checked = allChecked;
        updateBulkBar();
    });
});

function submitBulk(action) {
    if (action === 'bulk_update_status' && !document.getElementById('bulkStatus').value) {
        alert('Please select a status to apply.');
        return;
    }
    document.getElementById('bulkAction').value = action;
    document.getElementById('bulkForm').submit();
}
</script>

<?php require_once __DIR__ . '/../../../admin/includes/footer.php'; ?>
