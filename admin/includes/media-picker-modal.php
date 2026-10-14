<?php
$site_root_url = $site_root_url ?? ($base_url ?? '');
?>
<!-- Universal Media Picker Modal Component -->
<style>
.media-picker-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 100000;
    display: none;
    align-items: center;
    justify-content: center;
}
.media-picker-modal.active {
    display: flex;
}
.media-picker-backdrop {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(4px);
}
.media-picker-dialog {
    position: relative;
    z-index: 2;
    background: var(--bg-surface, #ffffff);
    border: 1px solid var(--border-light, rgba(0,0,0,0.1));
    border-radius: var(--radius-xl, 16px);
    width: 92%;
    max-width: 820px;
    max-height: 88vh;
    display: flex;
    flex-direction: column;
    box-shadow: 0 25px 60px rgba(0,0,0,0.3);
    animation: modalPop 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    overflow: hidden;
}
@keyframes modalPop {
    from { opacity: 0; transform: scale(0.96); }
    to { opacity: 1; transform: scale(1); }
}
.media-picker-header {
    padding: 16px 20px;
    border-bottom: 1px solid var(--border-light, rgba(0,0,0,0.08));
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: var(--bg-surface, #ffffff);
}
.media-picker-title {
    font-size: 16px;
    font-weight: 700;
    color: var(--text-primary, #111827);
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 0;
}
.media-picker-tabs {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 10px 20px;
    background: var(--bg-surface-secondary, #f8fafc);
    border-bottom: 1px solid var(--border-light, rgba(0,0,0,0.06));
    flex-wrap: wrap;
}
.media-picker-tab-btn {
    padding: 5px 12px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 500;
    color: var(--text-secondary, #64748b);
    border: 1px solid transparent;
    background: transparent;
    cursor: pointer;
    transition: all 0.15s ease;
}
.media-picker-tab-btn:hover {
    color: var(--text-primary, #0f172a);
    background: var(--bg-hover, rgba(0,0,0,0.04));
}
.media-picker-tab-btn.active {
    background: var(--primary-soft, rgba(91, 53, 232, 0.1));
    color: var(--primary, #5B35E8);
    font-weight: 600;
    border-color: rgba(91, 53, 232, 0.2);
}
.media-picker-search-bar {
    padding: 10px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    border-bottom: 1px solid var(--border-light, rgba(0,0,0,0.06));
}
.media-picker-body {
    padding: 18px 20px;
    overflow-y: auto;
    flex: 1;
    min-height: 280px;
}
.media-picker-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
    gap: 14px;
}
.media-picker-item {
    background: var(--bg-surface, #ffffff);
    border: 2px solid var(--border-light, rgba(0,0,0,0.08));
    border-radius: 10px;
    overflow: hidden;
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    display: flex;
    flex-direction: column;
    text-align: center;
    position: relative;
}
.media-picker-item:hover {
    transform: translateY(-3px);
    border-color: var(--primary, #5B35E8);
    box-shadow: 0 8px 18px rgba(91, 53, 232, 0.15);
}
.media-picker-item.selected {
    border-color: var(--primary, #5B35E8);
    box-shadow: 0 0 0 3px rgba(91, 53, 232, 0.25);
}
.media-picker-item-thumb {
    width: 100%;
    height: 100px;
    object-fit: cover;
    background: var(--bg-surface-secondary, #f1f5f9);
}
.media-picker-item-name {
    font-size: 11px;
    font-weight: 500;
    color: var(--text-primary, #1e293b);
    padding: 6px 8px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.media-picker-item-badge {
    position: absolute;
    top: 4px;
    right: 4px;
    font-size: 9px;
    padding: 2px 5px;
    border-radius: 4px;
    font-weight: 700;
    text-transform: uppercase;
    background: rgba(0,0,0,0.65);
    color: #fff;
}
.media-picker-footer {
    padding: 12px 20px;
    border-top: 1px solid var(--border-light, rgba(0,0,0,0.08));
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: var(--bg-surface, #ffffff);
}
.media-picker-upload-box {
    display: flex;
    align-items: center;
    gap: 8px;
}
</style>

<div class="media-picker-modal" id="universalMediaPickerModal">
    <div class="media-picker-backdrop" onclick="closeUniversalMediaPicker()"></div>
    <div class="media-picker-dialog">
        <!-- Header -->
        <div class="media-picker-header">
            <h3 class="media-picker-title">
                <i class="fas fa-images" style="color: var(--primary);"></i>
                <span>Choose Image from Media Library</span>
            </h3>
            <button type="button" class="btn btn-ghost btn-sm" onclick="closeUniversalMediaPicker()" style="font-size: 16px; padding: 4px 8px;">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <!-- Folder Filter Tabs -->
        <div class="media-picker-tabs">
            <button type="button" class="media-picker-tab-btn" data-folder="all" onclick="filterMediaPickerFolder('all')">
                <i class="fas fa-th"></i> All Media
            </button>
            <button type="button" class="media-picker-tab-btn" data-folder="services" onclick="filterMediaPickerFolder('services')">
                <i class="fas fa-cube" style="color: #5B35E8;"></i> Services
            </button>
            <button type="button" class="media-picker-tab-btn" data-folder="testimonials" onclick="filterMediaPickerFolder('testimonials')">
                <i class="fas fa-quote-left" style="color: #0EA5E9;"></i> Testimonials
            </button>
            <button type="button" class="media-picker-tab-btn" data-folder="projects" onclick="filterMediaPickerFolder('projects')">
                <i class="fas fa-folder" style="color: #10B981;"></i> Projects
            </button>
            <button type="button" class="media-picker-tab-btn" data-folder="general" onclick="filterMediaPickerFolder('general')">
                <i class="fas fa-image" style="color: #64748B;"></i> General
            </button>
        </div>

        <!-- Search & Quick Upload -->
        <div class="media-picker-search-bar">
            <div style="position: relative; flex: 1; max-width: 320px;">
                <input type="text" id="mediaPickerSearchInput" placeholder="Search by name..." class="form-input" style="padding-left: 30px; font-size: 12px; height: 32px;" oninput="onMediaPickerSearch(this.value)">
                <i class="fas fa-search" style="position: absolute; left: 10px; top: 10px; color: var(--text-tertiary); font-size: 11px;"></i>
            </div>

            <!-- In-Modal Quick Upload -->
            <div class="media-picker-upload-box">
                <input type="file" id="mediaPickerFileInput" accept=".jpg,.jpeg,.png,.gif,.webp,.svg" style="display: none;" onchange="uploadFromMediaPicker(this)">
                <button type="button" class="btn btn-primary btn-sm" onclick="document.getElementById('mediaPickerFileInput').click()" id="mediaPickerUploadBtn" style="font-size: 12px; padding: 5px 12px;">
                    <i class="fas fa-cloud-upload-alt"></i> Upload New
                </button>
            </div>
        </div>

        <!-- Body / Grid -->
        <div class="media-picker-body" id="mediaPickerBody">
            <div id="mediaPickerLoading" style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 200px; color: var(--text-secondary);">
                <i class="fas fa-spinner fa-spin" style="font-size: 28px; color: var(--primary); margin-bottom: 10px;"></i>
                <span style="font-size: 13px;">Loading images from server...</span>
            </div>

            <div class="media-picker-grid" id="mediaPickerGrid" style="display: none;"></div>

            <div id="mediaPickerEmpty" style="display: none; text-align: center; padding: 40px 20px; color: var(--text-secondary);">
                <i class="fas fa-images" style="font-size: 32px; color: var(--text-tertiary); margin-bottom: 8px; display: block;"></i>
                <div style="font-size: 14px; font-weight: 600; color: var(--text-primary);">No Images Found</div>
                <div style="font-size: 12px; margin-top: 4px;">Upload an image or select another folder.</div>
            </div>
        </div>

        <!-- Footer -->
        <div class="media-picker-footer">
            <div style="font-size: 12px; color: var(--text-secondary);" id="mediaPickerSelectionInfo">
                Click any image to select it immediately.
            </div>
            <button type="button" class="btn btn-secondary btn-sm" onclick="closeUniversalMediaPicker()">Cancel</button>
        </div>
    </div>
</div>

<script>
let mediaPickerState = {
    activeFolder: 'all',
    searchQuery: '',
    files: [],
    targetInputSelector: null,
    targetPreviewSelector: null,
    onSelectCallback: null
};

// Open the Universal Media Picker
function openUniversalMediaPicker(options = {}) {
    mediaPickerState.activeFolder = options.folder || 'all';
    mediaPickerState.targetInputSelector = options.targetInput || null;
    mediaPickerState.targetPreviewSelector = options.targetPreview || null;
    mediaPickerState.onSelectCallback = options.onSelect || null;
    mediaPickerState.searchQuery = '';

    const searchInput = document.getElementById('mediaPickerSearchInput');
    if (searchInput) searchInput.value = '';

    // Set active tab button
    document.querySelectorAll('.media-picker-tab-btn').forEach(btn => {
        if (btn.getAttribute('data-folder') === mediaPickerState.activeFolder) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });

    const modal = document.getElementById('universalMediaPickerModal');
    if (modal) modal.classList.add('active');

    fetchMediaPickerFiles();
}

function closeUniversalMediaPicker() {
    const modal = document.getElementById('universalMediaPickerModal');
    if (modal) modal.classList.remove('active');
}

function filterMediaPickerFolder(folder) {
    mediaPickerState.activeFolder = folder;
    document.querySelectorAll('.media-picker-tab-btn').forEach(btn => {
        if (btn.getAttribute('data-folder') === folder) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });
    fetchMediaPickerFiles();
}

let mediaPickerSearchTimeout = null;
function onMediaPickerSearch(val) {
    clearTimeout(mediaPickerSearchTimeout);
    mediaPickerSearchTimeout = setTimeout(() => {
        mediaPickerState.searchQuery = val.trim();
        fetchMediaPickerFiles();
    }, 250);
}

// Fetch files from admin/api/files.php
async function fetchMediaPickerFiles() {
    const loading = document.getElementById('mediaPickerLoading');
    const grid = document.getElementById('mediaPickerGrid');
    const empty = document.getElementById('mediaPickerEmpty');

    if (loading) loading.style.display = 'flex';
    if (grid) grid.style.display = 'none';
    if (empty) empty.style.display = 'none';

    try {
        let apiUrl = '<?= $site_root_url ?>/admin/api/files.php?action=list&folder=' + encodeURIComponent(mediaPickerState.activeFolder);
        if (mediaPickerState.searchQuery) {
            apiUrl += '&search=' + encodeURIComponent(mediaPickerState.searchQuery);
        }

        const res = await fetch(apiUrl);
        const data = await res.json();

        if (loading) loading.style.display = 'none';

        if (data.success && data.files && data.files.length > 0) {
            mediaPickerState.files = data.files;
            renderMediaPickerGrid(data.files);
            if (grid) grid.style.display = 'grid';
        } else {
            if (empty) empty.style.display = 'block';
        }
    } catch (err) {
        console.error('Failed to load media files:', err);
        if (loading) loading.style.display = 'none';
        if (empty) {
            empty.innerHTML = '<div style="color:var(--danger);"><i class="fas fa-exclamation-triangle"></i> Failed to load media files.</div>';
            empty.style.display = 'block';
        }
    }
}

// Render grid items
function renderMediaPickerGrid(files) {
    const grid = document.getElementById('mediaPickerGrid');
    if (!grid) return;
    grid.innerHTML = '';

    files.forEach(f => {
        const item = document.createElement('div');
        item.className = 'media-picker-item';
        item.title = f.file_title || f.file_name;
        
        let thumbUrl = f.relative_path;
        if (!thumbUrl.startsWith('http://') && !thumbUrl.startsWith('https://') && !thumbUrl.startsWith('/')) {
            thumbUrl = '<?= $site_root_url ?>/' + thumbUrl;
        }

        item.innerHTML = `
            <img src="${thumbUrl}" alt="${f.file_name}" class="media-picker-item-thumb" onerror="this.onerror=null; this.src='<?= $site_root_url ?>/assets/images/cta-banner.png';">
            <span class="media-picker-item-badge">${f.folder || 'media'}</span>
            <div class="media-picker-item-name">${f.file_title || f.file_name}</div>
        `;

        item.onclick = function() {
            selectMediaPickerFile(f);
        };

        grid.appendChild(item);
    });
}

// Direct selection handler
function selectMediaPickerFile(file) {
    // 1. Populate Target Input
    if (mediaPickerState.targetInputSelector) {
        const targetInput = document.querySelector(mediaPickerState.targetInputSelector);
        if (targetInput) {
            targetInput.value = file.relative_path;
            targetInput.dispatchEvent(new Event('input', { bubbles: true }));
            targetInput.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }

    // 2. Update Target Preview
    if (mediaPickerState.targetPreviewSelector) {
        const targetPreview = document.querySelector(mediaPickerState.targetPreviewSelector);
        if (targetPreview) {
            let fullSrc = file.relative_path;
            if (!fullSrc.startsWith('http://') && !fullSrc.startsWith('https://') && !fullSrc.startsWith('/')) {
                fullSrc = '<?= $site_root_url ?>/' + fullSrc;
            }
            targetPreview.src = fullSrc;
            targetPreview.style.display = 'block';

            // Also hide placeholder if exists
            const placeholder = document.querySelector(mediaPickerState.targetPreviewSelector + 'Placeholder');
            if (placeholder) placeholder.style.display = 'none';
        }
    }

    // 3. Callback execution
    if (typeof mediaPickerState.onSelectCallback === 'function') {
        mediaPickerState.onSelectCallback(file);
    }

    closeUniversalMediaPicker();
}

// Upload file directly from inside modal
async function uploadFromMediaPicker(input) {
    if (!input.files || input.files.length === 0) return;
    const file = input.files[0];

    const uploadBtn = document.getElementById('mediaPickerUploadBtn');
    const origText = uploadBtn.innerHTML;
    uploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';
    uploadBtn.disabled = true;

    const formData = new FormData();
    formData.append('file', file);
    formData.append('folder', mediaPickerState.activeFolder === 'all' ? 'general' : mediaPickerState.activeFolder);

    try {
        const res = await fetch('<?= $site_root_url ?>/admin/api/files.php?action=upload', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();

        uploadBtn.innerHTML = origText;
        uploadBtn.disabled = false;
        input.value = '';

        if (data.success && data.file) {
            // Select newly uploaded file directly!
            selectMediaPickerFile(data.file);
        } else {
            alert('Upload failed: ' + (data.error || 'Unknown error'));
        }
    } catch (err) {
        uploadBtn.innerHTML = origText;
        uploadBtn.disabled = false;
        input.value = '';
        alert('Upload error: ' + err.message);
    }
}
</script>
