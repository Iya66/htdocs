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
$canEdit = $canEdit ?? ($_SESSION['admin_role'] ?? 'admin') !== 'viewer';
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

/* ── KANBAN BOARD RESPONSIVENESS ──
/* Twelve columns need a scrollable track on narrow screens, with a wider
   minimum card width on tablet/desktop. */
.kanban-grid {
  scroll-snap-type: x proximity;
  -webkit-overflow-scrolling: touch;
}
.kanban-column {
  scroll-snap-align: start;
}
/* Mobile: single column, comfortable reading width */
@media (max-width: 640px) {
  .kanban-grid {
    grid-template-columns: 1fr !important;
    min-width: 0 !important;
  }
  .kanban-column {
    min-width: 0 !important;
    min-height: auto !important;
    padding: var(--space-4) !important;
  }
  .kanban-cards-container {
    min-height: 120px !important;
  }
  .kanban-column-header h4 {
    font-size: var(--text-xs) !important;
  }
}
/* Tablet: two columns */
@media (min-width: 641px) and (max-width: 1023px) {
  .kanban-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
  }
  .kanban-column {
    min-width: 0 !important;
  }
}
/* Desktop: three across, scrolling for the rest */
@media (min-width: 1024px) {
  .kanban-grid {
    grid-template-columns: repeat(3, minmax(250px, 1fr)) !important;
  }
}
/* Status dropdown: keep option text readable and tappable on mobile */
@media (max-width: 640px) {
  #leadStatus option {
    padding: 8px 12px;
    font-size: 14px;
  }
  #leadStatusHint {
    line-height: 1.45;
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

/* Quick Status Selector Control */
.lead-status-cell {
  display: flex;
  flex-direction: column;
  gap: 6px;
  align-items: flex-start;
}
.lead-status-badge {
  display: inline-flex;
  align-items: center;
  transition: all 0.25s ease;
}
.lead-status-select {
  font-size: 11px;
  font-weight: 500;
  padding: 4px 22px 4px 8px;
  height: 28px;
  border-radius: var(--radius-sm);
  border: 1px solid var(--border-light);
  background: var(--bg-surface);
  color: var(--text-primary);
  cursor: pointer;
  outline: none;
  transition: all 0.2s ease;
  background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
  background-repeat: no-repeat;
  background-position: right 6px center;
  background-size: 11px;
  -webkit-appearance: none;
  -moz-appearance: none;
  appearance: none;
  max-width: 140px;
}
.lead-status-select:hover {
  border-color: var(--primary);
  background-color: var(--bg-hover);
}
.lead-status-select:focus {
  border-color: var(--primary);
  box-shadow: 0 0 0 2.5px var(--primary-soft);
}
.lead-status-select:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  pointer-events: none;
}
.status-loading-spinner {
  display: inline-flex;
  align-items: center;
  color: var(--primary);
  font-size: 12px;
}

