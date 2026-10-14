<?php
/**
 * Admin Files & Media Manager View
 */
require_once __DIR__ . '/../../../admin/includes/header-pro.php';

$page_title = 'Media & Files Manager';

/** @var array $files */
/** @var string $csrf_token */
/** @var string $message */
/** @var string $message_type */
/** @var int $totalFiles */
/** @var string $totalSizeFormatted */
/** @var int $servicesCount */
/** @var int $testimonialsCount */
/** @var int $projectsCount */
/** @var int $generalCount */
/** @var string $selectedFolder */
/** @var string $searchQuery */
/** @var string $sortOption */

$files = $files ?? [];
$csrf_token = $csrf_token ?? '';
$message = $message ?? '';
$message_type = $message_type ?? '';
$totalFiles = $totalFiles ?? count($files);
$totalSizeFormatted = $totalSizeFormatted ?? '0 B';
$servicesCount = $servicesCount ?? 0;
$testimonialsCount = $testimonialsCount ?? 0;
$projectsCount = $projectsCount ?? 0;
$generalCount = $generalCount ?? 0;
$selectedFolder = $selectedFolder ?? 'all';
$searchQuery = $searchQuery ?? '';
$sortOption = $sortOption ?? 'newest';

// Determine host URL
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$script_name = $_SERVER['SCRIPT_NAME'] ?? '';
$base_dir = rtrim(dirname(dirname(dirname($script_name))), '/\\');
$site_root_url = $protocol . $host . ($base_dir ? $base_dir : '');
?>

<style>
/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}
.stat-card {
    background: var(--bg-surface);
    border: 1px solid var(--border-light);
    border-radius: var(--radius-lg);
    padding: 18px 20px;
    display: flex;
    align-items: center;
    gap: 16px;
    box-shadow: var(--shadow-xs);
    transition: all 0.2s ease;
}
.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-sm);
    border-color: var(--primary);
}
.stat-icon-wrapper {
    width: 48px;
    height: 48px;
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
}

/* Upload Dropzone */
.dropzone-box {
    background: var(--bg-surface);
    border: 2px dashed var(--border-light);
    border-radius: var(--radius-xl);
    padding: 24px 20px;
    text-align: center;
    margin-bottom: 24px;
    transition: all 0.2s ease;
    cursor: pointer;
    position: relative;
}
.dropzone-box.dragover, .dropzone-box:hover {
    border-color: var(--primary);
    background: var(--primary-soft);
}
.dropzone-icon {
    font-size: 32px;
    color: var(--primary);
    margin-bottom: 8px;
    display: inline-block;
    animation: bounce 2s infinite ease-in-out;
}
@keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-4px); }
}

/* Filter Bar */
.media-filter-bar {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    background: var(--bg-surface);
    border: 1px solid var(--border-light);
    border-radius: var(--radius-lg);
    padding: 12px 16px;
    margin-bottom: 20px;
    box-shadow: var(--shadow-xs);
}
.folder-tabs {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
}
.folder-tab-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 7px 14px;
    border-radius: var(--radius-md);
    font-size: 13px;
    font-weight: 500;
    color: var(--text-secondary);
    text-decoration: none;
    border: 1px solid transparent;
    transition: all 0.15s ease;
    background: transparent;
}
.folder-tab-btn:hover {
    color: var(--text-primary);
    background: var(--bg-hover);
}
.folder-tab-btn.active {
    color: var(--primary);
    background: var(--primary-soft);
    font-weight: 600;
    border-color: rgba(91, 53, 232, 0.2);
}
.folder-tab-badge {
    background: rgba(0, 0, 0, 0.06);
    padding: 2px 7px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
}
.folder-tab-btn.active .folder-tab-badge {
    background: var(--primary);
    color: #fff;
}

