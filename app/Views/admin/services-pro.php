<?php
require_once __DIR__ . '/../../../admin/includes/header-pro.php';

$page_title = 'Services Management';
/** @var array $services */
/** @var array $available_icons */
/** @var string $csrf_token */
/** @var string $message */
/** @var string $message_type */

$services = $services ?? [];
$available_icons = $available_icons ?? [];
$csrf_token = $csrf_token ?? '';
$message = $message ?? '';
$message_type = $message_type ?? '';
?>

<style>
.icon-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(48px, 1fr));
  gap: var(--space-4);
  margin-top: var(--space-3);
  max-height: 170px;
  overflow-y: auto;
  padding: 4px;
}
.icon-option {
  height: 48px;
  border: 1px solid var(--border-light);
  border-radius: var(--radius-md);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all var(--duration-fast) var(--ease-out);
  background: var(--bg-surface);
}
.icon-option:hover {
  transform: translateY(-2px);
  border-color: var(--primary);
  box-shadow: var(--shadow-sm);
}
.icon-option.selected {
  border-color: var(--primary);
  background: var(--primary-soft);
  box-shadow: var(--shadow-sm);
}
.icon-option i {
  font-size: 20px;
  color: var(--primary);
}

.service-icon {
  width: 44px;
  height: 44px;
  background: var(--primary-soft);
  border-radius: var(--radius-md);
  display: flex;
  align-items: center;
  justify-content: center;
  border: 1px solid var(--primary-shadow);
  flex-shrink: 0;
}
.service-icon i {
  font-size: 20px;
  color: var(--primary);
}

.service-name {
  font-weight: 600;
  color: var(--text-primary);
  font-size: var(--text-sm);
}
.service-slug {
  font-size: var(--text-xs);
  color: var(--text-secondary);
  font-family: monospace;
  margin-top: var(--space-1);
}

.order-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 28px;
  height: 28px;
  background: var(--bg-surface-secondary);
  border: 1px solid var(--border-light);
  border-radius: 6px;
  font-weight: 700;
  font-size: 13px;
  color: var(--text-primary);
}
.order-btn {
  background: none;
  border: 1px solid var(--border-light);
  border-radius: 4px;
  color: var(--text-secondary);
  cursor: pointer;
  padding: 2px 6px;
  font-size: 11px;
  line-height: 1;
  transition: all 0.2s ease;
}
.order-btn:hover {
  background: var(--primary-soft);
  color: var(--primary);
  border-color: var(--primary);
}

.filter-toolbar {
  display: flex;
  gap: 12px;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  margin-bottom: var(--space-4);
  background: var(--bg-surface);
  border: 1px solid var(--border-light);
  border-radius: var(--radius-md);
  padding: 12px 16px;
}
.filter-search-box {
  position: relative;
  flex: 1;
  min-width: 240px;
}
.filter-search-box i {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: var(--text-tertiary);
  font-size: 13px;
}
.filter-search-box input {
  padding-left: 36px !important;
  width: 100%;
}
</style>

<!-- Page Header -->
<div class="flex items-center justify-between mb-6" style="flex-wrap: wrap; gap: 12px;">
  <div>
    <h1 style="font-size: var(--text-2xl); font-weight: 600; letter-spacing: -0.02em; color: var(--text-primary);">
      Services Management
    </h1>
    <p class="text-sm text-muted" style="margin-top: var(--space-1);">
      Create, reorder, and configure public services, hero visual blocks, custom buttons, deliverables, and SEO tags.
    </p>
  </div>
  <div style="display: flex; gap: 8px;">
    <a href="../services" target="_blank" class="btn btn-secondary" title="View Public Services Page">
      <i class="fas fa-external-link-alt"></i> View Services Page
    </a>
    <button class="btn btn-primary" onclick="openModal()">
      <i class="fas fa-plus"></i> Add New Service
    </button>
  </div>
</div>

<?php if ($message): ?>
  <div class="alert alert-<?= $message_type ?>">
    <i class="fas fa-<?= $message_type === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
    <?= htmlspecialchars($message) ?>
  </div>
<?php endif; ?>

<!-- Filter & Search Toolbar -->
<div class="filter-toolbar">
  <div class="filter-search-box">
    <i class="fas fa-search"></i>
    <input type="text" id="serviceSearchInput" class="form-input" placeholder="Search services by title, slug, or description..." oninput="filterServicesTable()">
  </div>
  
  <div style="display: flex; gap: 10px; align-items: center;">
    <select id="serviceStatusFilter" class="form-select" style="min-width: 160px; padding: 7px 12px;" onchange="filterServicesTable()">
      <option value="all">All Statuses (All)</option>
      <option value="active">Active (Published)</option>
      <option value="inactive">Inactive (Draft)</option>
    </select>

    <button type="button" class="btn btn-ghost btn-sm" onclick="resetServiceFilters()" title="Reset Filters" style="padding: 7px 12px;">
      <i class="fas fa-rotate-left"></i> Reset
    </button>

    <div style="font-size: 13px; color: var(--text-secondary); font-weight: 600; white-space: nowrap;">
      <span id="visibleCountText"><?= count($services) ?></span> / <?= count($services) ?> Services
    </div>
  </div>
</div>

<!-- Bulk Actions Toolbar -->
<div id="bulkActionsBar" style="display: none; align-items: center; justify-content: space-between; background: var(--primary-soft, rgba(91,53,232,0.08)); border: 1px solid var(--primary, #5B35E8); border-radius: var(--radius-md, 12px); padding: 12px 18px; margin-bottom: var(--space-4, 16px); animation: fadeIn 0.2s ease;">
  <div style="display: flex; align-items: center; gap: 12px; font-size: var(--text-sm, 14px); font-weight: 600; color: var(--text-primary);">
    <i class="fas fa-check-circle" style="color: var(--primary); font-size: 16px;"></i>
    <span id="selectedCountText">0 services selected</span>
  </div>
  <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
    <button type="button" class="btn btn-sm" style="background: #10B981; color: #fff; border: none; padding: 6px 14px; border-radius: 8px; font-weight: 600;" onclick="submitBulkAction('bulk_activate')">
      <i class="fas fa-eye"></i> Activate Selected
    </button>
    <button type="button" class="btn btn-sm" style="background: #6B7280; color: #fff; border: none; padding: 6px 14px; border-radius: 8px; font-weight: 600;" onclick="submitBulkAction('bulk_deactivate')">
      <i class="fas fa-eye-slash"></i> Deactivate Selected
    </button>
    <button type="button" class="btn btn-sm" style="background: #EF4444; color: #fff; border: none; padding: 6px 14px; border-radius: 8px; font-weight: 600;" onclick="submitBulkAction('bulk_delete')">
      <i class="fas fa-trash"></i> Delete Selected
    </button>
    <button type="button" class="btn btn-ghost btn-sm" onclick="clearAllSelections()" title="Deselect all" style="padding: 6px 10px;">
      <i class="fas fa-times"></i> Clear
    </button>
  </div>
</div>

<!-- Hidden Bulk Action Form -->
<form id="bulkActionForm" method="POST" action="services.php" style="display: none;">
  <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
  <input type="hidden" name="action" id="bulkActionType" value="">
  <div id="bulkIdInputs"></div>
