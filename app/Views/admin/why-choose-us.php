<?php
require_once __DIR__ . '/../../../admin/includes/header.php';

/** @var array $items */
/** @var array $available_icons */
/** @var string $csrf_token */
/** @var string $message */
/** @var string $message_type */

$items = $items ?? [];
$available_icons = $available_icons ?? [];
$csrf_token = $csrf_token ?? '';
$message = $message ?? '';
$message_type = $message_type ?? '';
$total_items = count($items);
$active_items = count(array_filter($items, fn($i) => ($i['status'] ?? '') === 'active'));
$inactive_items = $total_items - $active_items;
?>

<style>
.wcu-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}
.wcu-stat-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius);
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 16px;
    transition: all var(--transition);
}
.wcu-stat-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-sm);
}
.wcu-stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
}
.wcu-stat-val {
    font-size: 24px;
    font-weight: 700;
    color: var(--text-main);
    line-height: 1.2;
}
.wcu-stat-label {
    font-size: 13px;
    color: var(--text-muted);
    margin-top: 2px;
}

.items-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 24px;
}
.item-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius);
    padding: 28px;
    position: relative;
    transition: all var(--transition);
    display: flex;
    flex-direction: column;
}
.item-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-md);
    border-color: var(--primary);
}
.item-card.inactive {
    opacity: 0.65;
}
.item-checkbox {
    position: absolute;
    top: 20px;
    left: 20px;
    z-index: 10;
}
.item-checkbox input[type="checkbox"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
    accent-color: var(--primary);
}
.bulk-bar {
    position: fixed;
    bottom: 0;
    left: 260px; /* sidebar width */
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
.items-grid.list-view { display: flex; flex-direction: column; gap: 12px; }
.items-grid.list-view .item-card { flex-direction: row; align-items: center; padding: 16px 20px; gap: 20px; }
.items-grid.list-view .item-icon { margin: 0; width: 44px; height: 44px; flex-shrink: 0; }
.items-grid.list-view .item-question { margin: 0; font-size: 15px; width: 250px; flex-shrink: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.items-grid.list-view .item-answer { margin: 0; flex-grow: 1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 400px; }
.items-grid.list-view .item-status { position: static; margin-right: auto; padding: 4px 10px; }
.items-grid.list-view .item-actions { border: none; padding: 0; margin-left: auto; flex-shrink: 0; }
.items-grid.list-view .item-checkbox { position: static; margin-right: 12px; }

.view-toggle { display: flex; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--btn-radius); overflow: hidden; }
.view-btn { background: none; border: none; padding: 8px 12px; color: var(--text-muted); cursor: pointer; transition: all 0.2s; }
.view-btn.active { background: color-mix(in srgb, var(--primary) 10%, transparent); color: var(--primary); }
.view-btn:hover:not(.active) { background: var(--bg-main); color: var(--text-main); }


.item-icon {
    width: 52px;
    height: 52px;
    background: color-mix(in srgb, var(--primary) 12%, transparent);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 20px;
}
.item-icon i {
    font-size: 22px;
    color: var(--primary);
}
.item-question {
    font-size: 16px;
    font-weight: 700;
    color: var(--text-main);
    margin-bottom: 10px;
    line-height: 1.4;
}
.item-answer {
    font-size: 14px;
    color: var(--text-muted);
    line-height: 1.65;
    flex-grow: 1;
    margin-bottom: 16px;
}

.item-status {
    position: absolute;
    top: 20px;
    right: 20px;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    border: 1px solid var(--border-color);
}
.item-status.status-active {
    background: color-mix(in srgb, var(--success) 15%, transparent);
    color: var(--success);
    border-color: color-mix(in srgb, var(--success) 30%, transparent);
}
.item-status.status-inactive {
    background: color-mix(in srgb, var(--danger) 15%, transparent);
    color: var(--danger);
    border-color: color-mix(in srgb, var(--danger) 30%, transparent);
}

.item-actions {
    display: flex;
    gap: 8px;
    padding-top: 18px;
    border-top: 1px solid var(--border-color);
}
.icon-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 10px;
    margin-top: 10px;
    max-height: 180px;
    overflow-y: auto;
    padding: 4px;
}
.icon-option {
    padding: 12px;
    border: 1px solid var(--border-color);
    border-radius: var(--btn-radius);
    text-align: center;
    cursor: pointer;
    transition: all var(--transition);
    background: var(--bg-main);
}
.icon-option:hover, .icon-option.selected {
    border-color: var(--primary);
    background: color-mix(in srgb, var(--primary) 12%, transparent);
    color: var(--primary);
}
.icon-option i {
    font-size: 18px;
    color: var(--text-muted);
    transition: all var(--transition);
}
.icon-option:hover i, .icon-option.selected i {
    color: var(--primary);
}