/* Grid Layout */
.media-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 18px;
    margin-bottom: 30px;
}
.media-card {
    background: var(--bg-surface);
    border: 1px solid var(--border-light);
    border-radius: var(--radius-lg);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    position: relative;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    box-shadow: var(--shadow-xs);
}
.media-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-md);
    border-color: var(--primary);
}
.media-card.selected {
    border-color: var(--primary);
    box-shadow: 0 0 0 2px var(--primary);
}
.media-thumb-container {
    width: 100%;
    height: 145px;
    position: relative;
    background: var(--bg-surface-secondary);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    cursor: pointer;
}
.media-thumb-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}
.media-card:hover .media-thumb-img {
    transform: scale(1.05);
}
.media-checkbox {
    position: absolute;
    top: 8px;
    left: 8px;
    z-index: 5;
    width: 18px;
    height: 18px;
    accent-color: var(--primary);
    cursor: pointer;
    background: rgba(255, 255, 255, 0.9);
    border-radius: 4px;
}
.media-badge-tag {
    position: absolute;
    top: 8px;
    right: 8px;
    z-index: 5;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 3px 8px;
    border-radius: 6px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.15);
}
.badge-services { background: #5B35E8; color: #fff; }
.badge-testimonials { background: #0EA5E9; color: #fff; }
.badge-projects { background: #10B981; color: #fff; }
.badge-general { background: #64748B; color: #fff; }

.media-info {
    padding: 12px;
    display: flex;
    flex-direction: column;
    flex-grow: 1;
}
.media-filename {
    font-size: 13px;
    font-weight: 600;
    color: var(--text-primary);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-bottom: 4px;
}
.media-meta-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 11px;
    color: var(--text-secondary);
    margin-bottom: 10px;
}
.media-actions-bar {
    display: flex;
    align-items: center;
    gap: 6px;
    border-top: 1px solid var(--border-light);
    padding-top: 8px;
    margin-top: auto;
}
.btn-icon-action {
    flex: 1;
    padding: 6px;
    border-radius: var(--radius-sm);
    font-size: 12px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    background: var(--bg-hover);
    color: var(--text-secondary);
    border: 1px solid var(--border-light);
    cursor: pointer;
    transition: all 0.15s ease;
}
.btn-icon-action:hover {
    background: var(--primary-soft);
    color: var(--primary);
    border-color: var(--primary);
}

/* Bulk Toolbar */
.bulk-bar {
    display: none;
    align-items: center;
    justify-content: space-between;
    background: var(--primary-soft);
    border: 1px solid var(--primary);
    border-radius: var(--radius-md);
    padding: 12px 18px;
    margin-bottom: 18px;
    animation: fadeIn 0.2s ease;
}

/* Toast alert for copy */
.toast-notice {
    position: fixed;
    bottom: 24px;
    right: 24px;
    background: #10B981;
    color: #fff;
    padding: 12px 20px;
    border-radius: var(--radius-lg);
    font-size: 13px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 10px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.18);
    z-index: 99999;
    opacity: 0;
    transform: translateY(20px);
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    pointer-events: none;
}
.toast-notice.show {
    opacity: 1;
    transform: translateY(0);
}
</style>

<!-- Page Header -->
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 style="font-size: var(--text-2xl); font-weight: 700; letter-spacing: -0.02em; color: var(--text-primary); display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-images" style="color: var(--primary);"></i> Media & Files Manager
        </h1>
        <p class="text-sm text-muted" style="margin-top: var(--space-1);">
            Upload, browse, and manage images to use seamlessly across Services, Testimonials, Projects, and pages.
        </p>
    </div>
    <div style="display: flex; gap: 10px; align-items: center;">
        <form method="POST" style="display:inline;">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <input type="hidden" name="action" value="sync">
            <button type="submit" class="btn btn-secondary btn-sm" title="Scan disk for any newly added files">
                <i class="fas fa-sync-alt"></i> Sync Disk Files
            </button>
        </form>
        <button class="btn btn-primary" onclick="toggleUploadBox()">
            <i class="fas fa-cloud-upload-alt"></i> Upload Files
        </button>
    </div>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?= $message_type === 'success' ? 'success' : ($message_type === 'warning' ? 'warning' : 'error') ?>">
        <i class="fas fa-<?= $message_type === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
        <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

<!-- Stats Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon-wrapper" style="background: rgba(91, 53, 232, 0.1); color: #5B35E8;">
            <i class="fas fa-photo-video"></i>
        </div>
        <div>
            <div style="font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-secondary);">Total Media</div>
            <div style="font-size: 22px; font-weight: 700; color: var(--text-primary);"><?= number_format($totalFiles) ?></div>
            <div style="font-size: 11px; color: var(--text-secondary);"><?= $totalSizeFormatted ?> storage used</div>
        </div>
    </div>

    <div class="stat-card" style="cursor: pointer;" onclick="window.location='files.php?folder=services'">
        <div class="stat-icon-wrapper" style="background: rgba(91, 53, 232, 0.1); color: #5B35E8;">
            <i class="fas fa-cube"></i>
        </div>
        <div>
            <div style="font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-secondary);">Services Images</div>
            <div style="font-size: 22px; font-weight: 700; color: var(--text-primary);"><?= number_format($servicesCount) ?></div>
            <div style="font-size: 11px; color: var(--primary);">Used for service cards</div>
        </div>
    </div>

    <div class="stat-card" style="cursor: pointer;" onclick="window.location='files.php?folder=testimonials'">
        <div class="stat-icon-wrapper" style="background: rgba(14, 165, 233, 0.1); color: #0EA5E9;">
            <i class="fas fa-quote-left"></i>
        </div>
        <div>
            <div style="font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-secondary);">Testimonial Avatars</div>
            <div style="font-size: 22px; font-weight: 700; color: var(--text-primary);"><?= number_format($testimonialsCount) ?></div>
            <div style="font-size: 11px; color: #0EA5E9;">Client photo assets</div>
        </div>
    </div>

    <div class="stat-card" style="cursor: pointer;" onclick="window.location='files.php?folder=projects'">
        <div class="stat-icon-wrapper" style="background: rgba(16, 185, 129, 0.1); color: #10B981;">
            <i class="fas fa-folder"></i>
        </div>
        <div>
            <div style="font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-secondary);">Projects Media</div>
            <div style="font-size: 22px; font-weight: 700; color: var(--text-primary);"><?= number_format($projectsCount) ?></div>
            <div style="font-size: 11px; color: #10B981;">Portfolio showcase items</div>
        </div>
    </div>
</div>

<!-- Expandable Upload Box -->
<div id="uploadSection" class="card" style="display: none; margin-bottom: 24px; border: 1.5px solid var(--primary); animation: fadeIn 0.2s ease;">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
        <h3 style="font-size: 16px; font-weight: 600; margin: 0; display: flex; align-items: center; gap: 8px;">
            <i class="fas fa-cloud-arrow-up" style="color: var(--primary);"></i> Upload New Media Images
        </h3>
        <button type="button" class="btn btn-ghost btn-sm" onclick="toggleUploadBox()">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <form method="POST" enctype="multipart/form-data" id="uploadForm">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
        <input type="hidden" name="action" value="upload">

        <div style="display: grid; grid-template-columns: 240px 1fr; gap: 20px; align-items: start;">
            <!-- Category Target Selection -->
            <div>
                <label class="form-label" style="font-weight: 600; font-size: 13px;">
                    <i class="fas fa-folder"></i> Target Category / Folder *
                </label>
                <select name="folder" id="uploadFolderSelect" class="form-select" style="font-size: 13px; font-weight: 500;">
                    <option value="services" <?= $selectedFolder === 'services' ? 'selected' : '' ?>>💼 Services (uploads/services/)</option>
                    <option value="testimonials" <?= $selectedFolder === 'testimonials' ? 'selected' : '' ?>>👤 Testimonials (uploads/testimonials/)</option>
                    <option value="projects" <?= $selectedFolder === 'projects' ? 'selected' : '' ?>>📁 Projects (uploads/projects/)</option>
                    <option value="general" <?= ($selectedFolder === 'general' || $selectedFolder === 'all') ? 'selected' : '' ?>>🌐 General Media (uploads/general/)</option>
                </select>
                <div style="font-size: 11px; color: var(--text-secondary); margin-top: 6px;">
                    Images will be organized automatically into this folder for instant picking.
                </div>
            </div>

            <!-- Drag & Drop Dropzone -->
            <div class="dropzone-box" id="dropzoneBox" onclick="document.getElementById('fileInputElement').click()">
                <i class="fas fa-cloud-upload-alt dropzone-icon"></i>
                <div style="font-size: 15px; font-weight: 600; color: var(--text-primary);">
                    Drag & Drop Images here, or <span style="color: var(--primary); text-decoration: underline;">Click to Browse</span>
                </div>
                <div style="font-size: 12px; color: var(--text-secondary); margin-top: 6px;">
                    Supports JPG, PNG, WEBP, GIF, SVG (Up to 10MB each. Multiple files supported)
                </div>
                <input type="file" name="files[]" id="fileInputElement" multiple accept=".jpg,.jpeg,.png,.gif,.webp,.svg" style="display: none;" onchange="handleFileSelect(this)">
                
                <!-- Selected Files Preview List -->
                <div id="fileQueuePreview" style="display: none; margin-top: 14px; text-align: left; background: var(--bg-surface-secondary); border-radius: var(--radius-md); padding: 10px 14px;">
                    <div style="font-size: 12px; font-weight: 600; color: var(--text-primary); margin-bottom: 6px;">Selected Files to Upload:</div>
                    <div id="fileQueueNames" style="font-size: 11px; color: var(--text-secondary); max-height: 80px; overflow-y: auto;"></div>
                </div>
            </div>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 16px; border-top: 1px solid var(--border-light); padding-top: 14px;">
            <button type="button" class="btn btn-secondary" onclick="toggleUploadBox()">Cancel</button>
            <button type="submit" class="btn btn-primary" id="uploadSubmitBtn">
                <i class="fas fa-upload"></i> Start Upload
            </button>
        </div>
    </form>
</div>

<!-- Bulk Actions Toolbar -->
<div id="bulkBar" class="bulk-bar">
    <div style="display: flex; align-items: center; gap: 12px; font-size: 14px; font-weight: 600; color: var(--text-primary);">
        <i class="fas fa-check-circle" style="color: var(--primary); font-size: 16px;"></i>
        <span id="bulkCountText">0 items selected</span>
    </div>
    <div style="display: flex; align-items: center; gap: 10px;">
        <select id="bulkFolderSelect" class="form-select form-select-sm" style="width: 160px; height: 32px; font-size: 13px;">
            <option value="">Move to Folder...</option>
            <option value="services">Services</option>
            <option value="testimonials">Testimonials</option>
            <option value="projects">Projects</option>
            <option value="general">General Media</option>
        </select>
        <button type="button" class="btn btn-secondary btn-sm" onclick="submitBulkFolderUpdate()">
            <i class="fas fa-folder-open"></i> Move
        </button>
        
        <div style="width: 1px; height: 24px; background: var(--border-light); margin: 0 4px;"></div>
        
        <button type="button" class="btn btn-sm" style="background: #EF4444; color: #fff; border: none; padding: 6px 14px; font-weight: 600;" onclick="submitBulkDelete()">
            <i class="fas fa-trash-alt"></i> Delete Selected
        </button>
        <button type="button" class="btn btn-ghost btn-sm" onclick="clearBulkSelection()">
            <i class="fas fa-times"></i> Clear Selection
        </button>
    </div>
</div>

<!-- Hidden Bulk Action Form -->
<form id="bulkForm" method="POST" action="files.php" style="display: none;">
    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
    <input type="hidden" name="action" id="bulkActionField" value="bulk_delete">
    <input type="hidden" name="folder" id="bulkFolderField" value="">
    <div id="bulkIdsContainer"></div>
</form>

<!-- Filter & Search Toolbar -->
<div class="media-filter-bar">
    <!-- Folder Category Tabs -->
    <div class="folder-tabs">
        <a href="files.php?folder=all<?= $searchQuery ? '&search='.urlencode($searchQuery) : '' ?>" class="folder-tab-btn <?= $selectedFolder === 'all' ? 'active' : '' ?>">
            <i class="fas fa-th"></i> All Files <span class="folder-tab-badge"><?= $totalFiles ?></span>
        </a>
        <a href="files.php?folder=services<?= $searchQuery ? '&search='.urlencode($searchQuery) : '' ?>" class="folder-tab-btn <?= $selectedFolder === 'services' ? 'active' : '' ?>">
            <i class="fas fa-cube" style="color: #5B35E8;"></i> Services <span class="folder-tab-badge"><?= $servicesCount ?></span>
        </a>
        <a href="files.php?folder=testimonials<?= $searchQuery ? '&search='.urlencode($searchQuery) : '' ?>" class="folder-tab-btn <?= $selectedFolder === 'testimonials' ? 'active' : '' ?>">
            <i class="fas fa-quote-left" style="color: #0EA5E9;"></i> Testimonials <span class="folder-tab-badge"><?= $testimonialsCount ?></span>
        </a>
        <a href="files.php?folder=projects<?= $searchQuery ? '&search='.urlencode($searchQuery) : '' ?>" class="folder-tab-btn <?= $selectedFolder === 'projects' ? 'active' : '' ?>">
            <i class="fas fa-folder" style="color: #10B981;"></i> Projects <span class="folder-tab-badge"><?= $projectsCount ?></span>
        </a>
        <a href="files.php?folder=general<?= $searchQuery ? '&search='.urlencode($searchQuery) : '' ?>" class="folder-tab-btn <?= $selectedFolder === 'general' ? 'active' : '' ?>">
            <i class="fas fa-image" style="color: #64748B;"></i> General <span class="folder-tab-badge"><?= $generalCount ?></span>
        </a>
    </div>

    <!-- Search & Sort Controls -->
    <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
        <!-- Search Input -->
        <form method="GET" action="files.php" style="margin: 0; display: flex; align-items: center;">
            <input type="hidden" name="folder" value="<?= htmlspecialchars($selectedFolder) ?>">
            <input type="hidden" name="sort" value="<?= htmlspecialchars($sortOption) ?>">
            <div style="position: relative;">
                <input type="text" name="search" value="<?= htmlspecialchars($searchQuery) ?>" placeholder="Search files..." class="form-input" style="padding-left: 32px; font-size: 12.5px; height: 36px; width: 190px;" id="mediaSearchInput">
                <i class="fas fa-search" style="position: absolute; left: 10px; top: 11px; color: var(--text-tertiary); font-size: 12px;"></i>
            </div>
        </form>

        <!-- Sort Select -->
        <select class="form-select" style="font-size: 12.5px; height: 36px; padding: 4px 10px; width: 140px;" onchange="changeSort(this.value)">
            <option value="newest" <?= $sortOption === 'newest' ? 'selected' : '' ?>>Newest First</option>
            <option value="oldest" <?= $sortOption === 'oldest' ? 'selected' : '' ?>>Oldest First</option>
            <option value="size_desc" <?= $sortOption === 'size_desc' ? 'selected' : '' ?>>Largest Size</option>
            <option value="size_asc" <?= $sortOption === 'size_asc' ? 'selected' : '' ?>>Smallest Size</option>
            <option value="name" <?= $sortOption === 'name' ? 'selected' : '' ?>>File Name A-Z</option>
        </select>

        <!-- View Switcher -->
        <div style="display: flex; border: 1px solid var(--border-light); border-radius: var(--radius-md); overflow: hidden;">
            <button type="button" class="btn btn-ghost btn-sm" id="viewGridBtn" style="background: var(--bg-hover); color: var(--primary); padding: 7px 10px;" onclick="setViewMode('grid')" title="Grid View">
                <i class="fas fa-th-large"></i>
            </button>
            <button type="button" class="btn btn-ghost btn-sm" id="viewListBtn" style="padding: 7px 10px;" onclick="setViewMode('list')" title="List View">
                <i class="fas fa-list"></i>
            </button>
        </div>
    </div>
</div>

<!-- Select All Checkbox Line -->
<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; font-size: 12px; color: var(--text-secondary); padding: 0 4px;">
    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-weight: 500;">
        <input type="checkbox" id="masterCheckbox" onchange="toggleSelectAllFiles(this)" style="cursor: pointer; accent-color: var(--primary); width: 15px; height: 15px;">
        <span>Select All (<?= count($files) ?> files)</span>
    </label>
    <span>Showing <?= count($files) ?> of <?= $totalFiles ?> items</span>
</div>

<!-- MEDIA GRID VIEW -->
<div id="mediaGridView" class="media-grid">
    <?php foreach ($files as $file): 
        $cleanPath = ltrim($file['file_path'], '/');
        $fullUrl = (str_starts_with($file['file_path'], 'http://') || str_starts_with($file['file_path'], 'https://'))
            ? $file['file_path']
            : $site_root_url . '/' . $cleanPath;
        $folderCategory = $file['folder'] ?? 'general';
        $badgeClass = 'badge-' . $folderCategory;
        $sizeFormatted = formatBytes((int)$file['file_size']);
    ?>
    <div class="media-card" id="file-card-<?= $file['id'] ?>" data-id="<?= $file['id'] ?>" data-name="<?= htmlspecialchars($file['file_name']) ?>" data-path="<?= htmlspecialchars($cleanPath) ?>" data-url="<?= htmlspecialchars($fullUrl) ?>" data-folder="<?= htmlspecialchars($folderCategory) ?>">
        <input type="checkbox" value="<?= $file['id'] ?>" class="file-item-checkbox media-checkbox" onchange="onFileCheckboxChange(this)">
        
        <span class="media-badge-tag <?= $badgeClass ?>"><?= htmlspecialchars($folderCategory) ?></span>

        <div class="media-thumb-container" onclick="openFileInspector(<?= htmlspecialchars(json_encode($file)) ?>)">
            <img src="../<?= htmlspecialchars($cleanPath) ?>" alt="<?= htmlspecialchars($file['file_title'] ?? $file['file_name']) ?>" class="media-thumb-img" loading="lazy" onerror="this.onerror=null; this.src='../assets/images/cta-banner.png';">
        </div>

        <div class="media-info">
            <div class="media-filename" title="<?= htmlspecialchars($file['file_name']) ?>">
                <?= htmlspecialchars($file['file_title'] ?: $file['file_name']) ?>
            </div>
            
            <div class="media-meta-row">
                <span><i class="fas fa-hdd"></i> <?= $sizeFormatted ?></span>
                <span><?= date('M d', strtotime($file['uploaded_at'])) ?></span>
            </div>

            <div class="media-actions-bar">
                <button type="button" class="btn-icon-action" onclick="copyToClipboard('<?= htmlspecialchars($cleanPath) ?>', 'Relative Path copied!')" title="Copy path to use in service / testimonial">
                    <i class="fas fa-copy"></i> Path
                </button>
                <button type="button" class="btn-icon-action" onclick="copyToClipboard('<?= htmlspecialchars($fullUrl) ?>', 'Full URL copied!')" title="Copy Full URL">
                    <i class="fas fa-link"></i> URL
                </button>
                <button type="button" class="btn-icon-action" onclick="openFileInspector(<?= htmlspecialchars(json_encode($file)) ?>)" title="Inspect & Options">
                    <i class="fas fa-eye"></i>
                </button>
                <form method="POST" style="margin: 0;" onsubmit="return confirm('Delete this file permanently?')">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= $file['id'] ?>">
                    <button type="submit" class="btn-icon-action" style="color: var(--danger);" title="Delete file">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- MEDIA LIST VIEW (TABLE) -->
<div id="mediaListView" class="card" style="display: none; margin-bottom: 30px;">
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th style="width: 40px; text-align: center;">
                        <input type="checkbox" onchange="toggleSelectAllFiles(this)" style="cursor: pointer; accent-color: var(--primary);">
                    </th>
                    <th style="width: 60px;">Preview</th>
                    <th>File Details</th>
                    <th>Category</th>
                    <th>Size</th>
                    <th>Uploaded Date</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($files as $file): 
                    $cleanPath = ltrim($file['file_path'], '/');
                    $fullUrl = (str_starts_with($file['file_path'], 'http://') || str_starts_with($file['file_path'], 'https://'))
                        ? $file['file_path']
                        : $site_root_url . '/' . $cleanPath;
                    $folderCategory = $file['folder'] ?? 'general';
                    $badgeClass = 'badge-' . $folderCategory;
                    $sizeFormatted = formatBytes((int)$file['file_size']);
                ?>
                <tr id="table-row-<?= $file['id'] ?>">
                    <td style="text-align: center;">
                        <input type="checkbox" value="<?= $file['id'] ?>" class="file-item-checkbox" onchange="onFileCheckboxChange(this)" style="cursor: pointer; accent-color: var(--primary);">
                    </td>
                    <td>
                        <img src="../<?= htmlspecialchars($cleanPath) ?>" alt="Thumb" style="width: 44px; height: 44px; border-radius: var(--radius-md); object-fit: cover; border: 1px solid var(--border-light); cursor: pointer;" onclick="openFileInspector(<?= htmlspecialchars(json_encode($file)) ?>)">
                    </td>
                    <td>
                        <div style="font-weight: 600; color: var(--text-primary); font-size: 13px;"><?= htmlspecialchars($file['file_name']) ?></div>
                        <div style="font-size: 11px; color: var(--text-secondary); font-family: monospace;"><?= htmlspecialchars($cleanPath) ?></div>
                    </td>
                    <td>
                        <span class="media-badge-tag <?= $badgeClass ?>" style="position: static; display: inline-block;"><?= htmlspecialchars($folderCategory) ?></span>
                    </td>
                    <td style="font-size: 13px; font-weight: 500; color: var(--text-primary);"><?= $sizeFormatted ?></td>
                    <td style="font-size: 12px; color: var(--text-secondary);"><?= date('M d, Y', strtotime($file['uploaded_at'])) ?></td>
                    <td style="text-align: right;">
                        <div style="display: flex; gap: 6px; justify-content: flex-end;">
                            <button type="button" class="btn btn-ghost btn-sm" onclick="copyToClipboard('<?= htmlspecialchars($cleanPath) ?>', 'Relative Path copied!')" title="Copy Path">
                                <i class="fas fa-copy"></i>
                            </button>
                            <button type="button" class="btn btn-ghost btn-sm" onclick="copyToClipboard('<?= htmlspecialchars($fullUrl) ?>', 'Full URL copied!')" title="Copy Full URL">
                                <i class="fas fa-link"></i>
                            </button>
                            <button type="button" class="btn btn-ghost btn-sm" onclick="openFileInspector(<?= htmlspecialchars(json_encode($file)) ?>)" title="View Details">
                                <i class="fas fa-eye"></i>
                            </button>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this file permanently?')">
                                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $file['id'] ?>">
                                <button type="submit" class="btn btn-ghost btn-sm" style="color: var(--danger);" title="Delete">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if (empty($files)): ?>
<div class="card" style="text-align: center; padding: 60px 20px;">
    <div style="width: 68px; height: 68px; background: var(--bg-surface-secondary); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
        <i class="fas fa-images" style="font-size: 28px; color: var(--text-tertiary);"></i>
    </div>
    <h3 style="font-size: 17px; font-weight: 600; color: var(--text-primary); margin-bottom: 6px;">No Media Files Found</h3>
    <p style="color: var(--text-secondary); font-size: 13px; max-width: 400px; margin: 0 auto 20px;">
        <?= $searchQuery ? 'No files match your search keyword "' . htmlspecialchars($searchQuery) . '". Try a different search.' : 'Upload image assets or click Sync Disk Files to import existing images from your server directory.' ?>
    </p>
    <div style="display: flex; gap: 10px; justify-content: center;">
        <button class="btn btn-primary btn-sm" onclick="toggleUploadBox()"><i class="fas fa-upload"></i> Upload First Image</button>
        <form method="POST" style="display:inline;">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <input type="hidden" name="action" value="sync">
            <button type="submit" class="btn btn-secondary btn-sm"><i class="fas fa-sync-alt"></i> Sync Disk Files</button>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- File Details & Inspector Modal -->
<div class="modal" id="fileInspectorModal">
    <div class="modal-backdrop" onclick="closeInspectorModal()"></div>
    <div class="modal-content" style="max-width: 720px;">
        <div class="modal-header">
            <h3 class="modal-title"><i class="fas fa-file-image"></i> <span id="inspectorTitle">File Details</span></h3>
            <button class="modal-close" onclick="closeInspectorModal()"><i class="fas fa-times"></i></button>
        </div>
        <div style="display: grid; grid-template-columns: 280px 1fr; gap: 24px; padding: 20px;">
            <!-- Left: High Res Preview -->
            <div style="background: var(--bg-surface-secondary); border-radius: var(--radius-lg); border: 1px solid var(--border-light); padding: 12px; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center;">
                <img id="inspectorImage" src="" alt="File Preview" style="max-width: 100%; max-height: 220px; border-radius: var(--radius-md); object-fit: contain; box-shadow: var(--shadow-sm);">
                <div style="margin-top: 14px; width: 100%; display: flex; gap: 8px;">
                    <a id="inspectorDownloadLink" href="" download class="btn btn-secondary btn-sm" style="flex: 1; text-align: center;">
                        <i class="fas fa-download"></i> Download
                    </a>
                    <a id="inspectorOpenLink" href="" target="_blank" class="btn btn-secondary btn-sm" style="flex: 1; text-align: center;">
                        <i class="fas fa-external-link-alt"></i> Open
                    </a>
                </div>
            </div>

            <!-- Right: Details & Editable Meta -->
            <div>
                <form method="POST" action="files.php">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    <input type="hidden" name="action" value="update_info">
                    <input type="hidden" name="id" id="inspectorFileId">

                    <div class="form-group" style="margin-bottom: 12px;">
                        <label class="form-label" style="font-size: 12px;"><i class="fas fa-tag"></i> Title / Display Name</label>
                        <input type="text" name="file_title" id="inspectorInputTitle" class="form-input" style="font-size: 13px;">
                    </div>

                    <div class="form-group" style="margin-bottom: 12px;">
                        <label class="form-label" style="font-size: 12px;"><i class="fas fa-folder"></i> Assigned Category</label>
                        <select name="folder" id="inspectorSelectFolder" class="form-select" style="font-size: 13px;">
                            <option value="services">💼 Services (uploads/services/)</option>
                            <option value="testimonials">👤 Testimonials (uploads/testimonials/)</option>
                            <option value="projects">📁 Projects (uploads/projects/)</option>
                            <option value="general">🌐 General Media (uploads/general/)</option>
                        </select>
                    </div>

                    <!-- Quick Copy Path Box -->
                    <div style="background: var(--bg-surface-secondary); border-radius: var(--radius-md); padding: 10px 12px; margin-bottom: 12px;">
                        <div style="font-size: 11px; font-weight: 600; color: var(--text-secondary); margin-bottom: 4px;">Relative Path (Use in Services / Testimonials):</div>
                        <div style="display: flex; gap: 6px;">
                            <input type="text" id="inspectorRelativePath" readonly class="form-input" style="font-size: 11.5px; padding: 4px 8px; font-family: monospace; background: var(--bg-surface);">
                            <button type="button" class="btn btn-primary btn-sm" onclick="copyToClipboard(document.getElementById('inspectorRelativePath').value, 'Relative path copied!')">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Full URL Box -->
                    <div style="background: var(--bg-surface-secondary); border-radius: var(--radius-md); padding: 10px 12px; margin-bottom: 14px;">
                        <div style="font-size: 11px; font-weight: 600; color: var(--text-secondary); margin-bottom: 4px;">Direct URL:</div>
                        <div style="display: flex; gap: 6px;">
                            <input type="text" id="inspectorFullUrl" readonly class="form-input" style="font-size: 11.5px; padding: 4px 8px; font-family: monospace; background: var(--bg-surface);">
                            <button type="button" class="btn btn-secondary btn-sm" onclick="copyToClipboard(document.getElementById('inspectorFullUrl').value, 'Full URL copied!')">
                                <i class="fas fa-link"></i>
                            </button>
                        </div>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border-light); padding-top: 12px;">
                        <div id="inspectorMetaText" style="font-size: 11px; color: var(--text-secondary);"></div>
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Toast Notice for Copy -->
<div id="copyToast" class="toast-notice">
    <i class="fas fa-check-circle" style="font-size: 16px;"></i>
    <span id="copyToastMessage">Copied to clipboard!</span>
</div>

<script>
// Toggle Upload Box
function toggleUploadBox() {
    const box = document.getElementById('uploadSection');
    if (box.style.display === 'none' || !box.style.display) {
        box.style.display = 'block';
        box.scrollIntoView({ behavior: 'smooth', block: 'start' });
    } else {
        box.style.display = 'none';
    }
}

// Handle File Selection Preview in Upload Form
function handleFileSelect(input) {
    const previewBox = document.getElementById('fileQueuePreview');
    const namesBox = document.getElementById('fileQueueNames');
    if (input.files && input.files.length > 0) {
        let names = [];
        for (let i = 0; i < input.files.length; i++) {
            names.push('• ' + input.files[i].name + ' (' + (input.files[i].size / 1024).toFixed(1) + ' KB)');
        }
        namesBox.innerHTML = names.join('<br>');
        previewBox.style.display = 'block';
    } else {
        previewBox.style.display = 'none';
    }
}

// Drag & Drop visual events
const dropzone = document.getElementById('dropzoneBox');
if (dropzone) {
    ['dragenter', 'dragover'].forEach(eventName => {
        dropzone.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropzone.classList.add('dragover');
        }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropzone.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropzone.classList.remove('dragover');
        }, false);
    });

    dropzone.addEventListener('drop', (e) => {
        const dt = e.dataTransfer;
        const files = dt.files;
        const fileInput = document.getElementById('fileInputElement');
        fileInput.files = files;
        handleFileSelect(fileInput);
    }, false);
}

