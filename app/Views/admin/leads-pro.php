<?php
require_once __DIR__ . '/../../../admin/includes/header-pro.php';

$page_title = 'Leads Management';
/** @var array $leads */
/** @var string $csrf_token */
/** @var string $message */
/** @var string $message_type */

$leads = $leads ?? [];
$csrf_token = $csrf_token ?? '';
$message = $message ?? '';
$message_type = $message_type ?? '';
?>

<style>
/* Avatar Initials */
.avatar {
  width: 40px; height: 40px;
  border-radius: var(--radius-md);
  display: flex; align-items: center; justify-content: center;
  font-size: 14px; font-weight: 600;
  color: var(--primary);
  background: var(--primary-soft);
  border: 1px solid var(--primary-shadow);
}
.lead-name-cell {
  display: flex;
  align-items: center;
  gap: var(--space-3);
}

/* View Switch and Kanban Board Styles */
.view-switch-container {
  background: var(--bg-surface);
  border: 1px solid var(--border-light);
  border-radius: var(--radius-md);
  padding: var(--space-1);
  display: inline-flex;
  gap: var(--space-1);
  margin-right: var(--space-3);
}
.view-switch-container button {
 border: none !important;
 background: transparent;
 color: var(--text-secondary);
 cursor: pointer;
 border-radius: var(--radius-sm);
 font-size: var(--text-sm);
 font-weight: 500;
 transition: all var(--duration-fast) var(--ease-out);
 display: flex;
 align-items: center;
 gap: var(--space-2);
}
.view-switch-container button.active {
 background: var(--primary) !important;
 color: #fff !important;
 box-shadow: var(--shadow-sm);
}
.view-switch-container button:hover {
 color: var(--text-primary);
 background: var(--bg-hover);
}

/* Danger-ghost modifier — replaces inline color: var(--danger) overrides */
.btn-ghost-danger {
 color: var(--danger);
}
.btn-ghost-danger:hover {
 color: var(--danger-text);
 background: var(--danger-soft);
}

.kanban-card:hover {
  border-color: var(--primary) !important;
  transform: translateY(-2px);
  box-shadow: var(--shadow-md) !important;
}
.kanban-column {
  min-width: 250px;
  transition: all var(--duration-base) var(--ease-out);
}
.kanban-cards-container {
  transition: background-color var(--duration-base) var(--ease-out), border-color var(--duration-base) var(--ease-out);
}
.kanban-cards-container.dragover {
  background: var(--primary-soft) !important;
  border: 2px dashed var(--primary) !important;
  border-radius: var(--radius-lg);
}
.sidebar-overlay {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  z-index: calc(var(--z-fixed) - 1);
}
.sidebar-overlay.active {
  display: block;
}
@media (max-width: 1024px) {
  .sidebar-overlay.active {
  display: block;
  }
}
</style>

<style>
/* ── BULK ACTION BAR ── */
.bulk-bar {
  display: none;
  position: sticky;
  top: 0;
  z-index: 50;
  background: var(--bg-surface);
  border: 1px solid var(--primary);
  border-radius: var(--radius-lg);
  padding: var(--space-3) var(--space-5);
  margin-bottom: var(--space-4);
  align-items: center;
  gap: var(--space-3);
  box-shadow: var(--shadow-lg);
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
  font-size: var(--text-sm);
  font-weight: 600;
  color: var(--primary);
  white-space: nowrap;
}
.bulk-bar-divider {
  width: 1px;
  height: 24px;
  background: var(--border-light);
}
.bulk-bar .form-select {
  min-width: 160px;
  padding: var(--space-2) var(--space-3);
  font-size: var(--text-sm);
}
.bulk-bar .btn {
  white-space: nowrap;
}
/* Checkbox styling */
.lead-checkbox {
  width: 16px;
  height: 16px;
  accent-color: var(--primary);
  cursor: pointer;
}
</style>

<!-- Page Header -->
<div class="flex items-center justify-between mb-6">
  <div>
  <h1 style="font-size: var(--text-2xl); font-weight: 600; letter-spacing: -0.02em; color: var(--text-primary);">
  Leads Management
  </h1>
  <p class="text-sm text-muted" style="margin-top: var(--space-1);">
  Manage your customer leads and track their status through the pipeline.
  </p>
  </div>
  <div class="flex items-center gap-2">
  <div class="view-switch-container">
  <button type="button" class="active" id="viewTableBtn" onclick="switchView('table')">
  <i class="fas fa-list"></i> Table
  </button>
  <button type="button" id="viewBoardBtn" onclick="switchView('board')">
  <i class="fas fa-table-columns"></i> Board
  </button>
  </div>
  <button class="btn btn-primary" onclick="openModal()">
  <i class="fas fa-plus"></i> New Lead
  </button>
  </div>