.btn-toggle {
    background: var(--bg-main);
    border: 1px solid var(--border-color);
    color: var(--text-main);
}
.btn-toggle:hover {
    background: color-mix(in srgb, var(--primary) 15%, transparent);
    border-color: var(--primary);
    color: var(--primary);
}
</style>

<div class="saas-page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-award" style="color: var(--primary);"></i> Why Choose Us Management</h1>
        <p class="page-subtitle">Configure value propositions, key differentiators, and competitive advantages.</p>
    </div>
    <div class="header-actions" style="display: flex; gap: 10px; align-items: center;">
        <div class="view-toggle">
            <button class="view-btn active" id="btnGrid" onclick="setView('grid')" title="Grid View"><i class="fas fa-th-large"></i></button>
            <button class="view-btn" id="btnList" onclick="setView('list')" title="List View"><i class="fas fa-list"></i></button>
        </div>
        <?php if ($total_items === 0): ?>
        <form method="POST" style="display:inline;">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <input type="hidden" name="action" value="seed_defaults">
            <button type="submit" class="btn" style="background: var(--bg-card); color: var(--text-main); border: 1px solid var(--border-color);">
                <i class="fas fa-magic"></i> Load Starter Highlights
            </button>
        </form>
        <?php endif; ?>
        <button class="btn btn-primary" onclick="openModal()"><i class="fas fa-plus"></i> Add Item</button>
    </div>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?= $message_type === 'success' ? 'success' : 'error' ?>" style="margin-bottom: 20px;">
        <i class="fas fa-<?= $message_type === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
        <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

<!-- Quick Stats -->
<div class="wcu-stats-grid">
    <div class="wcu-stat-card">
        <div class="wcu-stat-icon" style="background: color-mix(in srgb, var(--primary) 12%, transparent); color: var(--primary);">
            <i class="fas fa-layer-group"></i>
        </div>
        <div>
            <div class="wcu-stat-val"><?= $total_items ?></div>
            <div class="wcu-stat-label">Total Feature Highlights</div>
        </div>
    </div>
    <div class="wcu-stat-card">
        <div class="wcu-stat-icon" style="background: color-mix(in srgb, var(--success) 12%, transparent); color: var(--success);">
            <i class="fas fa-check-circle"></i>
        </div>
        <div>
            <div class="wcu-stat-val"><?= $active_items ?></div>
            <div class="wcu-stat-label">Active Items</div>
        </div>
    </div>
    <div class="wcu-stat-card">
        <div class="wcu-stat-icon" style="background: color-mix(in srgb, var(--text-muted) 12%, transparent); color: var(--text-muted);">
            <i class="fas fa-eye-slash"></i>
        </div>
        <div>
            <div class="wcu-stat-val"><?= $inactive_items ?></div>
            <div class="wcu-stat-label">Inactive Items</div>
        </div>
    </div>
</div>