// Change Sort Dropdown
function changeSort(sortVal) {
    const url = new URL(window.location.href);
    url.searchParams.set('sort', sortVal);
    window.location.href = url.toString();
}

// View Mode Switching (Grid vs List)
function setViewMode(mode) {
    const gridView = document.getElementById('mediaGridView');
    const listView = document.getElementById('mediaListView');
    const gridBtn = document.getElementById('viewGridBtn');
    const listBtn = document.getElementById('viewListBtn');

    if (mode === 'grid') {
        if (gridView) gridView.style.display = 'grid';
        if (listView) listView.style.display = 'none';
        gridBtn.style.background = 'var(--bg-hover)';
        gridBtn.style.color = 'var(--primary)';
        listBtn.style.background = 'transparent';
        listBtn.style.color = 'var(--text-secondary)';
        localStorage.setItem('media_view_mode', 'grid');
    } else {
        if (gridView) gridView.style.display = 'none';
        if (listView) listView.style.display = 'block';
        listBtn.style.background = 'var(--bg-hover)';
        listBtn.style.color = 'var(--primary)';
        gridBtn.style.background = 'transparent';
        gridBtn.style.color = 'var(--text-secondary)';
        localStorage.setItem('media_view_mode', 'list');
    }
}

// Restore saved view mode
(function() {
    const savedMode = localStorage.getItem('media_view_mode');
    if (savedMode === 'list') {
        setViewMode('list');
    }
})();