</div>

<?php if ($message): ?>
  <div class="alert alert-<?= $message_type ?>">
  <i class="fas fa-<?= $message_type === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
  <?= htmlspecialchars($message) ?>
  </div>
<?php endif; ?>

<div id="leadsTableContainer">

<!-- Bulk Action Bar -->
<div class="bulk-bar" id="bulkBar">
  <span class="bulk-bar-count"><span id="bulkCount">0</span> selected</span>
  <div class="bulk-bar-divider"></div>
  <form method="POST" id="bulkStatusForm" style="display:flex; align-items:center; gap: var(--space-2);">
    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
    <input type="hidden" name="action" value="bulk_update_status">
    <div id="bulkStatusIds"></div>
    <select name="status" class="form-select" required>
      <option value="" disabled selected>Change Status To…</option>
      <option value="new">New</option>
      <option value="contacted">Contacted</option>
      <option value="confirmed">Confirmed</option>
      <option value="lose">Lost</option>
    </select>
    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-sync-alt"></i> Apply</button>
  </form>
  <div class="bulk-bar-divider"></div>
  <form method="POST" id="bulkDeleteForm" onsubmit="return confirm('Permanently delete selected leads?')" style="display:flex; align-items:center;">
    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
    <input type="hidden" name="action" value="bulk_delete">
    <div id="bulkDeleteIds"></div>
    <button type="submit" class="btn btn-sm" style="background: var(--danger); color: #fff;"><i class="fas fa-trash"></i> Delete Selected</button>
  </form>
  <div style="margin-left: auto;">
    <button type="button" class="btn btn-ghost btn-sm" onclick="clearSelection()"><i class="fas fa-times"></i> Clear</button>
  </div>
</div>

<div class="card">
  <div class="table-responsive">
  <table style="min-width: auto;">
  <thead>
  <tr>
  <th style="width: 36px;"><input type="checkbox" class="lead-checkbox" id="selectAllCb" onclick="toggleSelectAll(this)"></th>
  <th>Lead</th>
  <th>Contact Info</th>
  <th>Service Requested</th>
  <th>Status</th>
  <th>Date Received</th>
  <th style="text-align: right;">Actions</th>
  </tr>
  </thead>
  <tbody>
  <?php foreach ($leads as $lead): ?>
  <tr>
  <td><input type="checkbox" class="lead-checkbox row-cb" value="<?= $lead['id'] ?>" onclick="updateBulkBar()"></td>
  <td>
  <div class="lead-name-cell">
  <div class="avatar"><?= strtoupper(substr($lead['name'], 0, 2)) ?></div>
  <div>
  <div style="font-weight: 500; color: var(--text-primary);"><?= htmlspecialchars($lead['name']) ?></div>
  <div style="font-size: var(--text-xs); color: var(--text-secondary);">ID: #<?= str_pad($lead['id'], 4, '0', STR_PAD_LEFT) ?></div>
  </div>
  </div>
  </td>
  <td>
  <div style="color: var(--text-primary);"><?= htmlspecialchars($lead['email'] ?? 'No Email') ?></div>
  <div style="font-size: var(--text-xs); color: var(--text-secondary);"><?= htmlspecialchars($lead['phone']) ?></div>
  </td>
  <td>
  <div style="font-weight: 500; color: var(--text-primary);"><?= htmlspecialchars($lead['service'] ?? 'General Inquiry') ?></div>
  </td>
  <td>
  <?php
  $status_class = 'badge-neutral';
  $status_text = ucfirst($lead['status'] ?? 'new');
  if (($lead['status'] ?? '') === 'new') {
  $status_class = 'badge-info';
  } elseif (($lead['status'] ?? '') === 'contacted') {
  $status_class = 'badge-warning';
  } elseif (($lead['status'] ?? '') === 'confirmed' || ($lead['status'] ?? '') === 'converted') {
  $status_class = 'badge-success';
  } elseif (($lead['status'] ?? '') === 'lose' || ($lead['status'] ?? '') === 'lost') {
  $status_class = 'badge-danger';
  }
  ?>
  <span class="badge-sm <?= $status_class ?>"><?= $status_text ?></span>
  </td>
  <td>
  <div style="color: var(--text-primary);"><?= date('M j, Y', strtotime($lead['created_at'])) ?></div>
  <div style="font-size: var(--text-xs); color: var(--text-secondary);"><?= date('h:i A', strtotime($lead['created_at'])) ?></div>
  </td>
  <td style="text-align: right;">
  <div style="display: flex; gap: var(--space-2); justify-content: flex-end;">
  <button class="btn btn-ghost btn-sm" onclick="editLead(<?= htmlspecialchars(json_encode($lead)) ?>)" title="Edit Lead">
  <i class="fas fa-edit"></i>
  </button>
  <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this lead permanently?')">
  <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
  <input type="hidden" name="action" value="delete">
  <input type="hidden" name="id" value="<?= $lead['id'] ?>">
  <button type="submit" class="btn btn-ghost btn-sm btn-ghost-danger" title="Delete Lead">
  <i class="fas fa-trash"></i>
  </button>
  </form>
  </div>
  </td>
  </tr>
  <?php endforeach; ?>

  <?php if (empty($leads)): ?>
  <tr>
  <td colspan="7" style="text-align:center; padding:var(--space-10) var(--space-4); border-bottom: none;">
  <div style="width: 48px; height: 48px; background: var(--bg-surface-secondary); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto var(--space-4);">
  <i class="fas fa-inbox" style="font-size: 20px; color: var(--text-tertiary);"></i>
  </div>
  <h3 style="font-size: var(--text-base); font-weight: 600; color: var(--text-primary); margin-bottom: var(--space-2);">
  No leads found
  </h3>
  <p style="color: var(--text-secondary); font-size: var(--text-sm); margin-bottom: var(--space-4);">
  When a new customer submits a contact form, they will appear here.
  </p>
  <button class="btn btn-primary btn-sm" onclick="openModal()"><i class="fas fa-plus"></i> Add First Lead manually</button>
  </td>
  </tr>
  <?php endif; ?>
  </tbody>
  </table>
  </div>