<form id="bulkForm" method="POST">
    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
    <input type="hidden" name="action" id="bulkAction" value="">
    
    <div style="margin-bottom: 16px; display: flex; align-items: center; gap: 12px; padding: 0 4px;">
        <input type="checkbox" id="selectAll" style="width: 16px; height: 16px; cursor: pointer; accent-color: var(--primary);">
        <label for="selectAll" style="font-size: 14px; font-weight: 600; color: var(--text-muted); cursor: pointer;">Select All</label>
    </div>

    <div class="items-grid" id="itemsGrid">
        <?php foreach ($items as $index => $item): ?>
        <div class="item-card <?= $item['status'] === 'inactive' ? 'inactive' : '' ?>">
            <div class="item-checkbox">
                <input type="checkbox" name="item_ids[]" value="<?= $item['id'] ?>" class="item-cb">
            </div>
            <div class="item-status status-<?= $item['status'] ?>"><i class="fas fa-<?= $item['status'] === 'active' ? 'check' : 'times' ?>"></i> <?= ucfirst($item['status']) ?></div>
            <div class="item-icon"><i class="fas <?= htmlspecialchars($item['icon'] ?? 'fa-star') ?>"></i></div>
            <div class="item-question"><?= htmlspecialchars($item['question']) ?></div>
            <div class="item-answer"><?= htmlspecialchars($item['answer']) ?></div>
            <div class="item-actions">
                <button type="button" class="btn btn-sm btn-edit" onclick="editItem(<?= htmlspecialchars(json_encode($item)) ?>)"><i class="fas fa-edit"></i> Edit</button>
                <button type="button" class="btn btn-sm btn-toggle" title="Toggle Status" onclick="document.getElementById('toggleForm<?= $item['id'] ?>').submit();"><i class="fas fa-<?= $item['status'] === 'active' ? 'eye-slash' : 'eye' ?>"></i></button>
                <button type="button" class="btn btn-sm btn-delete" title="Delete" onclick="if(confirm('Delete this highlight permanently?')) document.getElementById('deleteForm<?= $item['id'] ?>').submit();"><i class="fas fa-trash"></i></button>
            </div>
        </div>
        <?php endforeach; ?>
        <?php if (empty($items)): ?>
        <div style="grid-column: 1/-1; text-align:center; padding:60px 20px; background:var(--bg-card); border:1px dashed var(--border-color); border-radius:var(--border-radius); color:var(--text-muted);">
            <i class="fas fa-inbox" style="font-size: 54px; opacity: 0.3; margin-bottom: 16px; display: block;"></i>
            <h3 style="color: var(--text-main); margin-bottom: 8px;">No Items Found</h3>
            <p style="margin-bottom: 16px;">Add key differentiators or click to load starter items.</p>
            <button type="button" class="btn btn-primary" onclick="openModal()"><i class="fas fa-plus"></i> Add Item</button>
        </div>
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

<?php foreach ($items as $item): ?>
<form id="toggleForm<?= $item['id'] ?>" method="POST" style="display:none;"><input type="hidden" name="csrf_token" value="<?= $csrf_token ?>"><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= $item['id'] ?>"></form>
<form id="deleteForm<?= $item['id'] ?>" method="POST" style="display:none;"><input type="hidden" name="csrf_token" value="<?= $csrf_token ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $item['id'] ?>"></form>
<?php endforeach; ?>

<div class="modal" id="itemModal">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <h3 class="modal-title"><i class="fas fa-award"></i> <span id="modalTitle">Add New Item</span></h3>
            <button type="button" class="modal-close" onclick="closeModal()" aria-label="Close modal">&times;</button>
        </div>
        <form method="POST">
            <div class="modal-body">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <input type="hidden" name="action" id="formAction" value="create">
                <input type="hidden" name="id" id="itemId">
                
                <div class="form-group">
                    <label for="itemQuestion"><i class="fas fa-heading"></i> Title / Heading <span class="text-danger">*</span></label>
                    <input type="text" name="question" id="itemQuestion" class="form-control" placeholder="e.g. Proven Track Record" required>
                </div>
                
                <div class="form-group">
                    <label for="itemAnswer"><i class="fas fa-align-left"></i> Description <span class="text-danger">*</span></label>
                    <textarea name="answer" id="itemAnswer" class="form-control" placeholder="Describe the benefit or differentiator..." rows="4" required></textarea>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-icons"></i> Choose Icon <span class="text-danger">*</span></label>
                    <div class="icon-grid">
                        <?php foreach ($available_icons as $icon => $name): ?>
                        <div class="icon-option" data-icon="<?= $icon ?>" onclick="selectIcon(this)" title="<?= htmlspecialchars($name) ?>"><i class="fas <?= $icon ?>"></i></div>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" name="icon" id="selectedIcon" value="fa-star" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="itemSortOrder"><i class="fas fa-sort-numeric-down"></i> Sort Order</label>
                        <input type="number" name="sort_order" id="itemSortOrder" class="form-control" value="0" min="0">
                    </div>
                    <div class="form-group">
                        <label for="itemStatus"><i class="fas fa-toggle-on"></i> Status</label>
                        <select name="status" id="itemStatus" class="form-control">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal()"><i class="fas fa-times"></i> Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Item</button>
            </div>
        </form>
    </div>
