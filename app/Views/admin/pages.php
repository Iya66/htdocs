<?php
$pageTitle = 'Page & SEO Management';
require_once __DIR__ . '/../../../admin/includes/header.php';
?>

<div class="admin-container">
    <div class="saas-page-header">
        <div>
            <h1 class="page-title"><i class="fas fa-file-alt" style="color: var(--primary);"></i> Pages & SEO Management</h1>
            <p class="page-subtitle">Control frontend page visibility, custom SEO meta tags, and schema markup.</p>
        </div>
        <div class="header-actions">
            <button class="btn btn-primary" onclick="openPageModal()"><i class="fas fa-plus"></i> Add New Page</button>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="stats-grid fade-in" style="margin-bottom: 24px; display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;">
        <div class="stat-card" style="background: var(--card); padding: 24px; border-radius: var(--r); border: 1px solid rgba(108,59,255,0.08);">
            <h3>Total Pages</h3>
            <p style="font-size: 2rem; font-weight: 700; color: var(--text);"><?= $total ?></p>
        </div>
        <div class="stat-card" style="background: var(--card); padding: 24px; border-radius: var(--r); border: 1px solid rgba(37, 211, 102, 0.2);">
            <h3>Active Pages</h3>
            <p style="font-size: 2rem; font-weight: 700; color: #25D366;"><?= $active ?></p>
        </div>
        <div class="stat-card" style="background: var(--card); padding: 24px; border-radius: var(--r); border: 1px solid rgba(239, 68, 68, 0.2);">
            <h3>Disabled Pages</h3>
            <p style="font-size: 2rem; font-weight: 700; color: #ef4444;"><?= $disabled ?></p>
        </div>
    </div>

    <div class="admin-card fade-in">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div class="bulk-actions" style="display: flex; gap: 10px; align-items: center;">
                <select id="bulkAction" class="form-control" style="width: 150px; padding: 8px;">
                    <option value="">Bulk Actions</option>
                    <option value="1">Enable Selected</option>
                    <option value="0">Disable Selected</option>
                </select>
                <button class="btn btn-secondary" onclick="applyBulkAction()" style="padding: 8px 16px;">Apply</button>
            </div>
            <div class="search-box">
                <input type="text" id="pageSearch" placeholder="Search pages..." class="form-control" onkeyup="filterPages()" style="padding: 8px 16px; border-radius: 20px;">
            </div>
        </div>

        <div class="table-responsive">
            <table class="table" id="pagesTable">
                <thead>
                    <tr>
                        <th width="40"><input type="checkbox" id="selectAll" onclick="toggleSelectAll()"></th>
                        <th>Page Title</th>
                        <th>Slug URL</th>
                        <th>Status</th>
                        <th>Last Updated</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pages as $p): ?>
                        <tr>
                            <td><input type="checkbox" class="page-checkbox" value="<?= $p['id'] ?>"></td>
                            <td style="font-weight: 600; color: var(--text);"><?= htmlspecialchars($p['page_title']) ?></td>
                            <td style="color: var(--text2);"><?= htmlspecialchars($p['page_slug']) ?></td>
                            <td>
                                <label class="switch" style="position: relative; display: inline-block; width: 44px; height: 24px;">
                                    <input type="checkbox" onchange="toggleStatus(<?= $p['id'] ?>, this.checked)" <?= $p['is_active'] ? 'checked' : '' ?> style="opacity: 0; width: 0; height: 0;">
                                    <span class="slider round" style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: <?= $p['is_active'] ? '#6C3BFF' : '#ccc' ?>; transition: .4s; border-radius: 24px;"></span>
                                </label>
                            </td>
                            <td style="color: var(--text2); font-size: 0.9rem;"><?= date('M j, Y h:i A', strtotime($p['updated_at'])) ?></td>
                            <td class="text-right">
                                <?php if ($p['page_slug'] === 'contact.php'): ?>
                                    <a href="contact-us" class="btn btn-primary" style="padding: 6px 12px; font-size: 0.85rem; text-decoration: none; margin-right: 4px;">
                                        <i class="fas fa-map-location-dot"></i> Edit Map &amp; Info
                                    </a>
                                <?php endif; ?>
                                <button class="btn btn-secondary" onclick="editPage(<?= $p['id'] ?>)" style="padding: 6px 12px; font-size: 0.85rem;"><i class="fas fa-edit"></i> Edit</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Page Editor Modal -->