// Bulk Checkbox Handlers
function toggleSelectAllFiles(master) {
    const checkboxes = document.querySelectorAll('.file-item-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = master.checked;
        const card = document.getElementById('file-card-' + cb.value);
        if (card) {
            if (master.checked) card.classList.add('selected');
            else card.classList.remove('selected');
        }
        const row = document.getElementById('table-row-' + cb.value);
        if (row) {
            row.style.backgroundColor = master.checked ? 'rgba(91,53,232,0.05)' : '';
        }
    });
    updateBulkBar();
}

function onFileCheckboxChange(cb) {
    const card = document.getElementById('file-card-' + cb.value);
    if (card) {
        if (cb.checked) card.classList.add('selected');
        else card.classList.remove('selected');
    }
    const row = document.getElementById('table-row-' + cb.value);
    if (row) {
        row.style.backgroundColor = cb.checked ? 'rgba(91,53,232,0.05)' : '';
    }
    updateBulkBar();
}

function updateBulkBar() {
    const checked = document.querySelectorAll('.file-item-checkbox:checked');
    const bar = document.getElementById('bulkBar');
    const text = document.getElementById('bulkCountText');
    if (checked.length > 0) {
        bar.style.display = 'flex';
        text.innerText = checked.length + ' item(s) selected';
    } else {
        bar.style.display = 'none';
        const master = document.getElementById('masterCheckbox');
        if (master) master.checked = false;
    }
}