/* Toast Notification Styles */
.toast-container {
  position: fixed;
  top: 24px;
  right: 24px;
  z-index: 999999;
  display: flex;
  flex-direction: column;
  gap: 10px;
  pointer-events: none;
}
.toast {
  pointer-events: auto;
  min-width: 300px;
  max-width: 420px;
  background: var(--bg-surface, #ffffff);
  border: 1px solid var(--border-light, #e2e8f0);
  border-radius: var(--radius-md, 8px);
  padding: 12px 16px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
  display: flex;
  align-items: center;
  gap: 12px;
  transform: translateX(120%);
  transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.35s ease;
  opacity: 0;
}
.toast.show {
  transform: translateX(0);
  opacity: 1;
}
.toast-icon {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  font-size: 14px;
  flex-shrink: 0;
}
.toast-success .toast-icon {
  background: var(--success, #10b981);
}
.toast-danger .toast-icon, .toast-error .toast-icon {
  background: var(--danger, #ef4444);
}
.toast-info .toast-icon {
  background: var(--primary, #3b82f6);
}
.toast-content {
  flex: 1;
}
.toast-title {
  font-size: 13px;
  font-weight: 700;
  color: var(--text-primary, #0f172a);
  margin-bottom: 2px;
}
.toast-message {
  font-size: 12px;
  color: var(--text-secondary, #64748b);
  line-height: 1.4;
}
.toast-close {
  cursor: pointer;
  color: var(--text-secondary, #94a3b8);
  padding: 4px;
  font-size: 13px;
}
.toast-close:hover {
  color: var(--danger, #ef4444);
}

/* Stage Pills & Filter Toolbar (Section 7) */
.leads-filter-toolbar {
  background: var(--bg-surface, #ffffff);
  border: 1px solid var(--border-light, #e2e8f0);
  border-radius: var(--radius-lg, 12px);
  padding: 16px;
  margin-bottom: var(--space-5, 20px);
  box-shadow: 0 1px 3px rgba(0,0,0,0.03);
}
.stage-pills-bar {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
  align-items: center;
  margin-bottom: 14px;
  padding-bottom: 12px;
  border-bottom: 1px solid var(--border-light, #f1f5f9);
}
.stage-pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 5px 12px;
  border-radius: 9999px;
  font-size: 12px;
  font-weight: 500;
  text-decoration: none;
  color: var(--text-secondary, #64748b);
  background: var(--bg-surface-secondary, #f8fafc);
  border: 1px solid var(--border-light, #e2e8f0);
  transition: all 0.2s ease;
}
.stage-pill:hover {
  background: #f1f5f9;
  color: var(--text-primary, #0f172a);
}
.stage-pill.active {
  background: var(--pill-color, var(--primary, #3b82f6));
  color: #fff;
  border-color: var(--pill-color, var(--primary, #3b82f6));
  font-weight: 600;
}
.stage-pill .pill-count {
  display: inline-block;
  padding: 1px 6px;
  border-radius: 9999px;
  background: rgba(0,0,0,0.07);
  font-size: 11px;
}
.stage-pill.active .pill-count {
  background: rgba(255,255,255,0.25);
  color: #fff;
}
.stage-pill-overdue {
  background: rgba(239, 68, 68, 0.08);
  color: #ef4444;
  border-color: rgba(239, 68, 68, 0.25);
}
.stage-pill-overdue.active {
  background: #ef4444;
  color: #fff;
}

/* Follow-up Pills in Table */
.lead-followup-pill {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 2px 8px;
  border-radius: 6px;
  font-size: 11px;
  font-weight: 600;
  margin-top: 4px;
  cursor: pointer;
  transition: transform 0.15s ease;
}
.lead-followup-pill:hover {
  transform: translateY(-1px);
}
.followup-pill-overdue {
  background: rgba(239, 68, 68, 0.12);
  color: #b91c1c;
  border: 1px solid rgba(239, 68, 68, 0.3);
}
.followup-pill-upcoming {
  background: rgba(59, 130, 246, 0.1);
  color: #1d4ed8;
  border: 1px solid rgba(59, 130, 246, 0.25);
}

/* Stage Workflow Modal styling */
.stage-context-box {
  background: var(--bg-surface-secondary, #f8fafc);
  border: 1px solid var(--border-light, #e2e8f0);
  border-radius: var(--radius-md, 8px);
  padding: 12px 14px;
  margin-top: 10px;
}
.stage-history-timeline {
  max-height: 180px;
  overflow-y: auto;
  border: 1px solid var(--border-light, #e2e8f0);
  border-radius: var(--radius-md, 8px);
  padding: 10px 14px;
  background: var(--bg-surface-secondary, #f8fafc);
}
.stage-history-item {
  padding: 8px 0;
  border-bottom: 1px solid var(--border-light, #f1f5f9);
  font-size: 12px;
}
.stage-history-item:last-child {
  border-bottom: none;
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

<!-- Stage Filter Pills & Search Bar (Section 7) -->
<div class="leads-filter-toolbar">
  <!-- Quick Stage Pills -->
  <div class="stage-pills-bar">
    <a href="leads" class="stage-pill <?= (empty($_GET['stage']) && empty($_GET['q'])) ? 'active' : '' ?>">
      All Leads <span class="pill-count"><?= $total_leads_count ?? count($leads) ?></span>
    </a>
    <a href="leads?stage=new_lead" class="stage-pill <?= ($_GET['stage'] ?? '') === 'new_lead' ? 'active' : '' ?>" style="--pill-color: #3B82F6;">
      <i class="fas fa-inbox"></i> New <span class="pill-count"><?= ($lead_status_counts['new_lead'] ?? 0) + ($lead_status_counts['new'] ?? 0) ?></span>
    </a>
    <a href="leads?stage=followup_pending" class="stage-pill <?= ($_GET['stage'] ?? '') === 'followup_pending' ? 'active' : '' ?>" style="--pill-color: #F97316;">
      <i class="fas fa-bell"></i> Follow-up Pending <span class="pill-count"><?= $lead_status_counts['followup_pending'] ?? 0 ?></span>
    </a>
    <a href="leads?stage=meeting_scheduled" class="stage-pill <?= ($_GET['stage'] ?? '') === 'meeting_scheduled' ? 'active' : '' ?>" style="--pill-color: #0EA5E9;">
      <i class="fas fa-calendar-check"></i> Meetings <span class="pill-count"><?= $lead_status_counts['meeting_scheduled'] ?? 0 ?></span>
    </a>
    <a href="leads?stage=proposal_sent" class="stage-pill <?= ($_GET['stage'] ?? '') === 'proposal_sent' ? 'active' : '' ?>" style="--pill-color: #D946EF;">
      <i class="fas fa-file-invoice-dollar"></i> Proposals <span class="pill-count"><?= $lead_status_counts['proposal_sent'] ?? 0 ?></span>
    </a>
    <a href="leads?stage=converted" class="stage-pill <?= ($_GET['stage'] ?? '') === 'converted' ? 'active' : '' ?>" style="--pill-color: #10B981;">
      <i class="fas fa-circle-check"></i> Converted <span class="pill-count"><?= ($lead_status_counts['converted'] ?? 0) + ($lead_status_counts['confirmed'] ?? 0) ?></span>
    </a>
    <a href="leads?stage=not_interested" class="stage-pill <?= ($_GET['stage'] ?? '') === 'not_interested' ? 'active' : '' ?>" style="--pill-color: #EF4444;">
      <i class="fas fa-circle-xmark"></i> Not Interested <span class="pill-count"><?= ($lead_status_counts['not_interested'] ?? 0) + ($lead_status_counts['lose'] ?? 0) ?></span>
    </a>
    <?php if (($followup_counts['overdue'] ?? 0) > 0): ?>
    <a href="leads?stage=overdue" class="stage-pill stage-pill-overdue <?= ($_GET['stage'] ?? '') === 'overdue' ? 'active' : '' ?>">
      <i class="fas fa-triangle-exclamation"></i> Overdue Follow-ups <span class="pill-count"><?= $followup_counts['overdue'] ?></span>
    </a>
    <?php endif; ?>
  </div>

  <!-- Search & Stage Filter Form -->
  <form method="GET" action="leads" class="leads-filter-form" style="display: flex; gap: var(--space-2); align-items: center; flex-wrap: wrap;">
    <div style="position: relative; flex: 1; min-width: 220px;">
      <i class="fas fa-search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-secondary); font-size: 13px;"></i>
      <input type="text" name="q" value="<?= htmlspecialchars($_GET['q'] ?? $_GET['search'] ?? '', ENT_QUOTES, 'UTF-8') ?>" 
             placeholder="Search by customer name, phone, email, website..." 
             class="form-input" style="padding-left: 36px; height: 38px;">
    </div>

    <div style="min-width: 200px;">
      <select name="stage" class="form-select" style="height: 38px;">
        <option value="">All Stages (<?= $total_leads_count ?? count($leads) ?>)</option>
        <option value="overdue" <?= ($_GET['stage'] ?? '') === 'overdue' ? 'selected' : '' ?>>⚠️ Overdue Follow-ups (<?= $followup_counts['overdue'] ?? 0 ?>)</option>
        <option value="upcoming" <?= ($_GET['stage'] ?? '') === 'upcoming' ? 'selected' : '' ?>>📅 Upcoming Follow-ups (<?= $followup_counts['upcoming'] ?? 0 ?>)</option>
        <?php foreach (leadStatusesByGroup() as $groupKey => $group): ?>
          <optgroup label="<?= htmlspecialchars($group['label'], ENT_QUOTES, 'UTF-8') ?>">
          <?php foreach ($group['statuses'] as $sKey => $sMeta): ?>
            <option value="<?= htmlspecialchars($sKey, ENT_QUOTES, 'UTF-8') ?>" <?= ($_GET['stage'] ?? '') === $sKey ? 'selected' : '' ?>>
              <?= htmlspecialchars($sMeta['label'], ENT_QUOTES, 'UTF-8') ?> (<?= $lead_status_counts[$sKey] ?? 0 ?>)
            </option>
          <?php endforeach; ?>
          </optgroup>
        <?php endforeach; ?>
      </select>
    </div>

    <button type="submit" class="btn btn-primary" style="height: 38px; padding: 0 16px;">
      <i class="fas fa-filter"></i> Filter
    </button>
    <?php if (!empty($_GET['q']) || !empty($_GET['stage'])): ?>
    <a href="leads" class="btn btn-ghost" style="height: 38px; display: inline-flex; align-items: center;" title="Reset Filters">
      <i class="fas fa-times"></i> Reset
    </a>
    <?php endif; ?>
  </form>
</div>

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
      <?php foreach ($lead_status_options as $status_key => $status_meta): ?>
      <option value="<?= htmlspecialchars($status_key, ENT_QUOTES, 'UTF-8') ?>"
       title="<?= htmlspecialchars($status_meta['description'], ENT_QUOTES, 'UTF-8') ?>">
      <?= htmlspecialchars($status_meta['label'], ENT_QUOTES, 'UTF-8') ?>
      </option>
      <?php endforeach; ?>
      <?php if (!empty($lead_legacy_options)): ?>
      <optgroup label="Legacy (retired stages)">
      <?php foreach ($lead_legacy_options as $status_key => $status_meta): ?>
        <option value="<?= htmlspecialchars($status_key, ENT_QUOTES, 'UTF-8') ?>"
         title="<?= htmlspecialchars($status_meta['description'], ENT_QUOTES, 'UTF-8') ?>">
        <?= htmlspecialchars($status_meta['label'], ENT_QUOTES, 'UTF-8') ?>
        </option>
      <?php endforeach; ?>
      </optgroup>
      <?php endif; ?>
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
  <?php if (!empty($lead['email'])): ?>
    <div style="font-weight: 500;">
      <a href="mailto:<?= htmlspecialchars($lead['email']) ?>" style="color: var(--primary); text-decoration: none; display: inline-flex; align-items: center; gap: 4px;" title="Email <?= htmlspecialchars($lead['name']) ?>">
        <i class="fas fa-envelope" style="font-size: 11px;"></i> <?= htmlspecialchars($lead['email']) ?>
      </a>
    </div>
  <?php else: ?>
    <div style="color: var(--text-tertiary); font-size: var(--text-xs); font-style: italic;">No Email</div>
  <?php endif; ?>
  <div style="font-size: var(--text-xs); color: var(--text-secondary); margin-top: 2px;">
    <a href="tel:<?= htmlspecialchars($lead['phone']) ?>" style="color: inherit; text-decoration: none;">
      <i class="fas fa-phone" style="font-size: 10px; margin-right: 4px;"></i><?= htmlspecialchars($lead['phone']) ?>
    </a>
  </div>
  <?php if (!empty($lead['website'])): ?>
  <div style="font-size: var(--text-xs); margin-top: 2px;">
    <a href="<?= htmlspecialchars($lead['website']) ?>" target="_blank" style="color: var(--primary); text-decoration: none; display: inline-flex; align-items: center; gap: 4px;" title="<?= htmlspecialchars($lead['website']) ?>">
      <i class="fas fa-globe" style="font-size: 10px;"></i> <?= htmlspecialchars(parse_url($lead['website'], PHP_URL_HOST) ?: $lead['website']) ?>
    </a>
  </div>
  <?php endif; ?>
  </td>
  <td>
  <div style="font-weight: 500; color: var(--text-primary);"><?= htmlspecialchars($lead['service'] ?? 'General Inquiry') ?></div>
  </td>
  <td>
  <?php
  // leadStatusMeta() is the single authority for label, badge and colour —
  // it handles approved stages, legacy values and unknown values alike.
  $status_key = resolveLeadStatus($lead['status'] ?? '');
  $status_meta = leadStatusMeta($status_key);
  ?>
  <div class="lead-status-cell" id="lead-status-cell-<?= $lead['id'] ?>">
    <div style="display: flex; align-items: center; gap: 6px;">
      <span class="badge-sm <?= $status_meta['badge'] ?> lead-status-badge"
            title="<?= htmlspecialchars($status_meta['description'], ENT_QUOTES, 'UTF-8') ?>"
            data-status="<?= htmlspecialchars($status_key, ENT_QUOTES, 'UTF-8') ?>"
            id="lead-badge-<?= $lead['id'] ?>"><?= htmlspecialchars(leadStatusLabel($status_key), ENT_QUOTES, 'UTF-8') ?></span>
      <span class="status-loading-spinner" id="lead-spinner-<?= $lead['id'] ?>" style="display: none;">
        <i class="fas fa-spinner fa-spin"></i>
      </span>
    </div>

    <?php if ($canEdit): ?>
    <div class="status-quick-change" style="position: relative; display: inline-flex; align-items: center; margin-top: 2px;">
      <select class="lead-status-select" 
              id="status-select-<?= $lead['id'] ?>"
              data-lead-id="<?= $lead['id'] ?>"
              data-current-status="<?= htmlspecialchars($status_key, ENT_QUOTES, 'UTF-8') ?>"
              onchange="onStageSelectChange(<?= $lead['id'] ?>, this)"
              title="Change stage for <?= htmlspecialchars($lead['name'], ENT_QUOTES, 'UTF-8') ?>">
        <option value="" disabled>Change Stage…</option>
        <?php foreach (leadStatusesByGroup() as $group_key => $group): ?>
          <optgroup label="<?= htmlspecialchars($group['label'], ENT_QUOTES, 'UTF-8') ?>">
          <?php foreach ($group['statuses'] as $s_key => $s_meta): ?>
            <option value="<?= htmlspecialchars($s_key, ENT_QUOTES, 'UTF-8') ?>" <?= $status_key === $s_key ? 'selected' : '' ?>>
              <?= htmlspecialchars($s_meta['label'], ENT_QUOTES, 'UTF-8') ?>
            </option>
          <?php endforeach; ?>
          </optgroup>
        <?php endforeach; ?>
        <?php if (!empty($lead_legacy_options)): ?>
          <optgroup label="Legacy (Retired)">
          <?php foreach ($lead_legacy_options as $s_key => $s_meta): ?>
            <option value="<?= htmlspecialchars($s_key, ENT_QUOTES, 'UTF-8') ?>" <?= $status_key === $s_key ? 'selected' : '' ?>>
              <?= htmlspecialchars($s_meta['label'], ENT_QUOTES, 'UTF-8') ?>
            </option>
          <?php endforeach; ?>
          </optgroup>
        <?php endif; ?>
      </select>
    </div>
    <?php endif; ?>

    <?php if (!empty($lead['follow_up_date'])): 
      $fTime = strtotime($lead['follow_up_date']);
      $isOverdue = $fTime < time() && !in_array($status_key, ['converted', 'not_interested', 'lose']);
    ?>
    <div class="lead-followup-pill <?= $isOverdue ? 'followup-pill-overdue' : 'followup-pill-upcoming' ?>" 
         id="lead-followup-pill-<?= $lead['id'] ?>"
         onclick="openStageModal(<?= $lead['id'] ?>)" 
         title="<?= $isOverdue ? 'Overdue Follow-up!' : 'Scheduled Follow-up' ?>: <?= date('M j, Y g:i A', $fTime) ?>">
      <i class="fas <?= $isOverdue ? 'fa-triangle-exclamation' : 'fa-clock' ?>"></i>
      <span><?= $isOverdue ? 'Overdue: ' : 'Due: ' ?><?= date('M j, g:i A', $fTime) ?></span>
    </div>
    <?php endif; ?>
  </div>
  </td>
  <td>
  <div style="color: var(--text-primary);"><?= date('M j, Y', strtotime($lead['created_at'])) ?></div>
  <div style="font-size: var(--text-xs); color: var(--text-secondary);"><?= date('h:i A', strtotime($lead['created_at'])) ?></div>
  </td>
  <td style="text-align: right;">
  <div style="display: flex; gap: var(--space-2); justify-content: flex-end;">
  <?php if ($canEdit): ?>
  <button class="btn btn-ghost btn-sm" onclick="openStageModal(<?= $lead['id'] ?>)" title="Stage Workflow & Follow-up Details">
  <i class="fas fa-calendar-check" style="color: var(--primary);"></i>
  </button>
  <button class="btn btn-ghost btn-sm" onclick="focusStatusSelect(<?= $lead['id'] ?>)" title="Change Stage Dropdown">
  <i class="fas fa-arrows-rotate" style="font-size: 11px;"></i>
  </button>
  <?php endif; ?>
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

  <?php
  // Columns are generated from app/lead_statuses.php so the board can never
  // drift from the dropdown, the badge colours or the backend validation.
  $kanban_groups = leadStatusesByGroup();
  $has_legacy_leads = false;
  foreach ($leads as $lead) {
    if (!isValidLeadStatus($lead['status'] ?? null)) {
      $has_legacy_leads = true;
      break;
    }
  }
  ?>

  <?php foreach ($kanban_groups as $group_key => $group): ?>
  <?php foreach ($group['statuses'] as $status_key => $status_meta): ?>
  <div class="kanban-column" data-status="<?= htmlspecialchars($status_key, ENT_QUOTES, 'UTF-8') ?>"
   data-group="<?= htmlspecialchars($group_key, ENT_QUOTES, 'UTF-8') ?>"
   title="<?= htmlspecialchars($status_meta['description'], ENT_QUOTES, 'UTF-8') ?>"
   style="background: var(--bg-surface); border: 1px solid var(--border-light); border-radius: var(--radius-lg); padding: var(--space-5); min-height: 550px; display: flex; flex-direction: column;">
  <div class="kanban-column-header" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: var(--space-5); gap: var(--space-2);">
  <h4 style="font-size: var(--text-sm); font-weight: 600; color: var(--text-primary); margin: 0; display: flex; align-items: center; gap: var(--space-2);">
  <span style="display: inline-block; width: 10px; height: 10px; border-radius: 50%; background: <?= htmlspecialchars($status_meta['color'], ENT_QUOTES, 'UTF-8') ?>; flex-shrink: 0;"></span>
  <span><?= htmlspecialchars($status_meta['label'], ENT_QUOTES, 'UTF-8') ?></span>
  </h4>
  <span class="badge-sm <?= htmlspecialchars($status_meta['badge'], ENT_QUOTES, 'UTF-8') ?>" id="count-<?= htmlspecialchars($status_key, ENT_QUOTES, 'UTF-8') ?>">0</span>
  </div>
  <div class="kanban-cards-container" id="cards-<?= htmlspecialchars($status_key, ENT_QUOTES, 'UTF-8') ?>"
   style="flex-grow: 1; display: flex; flex-direction: column; gap: var(--space-3); min-height: 450px;"
   ondragover="allowDrop(event)" ondrop="dropLead(event, '<?= htmlspecialchars($status_key, ENT_QUOTES, 'UTF-8') ?>')" ondragenter="dragEnter(event)" ondragleave="dragLeave(event)">
  <!-- Dynamic cards -->
  </div>
  </div>
  <?php endforeach; ?>
  <?php endforeach; ?>

  <?php if ($has_legacy_leads): ?>
  <!-- Safety net: leads still holding a retired stage stay visible and draggable
    onto a current stage instead of silently disappearing from the board. -->
  <div class="kanban-column" data-status="legacy" style="background: var(--bg-surface); border: 1px dashed var(--warning); border-radius: var(--radius-lg); padding: var(--space-5); min-height: 550px; display: flex; flex-direction: column;">
  <div class="kanban-column-header" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: var(--space-5); gap: var(--space-2);">
  <h4 style="font-size: var(--text-sm); font-weight: 600; color: var(--text-primary); margin: 0; display: flex; align-items: center; gap: var(--space-2);">
  <span style="display: inline-block; width: 10px; height: 10px; border-radius: 50%; background: var(--warning); flex-shrink: 0;"></span>
  <span>Legacy</span>
  </h4>
  <span class="badge-sm badge-warning" id="count-legacy">0</span>
  </div>
  <div style="font-size: var(--text-xs); color: var(--text-secondary); margin-bottom: var(--space-3);">
  Drag these onto a current stage to migrate them.
  </div>
  <div class="kanban-cards-container" id="cards-legacy" style="flex-grow: 1; display: flex; flex-direction: column; gap: var(--space-3); min-height: 450px;" ondragover="allowDrop(event)" ondragenter="dragEnter(event)" ondragleave="dragLeave(event)">
  <!-- Dynamic cards -->
  </div>
  </div>
  <?php endif; ?>

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
  <label class="form-label" for="leadStatus"><i class="fas fa-toggle-on"></i> Pipeline Status</label>
  <select name="status" id="leadStatus" class="form-select" aria-describedby="leadStatusHint">
  <?php foreach (leadStatusesByGroup() as $group_key => $group): ?>
    <optgroup label="<?= htmlspecialchars($group['label'], ENT_QUOTES, 'UTF-8') ?>">
    <?php foreach ($group['statuses'] as $status_key => $status_meta): ?>
      <option value="<?= htmlspecialchars($status_key, ENT_QUOTES, 'UTF-8') ?>"
       data-description="<?= htmlspecialchars($status_meta['description'], ENT_QUOTES, 'UTF-8') ?>">
      <?= htmlspecialchars($status_meta['label'], ENT_QUOTES, 'UTF-8') ?>
      </option>
    <?php endforeach; ?>
    </optgroup>
  <?php endforeach; ?>
  <?php if (!empty($lead_legacy_options)): ?>
    <optgroup label="Legacy (retired stages)">
    <?php foreach ($lead_legacy_options as $status_key => $status_meta): ?>
      <option value="<?= htmlspecialchars($status_key, ENT_QUOTES, 'UTF-8') ?>"
       data-description="<?= htmlspecialchars($status_meta['description'], ENT_QUOTES, 'UTF-8') ?>">
      <?= htmlspecialchars($status_meta['label'], ENT_QUOTES, 'UTF-8') ?>
      </option>
    <?php endforeach; ?>
    </optgroup>
  <?php endif; ?>
  </select>
  <div class="form-hint" id="leadStatusHint">
  <i class="fas fa-circle-info" style="color: var(--info);"></i>
  <span id="leadStatusDescription">Newly received customer enquiry</span>
  </div>
  </div>

  <div class="form-row cols-2">
  <div class="form-group">
  <label class="form-label"><i class="fas fa-globe"></i> Website URL</label>
  <input type="url" name="website" id="leadWebsite" placeholder="https://example.com" class="form-input">
  </div>
  <div class="form-group">
  <label class="form-label"><i class="fas fa-calendar-alt"></i> Follow-up Date & Time</label>
  <input type="datetime-local" name="follow_up_date" id="leadFollowupDate" class="form-input">
  </div>
  </div>

  <div class="form-group">
  <label class="form-label"><i class="fas fa-comment"></i> Client Notes / Message</label>
  <textarea name="message" id="leadMessage" rows="3" placeholder="Any special requirements or background context..." class="form-textarea"></textarea>
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

<!-- Stage & Follow-up Workflow Modal (Sections 3, 4, 5, 9) -->
<div class="modal" id="stageWorkflowModal" style="display: none;">
  <div class="modal-backdrop" onclick="closeStageModal()"></div>
  <div class="modal-content" style="max-width: 620px;">
    <div class="modal-header">
      <h3 class="modal-title"><i class="fas fa-diagram-project"></i> <span id="stageModalTitle">Lead Stage & Workflow</span></h3>
      <button class="modal-close" onclick="closeStageModal()">
        <i class="fas fa-times"></i>
      </button>
    </div>

    <form id="stageWorkflowForm" onsubmit="submitStageWorkflow(event)">
      <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
      <input type="hidden" name="action" value="update_status">
      <input type="hidden" name="id" id="workflowLeadId" value="">

      <!-- Lead Summary Header -->
      <div style="background: var(--bg-surface-secondary); padding: 12px 16px; border-radius: var(--radius-md); margin-bottom: 16px; border: 1px solid var(--border-light); display: flex; justify-content: space-between; align-items: center;">
        <div>
          <div style="font-weight: 700; color: var(--text-primary); font-size: 14px;" id="workflowLeadName">Lead Name</div>
          <div style="font-size: 12px; color: var(--text-secondary);" id="workflowLeadContact">Phone / Email</div>
        </div>
        <div id="workflowLeadBadge"></div>
      </div>

      <!-- Stage Selector (All 12 Stages) -->
      <div class="form-group" style="margin-bottom: 14px;">
        <label class="form-label" style="font-weight: 600;"><i class="fas fa-toggle-on"></i> Target Pipeline Stage *</label>
        <select name="status" id="workflowStageSelect" class="form-select" onchange="onWorkflowStageSelectChange(this.value)" required>
          <?php foreach (leadStatusesByGroup() as $group_key => $group): ?>
            <optgroup label="<?= htmlspecialchars($group['label'], ENT_QUOTES, 'UTF-8') ?>">
            <?php foreach ($group['statuses'] as $s_key => $s_meta): ?>
              <option value="<?= htmlspecialchars($s_key, ENT_QUOTES, 'UTF-8') ?>"
                      data-description="<?= htmlspecialchars($s_meta['description'], ENT_QUOTES, 'UTF-8') ?>">
                <?= htmlspecialchars($s_meta['label'], ENT_QUOTES, 'UTF-8') ?>
              </option>
            <?php endforeach; ?>
            </optgroup>
          <?php endforeach; ?>
          <?php if (!empty($lead_legacy_options)): ?>
            <optgroup label="Legacy (Retired)">
            <?php foreach ($lead_legacy_options as $s_key => $s_meta): ?>
              <option value="<?= htmlspecialchars($s_key, ENT_QUOTES, 'UTF-8') ?>"
                      data-description="<?= htmlspecialchars($s_meta['description'], ENT_QUOTES, 'UTF-8') ?>">
                <?= htmlspecialchars($s_meta['label'], ENT_QUOTES, 'UTF-8') ?>
              </option>
            <?php endforeach; ?>
            </optgroup>
          <?php endif; ?>
        </select>
        <div class="form-hint" id="workflowStageHint" style="font-size: 11px; color: var(--text-secondary); margin-top: 4px;">
          <i class="fas fa-circle-info" style="color: var(--primary);"></i> <span id="workflowStageDesc">Stage description</span>
        </div>
      </div>

      <!-- Contextual Section: Converted Confirmation -->
      <div id="sectionConverted" class="stage-context-box" style="display: none; background: rgba(16, 185, 129, 0.08); border-color: rgba(16, 185, 129, 0.3);">
        <div style="font-weight: 700; color: #065f46; margin-bottom: 4px; display: flex; align-items: center; gap: 8px;">
          <i class="fas fa-circle-check" style="color: #10B981;"></i> Confirm Lead Conversion
        </div>
        <p style="font-size: 12px; color: #047857; margin-bottom: 0;">
          Customer agreed to proceed! Please confirm conversion and provide any project handover notes.
        </p>
      </div>

      <!-- Contextual Section: Lost Confirmation -->
      <div id="sectionLost" class="stage-context-box" style="display: none; background: rgba(239, 68, 68, 0.08); border-color: rgba(239, 68, 68, 0.3);">
        <div style="font-weight: 700; color: #991b1b; margin-bottom: 4px; display: flex; align-items: center; gap: 8px;">
          <i class="fas fa-circle-xmark" style="color: #EF4444;"></i> Confirm Lost / Not Interested
        </div>
        <p style="font-size: 12px; color: #b91c1c; margin-bottom: 8px;">
          Customer declined the offer. Please provide an optional reason for future reference.
        </p>
        <div class="form-group" style="margin-bottom: 0;">
          <label class="form-label" style="font-size: 12px;"><i class="fas fa-question-circle"></i> Reason for Declining</label>
          <input type="text" name="lost_reason" id="workflowLostReason" class="form-input" placeholder="e.g. Budget constraints, selected competitor, timing not right">
        </div>
      </div>

      <!-- Contextual Section: Follow-up Scheduling -->
      <div id="sectionFollowup" class="stage-context-box" style="display: none;">
        <div style="font-weight: 600; color: var(--text-primary); margin-bottom: 8px; display: flex; align-items: center; gap: 8px; font-size: 13px;">
          <i class="fas fa-clock" style="color: #F59E0B;"></i> Schedule Next Follow-up / Call
        </div>
        <div class="form-group" style="margin-bottom: 0;">
          <label class="form-label" style="font-size: 12px;">Next Follow-up Date & Time</label>
          <input type="datetime-local" name="follow_up_date" id="workflowFollowupDate" class="form-input">
        </div>
      </div>

      <!-- Contextual Section: Website URL -->
      <div id="sectionWebsite" class="stage-context-box" style="display: none;">
        <div class="form-group" style="margin-bottom: 0;">
          <label class="form-label" style="font-size: 12px;"><i class="fas fa-globe"></i> Customer Website URL</label>
          <input type="url" name="website" id="workflowWebsite" class="form-input" placeholder="https://www.example.com">
        </div>
      </div>

      <!-- Contextual Section: Proposal Reference -->
      <div id="sectionProposal" class="stage-context-box" style="display: none;">
        <div class="form-group" style="margin-bottom: 0;">
          <label class="form-label" style="font-size: 12px;"><i class="fas fa-file-invoice-dollar"></i> Proposal / Quote Reference</label>
          <input type="text" name="proposal_reference" id="workflowProposalRef" class="form-input" placeholder="e.g. PROP-2026-081 or quote link">
        </div>
      </div>

      <!-- Stage Notes -->
      <div class="form-group" style="margin-top: 14px;">
        <label class="form-label" style="font-size: 12px;"><i class="fas fa-clipboard-list"></i> Stage Notes / Remarks</label>
        <textarea name="stage_notes" id="workflowStageNotes" class="form-input" rows="3" placeholder="Add specific notes, customer feedback, or next steps..."></textarea>
      </div>

      <!-- Activity History Section (Section 9) -->
      <div style="margin-top: 16px;">
        <div style="font-weight: 600; font-size: 12px; color: var(--text-secondary); margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
          <i class="fas fa-history"></i> Stage Activity History
        </div>
        <div class="stage-history-timeline" id="workflowHistoryList">
          <div style="color: var(--text-tertiary); font-size: 12px; text-align: center; padding: 10px;">Loading history...</div>
        </div>
      </div>

      <div class="modal-footer" style="margin-top: 18px; display: flex; justify-content: flex-end; gap: 10px;">
        <button type="button" class="btn btn-secondary" onclick="closeStageModal()">Cancel</button>
        <button type="submit" class="btn btn-primary" id="btnSaveStageWorkflow">
          <i class="fas fa-save"></i> Save & Update Stage
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
  document.getElementById('leadWebsite').value = '';
  document.getElementById('leadFollowupDate').value = '';
  document.getElementById('leadMessage').value = '';
  document.getElementById('leadStatus').value = 'new_lead';
  updateStatusHint();
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
  document.getElementById('leadWebsite').value = lead.website || '';
  if (lead.follow_up_date) {
    const d = new Date(lead.follow_up_date.replace(' ', 'T'));
    if (!isNaN(d.getTime())) {
      const pad = n => String(n).padStart(2, '0');
      document.getElementById('leadFollowupDate').value = `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
    } else {
      document.getElementById('leadFollowupDate').value = '';
    }
  } else {
    document.getElementById('leadFollowupDate').value = '';
  }
  document.getElementById('leadMessage').value = lead.message || '';
  const statusSelect = document.getElementById('leadStatus');
  // Legacy values may not exist as an <option> — add one on the fly so the
  // stored value is always shown instead of the dropdown silently resetting.
  const currentStatus = lead.status || 'new_lead';
  if (!Array.from(statusSelect.options).some(o => o.value === currentStatus)) {
    const opt = document.createElement('option');
    opt.value = currentStatus;
    opt.textContent = currentStatus;
    opt.dataset.description = 'Legacy status — re-assign to a current stage';
    statusSelect.appendChild(opt);
  }
  statusSelect.value = currentStatus;
  updateStatusHint();
  document.getElementById('leadModal').classList.add('active');
}

// Show the description of the currently selected stage as helper text.
function updateStatusHint() {
  const select = document.getElementById('leadStatus');
  const hint = document.getElementById('leadStatusDescription');
  if (!select || !hint) return;
  const opt = select.options[select.selectedIndex];
  hint.textContent = (opt && opt.dataset.description) ? opt.dataset.description : '';
}

document.getElementById('leadStatus').addEventListener('change', updateStatusHint);

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

const LEAD_STAGES = <?php
  echo json_encode(array_map(static function ($meta) {
    return [
      'label' => $meta['label'],
      'badge' => $meta['badge'],
      'color' => $meta['color'],
    ];
  }, leadStatuses()), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>;

function renderKanbanBoard() {
  const columns = {};
  const counts = {};
  const legacyColumn = document.getElementById('cards-legacy');
  const hasLegacyColumn = legacyColumn !== null;

  Object.keys(LEAD_STAGES).forEach(status => {
    columns[status] = document.getElementById('cards-' + status);
    counts[status] = 0;
  });
  if (hasLegacyColumn) {
    columns['legacy'] = legacyColumn;
    counts['legacy'] = 0;
  }

  // Clear columns
  Object.keys(columns).forEach(status => {
    if (columns[status]) columns[status].innerHTML = '';
  });

  // Populate columns
  leadsData.forEach(lead => {
    const status = lead.status || 'new_lead';
    const column = columns[status] || (hasLegacyColumn ? columns['legacy'] : null);
    if (!column) return;
    counts[status in counts ? status : 'legacy']++;

    const stageMeta = LEAD_STAGES[status];
    const serviceName = lead.service ? lead.service : 'General Inquiry';
    const leadJson = JSON.stringify(lead).replace(/"/g, '&quot;');
    const stageBadge = stageMeta ? stageMeta.badge : 'badge-neutral';
    const stageLabel = stageMeta ? stageMeta.label : escapeHtml(status);

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
    <span class="badge-sm ${stageBadge}">${stageLabel}</span>
    <div style="display: flex; gap: var(--space-2);">
    <button class="btn btn-ghost btn-sm" onclick="editLead(${leadJson})" style="padding: var(--space-1);" title="Edit Lead">
    <i class="fas fa-edit" style="font-size: 10px;"></i>
    </button>
    </div>
    </div>
    </div>
  `;
    column.innerHTML += cardHTML;
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

  // Only the twelve approved stages are valid drop targets.
  if (!Object.prototype.hasOwnProperty.call(LEAD_STAGES, targetStatus)) {
  console.warn('Ignored drop onto non-current stage:', targetStatus);
  return;
  }

  const leadId = ev.dataTransfer.getData("leadId");

  const leadIndex = leadsData.findIndex(l => l.id == leadId);
  if (leadIndex !== -1 && leadsData[leadIndex].status !== targetStatus) {
  const previousStatus = leadsData[leadIndex].status;

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
  .then(res => res.json().then(data => ({ ok: res.ok, data })))
  .then(({ ok, data }) => {
  if (!ok || !data.success) {
    // Roll the card back so the board never disagrees with the database.
    console.error('Failed to sync updated status to database:', data && data.message);
    leadsData[leadIndex].status = previousStatus;
    renderKanbanBoard();
    alert('Could not update the lead status. Please try again.');
  }
  })
  .catch(err => {
  console.error('Network error during status update:', err);
  leadsData[leadIndex].status = previousStatus;
  renderKanbanBoard();
  alert('Network error while updating the lead status.');
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
// ── AJAX LEAD STATUS CHANGER ──
function changeLeadStatus(leadId, selectElement) {
  const newStatus = selectElement.value;
  const previousStatus = selectElement.dataset.currentStatus || '';
  if (!newStatus || newStatus === previousStatus) return;

  const spinner = document.getElementById(`lead-spinner-${leadId}`);
  const badge = document.getElementById(`lead-badge-${leadId}`);

  // Enter Loading State
  selectElement.disabled = true;
  if (spinner) spinner.style.display = 'inline-block';
  if (badge) badge.style.opacity = '0.5';

  const formData = new FormData();
  formData.append('action', 'update_status');
  formData.append('id', leadId);
  formData.append('status', newStatus);
  formData.append('csrf_token', '<?= $csrf_token ?>');

  fetch('leads.php', {
    method: 'POST',
    headers: {
      'X-Requested-With': 'XMLHttpRequest'
    },
    body: formData
  })
  .then(res => res.json().then(data => ({ ok: res.ok, status: res.status, data })))
  .then(({ ok, data }) => {
    if (!ok || !data.success) {
      throw new Error((data && data.message) ? data.message : 'Failed to update status.');
    }

    // Success: Update select current status
    selectElement.dataset.currentStatus = data.status;

    // Immediately Update Badge
    if (badge) {
      badge.className = `badge-sm ${data.badge || 'badge-neutral'} lead-status-badge`;
      badge.dataset.status = data.status;
      badge.title = data.label;
      badge.textContent = data.label;
      badge.style.opacity = '1';
    }

    // Synchronize leadsData for Kanban if populated
    if (typeof leadsData !== 'undefined' && Array.isArray(leadsData)) {
      const idx = leadsData.findIndex(l => l.id == leadId);
      if (idx !== -1) {
        leadsData[idx].status = data.status;
        const board = document.getElementById('leadsKanbanBoard');
        if (board && board.style.display !== 'none') {
          renderKanbanBoard();
        }
      }
    }

    showToast('success', 'Status Updated', data.message || `Lead status updated to ${data.label}`);
  })
  .catch(err => {
    console.error('Lead status update error:', err);
    // Revert select dropdown to previous status
    selectElement.value = previousStatus;
    if (badge) badge.style.opacity = '1';
    showToast('error', 'Update Failed', err.message || 'Could not update lead status.');
  })
  .finally(() => {
    selectElement.disabled = false;
    if (spinner) spinner.style.display = 'none';
  });
}

function focusStatusSelect(leadId) {
  const sel = document.getElementById(`status-select-${leadId}`);
  if (sel) {
    sel.focus();
    if (typeof sel.showPicker === 'function') {
      try { sel.showPicker(); } catch (e) {}
    }
  }
}

// ── ADVANCED STAGE WORKFLOW HANDLERS (SECTIONS 3, 4, 5, 9) ──
function onStageSelectChange(leadId, selectElement) {
  const newStatus = selectElement.value;
  const previousStatus = selectElement.dataset.currentStatus || '';
  if (!newStatus || newStatus === previousStatus) return;

  // Stages that benefit from contextual workflow modal (confirmation, follow-up date, website, proposal, notes)
  const needsWorkflowModal = [
    'converted', 'confirmed',
    'not_interested', 'lose',
    'call_not_answered', 'call_back_later',
    'followup_pending', 'meeting_scheduled',
    'already_has_website', 'website_improvement',
    'proposal_sent'
  ].includes(newStatus);

  if (needsWorkflowModal) {
    openStageModal(leadId, newStatus);
  } else {
    changeLeadStatus(leadId, selectElement);
  }
}

function openStageModal(leadId, targetStage = null) {
  const modal = document.getElementById('stageWorkflowModal');
  if (!modal) return;

  document.getElementById('workflowLeadId').value = leadId;
  document.getElementById('workflowLeadName').textContent = 'Loading lead details...';
  document.getElementById('workflowLeadContact').textContent = '';
  document.getElementById('workflowLeadBadge').innerHTML = '';
  document.getElementById('workflowHistoryList').innerHTML = '<div style="color: var(--text-tertiary); font-size: 12px; text-align: center; padding: 10px;"><i class="fas fa-spinner fa-spin"></i> Loading activity history...</div>';

  modal.style.display = 'flex';
  modal.classList.add('active');

  const formData = new FormData();
  formData.append('action', 'get_lead_details');
  formData.append('id', leadId);
  formData.append('csrf_token', '<?= $csrf_token ?>');

  fetch('leads.php', {
    method: 'POST',
    headers: { 'X-Requested-With': 'XMLHttpRequest' },
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    if (!data.success || !data.lead) {
      throw new Error(data.message || 'Could not load lead.');
    }
    const lead = data.lead;
    document.getElementById('workflowLeadName').textContent = lead.name || 'Unnamed Lead';
    document.getElementById('workflowLeadContact').textContent = `${lead.phone || ''} ${lead.email ? ' • ' + lead.email : ''}`;
    
    const meta = data.status_meta || {};
    document.getElementById('workflowLeadBadge').innerHTML = `
      <span class="badge-sm ${meta.badge || 'badge-neutral'}">${escapeHtml(meta.label || lead.status)}</span>
    `;

    const stageSelect = document.getElementById('workflowStageSelect');
    stageSelect.value = targetStage || lead.status;
    onWorkflowStageSelectChange(stageSelect.value);

    // Populate contextual inputs
    document.getElementById('workflowWebsite').value = lead.website || '';
    document.getElementById('workflowProposalRef').value = lead.proposal_reference || '';
    document.getElementById('workflowLostReason').value = lead.lost_reason || '';
    document.getElementById('workflowStageNotes').value = lead.stage_notes || '';

    if (lead.follow_up_date) {
      const d = new Date(lead.follow_up_date.replace(' ', 'T'));
      if (!isNaN(d.getTime())) {
        const pad = n => String(n).padStart(2, '0');
        document.getElementById('workflowFollowupDate').value = `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
      } else {
        document.getElementById('workflowFollowupDate').value = '';
      }
    } else {
      document.getElementById('workflowFollowupDate').value = '';
    }

    // Populate activity history (Section 9)
    const histBox = document.getElementById('workflowHistoryList');
    if (data.history && data.history.length > 0) {
      histBox.innerHTML = data.history.map(item => `
        <div class="stage-history-item">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2px;">
            <strong style="color: var(--text-primary); font-size: 11px;">${escapeHtml(item.admin_name || 'Admin')}</strong>
            <span style="color: var(--text-tertiary); font-size: 10px;">${escapeHtml(item.created_at)}</span>
          </div>
          <div style="color: var(--text-secondary); font-size: 11px;">${escapeHtml(item.details)}</div>
        </div>
      `).join('');
    } else {
      histBox.innerHTML = '<div style="color: var(--text-tertiary); font-size: 12px; text-align: center; padding: 10px;">No previous stage activities recorded.</div>';
    }
  })
  .catch(err => {
    console.error('Error fetching lead details:', err);
    showToast('error', 'Error', err.message || 'Could not load lead.');
  });
}

function closeStageModal() {
  const modal = document.getElementById('stageWorkflowModal');
  if (modal) {
    modal.style.display = 'none';
    modal.classList.remove('active');
  }
  // Revert select dropdown on table row if update was not saved
  const leadId = document.getElementById('workflowLeadId').value;
  if (leadId) {
    const sel = document.getElementById(`status-select-${leadId}`);
    if (sel && sel.dataset.currentStatus) {
      sel.value = sel.dataset.currentStatus;
    }
  }
}

function onWorkflowStageSelectChange(stage) {
  const descSpan = document.getElementById('workflowStageDesc');
  const sel = document.getElementById('workflowStageSelect');
  const opt = sel.options[sel.selectedIndex];
  if (descSpan && opt) {
    descSpan.textContent = opt.dataset.description || '';
  }

  // Hide all contextual sections first
  document.getElementById('sectionConverted').style.display = 'none';
  document.getElementById('sectionLost').style.display = 'none';
  document.getElementById('sectionFollowup').style.display = 'none';
  document.getElementById('sectionWebsite').style.display = 'none';
  document.getElementById('sectionProposal').style.display = 'none';

  if (stage === 'converted' || stage === 'confirmed') {
    document.getElementById('sectionConverted').style.display = 'block';
  } else if (stage === 'not_interested' || stage === 'lose') {
    document.getElementById('sectionLost').style.display = 'block';
  } else if (['call_not_answered', 'call_back_later', 'followup_pending', 'meeting_scheduled'].includes(stage)) {
    document.getElementById('sectionFollowup').style.display = 'block';
  } else if (['already_has_website', 'website_improvement'].includes(stage)) {
    document.getElementById('sectionWebsite').style.display = 'block';
  } else if (stage === 'proposal_sent') {
    document.getElementById('sectionProposal').style.display = 'block';
  }
}

function submitStageWorkflow(e) {
  e.preventDefault();
  const form = document.getElementById('stageWorkflowForm');
  const leadId = document.getElementById('workflowLeadId').value;
  const btn = document.getElementById('btnSaveStageWorkflow');

  btn.disabled = true;
  btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

  const formData = new FormData(form);

  fetch('leads.php', {
    method: 'POST',
    headers: { 'X-Requested-With': 'XMLHttpRequest' },
    body: formData
  })
  .then(res => res.json().then(data => ({ ok: res.ok, status: res.status, data })))
  .then(({ ok, data }) => {
    if (!ok || !data.success) {
      throw new Error((data && data.message) ? data.message : 'Failed to update stage.');
    }

    // Update table badge
    const badge = document.getElementById(`lead-badge-${leadId}`);
    if (badge) {
      badge.className = `badge-sm ${data.badge || 'badge-neutral'} lead-status-badge`;
      badge.dataset.status = data.status;
      badge.title = data.label;
      badge.textContent = data.label;
    }

    // Update select dropdown
    const sel = document.getElementById(`status-select-${leadId}`);
    if (sel) {
      sel.dataset.currentStatus = data.status;
      sel.value = data.status;
    }

    // Update follow-up indicator on table
    const cell = document.getElementById(`lead-status-cell-${leadId}`);
    let pill = document.getElementById(`lead-followup-pill-${leadId}`);
    if (data.follow_up_date) {
      const fTime = new Date(data.follow_up_date.replace(' ', 'T')).getTime();
      const isOverdue = fTime < Date.now() && !['converted', 'not_interested', 'lose'].includes(data.status);
      const pillHtml = `
        <div class="lead-followup-pill ${isOverdue ? 'followup-pill-overdue' : 'followup-pill-upcoming'}" 
             id="lead-followup-pill-${leadId}"
             onclick="openStageModal(${leadId})" 
             title="${isOverdue ? 'Overdue Follow-up!' : 'Scheduled Follow-up'}: ${data.follow_up_date}">
          <i class="fas ${isOverdue ? 'fa-triangle-exclamation' : 'fa-clock'}"></i>
          <span>${isOverdue ? 'Overdue' : 'Due'}: ${data.follow_up_date.split(' ')[0]}</span>
        </div>
      `;
      if (pill) {
        pill.outerHTML = pillHtml;
      } else if (cell) {
        cell.insertAdjacentHTML('beforeend', pillHtml);
      }
    } else if (pill) {
      pill.remove();
    }

    // Synchronize leadsData for Kanban
    if (typeof leadsData !== 'undefined' && Array.isArray(leadsData)) {
      const idx = leadsData.findIndex(l => l.id == leadId);
      if (idx !== -1) {
        leadsData[idx].status = data.status;
        leadsData[idx].follow_up_date = data.follow_up_date;
        leadsData[idx].stage_notes = data.stage_notes;
        const board = document.getElementById('leadsKanbanBoard');
        if (board && board.style.display !== 'none') {
          renderKanbanBoard();
        }
      }
    }

    showToast('success', 'Stage Updated', data.message || `Lead stage changed to ${data.label}`);
    closeStageModal();
  })
  .catch(err => {
    console.error('Workflow update error:', err);
    showToast('error', 'Update Failed', err.message || 'Could not update stage.');
  })
  .finally(() => {
    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-save"></i> Save & Update Stage';
  });
}


// ── TOAST NOTIFICATIONS ──
function showToast(type, title, message) {
  let container = document.getElementById('adminToastContainer');
  if (!container) {
    container = document.createElement('div');
    container.id = 'adminToastContainer';
    container.className = 'toast-container';
    document.body.appendChild(container);
  }

  const toast = document.createElement('div');
  const toastTypeClass = type === 'success' ? 'toast-success' : (type === 'error' ? 'toast-danger' : 'toast-info');
  toast.className = `toast ${toastTypeClass}`;
  
  const icon = type === 'success' ? 'fa-check' : (type === 'error' ? 'fa-exclamation-triangle' : 'fa-info');

  toast.innerHTML = `
    <div class="toast-icon"><i class="fas ${icon}"></i></div>
    <div class="toast-content">
      <div class="toast-title">${escapeHtml(title)}</div>
      <div class="toast-message">${escapeHtml(message)}</div>
    </div>
    <div class="toast-close" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></div>
  `;

  container.appendChild(toast);
  requestAnimationFrame(() => {
    toast.classList.add('show');
  });

  setTimeout(() => {
    toast.classList.remove('show');
    setTimeout(() => toast.remove(), 400);
  }, 4000);
}
</script>

<div class="toast-container" id="adminToastContainer"></div>

<?php require_once __DIR__ . '/../../../admin/includes/footer-pro.php'; ?>