</form>

<!-- Services Table Card -->
<div class="card">
  <div class="table-container">
    <table style="min-width: auto;" id="servicesMasterTable">
      <thead>
        <tr>
          <th style="width: 44px; text-align: center;">
            <input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll(this)" title="Select all services" style="cursor: pointer; width: 16px; height: 16px; accent-color: var(--primary);">
          </th>
          <th style="width: 90px; text-align: center;">Order</th>
          <th style="width: 60px;">Visual</th>
          <th>Service Details</th>
          <th>Summary & Description</th>
          <th style="width: 100px;">Status</th>
          <th style="text-align: right; width: 170px;">Actions</th>
        </tr>
      </thead>
      <tbody id="servicesTableBody">
      <?php foreach ($services as $index => $service): ?>
        <tr id="service-row-<?= $service['id'] ?>" class="service-row-item" data-status="<?= htmlspecialchars($service['status'] ?? 'active') ?>" data-id="<?= $service['id'] ?>" data-order="<?= (int)($service['sort_order'] ?? 0) ?>">
          <td style="text-align: center;">
            <input type="checkbox" value="<?= $service['id'] ?>" class="service-checkbox" onchange="updateBulkBar()" style="cursor: pointer; width: 16px; height: 16px; accent-color: var(--primary);">
          </td>
          
          <!-- Ordering Controls -->
          <td style="text-align: center;">
            <div style="display: inline-flex; align-items: center; gap: 4px;">
              <div style="display: flex; flex-direction: column; gap: 2px;">
                <button type="button" class="order-btn" title="Move Up" onclick="moveServiceOrder(<?= $service['id'] ?>, -1)" <?= $index === 0 ? 'disabled style="opacity:0.3; cursor:not-allowed;"' : '' ?>>
                  <i class="fas fa-chevron-up"></i>
                </button>
                <button type="button" class="order-btn" title="Move Down" onclick="moveServiceOrder(<?= $service['id'] ?>, 1)" <?= $index === count($services) - 1 ? 'disabled style="opacity:0.3; cursor:not-allowed;"' : '' ?>>
                  <i class="fas fa-chevron-down"></i>
                </button>
              </div>
              <span class="order-badge" title="Display Order"><?= (int)($service['sort_order'] ?? 0) ?></span>
            </div>
          </td>

          <!-- Visual / Thumbnail -->
          <td>
            <div style="display: flex; align-items: center; gap: 10px;">
              <?php 
              $thumb = '';
              if (!empty($service['image'])) {
                  $thumb = (str_starts_with($service['image'], 'http://') || str_starts_with($service['image'], 'https://') || str_starts_with($service['image'], '/')) ? $service['image'] : '../' . $service['image'];
              }
              ?>
              <?php if ($thumb): ?>
                <img src="<?= htmlspecialchars($thumb) ?>" alt="<?= htmlspecialchars($service['service_name']) ?>" style="width: 44px; height: 44px; border-radius: var(--radius-md); object-fit: cover; border: 1px solid var(--border-light);" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                <div class="service-icon" style="display: none;"><i class="fas <?= htmlspecialchars($service['icon'] ?: 'fa-briefcase') ?>"></i></div>
              <?php else: ?>
                <div class="service-icon"><i class="fas <?= htmlspecialchars($service['icon'] ?: 'fa-briefcase') ?>"></i></div>
              <?php endif; ?>
            </div>
          </td>

          <!-- Service Details -->
          <td>
            <div class="service-name"><?= htmlspecialchars($service['service_name']) ?></div>
            <div class="service-slug">/service/<?= htmlspecialchars($service['slug']) ?></div>
            <?php if (!empty($service['hero_heading'])): ?>
              <div style="font-size: 11px; color: var(--text-tertiary); margin-top: 2px;">
                <i class="fas fa-heading" style="font-size: 10px;"></i> <?= htmlspecialchars(mb_strimwidth($service['hero_heading'], 0, 45, '...')) ?>
              </div>
            <?php endif; ?>
          </td>

          <!-- Description -->
          <td>
            <div style="color: var(--text-secondary); font-size: var(--text-sm); max-width: 280px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?= htmlspecialchars($service['short_description'] ?? '') ?>">
              <?= htmlspecialchars($service['short_description'] ?? '') ?>
            </div>
          </td>

          <!-- Status -->
          <td>
            <?php $status_class = $service['status'] === 'active' ? 'badge-success' : 'badge-neutral'; ?>
            <span class="badge-sm <?= $status_class ?>">
              <i class="fas fa-circle" style="font-size: 8px; margin-right: 4px;"></i>
              <?= $service['status'] === 'active' ? 'Published' : 'Draft' ?>
            </span>
          </td>

          <!-- Actions -->
          <td style="text-align: right;">
            <div style="display: flex; gap: var(--space-2); justify-content: flex-end;">
              
              <!-- Live Preview Action -->
              <?php 
              $preview_url = ($service['status'] === 'active') 
                  ? '../service/' . htmlspecialchars($service['slug']) 
                  : '../service/' . htmlspecialchars($service['slug']) . '?preview=1';
              $preview_title = ($service['status'] === 'active') ? 'View Live Page' : 'Preview Draft Page';
              ?>
              <a href="<?= $preview_url ?>" target="_blank" class="btn btn-ghost btn-sm" title="<?= $preview_title ?>">
                <i class="fas fa-arrow-up-right-from-square" style="color: var(--primary);"></i>
              </a>

              <!-- Edit Action -->
              <button class="btn btn-ghost btn-sm" onclick="editService(<?= htmlspecialchars(json_encode($service)) ?>)" title="Edit Service">
                <i class="fas fa-edit"></i>
              </button>

              <!-- Toggle Visibility Action -->
              <form method="POST" action="services.php" style="display:inline;">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <input type="hidden" name="action" value="toggle">
                <input type="hidden" name="id" value="<?= $service['id'] ?>">
                <button type="submit" class="btn btn-ghost btn-sm" title="<?= $service['status'] === 'active' ? 'Unpublish (Switch to Draft)' : 'Publish Service' ?>">
                  <i class="fas fa-<?= $service['status'] === 'active' ? 'eye-slash' : 'eye' ?>" style="color: <?= $service['status'] === 'active' ? 'var(--text-secondary)' : '#10B981' ?>;"></i>
                </button>
              </form>

              <!-- Delete Action with Modal Dialog -->
              <button type="button" class="btn btn-ghost btn-sm" style="color: var(--danger);" title="Delete Service" onclick="confirmDeleteService(<?= $service['id'] ?>, '<?= addslashes(htmlspecialchars($service['service_name'])) ?>')">
                <i class="fas fa-trash"></i>
              </button>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>

      <?php if (empty($services)): ?>
        <tr id="noServicesNotice">
          <td colspan="7" style="text-align:center; padding:var(--space-10) var(--space-4); border-bottom: none;">
            <div style="width: 48px; height: 48px; background: var(--bg-surface-secondary); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto var(--space-4);">
              <i class="fas fa-briefcase" style="font-size: 20px; color: var(--text-tertiary);"></i>
            </div>
            <h3 style="font-size: var(--text-base); font-weight: 600; color: var(--text-primary); margin-bottom: var(--space-2);">
              No services found
            </h3>
            <p style="color: var(--text-secondary); font-size: var(--text-sm); margin-bottom: var(--space-4);">
              Add your first service offering to display it across the website and navigation dropdowns.
            </p>
            <button class="btn btn-primary btn-sm" onclick="openModal()"><i class="fas fa-plus"></i> Add First Service</button>
          </td>
        </tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- ========================================== -->
