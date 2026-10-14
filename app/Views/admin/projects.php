<?php
require_once __DIR__ . '/../../../admin/includes/header.php';
?>

<style>
/* ── VIEW SWITCHER ── */
.view-switch-container {
    background: var(--bg-card, #FFFFFF);
    border: 1px solid var(--border-color, #E2E8F0);
    border-radius: 8px;
    padding: 3px;
    display: inline-flex;
    gap: 3px;
}
.view-switch-container button {
    border: none;
    background: transparent;
    color: var(--text-muted, #64748B);
    cursor: pointer;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    padding: 6px 12px;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.view-switch-container button.active {
    background: var(--primary, #6366F1);
    color: #FFFFFF;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
.view-switch-container button:hover:not(.active) {
    color: var(--text-main, #0F172A);
    background: var(--bg-main, #F8FAFC);
}

/* ── BULK ACTION BAR ── */
.bulk-bar {
    display: none;
    position: sticky;
    top: 0;
    z-index: 50;
    background: var(--bg-card, #FFFFFF);
    border: 1px solid var(--primary, #6366F1);
    border-radius: 12px;
    padding: 12px 20px;
    margin-bottom: 20px;
    align-items: center;
    gap: 12px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
    animation: slideDown 0.2s ease;
    flex-wrap: wrap;
}
.bulk-bar.active {
    display: flex;
}
@keyframes slideDown {
    from { opacity: 0; transform: translateY(-8px); }
    to { opacity: 1; transform: translateY(0); }
}
.bulk-bar-count {
    font-size: 14px;
    font-weight: 600;
    color: var(--primary, #6366F1);
    white-space: nowrap;
}
.bulk-bar-divider {
    width: 1px;
    height: 24px;
    background: var(--border-color, #E2E8F0);
}
.bulk-bar .form-control {
    min-width: 150px;
    padding: 6px 12px;
    font-size: 13px;
    margin-bottom: 0;
}
.bulk-bar .btn {
    white-space: nowrap;
}
.project-checkbox {
    width: 17px;
    height: 17px;
    accent-color: var(--primary, #6366F1);
    cursor: pointer;
}

/* ── GRID STYLES ── */
.projects-grid { 
    display: grid; 
    grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); 
    gap: 24px; 
}
.project-card { 
    background: var(--bg-card, #FFFFFF); 
    border: 1px solid var(--border-color, #E2E8F0); 
    border-radius: var(--border-radius, 12px); 
    overflow: hidden; 
    transition: all var(--transition, 0.2s ease); 
    box-shadow: var(--shadow-sm, 0 1px 3px rgba(0,0,0,0.05)); 
    display: flex; 
    flex-direction: column;
    position: relative;
}
.project-card:hover { 
    transform: translateY(-4px); 
    box-shadow: var(--shadow-md, 0 6px 20px rgba(0,0,0,0.08)); 
    border-color: color-mix(in srgb, var(--primary, #6366F1) 35%, var(--border-color, #E2E8F0));
}
.card-select-wrap {
    position: absolute;
    top: 14px;
    left: 14px;
    z-index: 10;
    background: rgba(255, 255, 255, 0.9);
    border-radius: 6px;
    padding: 4px;
    display: flex;
    align-items: center;
    box-shadow: 0 2px 6px rgba(0,0,0,0.15);
    backdrop-filter: blur(4px);
}
.project-thumb { 
    width: 100%; 
    height: 180px; 
    background: color-mix(in srgb, var(--primary, #6366F1) 5%, var(--bg-main, #F8FAFC)); 
    display: flex; 
    align-items: center; 
    justify-content: center; 
    position: relative; 
    overflow: hidden; 
    border-bottom: 1px solid var(--border-color, #E2E8F0);
}
.project-thumb img { width: 100%; height: 100%; object-fit: cover; }
.project-thumb i { font-size: 48px; color: var(--text-muted, #94A3B8); opacity: 0.3; }
.project-thumb .status-badge { 
    position: absolute; 
    top: 14px; 
    right: 14px; 
    padding: 5px 12px; 
    border-radius: 20px; 
    font-size: 11.5px; 
    font-weight: 700; 
    backdrop-filter: blur(10px); 
    border: 1px solid rgba(255,255,255,0.25);
    box-shadow: 0 2px 8px rgba(0,0,0,0.12);
}
.status-pending { background: rgba(245, 158, 11, 0.9); color: #fff; }
.status-running { background: rgba(59, 130, 246, 0.9); color: #fff; }
.status-completed { background: rgba(16, 185, 129, 0.9); color: #fff; }
.status-cancelled { background: rgba(239, 68, 68, 0.9); color: #fff; }

.project-body { padding: 24px; flex: 1; display: flex; flex-direction: column; }
.project-name { font-size: 18px; font-weight: 600; color: var(--text-main, #0F172A); margin-bottom: 12px; letter-spacing: -0.01em; }

.project-meta { display: flex; flex-wrap: wrap; gap: 16px; margin-bottom: 20px; }
.project-meta span { font-size: 13px; color: var(--text-muted, #64748B); display: flex; align-items: center; gap: 6px; font-weight: 500; }
.project-meta span i { color: var(--primary, #6366F1); }

.project-budget { 
    font-size: 26px; font-weight: 700; color: var(--text-main, #0F172A); 
    margin-bottom: 16px; display: flex; align-items: baseline; gap: 6px;
    letter-spacing: -0.02em;
}
.project-budget small { font-size: 13px; color: var(--text-muted, #64748B); font-weight: 500; }

.project-link { font-size: 13px; color: var(--primary, #6366F1); margin-bottom: 20px; display: flex; align-items: center; gap: 6px; text-decoration: none; font-weight: 500; }
.project-link:hover { text-decoration: underline; }

.project-actions { 
    display: flex; gap: 8px; padding-top: 20px; 
    border-top: 1px solid var(--border-color, #E2E8F0); margin-top: auto;
}

/* ── LIST / TABLE STYLES ── */
.table-thumb {
    width: 44px;
    height: 44px;
    border-radius: 8px;
    object-fit: cover;
    background: var(--bg-main, #F8FAFC);
    border: 1px solid var(--border-color, #E2E8F0);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--primary, #6366F1);
}
.table-thumb img {
    width: 100%;
    height: 100%;
    border-radius: 7px;
    object-fit: cover;
}

.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
@media (max-width: 992px) { .form-row { grid-template-columns: 1fr; gap: 16px; } }
</style>

<!-- Page Header -->
<div class="saas-page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-diagram-project" style="color: var(--primary, #6366F1);"></i> Projects Management</h1>
        <p class="page-subtitle">Track project lifecycles, budgets, deadlines, deliverables, and portfolio case studies.</p>
    </div>
    <div class="header-actions" style="display: flex; align-items: center; gap: 12px;">
        <div class="view-switch-container">
            <button type="button" class="active" id="viewGridBtn" onclick="switchView('grid')">
                <i class="fas fa-th-large"></i> Grid
            </button>
            <button type="button" id="viewListBtn" onclick="switchView('list')">
                <i class="fas fa-list"></i> Table
            </button>
        </div>
        <button class="btn btn-primary" onclick="openModal()"><i class="fas fa-plus"></i> Add Project</button>
    </div>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?= $message_type ?>">
        <i class="fas fa-<?= $message_type === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i> 
        <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

<!-- ── STICKY BULK ACTION BAR ── -->
<div class="bulk-bar" id="bulkBar">
    <span class="bulk-bar-count"><span id="bulkCount">0</span> selected</span>
    
    <div class="bulk-bar-divider"></div>
    
    <!-- Bulk Status Form -->
    <form method="POST" id="bulkStatusForm" style="display:flex; align-items:center; gap: 8px;">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
        <input type="hidden" name="action" value="bulk_update_status">
        <div id="bulkStatusIds"></div>
        <select name="status" class="form-control" required>
            <option value="" disabled selected>Change Status…</option>
            <option value="pending">Pending</option>
            <option value="running">Running</option>
            <option value="completed">Completed</option>
            <option value="cancelled">Cancelled</option>
        </select>
        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-sync-alt"></i> Apply</button>
    </form>

    <div class="bulk-bar-divider"></div>

    <!-- Bulk Category Form -->
    <form method="POST" id="bulkCategoryForm" style="display:flex; align-items:center; gap: 8px;">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
        <input type="hidden" name="action" value="bulk_update_category">
        <div id="bulkCategoryIds"></div>
        <select name="category" class="form-control" required>
            <option value="" disabled selected>Change Category…</option>
            <option value="web">Website</option>
            <option value="seo">SEO</option>
            <option value="social">Social Media</option>
            <option value="ads">Paid Ads</option>
        </select>
        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-tag"></i> Set Category</button>
    </form>

    <div class="bulk-bar-divider"></div>

    <!-- Bulk Delete Form -->
    <form method="POST" id="bulkDeleteForm" onsubmit="return confirm('Permanently delete selected projects?')" style="display:flex; align-items:center;">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
        <input type="hidden" name="action" value="bulk_delete">
        <div id="bulkDeleteIds"></div>
        <button type="submit" class="btn btn-sm" style="background: #ef4444; color: #fff; border: 1px solid #dc2626;"><i class="fas fa-trash"></i> Delete Selected</button>
    </form>

    <div style="margin-left: auto;">
        <button type="button" class="btn btn-secondary btn-sm" onclick="clearSelection()"><i class="fas fa-times"></i> Clear</button>
    </div>
</div>

<!-- ═══ 1. GRID VIEW CONTAINER ═══ -->
<div id="projectsGridView">
    <div class="projects-grid">
        <?php foreach ($projects as $project): ?>
        <div class="project-card">
            <div class="card-select-wrap">
                <input type="checkbox" class="project-checkbox row-cb" value="<?= $project['id'] ?>" onclick="updateBulkBar()">
            </div>
            <div class="project-thumb">
                <?php if (!empty($project['thumbnail'])): ?>
                    <img src="<?= htmlspecialchars($project['thumbnail']) ?>" alt="">
                <?php else: ?>
                    <i class="fas fa-diagram-project"></i>
                <?php endif; ?>
                <span class="status-badge status-<?= htmlspecialchars($project['status'] ?? 'pending') ?>"><?= ucfirst(htmlspecialchars($project['status'] ?? 'pending')) ?></span>
            </div>
            <div class="project-body">
                <div style="display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 8px;">
                    <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; background: rgba(108, 59, 255, 0.08); color: var(--primary, #6366F1); padding: 2px 8px; border-radius: 12px;"><?= htmlspecialchars(strtoupper($project['category'] ?? 'WEB')) ?></span>
                    <span style="font-size: 11px; font-weight: 600; background: var(--bg-main, #F8FAFC); color: var(--text-muted, #64748B); padding: 2px 8px; border-radius: 12px; border: 1px solid var(--border-color, #E2E8F0);"><?= htmlspecialchars($project['industry'] ?? 'Digital') ?></span>
                </div>
                <div class="project-name"><?= htmlspecialchars($project['project_name']) ?></div>
                
                <?php if (!empty($project['description'])): ?>
                    <p style="font-size: 13px; color: var(--text-muted, #64748B); margin-bottom: 12px; line-height: 1.5; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;"><?= htmlspecialchars($project['description']) ?></p>
                <?php endif; ?>

                <?php if (!empty($project['results'])): ?>
                    <div style="font-size: 12px; color: var(--success, #10B981); font-weight: 600; margin-bottom: 12px; display: flex; align-items: center; gap: 5px;">
                        <i class="fas fa-chart-line"></i> <?= htmlspecialchars($project['results']) ?>
                    </div>
                <?php endif; ?>

                <div class="project-meta">
                    <span><i class="fas fa-calendar"></i> <?= !empty($project['project_datetime']) ? date('M d, Y', strtotime($project['project_datetime'])) : 'N/A' ?></span>
                    <?php if (!empty($project['client_name'])): ?>
                        <span><i class="fas fa-building"></i> <?= htmlspecialchars($project['client_name']) ?></span>
                    <?php endif; ?>
                </div>
                <div class="project-budget">₹<?= number_format((float)($project['budget'] ?? 0)) ?><small>Budget</small></div>
                <?php if (!empty($project['project_link'])): ?>
                    <a href="<?= htmlspecialchars($project['project_link']) ?>" target="_blank" class="project-link"><i class="fas fa-link"></i> External Link</a>
                <?php endif; ?>
                <div class="project-actions">
                    <button class="btn btn-sm btn-edit" onclick="editProject(<?= htmlspecialchars(json_encode($project)) ?>)" style="flex: 1; justify-content: center;"><i class="fas fa-edit"></i> Edit</button>
                    <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this project permanently?')">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= $project['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-delete" title="Delete"><i class="fas fa-trash"></i></button>
                    </form>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        
        <?php if (empty($projects)): ?>
        <div style="grid-column: 1/-1; text-align:center; padding:80px 20px; background: var(--bg-card, #FFFFFF); border-radius: var(--border-radius, 12px); border: 1px dashed var(--border-color, #E2E8F0);">
            <div style="width: 64px; height: 64px; background: var(--bg-main, #F8FAFC); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                <i class="fas fa-diagram-project" style="font-size: 24px; color: var(--text-muted, #94A3B8);"></i>
            </div>
            <h3 style="font-size: 18px; color: var(--text-main, #0F172A); margin-bottom: 8px;">No projects added</h3>
            <p style="color: var(--text-muted, #64748B); font-size: 15px; margin-bottom: 24px;">Start tracking your projects and displaying your portfolio.</p>
            <button class="btn btn-primary" onclick="openModal()"><i class="fas fa-plus"></i> Add First Project</button>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- ═══ 2. LIST / TABLE VIEW CONTAINER ═══ -->
<div id="projectsListView" style="display: none;">
    <div class="content-card">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th style="width: 36px;"><input type="checkbox" class="project-checkbox" id="selectAllCb" onclick="toggleSelectAll(this)"></th>
                        <th style="width: 60px;">Media</th>
                        <th>Project & Category</th>
                        <th>Industry</th>
                        <th>Client / Brand</th>
                        <th>Budget</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($projects as $project): 
                        $pStatus = $project['status'] ?? 'pending';
                    ?>
                    <tr>
                        <td><input type="checkbox" class="project-checkbox row-cb" value="<?= $project['id'] ?>" onclick="updateBulkBar()"></td>
                        <td>
                            <div class="table-thumb">
                                <?php if (!empty($project['thumbnail'])): ?>
                                    <img src="<?= htmlspecialchars($project['thumbnail']) ?>" alt="">
                                <?php else: ?>
                                    <i class="fas fa-diagram-project"></i>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <div style="font-weight: 600; color: var(--text-main, #0F172A); font-size: 14px; margin-bottom: 3px;">
                                <?= htmlspecialchars($project['project_name']) ?>
                            </div>
                            <span style="font-size: 10.5px; font-weight: 700; text-transform: uppercase; background: rgba(108, 59, 255, 0.08); color: var(--primary, #6366F1); padding: 2px 7px; border-radius: 10px;">
                                <?= htmlspecialchars(strtoupper($project['category'] ?? 'WEB')) ?>
                            </span>
                        </td>
                        <td style="color: var(--text-muted, #64748B); font-size: 13px;">
                            <?= htmlspecialchars($project['industry'] ?? 'Digital') ?>
                        </td>
                        <td>
                            <div style="font-size: 13px; font-weight: 500; color: var(--text-main, #0F172A);">
                                <?= htmlspecialchars($project['client_name'] ?: 'Internal / Direct') ?>
                            </div>
                        </td>
                        <td>
                            <span style="font-weight: 700; color: var(--text-main, #0F172A); font-size: 14px;">
                                ₹<?= number_format((float)($project['budget'] ?? 0)) ?>
                            </span>
                        </td>
                        <td>
                            <span class="status-badge status-<?= htmlspecialchars($pStatus) ?>" style="position: static; display: inline-block; padding: 3px 10px; font-size: 11px;">
                                <?= ucfirst(htmlspecialchars($pStatus)) ?>
                            </span>
                        </td>
                        <td style="color: var(--text-muted, #64748B); font-size: 12.5px;">
                            <?= !empty($project['project_datetime']) ? date('M d, Y', strtotime($project['project_datetime'])) : 'N/A' ?>
                        </td>
                        <td style="text-align: right;">
                            <div style="display: flex; gap: 6px; justify-content: flex-end; align-items: center;">
                                <button class="btn btn-sm" onclick="editProject(<?= htmlspecialchars(json_encode($project)) ?>)" style="background: var(--bg-main, #F8FAFC); color: var(--text-main, #0F172A); border: 1px solid var(--border-color, #E2E8F0);" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this project permanently?')">
                                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $project['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-delete" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>

                    <?php if (empty($projects)): ?>
                    <tr>
                        <td colspan="9" style="text-align:center; padding:80px 20px; background: var(--bg-card); border-bottom: none;">
                            <div style="width: 64px; height: 64px; background: var(--bg-main); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                                <i class="fas fa-diagram-project" style="font-size: 24px; color: var(--text-muted);"></i>
                            </div>
                            <h3 style="font-size: 18px; color: var(--text-main); margin-bottom: 8px;">No projects found</h3>
                            <p style="color: var(--text-muted); font-size: 15px; margin-bottom: 24px;">Start adding projects to showcase your work.</p>
                            <button class="btn btn-primary" onclick="openModal()"><i class="fas fa-plus"></i> Add First Project</button>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ═══ MODAL ═══ -->
<div class="modal" id="projectModal">
    <div class="modal-content" style="max-width: 680px;">
        <div class="modal-header">
            <h3 class="modal-title"><i class="fas fa-diagram-project"></i> <span id="modalTitle">Add New Project</span></h3>
            <button type="button" class="modal-close" onclick="closeModal()" aria-label="Close modal">&times;</button>
        </div>
        <form method="POST" enctype="multipart/form-data">
            <div class="modal-body">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <input type="hidden" name="action" id="formAction" value="create">
                <input type="hidden" name="id" id="projectId">
                <div class="form-group">
                    <label for="projectName"><i class="fas fa-tag"></i> Project Name <span class="text-danger">*</span></label>
                    <input type="text" name="project_name" id="projectName" required class="form-control" placeholder="e.g. E-Commerce SEO & Conversion">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="projectCategory"><i class="fas fa-filter"></i> Category <span class="text-danger">*</span></label>
                        <select name="category" id="projectCategory" class="form-control" required>
                            <option value="seo">SEO</option>
                            <option value="social">Social Media</option>
                            <option value="ads">Paid Ads</option>
                            <option value="web" selected>Website</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="projectIndustry"><i class="fas fa-industry"></i> Industry</label>
                        <input type="text" name="industry" id="projectIndustry" class="form-control" placeholder="e.g. E-Commerce, Healthcare, Real Estate">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="projectBudget"><i class="fas fa-rupee-sign"></i> Budget (₹) <span class="text-danger">*</span></label>
                        <input type="text" name="budget" id="projectBudget" required class="form-control" placeholder="e.g. 75000">
                    </div>
                    <div class="form-group">
                        <label for="projectStatus"><i class="fas fa-toggle-on"></i> Status</label>
                        <select name="status" id="projectStatus" class="form-control">
                            <option value="pending">Pending</option>
                            <option value="running">Running</option>
                            <option value="completed" selected>Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="projectClient"><i class="fas fa-building"></i> Client / Brand Name</label>
                        <input type="text" name="client_name" id="projectClient" class="form-control" placeholder="e.g. Acme Corporation">
                    </div>
                    <div class="form-group">
                        <label for="projectResults"><i class="fas fa-trophy"></i> Key Result / Metric</label>
                        <input type="text" name="results" id="projectResults" class="form-control" placeholder="e.g. 300% Traffic Growth (5x ROAS)">
                    </div>
                </div>
                <div class="form-group">
                    <label for="projectDescription"><i class="fas fa-align-left"></i> Description</label>
                    <textarea name="description" id="projectDescription" class="form-control" rows="3" placeholder="Brief project summary, deliverables, or case study overview..."></textarea>
                </div>
                <div class="form-group">
                    <label for="projectDatetime"><i class="fas fa-calendar"></i> Project Date & Time <span class="text-danger">*</span></label>
                    <input type="datetime-local" name="project_datetime" id="projectDatetime" required class="form-control">
                </div>
                <div class="form-group">
                    <label for="projectLink"><i class="fas fa-link"></i> Project Link</label>
                    <input type="url" name="project_link" id="projectLink" placeholder="https://example.com or /contact" class="form-control">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-upload"></i> Upload Thumbnail (JPG, PNG, WEBP, SVG)</label>
                    <input type="file" name="thumbnail_file" accept=".jpg,.jpeg,.png,.gif,.webp,.svg" class="form-control" style="cursor: pointer;">
                    <label style="margin-top: 10px;"><i class="fas fa-image"></i> Or Thumbnail URL</label>
                    <input type="url" name="thumbnail" id="projectThumb" placeholder="https://example.com/image.jpg" class="form-control">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal()"><i class="fas fa-times"></i> Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Project</button>
            </div>
        </form>
    </div>
</div>

<script>
// ── VIEW SWITCH LOGIC ──
function switchView(view) {
    const gridBtn = document.getElementById('viewGridBtn');
    const listBtn = document.getElementById('viewListBtn');
    const gridView = document.getElementById('projectsGridView');
    const listView = document.getElementById('projectsListView');

    if (view === 'grid') {
        gridBtn.classList.add('active');
        listBtn.classList.remove('active');
        gridView.style.display = 'block';
        listView.style.display = 'none';
        localStorage.setItem('project_view_preference', 'grid');
    } else {
        listBtn.classList.add('active');
        gridBtn.classList.remove('active');
        gridView.style.display = 'none';
        listView.style.display = 'block';
        localStorage.setItem('project_view_preference', 'list');
    }
}

// ── BULK ACTION FUNCTIONS ──
function getSelectedIds() {
    const checked = new Set();
    document.querySelectorAll('.row-cb:checked').forEach(cb => checked.add(cb.value));
    return Array.from(checked);
}

function updateBulkBar() {
    const ids = getSelectedIds();
    const bar = document.getElementById('bulkBar');
    const count = document.getElementById('bulkCount');
    const selectAll = document.getElementById('selectAllCb');
    const allCbs = document.querySelectorAll('#projectsListView .row-cb');

    count.textContent = ids.length;

    if (ids.length > 0) {
        bar.classList.add('active');
    } else {
        bar.classList.remove('active');
    }

    // Sync checkboxes between grid and list for same project ID
    const selectedSet = new Set(ids);
    document.querySelectorAll('.row-cb').forEach(cb => {
        cb.checked = selectedSet.has(cb.value);
    });

    if (selectAll) {
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
}

function toggleSelectAll(masterCb) {
    document.querySelectorAll('.row-cb').forEach(cb => {
        cb.checked = masterCb.checked;
    });
    updateBulkBar();
}

function clearSelection() {
    document.querySelectorAll('.row-cb').forEach(cb => cb.checked = false);
    const selectAll = document.getElementById('selectAllCb');
    if (selectAll) {
        selectAll.checked = false;
        selectAll.indeterminate = false;
    }
    updateBulkBar();
}

// Inject selected IDs into bulk forms before submission
document.getElementById('bulkStatusForm').addEventListener('submit', function(e) {
    const ids = getSelectedIds();
    if (ids.length === 0) { e.preventDefault(); alert('No projects selected.'); return; }
    document.getElementById('bulkStatusIds').innerHTML = '';
    ids.forEach(id => {
        const inp = document.createElement('input');
        inp.type = 'hidden'; inp.name = 'bulk_ids[]'; inp.value = id;
        document.getElementById('bulkStatusIds').appendChild(inp);
    });
});

document.getElementById('bulkCategoryForm').addEventListener('submit', function(e) {
    const ids = getSelectedIds();
    if (ids.length === 0) { e.preventDefault(); alert('No projects selected.'); return; }
    document.getElementById('bulkCategoryIds').innerHTML = '';
    ids.forEach(id => {
        const inp = document.createElement('input');
        inp.type = 'hidden'; inp.name = 'bulk_ids[]'; inp.value = id;
        document.getElementById('bulkCategoryIds').appendChild(inp);
    });
});

document.getElementById('bulkDeleteForm').addEventListener('submit', function(e) {
    const ids = getSelectedIds();
    if (ids.length === 0) { e.preventDefault(); alert('No projects selected.'); return; }
    document.getElementById('bulkDeleteIds').innerHTML = '';
    ids.forEach(id => {
        const inp = document.createElement('input');
        inp.type = 'hidden'; inp.name = 'bulk_ids[]'; inp.value = id;
        document.getElementById('bulkDeleteIds').appendChild(inp);
    });
});

// ── MODAL LOGIC ──
function openModal() {
    document.getElementById('modalTitle').textContent = 'Add New Project';
    document.getElementById('formAction').value = 'create';
    document.getElementById('projectId').value = '';
    document.getElementById('projectName').value = '';
    document.getElementById('projectCategory').value = 'web';
    document.getElementById('projectIndustry').value = '';
    document.getElementById('projectBudget').value = '';
    document.getElementById('projectClient').value = '';
    document.getElementById('projectResults').value = '';
    document.getElementById('projectDescription').value = '';
    const now = new Date();
    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
    document.getElementById('projectDatetime').value = now.toISOString().slice(0, 16);
    document.getElementById('projectLink').value = '';
    document.getElementById('projectThumb').value = '';
    document.getElementById('projectStatus').value = 'completed';
    document.getElementById('projectModal').classList.add('active');
}

function editProject(p) {
    document.getElementById('modalTitle').textContent = 'Edit Project';
    document.getElementById('formAction').value = 'update';
    document.getElementById('projectId').value = p.id;
    document.getElementById('projectName').value = p.project_name || '';
    document.getElementById('projectCategory').value = p.category || 'web';
    document.getElementById('projectIndustry').value = p.industry || '';
    document.getElementById('projectBudget').value = p.budget || '';
    document.getElementById('projectClient').value = p.client_name || '';
    document.getElementById('projectResults').value = p.results || '';
    document.getElementById('projectDescription').value = p.description || '';
    document.getElementById('projectDatetime').value = p.project_datetime ? p.project_datetime.replace(' ', 'T').slice(0, 16) : '';
    document.getElementById('projectLink').value = p.project_link || '';
    document.getElementById('projectThumb').value = p.thumbnail || '';
    document.getElementById('projectStatus').value = p.status || 'completed';
    document.getElementById('projectModal').classList.add('active');
}

function closeModal() { 
    document.getElementById('projectModal').classList.remove('active'); 
}

window.onclick = function(e) { 
    if (e.target === document.getElementById('projectModal')) closeModal(); 
}

// Initialise Preference View on load
document.addEventListener("DOMContentLoaded", function() {
    const viewPreference = localStorage.getItem('project_view_preference') || 'grid';
    switchView(viewPreference);
});
</script>

<?php require_once __DIR__ . '/../../../admin/includes/footer.php'; ?>