function clearBulkSelection() {
    document.querySelectorAll('.file-item-checkbox').forEach(cb => cb.checked = false);
    document.querySelectorAll('.media-card').forEach(c => c.classList.remove('selected'));
    document.querySelectorAll('tr[id^="table-row-"]').forEach(r => r.style.backgroundColor = '');
    const master = document.getElementById('masterCheckbox');
    if (master) master.checked = false;
    updateBulkBar();
}

function submitBulkDelete() {
    const checked = document.querySelectorAll('.file-item-checkbox:checked');
    if (checked.length === 0) return;
    if (!confirm('Are you sure you want to permanently delete ' + checked.length + ' selected file(s)?')) return;

    document.getElementById('bulkActionField').value = 'bulk_delete';

    const container = document.getElementById('bulkIdsContainer');
    container.innerHTML = '';
    checked.forEach(cb => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'ids[]';
        input.value = cb.value;
        container.appendChild(input);
    });
    document.getElementById('bulkForm').submit();
}

function submitBulkFolderUpdate() {
    const checked = document.querySelectorAll('.file-item-checkbox:checked');
    if (checked.length === 0) return;
    
    const folderSelect = document.getElementById('bulkFolderSelect');
    const folder = folderSelect.value;
    
    if (!folder) {
        alert('Please select a target folder.');
        return;
    }
    
    if (!confirm('Move ' + checked.length + ' file(s) to ' + folder + '?')) return;

    document.getElementById('bulkActionField').value = 'bulk_update_folder';
    document.getElementById('bulkFolderField').value = folder;

    const container = document.getElementById('bulkIdsContainer');
    container.innerHTML = '';
    checked.forEach(cb => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'ids[]';
        input.value = cb.value;
        container.appendChild(input);
    });
    document.getElementById('bulkForm').submit();
}