<!-- COMPREHENSIVE SERVICE MODAL (ADD / EDIT)  -->
<!-- ========================================== -->
<div class="modal" id="serviceModal">
  <div class="modal-backdrop" onclick="closeModal()"></div>
  <div class="modal-content" style="max-width: 860px; max-height: 92vh; display: flex; flex-direction: column;">
    <div class="modal-header">
      <h3 class="modal-title"><i class="fas fa-briefcase"></i> <span id="modalTitle">Add New Service</span></h3>
      <button class="modal-close" onclick="closeModal()">
        <i class="fas fa-times"></i>
      </button>
    </div>

    <form method="POST" action="services.php" enctype="multipart/form-data" style="display: flex; flex-direction: column; flex: 1; overflow: hidden;">
      <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
      <input type="hidden" name="action" id="formAction" value="create">
      <input type="hidden" name="id" id="serviceId">
      <input type="hidden" name="existing_image" id="serviceExistingImage">
      <input type="hidden" name="existing_hero_bg_image" id="serviceExistingHeroBg">
      <input type="hidden" name="existing_og_image" id="serviceExistingOgImage">

      <!-- Navigation Tabs -->
      <div style="display: flex; gap: 8px; border-bottom: 1px solid var(--border-light); padding: 10px 18px 8px; background: var(--bg-surface-secondary); overflow-x: auto; flex-shrink: 0;">
        <button type="button" class="btn btn-sm svc-tab-btn active" id="tabBtn-general" onclick="switchServiceTab('general')" style="border-radius: 20px;">
          <i class="fas fa-info-circle"></i> General & Info
        </button>
        <button type="button" class="btn btn-sm btn-ghost svc-tab-btn" id="tabBtn-hero" onclick="switchServiceTab('hero')" style="border-radius: 20px;">
          <i class="fas fa-wand-magic-sparkles"></i> Hero & Buttons
        </button>
        <button type="button" class="btn btn-sm btn-ghost svc-tab-btn" id="tabBtn-deliverables" onclick="switchServiceTab('deliverables')" style="border-radius: 20px;">
          <i class="fas fa-layer-group"></i> Deliverables
        </button>
        <button type="button" class="btn btn-sm btn-ghost svc-tab-btn" id="tabBtn-workflow" onclick="switchServiceTab('workflow')" style="border-radius: 20px;">
          <i class="fas fa-route"></i> Benefits & Workflow
        </button>
        <button type="button" class="btn btn-sm btn-ghost svc-tab-btn" id="tabBtn-faqs" onclick="switchServiceTab('faqs')" style="border-radius: 20px;">
          <i class="fas fa-question-circle"></i> FAQs
        </button>
        <button type="button" class="btn btn-sm btn-ghost svc-tab-btn" id="tabBtn-seo" onclick="switchServiceTab('seo')" style="border-radius: 20px;">
          <i class="fas fa-magnifying-glass-chart"></i> SEO & Social
        </button>
      </div>

      <!-- Tab Content Body -->
      <div style="overflow-y: auto; padding: 20px; flex: 1;">

        <!-- ===================================== -->
        <!-- TAB 1: GENERAL INFO & CARDS          -->
        <!-- ===================================== -->
        <div id="tabContent-general" class="svc-tab-pane">
          <div class="form-row cols-2">
            <div class="form-group">
              <label class="form-label"><i class="fas fa-tag"></i> Service Name *</label>
              <input type="text" name="service_name" id="serviceName" required class="form-input" placeholder="e.g. SEO Optimization" oninput="autoGenerateSlug(this.value)">
            </div>
            <div class="form-group">
              <label class="form-label"><i class="fas fa-link"></i> URL Slug *</label>
              <input type="text" name="slug" id="serviceSlug" required class="form-input" placeholder="e.g. seo-optimization">
              <div style="font-size: 11px; color: var(--text-secondary); margin-top: 4px;">Public URL: <code>/service/{slug}</code></div>
            </div>
          </div>

          <div class="form-row cols-2">
            <div class="form-group">
              <label class="form-label"><i class="fas fa-sort-numeric-down"></i> Display / Sort Order</label>
              <input type="number" name="sort_order" id="serviceSortOrder" class="form-input" placeholder="1" min="0" step="1">
              <div style="font-size: 11px; color: var(--text-secondary); margin-top: 4px;">Controls the position in dropdowns and listings (Lowest first).</div>
            </div>
            <div class="form-group">
              <label class="form-label"><i class="fas fa-toggle-on"></i> Publication Status</label>
              <select name="status" id="serviceStatus" class="form-select">
                <option value="active">Published (Active on website & dropdown)</option>
                <option value="inactive">Draft (Hidden from public)</option>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label"><i class="fas fa-align-left"></i> Short Description *</label>
            <textarea name="short_description" id="serviceShortDesc" required class="form-textarea" style="min-height: 65px;" placeholder="Brief summary of service for cards and meta snippets..."></textarea>
          </div>

          <div class="form-group">
            <label class="form-label"><i class="fas fa-file-alt"></i> Full Service Description</label>
            <textarea name="full_description" id="serviceFullDesc" class="form-textarea" style="min-height: 85px;" placeholder="Detailed service overview, methodology, and value proposition..."></textarea>
          </div>

          <div class="form-group">
            <label class="form-label"><i class="fas fa-icons"></i> Select Service Icon</label>
            <div class="icon-grid">
              <?php foreach ($available_icons as $icon => $name): ?>
                <div class="icon-option" data-icon="<?= $icon ?>" onclick="selectIcon(this)" title="<?= $name ?>"><i class="fas <?= $icon ?>"></i></div>
              <?php endforeach; ?>
            </div>
            <input type="hidden" name="icon" id="selectedIcon">
          </div>

          <!-- Primary Service Card Image Upload -->
          <div class="form-group">
            <label class="form-label"><i class="fas fa-image"></i> Primary Service Card Image</label>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; align-items: start;">
              <div style="border: 2px dashed var(--border-light); border-radius: var(--radius-md); padding: 14px; text-align: center; background: var(--bg-surface-secondary); cursor: pointer;" onclick="document.getElementById('serviceImageFile').click()">
                <i class="fas fa-cloud-arrow-up" style="font-size: 24px; color: var(--primary); margin-bottom: 6px;"></i>
                <div style="font-size: 12px; font-weight: 600; color: var(--text-primary);">Upload Card Image</div>
                <div style="font-size: 11px; color: var(--text-secondary); margin-top: 2px;">JPG, PNG, WEBP (Max 5MB)</div>
                <input type="file" name="image_file" id="serviceImageFile" accept=".jpg,.jpeg,.png,.gif,.webp" style="display: none;" onchange="previewImageFile(this, 'serviceImgPreview', 'serviceImgEmptyText', 'serviceImgClearBtn')">
              </div>

              <div id="serviceImgPreviewContainer" style="border: 1px solid var(--border-light); border-radius: var(--radius-md); padding: 10px; min-height: 90px; display: flex; flex-direction: column; align-items: center; justify-content: center; background: var(--bg-surface); text-align: center;">
                <img id="serviceImgPreview" src="" alt="Preview" style="max-height: 75px; max-width: 100%; border-radius: var(--radius-sm); object-fit: cover; display: none; margin-bottom: 6px;">
                <div id="serviceImgEmptyText" style="font-size: 12px; color: var(--text-tertiary);">
                  <i class="fas fa-image" style="font-size: 20px; display: block; margin-bottom: 4px;"></i>
                  No Card Image Selected
                </div>
                <button type="button" id="serviceImgClearBtn" class="btn btn-ghost btn-sm" style="display: none; color: var(--danger); font-size: 11px; padding: 2px 8px;" onclick="clearImageSlot('serviceImageFile', 'serviceImage', 'serviceExistingImage', 'serviceImgPreview', 'serviceImgEmptyText', 'serviceImgClearBtn')">
                  <i class="fas fa-trash-alt"></i> Remove
                </button>
              </div>
            </div>
            <div style="margin-top: 8px;">
              <input type="text" name="image" id="serviceImage" placeholder="Or enter image URL / uploads/services/..." class="form-input" style="font-size: 12px; padding: 6px 10px;" oninput="previewUrlSlot(this.value, 'serviceImgPreview', 'serviceImgEmptyText', 'serviceImgClearBtn')">
            </div>
          </div>
        </div>

        <!-- ===================================== -->
        <!-- TAB 2: HERO HEADING, BG & CTA BUTTONS -->
        <!-- ===================================== -->
        <div id="tabContent-hero" class="svc-tab-pane" style="display: none;">
          <div class="form-group">
            <label class="form-label"><i class="fas fa-heading"></i> Hero Custom Heading</label>
            <input type="text" name="hero_heading" id="serviceHeroHeading" class="form-input" placeholder="e.g. Scalable & Revenue-Driven SEO in Pondicherry">
            <div style="font-size: 11px; color: var(--text-secondary); margin-top: 4px;">Overrides the default hero header. Leave blank to automatically use the Service Name.</div>
          </div>

          <div class="form-group">
            <label class="form-label"><i class="fas fa-highlighter"></i> Highlighted Words in Hero Heading</label>
            <input type="text" name="hero_highlight" id="serviceHeroHighlight" class="form-input" placeholder="e.g. SEO in Pondicherry">
            <div style="font-size: 11px; color: var(--text-secondary); margin-top: 4px;">The specified phrase will be styled with the vibrant accent brand color.</div>
          </div>

          <!-- Hero Background Image Upload -->
          <div class="form-group">
            <label class="form-label"><i class="fas fa-panorama"></i> Hero Background Visual / Image</label>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; align-items: start;">
              <div style="border: 2px dashed var(--border-light); border-radius: var(--radius-md); padding: 14px; text-align: center; background: var(--bg-surface-secondary); cursor: pointer;" onclick="document.getElementById('serviceHeroBgFile').click()">
                <i class="fas fa-cloud-arrow-up" style="font-size: 24px; color: var(--primary); margin-bottom: 6px;"></i>
                <div style="font-size: 12px; font-weight: 600; color: var(--text-primary);">Upload Hero BG Visual</div>
                <div style="font-size: 11px; color: var(--text-secondary); margin-top: 2px;">High-res banner (Max 5MB)</div>
                <input type="file" name="hero_bg_file" id="serviceHeroBgFile" accept=".jpg,.jpeg,.png,.gif,.webp" style="display: none;" onchange="previewImageFile(this, 'heroBgPreview', 'heroBgEmptyText', 'heroBgClearBtn')">
              </div>

              <div id="heroBgPreviewContainer" style="border: 1px solid var(--border-light); border-radius: var(--radius-md); padding: 10px; min-height: 90px; display: flex; flex-direction: column; align-items: center; justify-content: center; background: var(--bg-surface); text-align: center;">
                <img id="heroBgPreview" src="" alt="Preview" style="max-height: 75px; max-width: 100%; border-radius: var(--radius-sm); object-fit: cover; display: none; margin-bottom: 6px;">
                <div id="heroBgEmptyText" style="font-size: 12px; color: var(--text-tertiary);">
                  <i class="fas fa-panorama" style="font-size: 20px; display: block; margin-bottom: 4px;"></i>
                  Using default hero gradient / card image
                </div>
                <button type="button" id="heroBgClearBtn" class="btn btn-ghost btn-sm" style="display: none; color: var(--danger); font-size: 11px; padding: 2px 8px;" onclick="clearImageSlot('serviceHeroBgFile', 'serviceHeroBgImage', 'serviceExistingHeroBg', 'heroBgPreview', 'heroBgEmptyText', 'heroBgClearBtn')">
                  <i class="fas fa-trash-alt"></i> Remove
                </button>
              </div>
            </div>
            <div style="margin-top: 8px;">
              <input type="text" name="hero_bg_image" id="serviceHeroBgImage" placeholder="Or enter custom hero banner URL / path" class="form-input" style="font-size: 12px; padding: 6px 10px;" oninput="previewUrlSlot(this.value, 'heroBgPreview', 'heroBgEmptyText', 'heroBgClearBtn')">
            </div>
          </div>

          <!-- Button Configuration -->
          <div style="border-top: 1px solid var(--border-light); padding-top: 16px; margin-top: 16px;">
            <div style="font-weight: 700; font-size: 13px; color: var(--text-primary); margin-bottom: 12px;">
              <i class="fas fa-circle-play" style="color: var(--primary);"></i> Action Buttons Configuration
            </div>

            <div class="form-row cols-2">
              <div class="form-group">
                <label class="form-label">Primary Button Text (Learn More)</label>
                <input type="text" name="learn_more_text" id="serviceLearnMoreText" class="form-input" placeholder="Learn More">
              </div>
              <div class="form-group">
                <label class="form-label">Primary Button Destination URL</label>
                <input type="text" name="learn_more_url" id="serviceLearnMoreUrl" class="form-input" placeholder="#cdm-deliverables">
              </div>
            </div>

            <div class="form-row cols-2">
              <div class="form-group">
                <label class="form-label">Secondary Button Text (Explore Service)</label>
                <input type="text" name="explore_btn_text" id="serviceExploreBtnText" class="form-input" placeholder="Explore Service">
              </div>
              <div class="form-group">
                <label class="form-label">Secondary Button Destination URL</label>
                <input type="text" name="explore_btn_url" id="serviceExploreBtnUrl" class="form-input" placeholder="#cdm-quote-card">
              </div>
            </div>
          </div>
        </div>

        <!-- ===================================== -->
        <!-- TAB 3: DELIVERABLES & FEATURES       -->
        <!-- ===================================== -->
        <div id="tabContent-deliverables" class="svc-tab-pane" style="display: none;">
          <div class="form-group">
            <label class="form-label"><i class="fas fa-list-check"></i> Deliverables & Core Features</label>
            <p style="font-size: 12px; color: var(--text-secondary); margin-bottom: 8px;">
              Enter each deliverable on a new line in <code>Title: Short description</code> format, or simply <code>Title</code>.
            </p>
            <textarea name="features" id="serviceFeatures" class="form-textarea" style="min-height: 240px; font-family: monospace; font-size: 13px;" placeholder="Comprehensive Keyword Research: Detailed search intent clustering&#10;On-Page Architecture: Meta tags, headings, schema & internal linking&#10;Technical Site Audits: Core Web Vitals, crawl budget & indexation fixes"></textarea>
          </div>
        </div>

        <!-- ===================================== -->
        <!-- TAB 4: BENEFITS & WORKFLOW           -->
        <!-- ===================================== -->
        <div id="tabContent-workflow" class="svc-tab-pane" style="display: none;">
          <div class="form-group">
            <label class="form-label"><i class="fas fa-award"></i> Key Business Benefits & Outcomes</label>
            <p style="font-size: 12px; color: var(--text-secondary); margin-bottom: 8px;">
              Enter one measurable benefit per line (e.g. <code>+140% Average Organic Traffic Increase</code>).
            </p>
            <textarea name="benefits" id="serviceBenefits" class="form-textarea" style="min-height: 120px; font-size: 13px;" placeholder="Dominant Search Rankings for High-Intent Commercial Keywords&#10;Sustainable Compounding Qualified Inquiries&#10;Higher Conversion Rates & Lower Acquisition Costs"></textarea>
          </div>

          <div style="margin-top: 24px; border-top: 1px solid var(--border-light); padding-top: 16px;">
            <label class="form-label" style="font-size: 13.5px; font-weight: 700; margin-bottom: 12px;"><i class="fas fa-route"></i> 4-Step Strategic Workflow Roadmap</label>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
              <!-- Step 1 -->
              <div style="background: var(--bg-surface-secondary); padding: 12px; border-radius: var(--radius-md); border: 1px solid var(--border-light);">
                <div style="font-weight: 700; font-size: 11px; color: var(--primary); margin-bottom: 6px;">STEP 01</div>
                <input type="text" name="step_title[]" id="stepTitle1" class="form-input" placeholder="Discovery & Audit" style="margin-bottom: 6px;">
                <textarea name="step_desc[]" id="stepDesc1" class="form-textarea" style="min-height: 50px; font-size: 12px;" placeholder="Step 1 description..."></textarea>
              </div>
              <!-- Step 2 -->
              <div style="background: var(--bg-surface-secondary); padding: 12px; border-radius: var(--radius-md); border: 1px solid var(--border-light);">
                <div style="font-weight: 700; font-size: 11px; color: var(--primary); margin-bottom: 6px;">STEP 02</div>
                <input type="text" name="step_title[]" id="stepTitle2" class="form-input" placeholder="Strategic Architecture" style="margin-bottom: 6px;">
                <textarea name="step_desc[]" id="stepDesc2" class="form-textarea" style="min-height: 50px; font-size: 12px;" placeholder="Step 2 description..."></textarea>
              </div>
              <!-- Step 3 -->
              <div style="background: var(--bg-surface-secondary); padding: 12px; border-radius: var(--radius-md); border: 1px solid var(--border-light);">
                <div style="font-weight: 700; font-size: 11px; color: var(--primary); margin-bottom: 6px;">STEP 03</div>
                <input type="text" name="step_title[]" id="stepTitle3" class="form-input" placeholder="Campaign Execution" style="margin-bottom: 6px;">
                <textarea name="step_desc[]" id="stepDesc3" class="form-textarea" style="min-height: 50px; font-size: 12px;" placeholder="Step 3 description..."></textarea>
              </div>
              <!-- Step 4 -->
              <div style="background: var(--bg-surface-secondary); padding: 12px; border-radius: var(--radius-md); border: 1px solid var(--border-light);">
                <div style="font-weight: 700; font-size: 11px; color: var(--primary); margin-bottom: 6px;">STEP 04</div>
                <input type="text" name="step_title[]" id="stepTitle4" class="form-input" placeholder="Optimization & Scaling" style="margin-bottom: 6px;">
                <textarea name="step_desc[]" id="stepDesc4" class="form-textarea" style="min-height: 50px; font-size: 12px;" placeholder="Step 4 description..."></textarea>
              </div>
            </div>
          </div>
        </div>

        <!-- ===================================== -->
        <!-- TAB 5: SERVICE FAQS                  -->
        <!-- ===================================== -->
        <div id="tabContent-faqs" class="svc-tab-pane" style="display: none;">
          <p style="font-size: 12px; color: var(--text-secondary); margin-bottom: 14px;">
            Frequently Asked Questions specific to this service. These render as an interactive accordion and automatically generate Google FAQ Schema structured data.
          </p>
          <?php for ($i = 1; $i <= 3; $i++): ?>
          <div style="background: var(--bg-surface-secondary); padding: 12px; border-radius: var(--radius-md); border: 1px solid var(--border-light); margin-bottom: 12px;">
            <label class="form-label" style="font-size: 12px; font-weight: 700;"><i class="fas fa-question-circle"></i> Question <?= $i ?></label>
            <input type="text" name="faq_q[]" id="faqQ<?= $i ?>" class="form-input" placeholder="e.g. How long does it take to see results?" style="margin-bottom: 8px;">
            <label class="form-label" style="font-size: 12px; font-weight: 700;"><i class="fas fa-comment-dots"></i> Answer <?= $i ?></label>
            <textarea name="faq_a[]" id="faqA<?= $i ?>" class="form-textarea" style="min-height: 55px; font-size: 12px;" placeholder="Detailed response..."></textarea>
          </div>
          <?php endfor; ?>
        </div>

        <!-- ===================================== -->
        <!-- TAB 6: SEO & SOCIAL METADATA         -->
        <!-- ===================================== -->
        <div id="tabContent-seo" class="svc-tab-pane" style="display: none;">
          <div class="form-group">
            <label class="form-label"><i class="fas fa-heading"></i> SEO Meta Title</label>
            <input type="text" name="seo_title" id="serviceSeoTitle" class="form-input" placeholder="e.g. Best SEO Company in Pondicherry | Cloud Aspire">
            <div style="font-size: 11px; color: var(--text-secondary); margin-top: 4px;">Recommended: 50-60 characters. Appears as page title in Google search results.</div>
          </div>

          <div class="form-group">
            <label class="form-label"><i class="fas fa-paragraph"></i> Meta Description</label>
            <textarea name="meta_description" id="serviceMetaDesc" class="form-textarea" style="min-height: 75px;" placeholder="e.g. Accelerate organic traffic and conversions with our certified SEO specialists in Pondicherry..."></textarea>
            <div style="font-size: 11px; color: var(--text-secondary); margin-top: 4px;">Recommended: 140-160 characters. Search engines use this snippet.</div>
          </div>

          <div class="form-group">
            <label class="form-label"><i class="fas fa-tags"></i> Meta Keywords</label>
            <input type="text" name="meta_keywords" id="serviceMetaKeywords" class="form-input" placeholder="e.g. seo pondicherry, search engine optimization, google rank, cloud aspire">
            <div style="font-size: 11px; color: var(--text-secondary); margin-top: 4px;">Comma separated keywords.</div>
          </div>

          <!-- Open Graph Image Upload -->
          <div class="form-group">
            <label class="form-label"><i class="fas fa-share-nodes"></i> Open Graph (Social Share) Image</label>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; align-items: start;">
              <div style="border: 2px dashed var(--border-light); border-radius: var(--radius-md); padding: 14px; text-align: center; background: var(--bg-surface-secondary); cursor: pointer;" onclick="document.getElementById('serviceOgImageFile').click()">
                <i class="fas fa-cloud-arrow-up" style="font-size: 24px; color: var(--primary); margin-bottom: 6px;"></i>
                <div style="font-size: 12px; font-weight: 600; color: var(--text-primary);">Upload Social Preview (1200x630)</div>
                <div style="font-size: 11px; color: var(--text-secondary); margin-top: 2px;">JPG, PNG, WEBP (Max 5MB)</div>
                <input type="file" name="og_image_file" id="serviceOgImageFile" accept=".jpg,.jpeg,.png,.gif,.webp" style="display: none;" onchange="previewImageFile(this, 'ogImgPreview', 'ogImgEmptyText', 'ogImgClearBtn')">
              </div>

              <div id="ogImgPreviewContainer" style="border: 1px solid var(--border-light); border-radius: var(--radius-md); padding: 10px; min-height: 90px; display: flex; flex-direction: column; align-items: center; justify-content: center; background: var(--bg-surface); text-align: center;">
                <img id="ogImgPreview" src="" alt="Preview" style="max-height: 75px; max-width: 100%; border-radius: var(--radius-sm); object-fit: cover; display: none; margin-bottom: 6px;">
                <div id="ogImgEmptyText" style="font-size: 12px; color: var(--text-tertiary);">
                  <i class="fas fa-image" style="font-size: 20px; display: block; margin-bottom: 4px;"></i>
                  Using primary card image
                </div>
                <button type="button" id="ogImgClearBtn" class="btn btn-ghost btn-sm" style="display: none; color: var(--danger); font-size: 11px; padding: 2px 8px;" onclick="clearImageSlot('serviceOgImageFile', 'serviceOgImage', 'serviceExistingOgImage', 'ogImgPreview', 'ogImgEmptyText', 'ogImgClearBtn')">
                  <i class="fas fa-trash-alt"></i> Remove
                </button>
              </div>
            </div>
            <div style="margin-top: 8px;">
              <input type="text" name="og_image" id="serviceOgImage" placeholder="Or enter OG image URL" class="form-input" style="font-size: 12px; padding: 6px 10px;" oninput="previewUrlSlot(this.value, 'ogImgPreview', 'ogImgEmptyText', 'ogImgClearBtn')">
            </div>
          </div>
        </div>

      </div>

      <div class="modal-footer" style="padding: 14px 20px; background: var(--bg-surface-secondary); border-top: 1px solid var(--border-light); margin-top: 0;">
        <button type="submit" class="btn btn-primary" style="flex: 1;">
          <i class="fas fa-save"></i> Save Service & Publish
        </button>
        <button type="button" class="btn btn-secondary" onclick="closeModal()">
          Cancel
        </button>
      </div>
    </form>
  </div>
