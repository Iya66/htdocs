<?php
require_once __DIR__ . '/../../../admin/includes/header.php';
?>

<!-- TinyMCE CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.2/tinymce.min.js" referrerpolicy="origin"></script>

<style>
/* Dashboard Match Styles */
.dashboard-header {
 background: linear-gradient(135deg, var(--bg-card) 0%, color-mix(in srgb, var(--primary) 5%, var(--bg-card)) 100%);
 border: 1px solid var(--border-color);
 border-radius: var(--border-radius);
 padding: 32px 40px;
 margin-bottom: 32px;
 display: flex;
 justify-content: space-between;
 align-items: center;
 box-shadow: var(--shadow-sm);
 position: relative;
 overflow: hidden;
}
.dashboard-header::before {
 content: '';
 position: absolute;
 top: -50%; right: -10%;
 width: 400px; height: 400px;
 background: radial-gradient(circle, color-mix(in srgb, var(--primary) 15%, transparent) 0%, transparent 70%);
 border-radius: 50%;
 z-index: 0;
 pointer-events: none;
}
.dashboard-header .welcome-text { position: relative; z-index: 1; }
.dashboard-header h1 { font-size: 28px; font-weight: 700; color: var(--text-main); margin-bottom: 8px; letter-spacing: -0.02em; }
.dashboard-header p { margin: 0; color: var(--text-muted); font-size: 15px; }
.dashboard-header .header-actions { position: relative; z-index: 1; display: flex; gap: 16px; }

/* Vibrant Neon Buttons matching Dashboard */
.btn-neon {
 background: #00ff2b;
 color: #0f172a;
 border: none;
 font-weight: 700;
 box-shadow: 0 4px 14px rgba(0, 255, 43, 0.3);
 transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
 display: inline-flex;
 align-items: center;
 gap: 8px;
}
.btn-neon:hover {
 background: #00e627;
 transform: translateY(-2px);
 box-shadow: 0 6px 20px rgba(0, 255, 43, 0.4);
 color: #0f172a;
}

.editor-container {
 background: var(--bg-card);
 border: 1px solid var(--border-color);
 border-radius: var(--border-radius);
 padding: 24px;
 box-shadow: var(--shadow-sm);
}
.tox-tinymce {
 border-radius: 8px !important;
 border-color: var(--border-color) !important;
}
.btn-lg {
 padding: 12px 24px;
 font-size: 16px;
}

/* Hide TinyMCE "Upgrade to Cloud" promotion & branding */
.tox-promotion,
.tox-promotion-link,
.tox-statusbar__branding,
.tox-statusbar__right .tox-ad,
.tox-ad,
.tox-notification--warning.tox-notification--nudge,
a[href*="tiny.cloud/try"],
a[href*="tiny.cloud/tinymce-upgrade"] {
 display: none !important;
 visibility: hidden !important;
 opacity: 0 !important;
 pointer-events: none !important;
}
</style>

<div class="dashboard-header">
 <div class="welcome-text">
 <h1><i class="fas fa-shield-halved" style="color: var(--primary); margin-right: 12px;"></i> Privacy Policy</h1>
 <p>Customize and manage your site's privacy terms.</p>
 </div>
</div>

<?php if ($message): ?>
 <div class="alert alert-<?= $message_type ?>">
 <i class="fas fa-<?= $message_type === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
 <?= htmlspecialchars($message) ?>
 </div>
<?php endif; ?>

<div class="editor-container">
 <form method="POST" id="privacyPolicyForm" onsubmit="if(typeof tinymce !== 'undefined') tinymce.triggerSave();">
 <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
 <input type="hidden" name="action" value="save_policy">

 <div class="form-group" style="margin-bottom: 12px;">
 <label style="font-size: 16px; font-weight: 600; margin: 0; color: var(--text-main);">Editor</label>
 </div>
 <div class="form-group">
 <textarea id="privacyEditor" name="privacy_policy" style="height: 500px; width: 100%;"><?= htmlspecialchars($current_policy) ?></textarea>
 </div>

 <div style="margin-top: 24px; text-align: right;">
 <button type="submit" class="btn btn-neon btn-lg">
 <i class="fas fa-save"></i> Save Privacy Policy
 </button>
 </div>
 </form>
</div>

<script>
tinymce.init({
 selector: '#privacyEditor',
 height: 600,
 promotion: false,
 branding: false,
 menubar: true,
 plugins: [
 'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
 'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
 'insertdatetime', 'media', 'table', 'help', 'wordcount'
 ],
 toolbar: 'undo redo | blocks | ' +
 'bold italic backcolor | alignleft aligncenter ' +
 'alignright alignjustify | bullist numlist outdent indent | ' +
 'removeformat | help',
 content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; font-size: 16px; }',
 skin: document.documentElement.getAttribute('data-theme') === 'dark' ? 'oxide-dark' : 'oxide',
 content_css: document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'default',
 setup: function (editor) {
 editor.on('change keyup NodeChange', function () {
 editor.save();
 });
 }
});

document.getElementById('privacyPolicyForm')?.addEventListener('submit', function() {
    if (typeof tinymce !== 'undefined') {
        tinymce.triggerSave();
    }
});
</script>

<?php require_once __DIR__ . '/../../../admin/includes/footer.php'; ?>