// Copy to Clipboard Toast
function copyToClipboard(text, msg = 'Copied to clipboard!') {
    if (!navigator.clipboard) {
        const el = document.createElement('textarea');
        el.value = text;
        document.body.appendChild(el);
        el.select();
        document.execCommand('copy');
        document.body.removeChild(el);
    } else {
        navigator.clipboard.writeText(text);
    }

    const toast = document.getElementById('copyToast');
    const toastMsg = document.getElementById('copyToastMessage');
    toastMsg.innerText = msg;
    toast.classList.add('show');
    setTimeout(() => {
        toast.classList.remove('show');
    }, 2400);
}

// File Inspector Lightbox Modal
function openFileInspector(file) {
    const cleanPath = file.file_path.replace(/^\/+/, '');
    const siteRoot = '<?= $site_root_url ?>';
    const fullUrl = (file.file_path.startsWith('http://') || file.file_path.startsWith('https://')) ? file.file_path : siteRoot + '/' + cleanPath;

    document.getElementById('inspectorFileId').value = file.id;
    document.getElementById('inspectorTitle').innerText = file.file_name;
    document.getElementById('inspectorInputTitle').value = file.file_title || '';
    document.getElementById('inspectorSelectFolder').value = file.folder || 'general';
    document.getElementById('inspectorRelativePath').value = cleanPath;
    document.getElementById('inspectorFullUrl').value = fullUrl;
    
    document.getElementById('inspectorImage').src = '../' + cleanPath;
    document.getElementById('inspectorDownloadLink').href = '../' + cleanPath;
    document.getElementById('inspectorOpenLink').href = fullUrl;

    document.getElementById('inspectorMetaText').innerText = 'Size: ' + (file.file_size ? (file.file_size/1024).toFixed(1) + ' KB' : 'N/A') + ' • ' + (file.file_type || '');

    document.getElementById('fileInspectorModal').classList.add('active');
}

function closeInspectorModal() {
    document.getElementById('fileInspectorModal').classList.remove('active');
}
</script>

<?php require_once __DIR__ . '/../../../admin/includes/footer-pro.php'; ?>