</div>

<!-- ========================================== -->
<!-- DEDICATED DELETE CONFIRMATION MODAL       -->
<!-- ========================================== -->
<div class="modal" id="deleteConfirmModal">
  <div class="modal-backdrop" onclick="closeDeleteModal()"></div>
  <div class="modal-content" style="max-width: 460px; text-align: center; padding: 24px;">
    <div style="width: 54px; height: 54px; border-radius: 50%; background: rgba(239, 68, 68, 0.12); color: #EF4444; display: flex; align-items: center; justify-content: center; font-size: 24px; margin: 0 auto 16px;">
      <i class="fas fa-triangle-exclamation"></i>
    </div>
    <h3 style="font-size: 18px; font-weight: 700; color: var(--text-primary); margin-bottom: 8px;">
      Confirm Service Deletion
    </h3>
    <p style="font-size: 13.5px; color: var(--text-secondary); line-height: 1.5; margin-bottom: 20px;">
      Are you sure you want to permanently delete <strong id="deleteTargetName" style="color: var(--text-primary);"></strong>?
      This will remove it from all navigation dropdowns and public URLs immediately.
    </p>

    <form method="POST" action="services.php" style="display: flex; gap: 10px; justify-content: center;">
      <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
      <input type="hidden" name="action" value="delete">
      <input type="hidden" name="id" id="deleteTargetId" value="">
      <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()" style="flex: 1;">Cancel</button>
      <button type="submit" class="btn btn-danger" style="flex: 1; background: #EF4444; color: #fff; border: none;">
        <i class="fas fa-trash"></i> Delete Service
      </button>
    </form>
  </div>
