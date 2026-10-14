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
  transform: translateY(-4px);
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
  width: 48px;
  height: 48px;
  background: var(--primary-soft);
  border-radius: var(--radius-md);
  display: flex;
  align-items: center;
  justify-content: center;
  border: 1px solid var(--primary-shadow);
}
.service-icon i {
  font-size: 22px;
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
</style>

<!-- Page Header -->
<div class="flex items-center justify-between mb-6">
  <div>
  <h1 style="font-size: var(--text-2xl); font-weight: 600; letter-spacing: -0.02em; color: var(--text-primary);">
  Services Management
  </h1>
  <p class="text-sm text-muted" style="margin-top: var(--space-1);">
  Manage your service offerings.
  </p>
  </div>
  <button class="btn btn-primary" onclick="openModal()">
  <i class="fas fa-plus"></i> Add Service
  </button>
</div>

<?php if ($message): ?>
  <div class="alert alert-<?= $message_type ?>">
  <i class="fas fa-<?= $message_type === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
  <?= htmlspecialchars($message) ?>
  </div>
<?php endif; ?>

<!-- Bulk Actions Toolbar -->
<div id="bulkActionsBar" style="display: none; align-items: center; justify-content: space-between; background: var(--primary-soft, rgba(91,53,232,0.08)); border: 1px solid var(--primary, #5B35E8); border-radius: var(--radius-md, 12px); padding: 12px 18px; margin-bottom: var(--space-4, 16px); animation: fadeIn 0.2s ease;">
  <div style="display: flex; align-items: center; gap: 12px; font-size: var(--text-sm, 14px); font-weight: 600; color: var(--text-primary);">
    <i class="fas fa-check-circle" style="color: var(--primary); font-size: 16px;"></i>
    <span id="selectedCountText">0 services selected</span>
  </div>
  <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
    <button type="button" class="btn btn-sm" style="background: #10B981; color: #fff; border: none; padding: 6px 14px; border-radius: 8px; font-weight: 600;" onclick="submitBulkAction('bulk_activate')">
      <i class="fas fa-eye"></i> Activate
    </button>
    <button type="button" class="btn btn-sm" style="background: #6B7280; color: #fff; border: none; padding: 6px 14px; border-radius: 8px; font-weight: 600;" onclick="submitBulkAction('bulk_deactivate')">
      <i class="fas fa-eye-slash"></i> Deactivate
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
<form id="bulkActionForm" method="POST" action="/admin/services.php" style="display: none;">
  <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
  <input type="hidden" name="action" id="bulkActionType" value="">
  <div id="bulkIdInputs"></div>
</form>

<div class="card">
  <div class="table-container">
  <table style="min-width: auto;">
  <thead>
  <tr>
  <th style="width: 44px; text-align: center;">
    <input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll(this)" title="Select all services" style="cursor: pointer; width: 16px; height: 16px; accent-color: var(--primary);">
  </th>
  <th style="width: 70px;">Icon</th>
  <th>Service Details</th>
  <th>Description</th>
  <th>Status</th>
  <th style="text-align: right;">Actions</th>
  </tr>
  </thead>
  <tbody>
  <?php foreach ($services as $service): ?>
  <tr id="service-row-<?= $service['id'] ?>">
  <td style="text-align: center;">
    <input type="checkbox" value="<?= $service['id'] ?>" class="service-checkbox" onchange="updateBulkBar()" style="cursor: pointer; width: 16px; height: 16px; accent-color: var(--primary);">
  </td>
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
  <td>
  <div class="service-name"><?= htmlspecialchars($service['service_name']) ?></div>
  <div class="service-slug">/<?= htmlspecialchars($service['slug']) ?></div>
  </td>
  <td>
  <div style="color: var(--text-secondary); font-size: var(--text-sm); max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
  <?= htmlspecialchars($service['short_description']) ?>
  </div>
  </td>
  <td>
  <?php
  $status_class = $service['status'] === 'active' ? 'badge-success' : 'badge-neutral';
  ?>
  <span class="badge-sm <?= $status_class ?>"><?= ucfirst($service['status']) ?></span>
  </td>
  <td style="text-align: right;">
  <div style="display: flex; gap: var(--space-2); justify-content: flex-end;">
  <a href="../service/<?= htmlspecialchars($service['slug']) ?>" target="_blank" class="btn btn-ghost btn-sm" title="View Live Page">
  <i class="fas fa-external-link-alt" style="color: var(--primary);"></i>
  </a>
  <button class="btn btn-ghost btn-sm" onclick="editService(<?= htmlspecialchars(json_encode($service)) ?>)" title="Edit">
  <i class="fas fa-edit"></i>
  </button>
  <form method="POST" style="display:inline;">
  <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
  <input type="hidden" name="action" value="toggle">
  <input type="hidden" name="id" value="<?= $service['id'] ?>">
  <button type="submit" class="btn btn-ghost btn-sm" title="Toggle Visibility">
  <i class="fas fa-<?= $service['status'] === 'active' ? 'eye-slash' : 'eye' ?>"></i>
  </button>
  </form>
  <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this service permanently?')">
  <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
  <input type="hidden" name="action" value="delete">
  <input type="hidden" name="id" value="<?= $service['id'] ?>">
  <button type="submit" class="btn btn-ghost btn-sm" style="color: var(--danger);" title="Delete">
  <i class="fas fa-trash"></i>
  </button>
  </form>
  </div>
  </td>
  </tr>
  <?php endforeach; ?>

  <?php if (empty($services)): ?>
  <tr>
  <td colspan="7" style="text-align:center; padding:var(--space-10) var(--space-4); border-bottom: none;">
  <div style="width: 48px; height: 48px; background: var(--bg-surface-secondary); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto var(--space-4);">
  <i class="fas fa-briefcase" style="font-size: 20px; color: var(--text-tertiary);"></i>
  </div>
  <h3 style="font-size: var(--text-base); font-weight: 600; color: var(--text-primary); margin-bottom: var(--space-2);">
  No services defined
  </h3>
  <p style="color: var(--text-secondary); font-size: var(--text-sm); margin-bottom: var(--space-4);">
  Create your first service offering to start building your portfolio.
  </p>
  <button class="btn btn-primary btn-sm" onclick="openModal()"><i class="fas fa-plus"></i> Add First Service</button>
  </td>
  </tr>
  <?php endif; ?>
  </tbody>
  </table>
  </div>
</div>

<!-- Modal -->
<div class="modal" id="serviceModal">
  <div class="modal-backdrop" onclick="closeModal()"></div>
  <div class="modal-content" style="max-width: 820px; max-height: 90vh; display: flex; flex-direction: column;">
  <div class="modal-header">
  <h3 class="modal-title"><i class="fas fa-briefcase"></i> <span id="modalTitle">Add New Service</span></h3>
  <button class="modal-close" onclick="closeModal()">
  <i class="fas fa-times"></i>
  </button>
  </div>

  <form method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; flex: 1; overflow: hidden;">
  <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
  <input type="hidden" name="action" id="formAction" value="create">
  <input type="hidden" name="id" id="serviceId">
  <input type="hidden" name="existing_image" id="serviceExistingImage">

  <!-- Service Tabs Navigation -->
  <div style="display: flex; gap: 8px; border-bottom: 1px solid var(--border-light); padding: 12px 20px 8px; background: var(--bg-surface-secondary); overflow-x: auto; flex-shrink: 0;">
    <button type="button" class="btn btn-sm svc-tab-btn active" id="tabBtn-general" onclick="switchServiceTab('general')" style="border-radius: 20px;">
      <i class="fas fa-info-circle"></i> General & Media
    </button>
    <button type="button" class="btn btn-sm btn-ghost svc-tab-btn" id="tabBtn-deliverables" onclick="switchServiceTab('deliverables')" style="border-radius: 20px;">
      <i class="fas fa-layer-group"></i> Deliverables (6)
    </button>
    <button type="button" class="btn btn-sm btn-ghost svc-tab-btn" id="tabBtn-workflow" onclick="switchServiceTab('workflow')" style="border-radius: 20px;">
      <i class="fas fa-route"></i> 4-Step Process & Benefits
    </button>
    <button type="button" class="btn btn-sm btn-ghost svc-tab-btn" id="tabBtn-faqs" onclick="switchServiceTab('faqs')" style="border-radius: 20px;">
      <i class="fas fa-question-circle"></i> Service FAQs
    </button>
    <button type="button" class="btn btn-sm btn-ghost svc-tab-btn" id="tabBtn-seo" onclick="switchServiceTab('seo')" style="border-radius: 20px;">
      <i class="fas fa-magnifying-glass-chart"></i> SEO Tags
    </button>
  </div>

  <div style="overflow-y: auto; padding: 20px; flex: 1;">

    <!-- TAB 1: GENERAL INFO -->
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

      <div class="form-group">
        <label class="form-label"><i class="fas fa-align-left"></i> Short Description *</label>
        <textarea name="short_description" id="serviceShortDesc" required class="form-textarea" style="min-height: 65px;" placeholder="Brief summary of service for cards and meta snippets..."></textarea>
      </div>

      <div class="form-group">
        <label class="form-label"><i class="fas fa-file-alt"></i> Full Service Description</label>
        <textarea name="full_description" id="serviceFullDesc" class="form-textarea" style="min-height: 90px;" placeholder="Detailed service overview, methodology, and value proposition..."></textarea>
      </div>

      <div class="form-group">
        <label class="form-label"><i class="fas fa-icons"></i> Select Icon</label>
        <div class="icon-grid">
        <?php foreach ($available_icons as $icon => $name): ?>
        <div class="icon-option" data-icon="<?= $icon ?>" onclick="selectIcon(this)" title="<?= $name ?>"><i class="fas <?= $icon ?>"></i></div>
        <?php endforeach; ?>
        </div>
        <input type="hidden" name="icon" id="selectedIcon">
      </div>

      <!-- Image Upload & Preview Section -->
      <div class="form-group">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
          <label class="form-label" style="margin: 0;"><i class="fas fa-image"></i> Service Banner Image</label>
          <button type="button" class="btn btn-secondary btn-sm" style="font-size: 11px; padding: 3px 10px;" onclick="openMediaPickerForService()">
            <i class="fas fa-images" style="color: var(--primary);"></i> Browse Media Library
          </button>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; align-items: start;">
          <!-- File Upload Box -->
          <div style="border: 2px dashed var(--border-light); border-radius: var(--radius-md); padding: 14px; text-align: center; background: var(--bg-surface-secondary); cursor: pointer; transition: all 0.2s ease;" onclick="document.getElementById('serviceImageFile').click()" id="uploadDropzone">
            <i class="fas fa-cloud-arrow-up" style="font-size: 24px; color: var(--primary); margin-bottom: 6px;"></i>
            <div style="font-size: 12px; font-weight: 600; color: var(--text-primary);">Click to Upload from Computer</div>
            <div style="font-size: 11px; color: var(--text-secondary); margin-top: 2px;">JPG, PNG, GIF, WEBP (Max 5MB)</div>
            <input type="file" name="image_file" id="serviceImageFile" accept=".jpg,.jpeg,.png,.gif,.webp" style="display: none;" onchange="previewServiceImage(this)">
          </div>

          <!-- Preview / Image Status -->
          <div id="serviceImgPreviewContainer" style="border: 1px solid var(--border-light); border-radius: var(--radius-md); padding: 12px; min-height: 90px; display: flex; flex-direction: column; align-items: center; justify-content: center; background: var(--bg-surface); text-align: center;">
            <img id="serviceImgPreview" src="" alt="Preview" style="max-height: 80px; max-width: 100%; border-radius: var(--radius-sm); object-fit: cover; display: none; margin-bottom: 6px;">
            <div id="serviceImgEmptyText" style="font-size: 12px; color: var(--text-tertiary);">
              <i class="fas fa-image" style="font-size: 20px; display: block; margin-bottom: 4px;"></i>
              No Image Selected
            </div>
            <button type="button" id="serviceImgClearBtn" class="btn btn-ghost btn-sm" style="display: none; color: var(--danger); font-size: 11px; padding: 2px 8px; margin-top: 4px;" onclick="clearServiceImage()">
              <i class="fas fa-trash-alt"></i> Remove
            </button>
          </div>
        </div>

        <!-- Direct URL fallback -->
        <div style="margin-top: 10px;">
          <div style="font-size: 11px; color: var(--text-secondary); margin-bottom: 4px;"><i class="fas fa-link"></i> Or Enter Image Path / URL:</div>
          <input type="text" name="image" id="serviceImage" placeholder="uploads/services/service_... or https://..." class="form-input" style="font-size: 12px; padding: 7px 12px;" oninput="previewServiceUrl(this.value)">
        </div>
      </div>

      <div class="form-group">
        <label class="form-label"><i class="fas fa-toggle-on"></i> Service Status</label>
        <select name="status" id="serviceStatus" class="form-select">
          <option value="active">Active (Visible)</option>
          <option value="inactive">Inactive (Hidden)</option>
        </select>
      </div>
    </div>

    <!-- TAB 2: DELIVERABLES & FEATURES -->
    <div id="tabContent-deliverables" class="svc-tab-pane" style="display: none;">
      <div class="form-group">
        <label class="form-label"><i class="fas fa-list-check"></i> Deliverables & Core Features</label>
        <p style="font-size: 12px; color: var(--text-secondary); margin-bottom: 8px;">
          Enter each deliverable on a new line. You can write <code>Feature Title: Short explanation</code> or just the <code>Feature Title</code>.
        </p>
        <textarea name="features" id="serviceFeatures" class="form-textarea" style="min-height: 220px; font-family: monospace; font-size: 13px;" placeholder="Comprehensive Keyword Research: Detailed search intent clustering&#10;On-Page Architecture: Meta tags, headings, schema & internal linking&#10;Technical SEO Audits: Core Web Vitals, crawl budget & indexation fixes"></textarea>
      </div>
    </div>

    <!-- TAB 3: WORKFLOW & BENEFITS -->
    <div id="tabContent-workflow" class="svc-tab-pane" style="display: none;">
      <div class="form-group">
        <label class="form-label"><i class="fas fa-award"></i> Key Business Benefits & Outcomes</label>
        <p style="font-size: 12px; color: var(--text-secondary); margin-bottom: 8px;">
          Enter one measurable outcome per line (e.g. <code>+140% Average Organic Traffic Increase</code>).
        </p>
        <textarea name="benefits" id="serviceBenefits" class="form-textarea" style="min-height: 120px; font-size: 13px;" placeholder="Dominant Search Rankings for High-Intent Commercial Keywords&#10;Sustainable Compounding Qualified Inquiries&#10;Higher Conversion Rates & Lower Acquisition Costs"></textarea>
      </div>

      <div style="margin-top: 24px; border-top: 1px solid var(--border-light); padding-top: 20px;">
        <label class="form-label" style="font-size: 14px; font-weight: 700; margin-bottom: 12px;"><i class="fas fa-route"></i> 4-Step Strategic Workflow Roadmap</label>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
          <!-- Step 1 -->
          <div style="background: var(--bg-surface-secondary); padding: 14px; border-radius: var(--radius-md); border: 1px solid var(--border-light);">
            <div style="font-weight: 700; font-size: 12px; color: var(--primary); margin-bottom: 8px;">STEP 01</div>
            <input type="text" name="step_title[]" id="stepTitle1" class="form-input" placeholder="Step 1 Title (e.g. Discovery & Audit)" style="margin-bottom: 6px;">
            <textarea name="step_desc[]" id="stepDesc1" class="form-textarea" style="min-height: 55px; font-size: 12px;" placeholder="Step 1 Description..."></textarea>
          </div>

          <!-- Step 2 -->
          <div style="background: var(--bg-surface-secondary); padding: 14px; border-radius: var(--radius-md); border: 1px solid var(--border-light);">
            <div style="font-weight: 700; font-size: 12px; color: var(--primary); margin-bottom: 8px;">STEP 02</div>
            <input type="text" name="step_title[]" id="stepTitle2" class="form-input" placeholder="Step 2 Title (e.g. Strategic Architecture)" style="margin-bottom: 6px;">
            <textarea name="step_desc[]" id="stepDesc2" class="form-textarea" style="min-height: 55px; font-size: 12px;" placeholder="Step 2 Description..."></textarea>
          </div>

          <!-- Step 3 -->
          <div style="background: var(--bg-surface-secondary); padding: 14px; border-radius: var(--radius-md); border: 1px solid var(--border-light);">
            <div style="font-weight: 700; font-size: 12px; color: var(--primary); margin-bottom: 8px;">STEP 03</div>
            <input type="text" name="step_title[]" id="stepTitle3" class="form-input" placeholder="Step 3 Title (e.g. Campaign Execution)" style="margin-bottom: 6px;">
            <textarea name="step_desc[]" id="stepDesc3" class="form-textarea" style="min-height: 55px; font-size: 12px;" placeholder="Step 3 Description..."></textarea>
          </div>

          <!-- Step 4 -->
          <div style="background: var(--bg-surface-secondary); padding: 14px; border-radius: var(--radius-md); border: 1px solid var(--border-light);">
            <div style="font-weight: 700; font-size: 12px; color: var(--primary); margin-bottom: 8px;">STEP 04</div>
            <input type="text" name="step_title[]" id="stepTitle4" class="form-input" placeholder="Step 4 Title (e.g. Optimization & Scaling)" style="margin-bottom: 6px;">
            <textarea name="step_desc[]" id="stepDesc4" class="form-textarea" style="min-height: 55px; font-size: 12px;" placeholder="Step 4 Description..."></textarea>
          </div>
        </div>
      </div>
    </div>

    <!-- TAB 4: SERVICE FAQS -->
    <div id="tabContent-faqs" class="svc-tab-pane" style="display: none;">
      <p style="font-size: 12px; color: var(--text-secondary); margin-bottom: 14px;">
        Frequently Asked Questions specific to this service. These render as an interactive accordion and automatically generate Google FAQ Schema structured data!
      </p>

      <!-- FAQ 1 -->
      <div style="background: var(--bg-surface-secondary); padding: 14px; border-radius: var(--radius-md); border: 1px solid var(--border-light); margin-bottom: 12px;">
        <label class="form-label" style="font-size: 12px; font-weight: 700;"><i class="fas fa-question-circle"></i> Question 1</label>
        <input type="text" name="faq_q[]" id="faqQ1" class="form-input" placeholder="e.g. How long does it take to see results?" style="margin-bottom: 8px;">
        <label class="form-label" style="font-size: 12px; font-weight: 700;"><i class="fas fa-comment-dots"></i> Answer 1</label>
        <textarea name="faq_a[]" id="faqA1" class="form-textarea" style="min-height: 60px; font-size: 12px;" placeholder="Detailed response..."></textarea>
      </div>

      <!-- FAQ 2 -->
      <div style="background: var(--bg-surface-secondary); padding: 14px; border-radius: var(--radius-md); border: 1px solid var(--border-light); margin-bottom: 12px;">
        <label class="form-label" style="font-size: 12px; font-weight: 700;"><i class="fas fa-question-circle"></i> Question 2</label>
        <input type="text" name="faq_q[]" id="faqQ2" class="form-input" placeholder="e.g. What deliverables do I receive monthly?" style="margin-bottom: 8px;">
        <label class="form-label" style="font-size: 12px; font-weight: 700;"><i class="fas fa-comment-dots"></i> Answer 2</label>
        <textarea name="faq_a[]" id="faqA2" class="form-textarea" style="min-height: 60px; font-size: 12px;" placeholder="Detailed response..."></textarea>
      </div>

      <!-- FAQ 3 -->
      <div style="background: var(--bg-surface-secondary); padding: 14px; border-radius: var(--radius-md); border: 1px solid var(--border-light);">
        <label class="form-label" style="font-size: 12px; font-weight: 700;"><i class="fas fa-question-circle"></i> Question 3</label>
        <input type="text" name="faq_q[]" id="faqQ3" class="form-input" placeholder="e.g. Do you provide a dedicated account manager?" style="margin-bottom: 8px;">
        <label class="form-label" style="font-size: 12px; font-weight: 700;"><i class="fas fa-comment-dots"></i> Answer 3</label>
        <textarea name="faq_a[]" id="faqA3" class="form-textarea" style="min-height: 60px; font-size: 12px;" placeholder="Detailed response..."></textarea>
      </div>
    </div>

    <!-- TAB 5: SEO METADATA -->
    <div id="tabContent-seo" class="svc-tab-pane" style="display: none;">
      <div class="form-group">
        <label class="form-label"><i class="fas fa-heading"></i> SEO Meta Title</label>
        <input type="text" name="seo_title" id="serviceSeoTitle" class="form-input" placeholder="e.g. Best SEO Company in Pondicherry | Cloud Aspire">
        <div style="font-size: 11px; color: var(--text-secondary); margin-top: 4px;">Recommended: 50-60 characters. Appears as page title in Google search results.</div>
      </div>

      <div class="form-group">
        <label class="form-label"><i class="fas fa-paragraph"></i> Meta Description</label>
        <textarea name="meta_description" id="serviceMetaDesc" class="form-textarea" style="min-height: 80px;" placeholder="e.g. Accelerate organic traffic and conversions with our certified SEO specialists in Pondicherry..."></textarea>
        <div style="font-size: 11px; color: var(--text-secondary); margin-top: 4px;">Recommended: 140-160 characters. Search engines use this snippet.</div>
      </div>

      <div class="form-group">
        <label class="form-label"><i class="fas fa-tags"></i> Meta Keywords</label>
        <input type="text" name="meta_keywords" id="serviceMetaKeywords" class="form-input" placeholder="e.g. seo pondicherry, search engine optimization, google rank, cloud aspire">
        <div style="font-size: 11px; color: var(--text-secondary); margin-top: 4px;">Comma separated keywords.</div>
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

<script>
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

function selectIcon(el) {
  document.querySelectorAll('.icon-option').forEach(o => o.classList.remove('selected'));
  el.classList.add('selected');
  document.getElementById('selectedIcon').value = el.dataset.icon;
}

function previewServiceImage(input) {
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = function(e) {
      const img = document.getElementById('serviceImgPreview');
      const emptyText = document.getElementById('serviceImgEmptyText');
      const clearBtn = document.getElementById('serviceImgClearBtn');
      img.src = e.target.result;
      img.style.display = 'block';
      emptyText.style.display = 'none';
      clearBtn.style.display = 'inline-flex';
    };
    reader.readAsDataURL(input.files[0]);
  }
}