</div>

<script>
function selectIcon(el) { 
    document.querySelectorAll('.icon-option').forEach(o => o.classList.remove('selected')); 
    el.classList.add('selected'); 
    document.getElementById('selectedIcon').value = el.dataset.icon; 
}
function openModal() { 
    document.getElementById('modalTitle').textContent = 'Add New Item'; 
    document.getElementById('formAction').value = 'create'; 
    document.getElementById('itemId').value = ''; 
    document.getElementById('itemQuestion').value = ''; 
    document.getElementById('itemAnswer').value = ''; 
    document.getElementById('itemSortOrder').value = '0';
    document.getElementById('selectedIcon').value = 'fa-star'; 
    document.getElementById('itemStatus').value = 'active'; 
    document.querySelectorAll('.icon-option').forEach(o => o.classList.remove('selected')); 
    document.getElementById('itemModal').classList.add('active'); 
}
function editItem(item) { 
    document.getElementById('modalTitle').textContent = 'Edit Item'; 
    document.getElementById('formAction').value = 'update'; 
    document.getElementById('itemId').value = item.id; 
    document.getElementById('itemQuestion').value = item.question; 
    document.getElementById('itemAnswer').value = item.answer; 
    document.getElementById('itemSortOrder').value = item.sort_order ?? 0;
    document.getElementById('itemStatus').value = item.status; 
    document.getElementById('selectedIcon').value = item.icon; 
    document.querySelectorAll('.icon-option').forEach(o => o.classList.remove('selected')); 
    const sel = document.querySelector(`.icon-option[data-icon="${item.icon}"]`); 
    if (sel) sel.classList.add('selected'); 
    document.getElementById('itemModal').classList.add('active'); 
}
function closeModal() { 
    document.getElementById('itemModal').classList.remove('active'); 
}
window.onclick = function(e) { 
    if (e.target === document.getElementById('itemModal')) closeModal(); 
}

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
        itemCbs.forEach(cb => cb.checked = e.target.checked);
        updateBulkBar();
    });
}

itemCbs.forEach(cb => {
    cb.addEventListener('change', () => {
        const allChecked = document.querySelectorAll('.item-cb:checked').length === itemCbs.length;
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

// View Toggle Logic
function setView(view) {
    const grid = document.getElementById('itemsGrid');
    const btnGrid = document.getElementById('btnGrid');
    const btnList = document.getElementById('btnList');
    
    if (view === 'list') {
        grid.classList.add('list-view');
        btnList.classList.add('active');
        btnGrid.classList.remove('active');
        localStorage.setItem('wcuViewPreference', 'list');
    } else {
        grid.classList.remove('list-view');
        btnGrid.classList.add('active');
        btnList.classList.remove('active');
        localStorage.setItem('wcuViewPreference', 'grid');
    }
}

// Load preference on start
document.addEventListener('DOMContentLoaded', () => {
    const pref = localStorage.getItem('wcuViewPreference');
    if (pref === 'list') {
        setView('list');
    }
});
</script>

<?php require_once __DIR__ . '/../../../admin/includes/footer.php'; ?>