</div>
</div>

<!-- Kanban Board Layout Section -->
<div class="kanban-board" id="leadsKanbanBoard" style="display: none; margin-bottom: var(--space-8);">
  <div class="kanban-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: var(--space-5); overflow-x: auto; padding-bottom: var(--space-3); align-items: start;">

  <!-- Column 1: New -->
  <div class="kanban-column" data-status="new" style="background: var(--bg-surface); border: 1px solid var(--border-light); border-radius: var(--radius-lg); padding: var(--space-5); min-height: 550px; display: flex; flex-direction: column;">
  <div class="kanban-column-header" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: var(--space-5);">
  <h4 style="font-size: var(--text-sm); font-weight: 600; color: var(--text-primary); margin: 0; display: flex; align-items: center; gap: var(--space-2);">
  <span style="display: inline-block; width: 10px; height: 10px; border-radius: 50%; background: var(--info);"></span>
  New Leads
  </h4>
  <span class="badge-sm badge-info" id="count-new">0</span>
  </div>
  <div class="kanban-cards-container" id="cards-new" style="flex-grow: 1; display: flex; flex-direction: column; gap: var(--space-3); min-height: 450px;" ondragover="allowDrop(event)" ondrop="dropLead(event, 'new')" ondragenter="dragEnter(event)" ondragleave="dragLeave(event)">
  <!-- Dynamic cards -->
  </div>
  </div>

  <!-- Column 2: Contacted -->
  <div class="kanban-column" data-status="contacted" style="background: var(--bg-surface); border: 1px solid var(--border-light); border-radius: var(--radius-lg); padding: var(--space-5); min-height: 550px; display: flex; flex-direction: column;">
  <div class="kanban-column-header" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: var(--space-5);">
  <h4 style="font-size: var(--text-sm); font-weight: 600; color: var(--text-primary); margin: 0; display: flex; align-items: center; gap: var(--space-2);">
  <span style="display: inline-block; width: 10px; height: 10px; border-radius: 50%; background: var(--warning);"></span>
  Contacted
  </h4>
  <span class="badge-sm badge-warning" id="count-contacted">0</span>
  </div>
  <div class="kanban-cards-container" id="cards-contacted" style="flex-grow: 1; display: flex; flex-direction: column; gap: var(--space-3); min-height: 450px;" ondragover="allowDrop(event)" ondrop="dropLead(event, 'contacted')" ondragenter="dragEnter(event)" ondragleave="dragLeave(event)">
  <!-- Dynamic cards -->
  </div>
  </div>

  <!-- Column 3: Confirmed -->
  <div class="kanban-column" data-status="confirmed" style="background: var(--bg-surface); border: 1px solid var(--border-light); border-radius: var(--radius-lg); padding: var(--space-5); min-height: 550px; display: flex; flex-direction: column;">
  <div class="kanban-column-header" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: var(--space-5);">
  <h4 style="font-size: var(--text-sm); font-weight: 600; color: var(--text-primary); margin: 0; display: flex; align-items: center; gap: var(--space-2);">
  <span style="display: inline-block; width: 10px; height: 10px; border-radius: 50%; background: var(--success);"></span>
  Won / Confirmed
  </h4>
  <span class="badge-sm badge-success" id="count-confirmed">0</span>
  </div>
  <div class="kanban-cards-container" id="cards-confirmed" style="flex-grow: 1; display: flex; flex-direction: column; gap: var(--space-3); min-height: 450px;" ondragover="allowDrop(event)" ondrop="dropLead(event, 'confirmed')" ondragenter="dragEnter(event)" ondragleave="dragLeave(event)">
  <!-- Dynamic cards -->
  </div>
  </div>

  <!-- Column 4: Lost -->
  <div class="kanban-column" data-status="lose" style="background: var(--bg-surface); border: 1px solid var(--border-light); border-radius: var(--radius-lg); padding: var(--space-5); min-height: 550px; display: flex; flex-direction: column;">
  <div class="kanban-column-header" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: var(--space-5);">
  <h4 style="font-size: var(--text-sm); font-weight: 600; color: var(--text-primary); margin: 0; display: flex; align-items: center; gap: var(--space-2);">
  <span style="display: inline-block; width: 10px; height: 10px; border-radius: 50%; background: var(--danger);"></span>
  Lost / Closed
  </h4>
  <span class="badge-sm badge-danger" id="count-lose">0</span>
  </div>
  <div class="kanban-cards-container" id="cards-lose" style="flex-grow: 1; display: flex; flex-direction: column; gap: var(--space-3); min-height: 450px;" ondragover="allowDrop(event)" ondrop="dropLead(event, 'lose')" ondragenter="dragEnter(event)" ondragleave="dragLeave(event)">
  <!-- Dynamic cards -->
  </div>
  </div>

  </div>