function previewServiceUrl(url) {
  const img = document.getElementById('serviceImgPreview');
  const emptyText = document.getElementById('serviceImgEmptyText');
  const clearBtn = document.getElementById('serviceImgClearBtn');
  if (url && url.trim()) {
    let finalSrc = url.trim();
    if (!finalSrc.startsWith('http://') && !finalSrc.startsWith('https://') && !finalSrc.startsWith('/')) {
      finalSrc = '../' + finalSrc;
    }
    img.src = finalSrc;
    img.style.display = 'block';
    emptyText.style.display = 'none';
    clearBtn.style.display = 'inline-flex';
  } else if (!document.getElementById('serviceImageFile').value) {
    img.style.display = 'none';
    emptyText.style.display = 'block';
    clearBtn.style.display = 'none';
  }
}

function clearServiceImage() {
  document.getElementById('serviceImageFile').value = '';
  document.getElementById('serviceImage').value = '';
  document.getElementById('serviceExistingImage').value = '';
  const img = document.getElementById('serviceImgPreview');
  img.src = '';
  img.style.display = 'none';
  document.getElementById('serviceImgEmptyText').style.display = 'block';
  document.getElementById('serviceImgClearBtn').style.display = 'none';
}

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

function openModal() {
  document.getElementById('modalTitle').textContent = 'Add New Service';
  document.getElementById('formAction').value = 'create';
  document.getElementById('serviceId').value = '';
  document.getElementById('serviceName').value = '';
  document.getElementById('serviceSlug').value = '';
  document.getElementById('serviceShortDesc').value = '';
  document.getElementById('serviceFullDesc').value = '';
  document.getElementById('serviceStatus').value = 'active';
  document.getElementById('selectedIcon').value = '';

  // Reset rich fields
  document.getElementById('serviceFeatures').value = '';
  document.getElementById('serviceBenefits').value = '';
  for (let i = 1; i <= 4; i++) {
    document.getElementById('stepTitle' + i).value = '';
    document.getElementById('stepDesc' + i).value = '';
  }
  for (let i = 1; i <= 3; i++) {
    document.getElementById('faqQ' + i).value = '';
    document.getElementById('faqA' + i).value = '';
  }
  document.getElementById('serviceSeoTitle').value = '';
  document.getElementById('serviceMetaDesc').value = '';
  document.getElementById('serviceMetaKeywords').value = '';

  clearServiceImage();
  document.querySelectorAll('.icon-option').forEach(o => o.classList.remove('selected'));
  switchServiceTab('general');
  document.getElementById('serviceModal').classList.add('active');
}