<div id="pageModal" class="modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 9999; overflow-y: auto;">
    <div class="modal-content" style="background: #fff; max-width: 900px; margin: 40px auto; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
        <div class="modal-header">
            <h2 id="modalTitle" class="modal-title">Edit Page</h2>
            <button type="button" class="modal-close" onclick="closeModal()" aria-label="Close modal">&times;</button>
        </div>
        <form id="pageForm" onsubmit="savePage(event)">
            <input type="hidden" name="action" value="save_page">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <input type="hidden" name="id" id="pageId" value="0">
            
            <div class="modal-body" style="padding: 0;">
                <!-- Tabs -->
                <div class="tabs" style="display: flex; border-bottom: 1px solid #eee; background: #fafafa;">
                    <div class="tab active" onclick="switchTab('general')" style="padding: 16px 24px; cursor: pointer; font-weight: 600; color: var(--primary); border-bottom: 2px solid var(--primary);">General</div>
                    <div class="tab" onclick="switchTab('seo')" style="padding: 16px 24px; cursor: pointer; font-weight: 500; color: var(--text2);">SEO</div>
                    <div class="tab" onclick="switchTab('content')" style="padding: 16px 24px; cursor: pointer; font-weight: 500; color: var(--text2);">Content</div>
                    <div class="tab" onclick="switchTab('advanced')" style="padding: 16px 24px; cursor: pointer; font-weight: 500; color: var(--text2);">Advanced</div>
                </div>
                
                <div style="padding: 32px;">
                    <!-- General Tab -->
                    <div id="tab-general" class="tab-content">
                        <div class="form-group" style="margin-bottom: 20px;">
                            <label style="display: block; margin-bottom: 8px; font-weight: 600;">Page Title *</label>
                            <input type="text" name="page_title" id="pageTitle" class="form-control" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px;">
                        </div>
                        <div class="form-group" style="margin-bottom: 20px;">
                            <label style="display: block; margin-bottom: 8px; font-weight: 600;">Slug URL *</label>
                            <input type="text" name="page_slug" id="pageSlug" class="form-control" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px;">
                            <small style="color: var(--text2); display: block; margin-top: 4px;">e.g., services.php or custom-page.php</small>
                        </div>
                        <div class="form-group" style="margin-bottom: 20px;">
                            <label style="display: flex; align-items: center; gap: 10px; font-weight: 600; cursor: pointer;">
                                <input type="checkbox" name="is_active" id="isActive" value="1" style="width: 20px; height: 20px;">
                                Page is Active (Publicly Accessible)
                            </label>
                        </div>
                    </div>

                    <!-- SEO Tab -->
                    <div id="tab-seo" class="tab-content" style="display: none;">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div class="form-group">
                                <label style="display: block; margin-bottom: 8px; font-weight: 600;">SEO Title</label>
                                <input type="text" name="seo_title" id="seoTitle" class="form-control" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px;">
                            </div>
                            <div class="form-group">
                                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Canonical URL</label>
                                <input type="text" name="canonical_url" id="canonicalUrl" class="form-control" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px;">
                            </div>
                        </div>
                        <div class="form-group" style="margin-top: 20px;">
                            <label style="display: block; margin-bottom: 8px; font-weight: 600;">Meta Description</label>
                            <textarea name="meta_description" id="metaDesc" class="form-control" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; height: 80px;"></textarea>
                        </div>
                        <div class="form-group" style="margin-top: 20px;">
                            <label style="display: block; margin-bottom: 8px; font-weight: 600;">Meta Keywords</label>
                            <input type="text" name="meta_keywords" id="metaKeywords" class="form-control" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px;">
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px;">
                            <div class="form-group">
                                <label style="display: block; margin-bottom: 8px; font-weight: 600;">OG Title (Facebook/LinkedIn)</label>
                                <input type="text" name="og_title" id="ogTitle" class="form-control" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px;">
                            </div>
                            <div class="form-group">
                                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Twitter Title</label>
                                <input type="text" name="twitter_title" id="twitterTitle" class="form-control" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px;">
                            </div>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px;">
                            <div class="form-group">
                                <label style="display: block; margin-bottom: 8px; font-weight: 600;">OG Image URL</label>
                                <input type="text" name="og_image" id="ogImage" class="form-control" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px;">
                            </div>
                            <div class="form-group">
                                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Twitter Image URL</label>
                                <input type="text" name="twitter_image" id="twitterImage" class="form-control" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px;">
                            </div>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px;">
                            <div class="form-group">
                                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Robots Meta</label>
                                <select name="robots_meta" id="robotsMeta" class="form-control" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px;">
                                    <option value="index, follow">index, follow</option>
                                    <option value="noindex, follow">noindex, follow</option>
                                    <option value="index, nofollow">index, nofollow</option>
                                    <option value="noindex, nofollow">noindex, nofollow</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Content Tab -->
                    <div id="tab-content" class="tab-content" style="display: none;">
                        <p style="color: var(--text2); margin-bottom: 20px;">These fields can be rendered dynamically on your page template.</p>
                        <div class="form-group" style="margin-bottom: 20px;">
                            <label style="display: block; margin-bottom: 8px; font-weight: 600;">Hero Section Text</label>
                            <textarea name="hero_section" id="heroSection" class="form-control" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; height: 80px;"></textarea>
                        </div>
                        <div class="form-group" style="margin-bottom: 20px;">
                            <label style="display: block; margin-bottom: 8px; font-weight: 600;">Main Content (HTML Support)</label>
                            <textarea name="main_content" id="mainContent" class="form-control" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; height: 200px; font-family: monospace;"></textarea>
                        </div>
                    </div>

                    <!-- Advanced Tab -->
                    <div id="tab-advanced" class="tab-content" style="display: none;">
                        <div class="form-group" style="margin-bottom: 20px;">
                            <label style="display: block; margin-bottom: 8px; font-weight: 600;">Schema Markup (JSON-LD)</label>
                            <textarea name="schema_markup" id="schemaMarkup" class="form-control" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; height: 120px; font-family: monospace;" placeholder="<script type='application/ld+json'>...</script>"></textarea>
                        </div>
                        <div class="form-group" style="margin-bottom: 20px;">
                            <label style="display: block; margin-bottom: 8px; font-weight: 600;">Custom CSS</label>
                            <textarea name="custom_css" id="customCss" class="form-control" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; height: 100px; font-family: monospace;"></textarea>
                        </div>
                        <div class="form-group" style="margin-bottom: 20px;">
                            <label style="display: block; margin-bottom: 8px; font-weight: 600;">Custom JavaScript</label>
                            <textarea name="custom_js" id="customJs" class="form-control" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; height: 100px; font-family: monospace;"></textarea>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer" style="padding: 24px 32px; border-top: 1px solid #eee; display: flex; justify-content: flex-end; gap: 16px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" id="saveBtn">Save Page Settings</button>
            </div>
        </form>
    </div>
