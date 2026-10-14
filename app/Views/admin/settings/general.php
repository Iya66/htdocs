<?php
require_once __DIR__ . '/../../../../admin/includes/header.php';
?>

<style>
.settings-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--border-radius); padding: 32px; box-shadow: var(--shadow-sm); margin-bottom: 24px; transition: all var(--transition); }
.settings-card h3 { font-size: 18px; color: var(--text-main); margin-bottom: 16px; display: flex; align-items: center; gap: 12px; font-weight: 700; }
.settings-card h3 i { color: var(--primary); font-size: 20px; }

.form-group { margin-bottom: 24px; }
.form-group label { display: block; margin-bottom: 8px; color: var(--text-main); font-weight: 600; font-size: 14px; }
.form-group label span { color: var(--danger); }
.form-group input[type="text"] { 
    width: 100%; padding: 14px 18px; 
    border: 1px solid var(--border-color); border-radius: var(--btn-radius); 
    font-size: 14px; background: var(--bg-main); color: var(--text-main); 
    transition: all var(--transition); 
}
.form-group input[type="text"]:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px color-mix(in srgb, var(--primary) 20%, transparent); }

.file-upload-box {
    border: 2px dashed var(--border-color); border-radius: var(--border-radius);
    padding: 40px 20px; text-align: center; background: var(--bg-main);
    transition: all var(--transition); cursor: pointer;
}
.file-upload-box:hover { border-color: var(--primary); background: color-mix(in srgb, var(--primary) 5%, transparent); }