</div>

<!-- Modal -->
<div class="modal" id="leadModal">
  <div class="modal-backdrop" onclick="closeModal()"></div>
  <div class="modal-content" style="max-width: 600px;">
  <div class="modal-header">
  <h3 class="modal-title"><i class="fas fa-user-plus"></i> <span id="modalTitle">Add New Lead</span></h3>
  <button class="modal-close" onclick="closeModal()">
  <i class="fas fa-times"></i>
  </button>
  </div>
  <form method="POST">
  <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
  <input type="hidden" name="action" id="formAction" value="create">
  <input type="hidden" name="id" id="leadId">

  <div class="form-row cols-2">
  <div class="form-group">
  <label class="form-label"><i class="fas fa-user"></i> Full Name *</label>
  <input type="text" name="name" id="leadName" required placeholder="John Doe" class="form-input">
  </div>
  <div class="form-group">
  <label class="form-label"><i class="fas fa-phone"></i> Phone Number *</label>
  <input type="text" name="phone" id="leadPhone" required placeholder="+1 (555) 000-0000" class="form-input">
  </div>
  </div>

  <div class="form-row cols-2">
  <div class="form-group">
  <label class="form-label"><i class="fas fa-envelope"></i> Email Address</label>
  <input type="email" name="email" id="leadEmail" placeholder="john@example.com" class="form-input">
  </div>
  <div class="form-group">
  <label class="form-label"><i class="fas fa-briefcase"></i> Service Requested</label>
  <input type="text" name="service" id="leadService" placeholder="e.g. Web Development" class="form-input">
  </div>
  </div>

  <div class="form-group">
  <label class="form-label"><i class="fas fa-toggle-on"></i> Pipeline Status</label>
  <select name="status" id="leadStatus" class="form-select">
  <option value="new">New (Just arrived)</option>
  <option value="contacted">Contacted (In talks)</option>
  <option value="confirmed">Confirmed (Won)</option>
  <option value="lose">Lost (Closed)</option>
  </select>
  </div>

  <div class="form-group">
  <label class="form-label"><i class="fas fa-comment"></i> Client Notes / Message</label>
  <textarea name="message" id="leadMessage" rows="4" placeholder="Any special requirements or background context..." class="form-textarea"></textarea>
  </div>

  <div class="modal-footer">
  <button type="submit" class="btn btn-primary" style="flex: 1;">
  <i class="fas fa-save"></i> Save Lead Data
  </button>
  <button type="button" class="btn btn-secondary" onclick="closeModal()">
  Cancel
  </button>
  </div>
  </form>
  </div>