</div>

<script>
// Tab Switching
function switchTab(tabId) {
    document.querySelectorAll('.tab-content').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.tab').forEach(el => {
        el.style.borderBottom = 'none';
        el.style.color = 'var(--text2)';
        el.style.fontWeight = '500';
    });
    
    document.getElementById('tab-' + tabId).style.display = 'block';
    const activeTab = event.currentTarget;
    activeTab.style.borderBottom = '2px solid var(--primary)';
    activeTab.style.color = 'var(--primary)';
    activeTab.style.fontWeight = '600';
}

// Checkbox styling fix for toggle
document.querySelectorAll('.slider').forEach(el => {
    el.addEventListener('click', function() {
        const checkbox = this.previousElementSibling;
        checkbox.checked = !checkbox.checked;
        this.style.backgroundColor = checkbox.checked ? '#6C3BFF' : '#ccc';
        checkbox.dispatchEvent(new Event('change'));
    });
});

function toggleStatus(id, status) {
    const formData = new FormData();
    formData.append('action', 'toggle_status');
    formData.append('csrf_token', '<?= $csrf_token ?>');
    formData.append('id', id);
    formData.append('status', status ? 1 : 0);

    fetch('pages.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (!data.success) alert(data.message);
    })
    .catch(err => console.error(err));
}

