<?php
require_once __DIR__ . '/../../../../admin/includes/header.php';
?>

<style>
.settings-card { 
    background: var(--bg-card, #ffffff); 
    border: 1px solid var(--border-color, #e2e8f0); 
    border-radius: var(--border-radius, 14px); 
    padding: 30px; 
    box-shadow: var(--shadow-sm, 0 4px 15px rgba(0,0,0,0.03)); 
    margin-bottom: 24px; 
}
.settings-card h3 { 
    font-size: 17px; 
    color: var(--text-main, #0f172a); 
    margin-bottom: 12px; 
    display: flex; 
    align-items: center; 
    gap: 10px; 
    font-weight: 700; 
}
.settings-card h3 i { color: var(--primary, #4f46e5); }
.card-subtitle { font-size: 13px; color: var(--text-muted, #64748b); margin-bottom: 24px; }

.form-group { margin-bottom: 20px; }
.form-group label { display: block; margin-bottom: 6px; color: var(--text-main, #0f172a); font-weight: 600; font-size: 13.5px; }
.form-group label span.opt { font-weight: 400; color: var(--text-muted, #64748b); font-size: 12px; }
.form-group input[type="text"], 
.form-group input[type="url"], 
.form-group textarea { 
    width: 100%; padding: 12px 16px; 
    border: 1px solid var(--border-color, #cbd5e1); 
    border-radius: 8px; 
    font-size: 14px; 
    background: var(--bg-main, #f8fafc); 
    color: var(--text-main, #0f172a); 
    transition: all 0.2s ease;
    box-sizing: border-box;
}
.form-group input:focus, .form-group textarea:focus { 
    outline: none; 
    border-color: var(--primary, #4f46e5); 
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15); 
    background: #ffffff;
}

.grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
@media (max-width: 850px) { .grid-2 { grid-template-columns: 1fr; gap: 15px; } }

.btn-save-section {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: var(--primary, #4f46e5);
    color: #ffffff;
    padding: 10px 22px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 13.5px;
    border: none;
    cursor: pointer;
    transition: all 0.2s ease;
}
.btn-save-section:hover {
    opacity: 0.92;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);
}
</style>

<div class="saas-page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-home" style="color: var(--primary);"></i> Homepage &amp; Content Management</h1>
        <p class="page-subtitle">Control and customize the About Us story, Call to Action banners, and Client showcase dynamically.</p>
    </div>
</div>

<?php require_once __DIR__ . '/../../../../admin/includes/settings-nav.php'; ?>

<?php if (!empty($flash_msg)): ?>
<div class="alert alert-<?= $flash_type ?>">
    <i class="fas fa-<?= $flash_type === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i> 
    <?= htmlspecialchars($flash_msg) ?>
</div>
<?php endif; ?>

<!-- ══════════════════════════════════════════════
     SECTION 1: ABOUT US & BRAND STORY
     ══════════════════════════════════════════════ -->
<form method="POST" id="about">
    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
    <input type="hidden" name="action" value="save_about">

    <div class="settings-card">
        <h3><i class="fas fa-building"></i> Homepage About Us &amp; Brand Story</h3>
        <p class="card-subtitle">Manage the primary story, headings, and metric badges displayed right below the hero services.</p>

        <div class="grid-2">
            <div class="form-group">
                <label>Section Heading</label>
                <input type="text" name="about_title" value="<?= htmlspecialchars($about_title) ?>" required>
            </div>
            <div class="form-group">
                <label>Highlight Word / Phrase <span class="opt">(Colored with theme accent)</span></label>
                <input type="text" name="about_highlight" value="<?= htmlspecialchars($about_highlight) ?>" placeholder="e.g. Best Digital Marketing">
            </div>
        </div>

        <div class="form-group">
            <label>Paragraph 1 <span class="opt">(Company Introduction)</span></label>
            <textarea name="about_p1" rows="3" required><?= htmlspecialchars($about_p1) ?></textarea>
        </div>

        <div class="form-group">
            <label>Paragraph 2 <span class="opt">(Specializations &amp; Core Strengths)</span></label>
            <textarea name="about_p2" rows="3"><?= htmlspecialchars($about_p2) ?></textarea>
        </div>

        <div class="form-group">
            <label>Paragraph 3 <span class="opt">(Call to Action Line)</span></label>
            <textarea name="about_p3" rows="2"><?= htmlspecialchars($about_p3) ?></textarea>
        </div>

        <div class="grid-2" style="margin-top: 15px;">
            <div style="background: var(--bg-main, #f8fafc); padding: 18px; border-radius: 10px; border: 1px solid var(--border-color, #e2e8f0);">
                <label style="font-weight: 700; margin-bottom: 10px; display: block; color: var(--primary);"><i class="fas fa-chart-line"></i> Metric Card 1</label>
                <div class="form-group">
                    <label>Title</label>
                    <input type="text" name="about_metric1_title" value="<?= htmlspecialchars($about_metric1_title) ?>">
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label>Subtitle / Description</label>
                    <input type="text" name="about_metric1_sub" value="<?= htmlspecialchars($about_metric1_sub) ?>">
                </div>
            </div>

            <div style="background: var(--bg-main, #f8fafc); padding: 18px; border-radius: 10px; border: 1px solid var(--border-color, #e2e8f0);">
                <label style="font-weight: 700; margin-bottom: 10px; display: block; color: var(--primary);"><i class="fas fa-gauge-high"></i> Metric Card 2</label>
                <div class="form-group">
                    <label>Title</label>
                    <input type="text" name="about_metric2_title" value="<?= htmlspecialchars($about_metric2_title) ?>">
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label>Subtitle / Description</label>
                    <input type="text" name="about_metric2_sub" value="<?= htmlspecialchars($about_metric2_sub) ?>">
                </div>
            </div>
        </div>

        <div class="grid-2" style="margin-top: 15px;">
            <div class="form-group">
                <label>Experience Badge Title</label>
                <input type="text" name="about_exp_title" value="<?= htmlspecialchars($about_exp_title) ?>" placeholder="e.g. 5+ Years of Industry Expertise">
            </div>
            <div class="form-group">
                <label>Experience Badge Subtitle</label>
                <input type="text" name="about_exp_sub" value="<?= htmlspecialchars($about_exp_sub) ?>" placeholder="e.g. Pondicherry’s Most Trusted Digital Firm">
            </div>
        </div>

        <button type="submit" class="btn-save-section">
            <i class="fas fa-save"></i> Save About Us Section
        </button>
    </div>
</form>

<!-- ══════════════════════════════════════════════
     SECTION 2: CTA PARALLAX BANNER
     ══════════════════════════════════════════════ -->
<form method="POST" id="cta">
    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
    <input type="hidden" name="action" value="save_cta">

    <div class="settings-card">
        <h3><i class="fas fa-bullhorn"></i> Parallax Call-to-Action Banner</h3>
        <p class="card-subtitle">Control the attention-grabbing banner shown across the website with the direct phone call action.</p>

        <div class="form-group">
            <label>Banner Heading</label>
            <input type="text" name="cta_title" value="<?= htmlspecialchars($cta_title) ?>" required>
        </div>

        <div class="grid-2">
            <div class="form-group">
                <label>Banner Subtitle</label>
                <input type="text" name="cta_subtitle" value="<?= htmlspecialchars($cta_subtitle) ?>" required>
            </div>
            <div class="form-group">
                <label>Button Label Text</label>
                <input type="text" name="cta_btn_text" value="<?= htmlspecialchars($cta_btn_text) ?>" required>
            </div>
        </div>

        <button type="submit" class="btn-save-section">
            <i class="fas fa-save"></i> Save CTA Banner
        </button>
    </div>
</form>

<!-- ══════════════════════════════════════════════
     SECTION 3: INNOVATIVE STRATEGIES
     ══════════════════════════════════════════════ -->
<form method="POST" id="innovative">
    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
    <input type="hidden" name="action" value="save_innovative">

    <div class="settings-card">
        <h3><i class="fas fa-lightbulb"></i> Innovative Strategies Section</h3>
        <p class="card-subtitle">Manage the feature bullets and strategy summary displayed before the stats counter.</p>

        <div class="grid-2">
            <div class="form-group">
                <label>Heading</label>
                <input type="text" name="innovative_title" value="<?= htmlspecialchars($innovative_title) ?>" required>
            </div>
            <div class="form-group">
                <label>Highlight Word / Phrase</label>
                <input type="text" name="innovative_highlight" value="<?= htmlspecialchars($innovative_highlight) ?>">
            </div>
        </div>

        <div class="form-group">
            <label>Description Paragraph</label>
            <textarea name="innovative_desc" rows="3" required><?= htmlspecialchars($innovative_desc) ?></textarea>
        </div>

        <div class="form-group">
            <label>Feature Bullet 1</label>
            <input type="text" name="innovative_b1" value="<?= htmlspecialchars($innovative_b1) ?>" required>
        </div>

        <div class="form-group">
            <label>Feature Bullet 2</label>
            <input type="text" name="innovative_b2" value="<?= htmlspecialchars($innovative_b2) ?>" required>
        </div>

        <div class="form-group">
            <label>Feature Bullet 3</label>
            <input type="text" name="innovative_b3" value="<?= htmlspecialchars($innovative_b3) ?>" required>
        </div>

        <div class="form-group">
            <label>Concluding Footer Note</label>
            <input type="text" name="innovative_footer" value="<?= htmlspecialchars($innovative_footer) ?>">
        </div>

        <button type="submit" class="btn-save-section">
            <i class="fas fa-save"></i> Save Innovative Strategies
        </button>
    </div>
</form>

<!-- ══════════════════════════════════════════════
     SECTION 4: CLIENTS SHOWCASE LIST
     ══════════════════════════════════════════════ -->
<form method="POST" id="clients">
    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
    <input type="hidden" name="action" value="save_clients">

    <div class="settings-card">
        <h3><i class="fas fa-users-viewfinder"></i> 3D Client Showcase &amp; Brands</h3>
        <p class="card-subtitle">Customize the brand partners that appear on the auto-flipping client grid on the homepage.</p>

        <div class="form-group">
            <label>Section Heading</label>
            <input type="text" name="clients_heading" value="<?= htmlspecialchars($clients_heading) ?>" required>
        </div>

        <div class="form-group">
            <label>Client Brand Names <span class="opt">(Separated by commas)</span></label>
            <textarea name="clients_list" rows="4" placeholder="Brand 1, Brand 2, Brand 3..." required><?= htmlspecialchars($clients_list) ?></textarea>
            <span style="font-size: 12px; color: var(--text-muted, #64748b); margin-top: 6px; display: block;">
                Each brand name generates an interactive 3D card on the homepage. Client names from your active projects are also automatically included!
            </span>
        </div>

        <button type="submit" class="btn-save-section">
            <i class="fas fa-save"></i> Save Client Brands
        </button>
    </div>
</form>