</div>

<script>
let leadsData = <?php echo json_encode($leads); ?>;

function openModal() {
  document.getElementById('modalTitle').textContent = 'Add New Lead';
  document.getElementById('formAction').value = 'create';
  document.getElementById('leadId').value = '';
  document.getElementById('leadName').value = '';
  document.getElementById('leadPhone').value = '';
  document.getElementById('leadEmail').value = '';
  document.getElementById('leadService').value = '';
  document.getElementById('leadMessage').value = '';
  document.getElementById('leadStatus').value = 'new';
  document.getElementById('leadModal').classList.add('active');
}

function editLead(lead) {
  document.getElementById('modalTitle').textContent = 'Edit Lead';
  document.getElementById('formAction').value = 'update';
  document.getElementById('leadId').value = lead.id;
  document.getElementById('leadName').value = lead.name;
  document.getElementById('leadPhone').value = lead.phone;
  document.getElementById('leadEmail').value = lead.email || '';
  document.getElementById('leadService').value = lead.service || '';
  document.getElementById('leadMessage').value = lead.message || '';
  document.getElementById('leadStatus').value = lead.status;
  document.getElementById('leadModal').classList.add('active');
}

function closeModal() {
  document.getElementById('leadModal').classList.remove('active');
}

// Kanban board rendering & drag-and-drop functionality
function escapeHtml(string) {
  if (!string) return '';
  return String(string)
  .replace(/&/g, '&amp;')
  .replace(/</g, '&lt;')
  .replace(/>/g, '&gt;')
  .replace(/"/g, '&quot;')
  .replace(/'/g, '&#039;');
}

function formatDate(dateStr) {
  if (!dateStr) return '';
  const date = new Date(dateStr);
  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
}

function renderKanbanBoard() {
  const columns = {
  new: document.getElementById('cards-new'),
  contacted: document.getElementById('cards-contacted'),
  confirmed: document.getElementById('cards-confirmed'),
  lose: document.getElementById('cards-lose')
  };

  const counts = { new: 0, contacted: 0, confirmed: 0, lose: 0 };

  // Clear columns
  Object.keys(columns).forEach(status => {
  if (columns[status]) columns[status].innerHTML = '';
  });

  // Populate columns
  leadsData.forEach(lead => {
  const status = lead.status || 'new';
  if (columns[status]) {
  counts[status]++;

  const serviceName = lead.service ? lead.service : 'General Inquiry';
  const leadJson = JSON.stringify(lead).replace(/"/g, '&quot;');

  const cardHTML = `
  <div class="kanban-card card" draggable="true" ondragstart="dragLead(event, ${lead.id})" id="lead-card-${lead.id}">
  <div style="font-weight: 600; color: var(--text-primary); margin-bottom: var(--space-2); font-size: var(--text-sm);">${escapeHtml(lead.name)}</div>
  <div style="font-size: var(--text-xs); color: var(--text-secondary); margin-bottom: var(--space-3); display: flex; align-items: center; gap: var(--space-2);">
  <i class="fas fa-briefcase" style="color: var(--primary);"></i> ${escapeHtml(serviceName)}
  </div>
  <div style="font-size: 11px; color: var(--text-secondary); margin-bottom: var(--space-3); display: flex; flex-direction: column; gap: var(--space-2);">
  <div style="display:flex; align-items:center; gap:var(--space-2);"><i class="fas fa-envelope" style="width:12px; text-align:center;"></i> ${escapeHtml(lead.email || 'No Email')}</div>
  <div style="display:flex; align-items:center; gap:var(--space-2);"><i class="fas fa-phone" style="width:12px; text-align:center;"></i> ${escapeHtml(lead.phone)}</div>
  </div>
  <div style="display: flex; align-items: center; justify-content: space-between; border-top: 1px solid var(--border-light); padding-top: var(--space-3); margin-top: var(--space-3);">
  <span style="font-size: 10px; color: var(--text-secondary);">${formatDate(lead.created_at)}</span>
  <div style="display: flex; gap: var(--space-2);">
  <button class="btn btn-ghost btn-sm" onclick="editLead(${leadJson})" style="padding: var(--space-1);" title="Edit Lead">
  <i class="fas fa-edit" style="font-size: 10px;"></i>
  </button>
  </div>
  </div>
  </div>
  `;
  columns[status].innerHTML += cardHTML;
  }
  });

  // Update count badges
  Object.keys(counts).forEach(status => {
  const badge = document.getElementById(`count-${status}`);
  if (badge) badge.textContent = counts[status];
  });
}

function dragLead(ev, leadId) {
  ev.dataTransfer.setData("leadId", leadId);
}

function allowDrop(ev) {
  ev.preventDefault();
}

function dragEnter(ev) {
  ev.currentTarget.classList.add('dragover');
}

function dragLeave(ev) {
  ev.currentTarget.classList.remove('dragover');
}

function dropLead(ev, targetStatus) {
  ev.preventDefault();
  ev.currentTarget.classList.remove('dragover');
  const leadId = ev.dataTransfer.getData("leadId");

  const leadIndex = leadsData.findIndex(l => l.id == leadId);
  if (leadIndex !== -1 && leadsData[leadIndex].status !== targetStatus) {
  // Optimistic UI update
  leadsData[leadIndex].status = targetStatus;
  renderKanbanBoard();

  // Save to database asynchronously
  const formData = new FormData();
  formData.append('action', 'update_status');
  formData.append('id', leadId);
  formData.append('status', targetStatus);
  formData.append('csrf_token', '<?= $csrf_token ?>');

  fetch('leads.php', {
  method: 'POST',
  body: formData
  })
  .then(res => res.json())
  .then(data => {
  if (!data.success) {
  console.error('Failed to sync updated status to database.');
  }
  })
  .catch(err => {
  console.error('Network error during status update:', err);
  });
  }
}

function switchView(view) {
  const tableBtn = document.getElementById('viewTableBtn');
  const boardBtn = document.getElementById('viewBoardBtn');
  const tableContainer = document.getElementById('leadsTableContainer');
  const boardContainer = document.getElementById('leadsKanbanBoard');

  if (view === 'table') {
  tableBtn.classList.add('active');
  boardBtn.classList.remove('active');
  tableContainer.style.display = 'block';
  boardContainer.style.display = 'none';
  localStorage.setItem('lead_view_preference', 'table');
  } else {
  boardBtn.classList.add('active');
  tableBtn.classList.remove('active');
  tableContainer.style.display = 'none';
  boardContainer.style.display = 'block';
  localStorage.setItem('lead_view_preference', 'board');
  renderKanbanBoard();
  }
}

// Initialise Preference View
document.addEventListener("DOMContentLoaded", function() {
  const viewPreference = localStorage.getItem('lead_view_preference') || 'table';
  switchView(viewPreference);
});

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
  document.getElementById('selectAllCb').checked = false;
  document.getElementById('selectAllCb').indeterminate = false;
  updateBulkBar();
}

// Inject selected IDs into bulk forms before submission
document.getElementById('bulkStatusForm').addEventListener('submit', function(e) {
  const ids = getSelectedIds();
  if (ids.length === 0) { e.preventDefault(); alert('No leads selected.'); return; }
  document.getElementById('bulkStatusIds').innerHTML = '';
  ids.forEach(id => {
    const inp = document.createElement('input');
    inp.type = 'hidden'; inp.name = 'bulk_ids[]'; inp.value = id;
    document.getElementById('bulkStatusIds').appendChild(inp);
  });
});

document.getElementById('bulkDeleteForm').addEventListener('submit', function(e) {
  const ids = getSelectedIds();
  if (ids.length === 0) { e.preventDefault(); alert('No leads selected.'); return; }
  document.getElementById('bulkDeleteIds').innerHTML = '';
  ids.forEach(id => {
    const inp = document.createElement('input');
    inp.type = 'hidden'; inp.name = 'bulk_ids[]'; inp.value = id;
    document.getElementById('bulkDeleteIds').appendChild(inp);
  });
});
</script>

<?php require_once __DIR__ . '/../../../admin/includes/footer-pro.php'; ?>