</div>

<script>
// Filter & Search Handler
function filterServicesTable() {
  const query = (document.getElementById('serviceSearchInput').value || '').toLowerCase().trim();
  const statusFilter = document.getElementById('serviceStatusFilter').value;
  const rows = document.querySelectorAll('.service-row-item');
  let visibleCount = 0;

  rows.forEach(row => {
    const text = row.textContent.toLowerCase();
    const rowStatus = row.getAttribute('data-status') || 'active';
    const matchesQuery = !query || text.includes(query);
    const matchesStatus = (statusFilter === 'all') || (rowStatus === statusFilter);

    if (matchesQuery && matchesStatus) {
      row.style.display = '';
      visibleCount++;
    } else {
      row.style.display = 'none';
    }
  });

  const countElem = document.getElementById('visibleCountText');
  if (countElem) countElem.textContent = visibleCount;
}

function resetServiceFilters() {
  document.getElementById('serviceSearchInput').value = '';
  document.getElementById('serviceStatusFilter').value = 'all';
  filterServicesTable();
}

// Bulk Selection Handlers
function toggleSelectAll(master) {
  const checkboxes = document.querySelectorAll('.service-checkbox');
  checkboxes.forEach(cb => {
    cb.checked = master.checked;
    const row = document.getElementById('service-row-' + cb.value);
    if (row) {
      row.style.backgroundColor = master.checked ? 'rgba(91,53,232,0.04)' : '';
    }
  });
  updateBulkBar();
}