function toggleSelectAll() {
    const isChecked = document.getElementById('selectAll').checked;
    document.querySelectorAll('.page-checkbox').forEach(cb => cb.checked = isChecked);
}

function applyBulkAction() {
    const action = document.getElementById('bulkAction').value;
    if (action === '') return;
    
    const ids = Array.from(document.querySelectorAll('.page-checkbox:checked')).map(cb => cb.value);
    if (ids.length === 0) {
        alert('Select at least one page.');
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'bulk_toggle');
    formData.append('csrf_token', '<?= $csrf_token ?>');
    formData.append('ids', JSON.stringify(ids));
    formData.append('status', action);
    
    fetch('pages.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) location.reload();
        else alert(data.message);
    });
}

function filterPages() {
    const filter = document.getElementById('pageSearch').value.toLowerCase();
    const rows = document.querySelectorAll('#pagesTable tbody tr');
    rows.forEach(row => {
        const title = row.cells[1].textContent.toLowerCase();
        const slug = row.cells[2].textContent.toLowerCase();
        if (title.includes(filter) || slug.includes(filter)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function openPageModal() {
    document.getElementById('pageForm').reset();
    document.getElementById('pageId').value = '0';
    document.getElementById('modalTitle').textContent = 'Add New Page';
    document.getElementById('isActive').checked = true;
    document.getElementById('pageModal').style.display = 'block';
    document.querySelector('.tab').click(); // Switch to General
}

function closeModal() {
    document.getElementById('pageModal').style.display = 'none';
}

function editPage(id) {
    const formData = new FormData();
    formData.append('action', 'get_page');
    formData.append('csrf_token', '<?= $csrf_token ?>');
    formData.append('id', id);
    
    fetch('pages.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(res => {
        if (res.success) {
            const data = res.data;
            document.getElementById('pageId').value = data.id;
            document.getElementById('modalTitle').textContent = 'Edit Page: ' + data.page_title;
            
            // General
            document.getElementById('pageTitle').value = data.page_title;
            document.getElementById('pageSlug').value = data.page_slug;
            document.getElementById('isActive').checked = (data.is_active == 1);
            
            // SEO
            document.getElementById('seoTitle').value = data.seo_title || '';
            document.getElementById('canonicalUrl').value = data.canonical_url || '';
            document.getElementById('metaDesc').value = data.meta_description || '';
            document.getElementById('metaKeywords').value = data.meta_keywords || '';
            document.getElementById('ogTitle').value = data.og_title || '';
            document.getElementById('twitterTitle').value = data.twitter_title || '';
            document.getElementById('ogImage').value = data.og_image || '';
            document.getElementById('twitterImage').value = data.twitter_image || '';
            document.getElementById('robotsMeta').value = data.robots_meta || 'index, follow';
            
            // Content
            document.getElementById('heroSection').value = data.hero_section || '';
            document.getElementById('mainContent').value = data.main_content || '';
            
            // Advanced
            document.getElementById('schemaMarkup').value = data.schema_markup || '';
            document.getElementById('customCss').value = data.custom_css || '';
            document.getElementById('customJs').value = data.custom_js || '';
            
            document.getElementById('pageModal').style.display = 'block';
            document.querySelector('.tab').click(); // Switch to General
        }
    });
}

function savePage(e) {
    e.preventDefault();
    const btn = document.getElementById('saveBtn');
    btn.textContent = 'Saving...';
    btn.disabled = true;
    
    const formData = new FormData(e.target);
    fetch('pages.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message);
            btn.textContent = 'Save Page Settings';
            btn.disabled = false;
        }
    })
    .catch(err => {
        console.error(err);
        btn.textContent = 'Save Page Settings';
        btn.disabled = false;
    });
}
</script>

<?php require_once __DIR__ . '/../../../admin/includes/footer.php'; ?>