.upload-btn {
    display: inline-flex; align-items: center; gap: 10px;
    padding: 12px 24px; background: color-mix(in srgb, var(--primary) 15%, transparent); color: var(--primary);
    border-radius: var(--btn-radius); font-size: 14px; font-weight: 600;
    transition: all var(--transition); cursor: pointer; border: 1px solid color-mix(in srgb, var(--primary) 30%, transparent);
}
.upload-btn:hover { background: var(--primary); color: #fff; transform: translateY(-2px); box-shadow: var(--shadow-sm); }
.upload-btn i { font-size: 18px; }

.upload-preview { margin-top: 24px; padding: 24px; background: var(--bg-main); border-radius: var(--border-radius); border: 1px solid var(--border-color); }
.preview-img {
    display: inline-flex; justify-content: center; align-items: center;
    width: 80px; height: 80px; border-radius: var(--btn-radius);
    background: color-mix(in srgb, var(--primary) 10%, transparent); color: var(--primary); font-size: 28px;
    box-shadow: var(--shadow-sm); border: 1px solid var(--border-color); overflow: hidden;
}
.preview-img img { width: 100%; height: 100%; object-fit: contain; }

.grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
@media (max-width: 992px) { .grid-2 { grid-template-columns: 1fr; } }
.col-full { grid-column: 1 / -1; }

.files-list { margin-top: 32px; }
.files-table { width: 100%; border-collapse: separate; border-spacing: 0; background: var(--bg-card); border-radius: var(--border-radius); overflow: hidden; border: 1px solid var(--border-color); }
.files-table th { color: var(--text-muted); padding: 16px 20px; text-align: left; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 1px solid var(--border-color); background: var(--bg-main); }
.files-table td { padding: 16px 20px; border-bottom: 1px solid var(--border-color); font-size: 14px; color: var(--text-main); }
.files-table tr:last-child td { border-bottom: none; }
.files-table tr:hover td { background: color-mix(in srgb, var(--primary) 5%, transparent); }

.type-badge { padding: 6px 12px; border-radius: 8px; font-size: 11px; font-weight: 600; border: 1px solid var(--border-color); }
.type-txt { background: color-mix(in srgb, var(--info) 15%, transparent); color: var(--info); border-color: color-mix(in srgb, var(--info) 30%, transparent); }
.type-xml { background: color-mix(in srgb, var(--warning) 15%, transparent); color: var(--warning); border-color: color-mix(in srgb, var(--warning) 30%, transparent); }
.type-html { background: color-mix(in srgb, var(--accent) 15%, transparent); color: var(--accent); border-color: color-mix(in srgb, var(--accent) 30%, transparent); }
.type-image { background: color-mix(in srgb, var(--success) 15%, transparent); color: var(--success); border-color: color-mix(in srgb, var(--success) 30%, transparent); }

.file-item { display: flex; align-items: center; justify-content: space-between; padding: 16px; background: var(--bg-card); border-radius: var(--btn-radius); margin-bottom: 12px; border: 1px solid var(--border-color); transition: all var(--transition); }
.file-item:hover { border-color: var(--primary); box-shadow: var(--shadow-sm); }
.file-info { display: flex; align-items: center; gap: 16px; }
.file-info i { color: var(--primary); font-size: 24px; }
.file-info .name { font-weight: 600; color: var(--text-main); font-size: 14px; display: block; }
.file-info .size { font-size: 12px; color: var(--text-muted); }
</style>

<div class="saas-page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-sliders-h" style="color: var(--primary);"></i> General Site Settings</h1>
        <p class="page-subtitle">Configure website identity, brand logos, web manifest, and search crawler files.</p>
    </div>
</div>

<?php require_once __DIR__ . '/../../../../admin/includes/settings-nav.php'; ?>

<?php if ($delete_msg): ?>
<div class="alert alert-<?= $delete_type ?>"><i class="fas fa-<?= $delete_type === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i> <?= htmlspecialchars($delete_msg) ?></div>
<?php endif; ?>

<!-- ============================================ -->
<!-- SECTION 1: SITE NAME -->
<!-- ============================================ -->
<?php if ($site_name_msg): ?>
<div class="alert alert-<?= $site_name_type ?>"><i class="fas fa-<?= $site_name_type === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i> <?= htmlspecialchars($site_name_msg) ?></div>
<?php endif; ?>

<form method="POST">
<input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
<div class="settings-card">
    <h3><i class="fas fa-globe"></i> Site Name</h3>
    <div class="form-group">
        <label>Website Name <span>*</span></label>
        <input type="text" name="site_name" value="<?= htmlspecialchars($site_name) ?>" required placeholder="Enter your website name">
    </div>
    <button type="submit" name="save_site_name" class="btn">
        <i class="fas fa-save"></i> Save Site Name
    </button>
</div>
</form>



<!-- ============================================ -->
<!-- SECTION 3: LOGO -->
<!-- ============================================ -->
<?php if ($logo_msg): ?>
<div class="alert alert-<?= $logo_type ?>"><i class="fas fa-<?= $logo_type === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i> <?= htmlspecialchars($logo_msg) ?></div>
<?php endif; ?>

<!-- Delete Logo Form (Outside outer form to avoid nesting) -->
<form id="deleteLogoForm" method="POST" style="display: none;">
    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
    <input type="hidden" name="delete_file" value="1">
    <input type="hidden" name="file_key" value="site_logo">
</form>

<form method="POST" enctype="multipart/form-data">
<input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
<div class="settings-card">
    <h3><i class="fas fa-image"></i> Site Logo</h3>
    <div class="upload-preview">
        <?php if ($site_logo): ?>
            <div class="preview-img">
                <img src="<?= $base_path . '../' . htmlspecialchars($site_logo) ?>" alt="Logo">
            </div>
            <div style="margin-top: 15px;">
                <label for="newLogo" class="upload-btn" style="display: inline-flex;">
                    <i class="fas fa-sync-alt"></i> Change Logo
                </label>
                <input type="file" name="site_logo" id="newLogo" accept="image/png,image/jpeg,image/gif,image/svg+xml" style="display: none;" onchange="this.form.submit()">
                <button type="submit" form="deleteLogoForm" class="btn-delete" style="margin-left: 10px;"><i class="fas fa-trash"></i> Remove</button>
            </div>
        <?php else: ?>
            <div class="file-upload-box">
                <input type="file" name="site_logo" id="logoUpload" accept="image/png,image/jpeg,image/gif,image/svg+xml" style="display: none;">
                <label for="logoUpload" class="upload-btn">
                    <i class="fas fa-upload"></i> Upload Logo
                </label>
                <p style="margin-top: 10px; color: #666; font-size: 12px;">PNG, JPG, GIF, SVG (Max 2MB)</p>
            </div>
        <?php endif; ?>
    </div>
    <button type="submit" name="save_logo" class="btn">
        <i class="fas fa-save"></i> Save Logo
    </button>
</div>
</form>

<!-- ============================================ -->
<!-- SECTION 4: FAVICON -->
<!-- ============================================ -->
<?php if ($favicon_msg): ?>
<div class="alert alert-<?= $favicon_type ?>"><i class="fas fa-<?= $favicon_type === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i> <?= htmlspecialchars($favicon_msg) ?></div>
<?php endif; ?>

<!-- Delete Favicon Form (Outside outer form to avoid nesting) -->
<form id="deleteFaviconForm" method="POST" style="display: none;">
    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
    <input type="hidden" name="delete_file" value="1">
    <input type="hidden" name="file_key" value="site_favicon">
</form>

<form method="POST" enctype="multipart/form-data">
<input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
<div class="settings-card">
    <h3><i class="fas fa-bookmark"></i> Site Favicon</h3>
    <div class="upload-preview">
        <?php if ($favicon): ?>
            <div class="preview-img">
                <img src="<?= $base_path . '../' . htmlspecialchars($favicon) ?>" alt="Favicon">
            </div>
            <div style="margin-top: 15px;">
                <label for="newFavicon" class="upload-btn" style="display: inline-flex;">
                    <i class="fas fa-sync-alt"></i> Change Favicon
                </label>
                <input type="file" name="site_favicon" id="newFavicon" accept="image/png,image/jpeg,image/gif,image/svg+xml,image/x-icon" style="display: none;" onchange="this.form.submit()">
                <button type="submit" form="deleteFaviconForm" class="btn-delete" style="margin-left: 10px;"><i class="fas fa-trash"></i> Remove</button>
            </div>
        <?php else: ?>
            <div class="file-upload-box">
                <input type="file" name="site_favicon" id="faviconUpload" accept="image/png,image/jpeg,image/gif,image/svg+xml,image/x-icon" style="display: none;">
                <label for="faviconUpload" class="upload-btn">
                    <i class="fas fa-upload"></i> Upload Favicon
                </label>
                <p style="margin-top: 10px; color: #666; font-size: 12px;">PNG, JPG, ICO, SVG (Max 1MB)</p>
            </div>
        <?php endif; ?>
    </div>
    <button type="submit" name="save_favicon" class="btn">
        <i class="fas fa-save"></i> Save Favicon
    </button>
</div>
</form>

<!-- ============================================ -->
<!-- SECTION 5: FILE UPLOADS (TXT, XML, HTML) -->
<!-- ============================================ -->
<?php if ($files_msg): ?>
<div class="alert alert-<?= $files_type ?>"><i class="fas fa-<?= $files_type === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i> <?= htmlspecialchars($files_msg) ?></div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
<input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
<div class="settings-card">
    <h3><i class="fas fa-file-upload"></i> Upload Files (TXT, XML, HTML)</h3>
    <div class="grid-2">
        <!-- TXT Upload -->
        <div class="upload-preview">
            <div class="file-type-badge">.TXT</div>
            <h4><i class="fas fa-file-alt"></i> Text File</h4>
            <?php if ($upload_txt): ?>
                <div class="file-item">
                    <div class="file-info">
                        <i class="fas fa-file-alt"></i>
                        <div>
                            <div class="name"><?= basename($upload_txt) ?></div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <input type="file" name="upload_txt" id="txtUpload" accept=".txt,text/plain" style="display: none;" onchange="this.form.submit()">
                <label for="txtUpload" class="upload-btn" style="width: 100%;">
                    <i class="fas fa-upload"></i> Upload TXT
                </label>
            <?php endif; ?>
        </div>

        <!-- XML Upload -->
        <div class="upload-preview">
            <div class="file-type-badge">.XML</div>
            <h4><i class="fas fa-file-code"></i> XML File</h4>
            <?php if ($upload_xml): ?>
                <div class="file-item">
                    <div class="file-info">
                        <i class="fas fa-file-code"></i>
                        <div>
                            <div class="name"><?= basename($upload_xml) ?></div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <input type="file" name="upload_xml" id="xmlUpload" accept=".xml,application/xml,text/xml" style="display: none;" onchange="this.form.submit()">
                <label for="xmlUpload" class="upload-btn" style="width: 100%;">
                    <i class="fas fa-upload"></i> Upload XML
                </label>
            <?php endif; ?>
        </div>

        <!-- HTML Upload -->
        <div class="col-full">
            <div class="upload-preview">
                <div class="file-type-badge">.HTML</div>
                <h4><i class="fas fa-file-code"></i> HTML File</h4>
                <?php if ($upload_html): ?>
                    <div class="file-item">
                        <div class="file-info">
                            <i class="fas fa-file-code"></i>
                            <div>
                                <div class="name"><?= basename($upload_html) ?></div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <input type="file" name="upload_html" id="htmlUpload" accept=".html,.htm,text/html" style="display: none;" onchange="this.form.submit()">
                    <label for="htmlUpload" class="upload-btn" style="width: 100%;">
                        <i class="fas fa-upload"></i> Upload HTML
                    </label>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <button type="submit" name="save_files" class="btn">
        <i class="fas fa-save"></i> Save All Files
    </button>
</div>
</form>

<!-- ============================================ -->
<!-- ALL UPLOADED FILES LIST -->
<!-- ============================================ -->
<?php if (!empty($all_uploads)): ?>
<div class="settings-card">
    <h3><i class="fas fa-list"></i> All Uploaded Files</h3>
    <table class="files-table">
        <thead>
            <tr>
                <th>File Name</th>
                <th>Type</th>
                <th>Size</th>
                <th>Uploaded</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($all_uploads as $file): ?>
            <?php
                $ext = strtolower(pathinfo($file['file_name'], PATHINFO_EXTENSION));
                $type_class = 'type-' . $ext;
                if (in_array($ext, ['png','jpg','jpeg','gif','svg','ico'])) $type_class = 'type-image';
            ?>
            <tr>
                <td><i class="fas fa-file"></i> <?= htmlspecialchars($file['file_name']) ?></td>
                <td><span class="type-badge <?= $type_class ?>"><?= strtoupper($ext) ?></span></td>
                <td><?= number_format($file['file_size'] / 1024, 1) ?> KB</td>
                <td><?= date('M d, Y H:i', strtotime($file['uploaded_at'])) ?></td>
                <td>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                        <input type="hidden" name="delete_file" value="1">
                        <input type="hidden" name="file_key" value="<?= htmlspecialchars($file['file_type']) ?>">
                        <button type="submit" class="btn-delete"><i class="fas fa-trash"></i></button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../../../../admin/includes/footer.php'; ?>