function updateBulkBar() {
  const checkboxes = document.querySelectorAll('.service-checkbox:checked');
  const allCheckboxes = document.querySelectorAll('.service-checkbox');
  const count = checkboxes.length;
  const total = allCheckboxes.length;
  const bar = document.getElementById('bulkActionsBar');
  const countText = document.getElementById('selectedCountText');
  const master = document.getElementById('selectAllCheckbox');

  if (master) {
    master.checked = count > 0 && count === total;
    master.indeterminate = count > 0 && count < total;
  }

  allCheckboxes.forEach(cb => {
    const row = document.getElementById('service-row-' + cb.value);
    if (row) {
      row.style.backgroundColor = cb.checked ? 'rgba(91,53,232,0.04)' : '';
    }
  });

  if (count > 0) {
    bar.style.display = 'flex';
    countText.textContent = count + (count === 1 ? ' service selected' : ' services selected');
  } else {
    bar.style.display = 'none';
  }
}

function clearAllSelections() {
  const master = document.getElementById('selectAllCheckbox');
  if (master) {
    master.checked = false;
    master.indeterminate = false;
  }
  document.querySelectorAll('.service-checkbox').forEach(cb => {
    cb.checked = false;
    const row = document.getElementById('service-row-' + cb.value);
    if (row) row.style.backgroundColor = '';
  });
  updateBulkBar();
}