function editService(s) {
  document.getElementById('modalTitle').textContent = 'Edit Service';
  document.getElementById('formAction').value = 'update';
  document.getElementById('serviceId').value = s.id;
  document.getElementById('serviceName').value = s.service_name;
  document.getElementById('serviceSlug').value = s.slug;
  document.getElementById('serviceShortDesc').value = s.short_description || '';
  document.getElementById('serviceFullDesc').value = s.full_description || '';
  document.getElementById('serviceStatus').value = s.status || 'active';
  document.getElementById('selectedIcon').value = s.icon || '';
  document.getElementById('serviceImageFile').value = '';
  document.getElementById('serviceExistingImage').value = s.image || '';
  document.getElementById('serviceImage').value = s.image || '';

  // 1. Populate Features
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

  // 2. Populate Benefits
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

  // 3. Populate Process Steps
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

  // 4. Populate FAQs
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

  // 5. Populate SEO Metadata
  document.getElementById('serviceSeoTitle').value = s.seo_title || '';
  document.getElementById('serviceMetaDesc').value = s.meta_description || '';
  document.getElementById('serviceMetaKeywords').value = s.meta_keywords || '';

  if (s.image) {
    previewServiceUrl(s.image);
  } else {
    clearServiceImage();
  }
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

function openMediaPickerForService() {
  openUniversalMediaPicker({
    folder: 'services',
    targetInput: '#serviceImage',
    targetPreview: '#serviceImgPreview',
    onSelect: function(file) {
      document.getElementById('serviceImageFile').value = '';
      document.getElementById('serviceExistingImage').value = file.relative_path;
      document.getElementById('serviceImgEmptyText').style.display = 'none';
      document.getElementById('serviceImgClearBtn').style.display = 'inline-flex';
    }
  });
}
</script>

<?php require_once __DIR__ . '/../../../admin/includes/media-picker-modal.php'; ?>
<?php require_once __DIR__ . '/../../../admin/includes/footer-pro.php'; ?>