function submitBulkAction(actionType) {
  const checked = Array.from(document.querySelectorAll('.service-checkbox:checked')).map(cb => cb.value);
  if (checked.length === 0) {
    alert('Please select at least one service.');
    return;
  }

  if (actionType === 'bulk_delete') {
    if (!confirm(`Are you sure you want to permanently delete ${checked.length} selected service(s)?`)) {
      return;
    }
  }

  const container = document.getElementById('bulkIdInputs');
  container.innerHTML = '';
  checked.forEach(id => {
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'ids[]';
    input.value = id;
    container.appendChild(input);
  });

  document.getElementById('bulkActionType').value = actionType;
  document.getElementById('bulkActionForm').submit();
}

// Icon Selector
function selectIcon(el) {
  document.querySelectorAll('.icon-option').forEach(o => o.classList.remove('selected'));
  el.classList.add('selected');
  document.getElementById('selectedIcon').value = el.dataset.icon;
}

// Generic Image Upload Preview & Clear Helpers
function previewImageFile(input, imgId, emptyId, clearId) {
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = function(e) {
      const img = document.getElementById(imgId);
      const emptyText = document.getElementById(emptyId);
      const clearBtn = document.getElementById(clearId);
      img.src = e.target.result;
      img.style.display = 'block';
      emptyText.style.display = 'none';
      clearBtn.style.display = 'inline-flex';
    };
    reader.readAsDataURL(input.files[0]);
  }
}

function previewUrlSlot(url, imgId, emptyId, clearId) {
  const img = document.getElementById(imgId);
  const emptyText = document.getElementById(emptyId);
  const clearBtn = document.getElementById(clearId);
  if (url && url.trim()) {
    let finalSrc = url.trim();
    if (!finalSrc.startsWith('http://') && !finalSrc.startsWith('https://') && !finalSrc.startsWith('/')) {
      finalSrc = '../' + finalSrc;
    }
    img.src = finalSrc;
    img.style.display = 'block';
    emptyText.style.display = 'none';
    clearBtn.style.display = 'inline-flex';
  } else {
    img.style.display = 'none';
    emptyText.style.display = 'block';
    clearBtn.style.display = 'none';
  }
}

function clearImageSlot(fileInputId, textInputId, existingHiddenId, imgId, emptyId, clearId) {
  document.getElementById(fileInputId).value = '';
  document.getElementById(textInputId).value = '';
  document.getElementById(existingHiddenId).value = '';
  const img = document.getElementById(imgId);
  img.src = '';
  img.style.display = 'none';
  document.getElementById(emptyId).style.display = 'block';
  document.getElementById(clearId).style.display = 'none';
}

// Tabs Switcher
function switchServiceTab(tabName) {
  document.querySelectorAll('.svc-tab-btn').forEach(b => {
    b.classList.remove('active');
    b.classList.add('btn-ghost');
  });
  document.querySelectorAll('.svc-tab-pane').forEach(p => p.style.display = 'none');

  const btn = document.getElementById('tabBtn-' + tabName);
  if (btn) {
    btn.classList.add('active');
    btn.classList.remove('btn-ghost');
  }
  const pane = document.getElementById('tabContent-' + tabName);
  if (pane) pane.style.display = 'block';
}

function autoGenerateSlug(text) {
  const slugInput = document.getElementById('serviceSlug');
  const action = document.getElementById('formAction').value;
  if (action === 'create' && slugInput) {
    slugInput.value = text.toLowerCase()
      .trim()
      .replace(/[^\w\s-]/g, '')
      .replace(/[\s_-]+/g, '-')
      .replace(/^-+|-+$/g, '');
  }
}

// Open Modal for Create
function openModal() {
  document.getElementById('modalTitle').textContent = 'Add New Service';
  document.getElementById('formAction').value = 'create';
  document.getElementById('serviceId').value = '';
  document.getElementById('serviceName').value = '';
  document.getElementById('serviceSlug').value = '';
  document.getElementById('serviceShortDesc').value = '';
  document.getElementById('serviceFullDesc').value = '';
  document.getElementById('serviceSortOrder').value = '';
  document.getElementById('serviceStatus').value = 'active';
  document.getElementById('selectedIcon').value = '';

  // Hero & Buttons
  document.getElementById('serviceHeroHeading').value = '';
  document.getElementById('serviceHeroHighlight').value = '';
  document.getElementById('serviceLearnMoreText').value = 'Learn More';
  document.getElementById('serviceLearnMoreUrl').value = '#cdm-deliverables';
  document.getElementById('serviceExploreBtnText').value = 'Explore Service';
  document.getElementById('serviceExploreBtnUrl').value = '#cdm-quote-card';

  // Features & Benefits
  document.getElementById('serviceFeatures').value = '';
  document.getElementById('serviceBenefits').value = '';

  // Process & FAQs
  for (let i = 1; i <= 4; i++) {
    document.getElementById('stepTitle' + i).value = '';
    document.getElementById('stepDesc' + i).value = '';
  }
  for (let i = 1; i <= 3; i++) {
    document.getElementById('faqQ' + i).value = '';
    document.getElementById('faqA' + i).value = '';
  }

  // SEO & Social
  document.getElementById('serviceSeoTitle').value = '';
  document.getElementById('serviceMetaDesc').value = '';
  document.getElementById('serviceMetaKeywords').value = '';

  clearImageSlot('serviceImageFile', 'serviceImage', 'serviceExistingImage', 'serviceImgPreview', 'serviceImgEmptyText', 'serviceImgClearBtn');
  clearImageSlot('serviceHeroBgFile', 'serviceHeroBgImage', 'serviceExistingHeroBg', 'heroBgPreview', 'heroBgEmptyText', 'heroBgClearBtn');
  clearImageSlot('serviceOgImageFile', 'serviceOgImage', 'serviceExistingOgImage', 'ogImgPreview', 'ogImgEmptyText', 'ogImgClearBtn');

  document.querySelectorAll('.icon-option').forEach(o => o.classList.remove('selected'));
  switchServiceTab('general');
  document.getElementById('serviceModal').classList.add('active');
}

// Edit Service
function editService(s) {
  document.getElementById('modalTitle').textContent = 'Edit Service — ' + s.service_name;
  document.getElementById('formAction').value = 'update';
  document.getElementById('serviceId').value = s.id;
  document.getElementById('serviceName').value = s.service_name || '';
  document.getElementById('serviceSlug').value = s.slug || '';
  document.getElementById('serviceShortDesc').value = s.short_description || '';
  document.getElementById('serviceFullDesc').value = s.full_description || '';
  document.getElementById('serviceSortOrder').value = s.sort_order !== null ? s.sort_order : 0;
  document.getElementById('serviceStatus').value = s.status || 'active';
  document.getElementById('selectedIcon').value = s.icon || '';

  // Hero & Buttons
  document.getElementById('serviceHeroHeading').value = s.hero_heading || '';
  document.getElementById('serviceHeroHighlight').value = s.hero_highlight || '';
  document.getElementById('serviceLearnMoreText').value = s.learn_more_text || 'Learn More';
  document.getElementById('serviceLearnMoreUrl').value = s.learn_more_url || '#cdm-deliverables';
  document.getElementById('serviceExploreBtnText').value = s.explore_btn_text || 'Explore Service';
  document.getElementById('serviceExploreBtnUrl').value = s.explore_btn_url || '#cdm-quote-card';

  // Images
  document.getElementById('serviceImageFile').value = '';
  document.getElementById('serviceExistingImage').value = s.image || '';
  document.getElementById('serviceImage').value = s.image || '';
  if (s.image) {
    previewUrlSlot(s.image, 'serviceImgPreview', 'serviceImgEmptyText', 'serviceImgClearBtn');
  } else {
    clearImageSlot('serviceImageFile', 'serviceImage', 'serviceExistingImage', 'serviceImgPreview', 'serviceImgEmptyText', 'serviceImgClearBtn');
  }

  document.getElementById('serviceHeroBgFile').value = '';
  document.getElementById('serviceExistingHeroBg').value = s.hero_bg_image || '';
  document.getElementById('serviceHeroBgImage').value = s.hero_bg_image || '';
  if (s.hero_bg_image) {
    previewUrlSlot(s.hero_bg_image, 'heroBgPreview', 'heroBgEmptyText', 'heroBgClearBtn');
  } else {
    clearImageSlot('serviceHeroBgFile', 'serviceHeroBgImage', 'serviceExistingHeroBg', 'heroBgPreview', 'heroBgEmptyText', 'heroBgClearBtn');
  }

  document.getElementById('serviceOgImageFile').value = '';
  document.getElementById('serviceExistingOgImage').value = s.og_image || '';
  document.getElementById('serviceOgImage').value = s.og_image || '';
  if (s.og_image) {
    previewUrlSlot(s.og_image, 'ogImgPreview', 'ogImgEmptyText', 'ogImgClearBtn');
  } else {
    clearImageSlot('serviceOgImageFile', 'serviceOgImage', 'serviceExistingOgImage', 'ogImgPreview', 'ogImgEmptyText', 'ogImgClearBtn');
  }

  // Features
  let featuresText = '';
  if (s.features) {
    try {
      const parsedFeat = typeof s.features === 'string' ? JSON.parse(s.features) : s.features;
      if (Array.isArray(parsedFeat)) {
        featuresText = parsedFeat.map(f => {
          if (typeof f === 'object' && f !== null) {
            return f.title + (f.desc ? ': ' + f.desc : '');
          }
          return String(f);
        }).join('\n');
      } else {
        featuresText = String(s.features);
      }
    } catch (e) {
      featuresText = String(s.features);
    }
  }
  document.getElementById('serviceFeatures').value = featuresText;

  // Benefits
  let benefitsText = '';
  if (s.benefits) {
    try {
      const parsedBen = typeof s.benefits === 'string' ? JSON.parse(s.benefits) : s.benefits;
      if (Array.isArray(parsedBen)) {
        benefitsText = parsedBen.join('\n');
      } else {
        benefitsText = String(s.benefits);
      }
    } catch (e) {
      benefitsText = String(s.benefits);
    }
  }
  document.getElementById('serviceBenefits').value = benefitsText;

  // Process Steps
  for (let i = 1; i <= 4; i++) {
    document.getElementById('stepTitle' + i).value = '';
    document.getElementById('stepDesc' + i).value = '';
  }
  if (s.process_steps) {
    try {
      const parsedSteps = typeof s.process_steps === 'string' ? JSON.parse(s.process_steps) : s.process_steps;
      if (Array.isArray(parsedSteps)) {
        parsedSteps.forEach((st, idx) => {
          if (idx < 4) {
            const stepNum = idx + 1;
            document.getElementById('stepTitle' + stepNum).value = st.title || '';
            document.getElementById('stepDesc' + stepNum).value = st.desc || '';
          }
        });
      }
    } catch (e) {}
  }

  // FAQs
  for (let i = 1; i <= 3; i++) {
    document.getElementById('faqQ' + i).value = '';
    document.getElementById('faqA' + i).value = '';
  }
  if (s.faqs) {
    try {
      const parsedFaqs = typeof s.faqs === 'string' ? JSON.parse(s.faqs) : s.faqs;
      if (Array.isArray(parsedFaqs)) {
        parsedFaqs.forEach((fq, idx) => {
          if (idx < 3) {
            const faqNum = idx + 1;
            document.getElementById('faqQ' + faqNum).value = fq.question || '';
            document.getElementById('faqA' + faqNum).value = fq.answer || '';
          }
        });
      }
    } catch (e) {}
  }

  // SEO Metadata
  document.getElementById('serviceSeoTitle').value = s.seo_title || '';
  document.getElementById('serviceMetaDesc').value = s.meta_description || '';
  document.getElementById('serviceMetaKeywords').value = s.meta_keywords || '';

  // Icon
  document.querySelectorAll('.icon-option').forEach(o => o.classList.remove('selected'));
  if (s.icon) {
    const sel = document.querySelector(`.icon-option[data-icon="${s.icon}"]`);
    if (sel) sel.classList.add('selected');
  }

  switchServiceTab('general');
  document.getElementById('serviceModal').classList.add('active');
}

function closeModal() {
  document.getElementById('serviceModal').classList.remove('active');
}

// Delete Confirmation Modal
function confirmDeleteService(id, name) {
  document.getElementById('deleteTargetId').value = id;
  document.getElementById('deleteTargetName').textContent = name;
  document.getElementById('deleteConfirmModal').classList.add('active');
}

function closeDeleteModal() {
  document.getElementById('deleteConfirmModal').classList.remove('active');
}

// Instant Reordering Action
function moveServiceOrder(serviceId, direction) {
  const rows = Array.from(document.querySelectorAll('.service-row-item'));
  const currentIndex = rows.findIndex(r => parseInt(r.getAttribute('data-id')) === serviceId);
  if (currentIndex === -1) return;

  const targetIndex = currentIndex + direction;
  if (targetIndex < 0 || targetIndex >= rows.length) return;

  // Swap in DOM and calculate orders
  const orders = {};
  const reorderedRows = [...rows];
  const temp = reorderedRows[currentIndex];
  reorderedRows[currentIndex] = reorderedRows[targetIndex];
  reorderedRows[targetIndex] = temp;

  reorderedRows.forEach((r, idx) => {
    const rId = parseInt(r.getAttribute('data-id'));
    orders[rId] = idx + 1;
  });

  // Submit via AJAX
  const formData = new FormData();
  formData.append('csrf_token', '<?= $csrf_token ?>');
  formData.append('action', 'reorder');
  formData.append('ajax', '1');
  for (const [sId, sOrder] of Object.entries(orders)) {
    formData.append(`orders[${sId}]`, sOrder);
  }

  fetch('services.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      window.location.reload();
    } else {
      alert(data.message || 'Failed to update order');
    }
  })
  .catch(err => {
    console.error(err);
    window.location.reload();
  });
}
</script>

<?php require_once __DIR__ . '/../../../admin/includes/footer-pro.php'; ?>
