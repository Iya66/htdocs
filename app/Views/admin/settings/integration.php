<?php
require_once __DIR__ . '/../../../../admin/includes/header.php';
?>

<style>
.settings-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--border-radius); padding: 32px; box-shadow: var(--shadow-sm); margin-bottom: 24px; transition: all var(--transition); }
.settings-card h3 { font-size: 18px; color: var(--text-main); margin-bottom: 24px; display: flex; align-items: center; gap: 12px; font-weight: 700; }
.settings-card h3 i { color: var(--primary); font-size: 20px; }

.form-group { margin-bottom: 24px; }
.form-group label { display: block; margin-bottom: 8px; color: var(--text-main); font-weight: 600; font-size: 14px; }
.form-group input, .form-group select, .form-group textarea { 
    width: 100%; padding: 14px 18px; 
    border: 1px solid var(--border-color); border-radius: var(--btn-radius); 
    font-size: 14px; background: var(--bg-main); color: var(--text-main); 
    transition: all var(--transition); 
}
.form-group input:focus, .form-group textarea:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px color-mix(in srgb, var(--primary) 20%, transparent); }
.form-group textarea { min-height: 120px; resize: vertical; }
.form-group .hint { font-size: 12px; color: var(--text-muted); margin-top: 6px; display: block; }

.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
.form-row-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
.checkbox-group { display: flex; align-items: center; gap: 12px; }
.checkbox-group input[type="checkbox"] { width: 18px; height: 18px; cursor: pointer; accent-color: var(--primary); }

.tab-nav { display: flex; gap: 12px; margin-bottom: 32px; flex-wrap: wrap; }
.tab-btn { 
    padding: 12px 24px; background: var(--bg-card); 
    border: 1px solid var(--border-color); border-radius: var(--btn-radius); 
    cursor: pointer; font-size: 14px; font-weight: 600; color: var(--text-muted); 
    transition: all var(--transition); 
}
.tab-btn:hover, .tab-btn.active { background: var(--primary); color: #fff; border-color: var(--primary); }
.tab-content { display: none; }
.tab-content.active { display: block; animation: fadeIn var(--transition) ease forwards; }

.tool-card { 
    background: var(--bg-main); border-radius: var(--border-radius); padding: 32px 24px; 
    text-align: center; border: 2px dashed var(--border-color); transition: all var(--transition); 
}
.tool-card:hover { border-color: var(--primary); background: color-mix(in srgb, var(--primary) 5%, transparent); transform: translateY(-4px); }
.tool-card i { font-size: 48px; color: var(--primary); margin-bottom: 20px; }
.tool-card h4 { color: var(--text-main); margin-bottom: 12px; font-size: 18px; }
.tool-card p { color: var(--text-muted); font-size: 14px; margin-bottom: 24px; }
.tools-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px; }

.social-list { background: var(--bg-main); border-radius: var(--border-radius); padding: 24px; max-height: 600px; overflow-y: auto; border: 1px solid var(--border-color); }
.social-item { display: flex; align-items: center; gap: 20px; padding: 20px; background: var(--bg-card); border-radius: var(--border-radius); margin-bottom: 16px; border: 1px solid var(--border-color); flex-wrap: wrap; transition: all var(--transition); }
.social-item:hover { border-color: var(--primary); box-shadow: var(--shadow-sm); }
.social-item:last-child { margin-bottom: 0; }
.social-item .social-icon { width: 48px; height: 48px; min-width: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px; color: #fff; }
.social-item .social-content { flex: 1; min-width: 250px; }
.social-item .social-header { display: flex; align-items: center; gap: 16px; margin-bottom: 12px; flex-wrap: wrap; justify-content: space-between; }
.social-item .social-name { font-weight: 700; color: var(--text-main); font-size: 15px; }
.social-item .social-toggle { font-size: 14px; color: var(--text-main); font-weight: 500; }
.social-item input[type="url"] { 
    width: 100%; padding: 12px 16px; 
    border: 1px solid var(--border-color); border-radius: var(--btn-radius); 
    font-size: 14px; background: var(--bg-main); color: var(--text-main); 
    transition: all var(--transition); 
}
.social-item input[type="url"]:focus { 
    outline: none; border-color: var(--primary); 
    box-shadow: 0 0 0 3px color-mix(in srgb, var(--primary) 20%, transparent); 
}

.social-preview { display: flex; gap: 16px; padding: 40px; justify-content: center; flex-wrap: wrap; background: var(--bg-main); border-radius: var(--border-radius); border: 1px solid var(--border-color); }
.social-preview a { width: 56px; height: 56px; border-radius: 16px; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 24px; transition: all var(--transition); text-decoration: none; }
.social-preview a:hover { transform: translateY(-6px) scale(1.05); box-shadow: var(--shadow-md); }

@media(max-width: 768px) { .form-row { grid-template-columns: 1fr; } .form-row-3 { grid-template-columns: 1fr; } }
</style>

<div class="saas-header">
    <h1 class="saas-header-title">
        <i class="fas fa-plug" aria-hidden="true" style="color: var(--primary);"></i>
        Integrations & SEO
    </h1>
    <p class="saas-header-subtitle">Manage analytics trackers, search verification tags, and third-party API credentials.</p>
</div>

<?php require_once __DIR__ . '/../../../../admin/includes/settings-nav.php'; ?>

<?php if ($message): ?>
<div class="alert alert-<?= $message_type ?>">
    <i class="fas fa-<?= $message_type === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
    <?= htmlspecialchars($message) ?>
</div>
<?php endif; ?>

<div class="tab-nav">
    <button class="tab-btn active" onclick="showTab('analytics')"><i class="fas fa-chart-line"></i> Analytics</button>
    <button class="tab-btn" onclick="showTab('whatsapp')"><i class="fab fa-whatsapp" style="color: #25D366;"></i> WhatsApp (WABA)</button>
    <button class="tab-btn" onclick="showTab('social')"><i class="fas fa-share-alt"></i> Social Media</button>
    <button class="tab-btn" onclick="showTab('seo')"><i class="fas fa-search"></i> SEO Settings</button>
    <button class="tab-btn" onclick="showTab('tools')"><i class="fas fa-tools"></i> SEO Tools</button>
</div>

<!-- Analytics Tab -->
<div id="analytics" class="tab-content active">
    <div class="settings-card">
        <h3><i class="fab fa-google"></i> Google Analytics</h3>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <div class="form-row">
                <div class="form-group">
                    <label>Google Analytics Measurement ID</label>
                    <input type="text" name="google_analytics_id" value="<?= htmlspecialchars(getSettingValue('analytics_google_id')) ?>" placeholder="G-XXXXXXXXXX">
                    <span class="hint">Found in your Google Analytics dashboard → Admin → Data Streams</span>
                </div>
                <div class="form-group">
                    <label>&nbsp;</label>
                    <div class="checkbox-group">
                        <input type="checkbox" name="google_analytics_enabled" id="google_analytics_enabled" <?= getSettingValue('analytics_google_enabled') === '1' ? 'checked' : '' ?>>
                        <label for="google_analytics_enabled">Enable Google Analytics</label>
                    </div>
                </div>
            </div>
            <button type="submit" name="save_analytics" class="btn btn-success"><i class="fas fa-save"></i> Save Analytics</button>
        </form>
    </div>

    <div class="settings-card">
        <h3><i class="fas fa-tags"></i> Google Tag Manager</h3>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <input type="hidden" name="save_analytics" value="1">
            <div class="form-row">
                <div class="form-group">
                    <label>Google Tag Manager ID</label>
                    <input type="text" name="tag_manager_id" value="<?= htmlspecialchars(getSettingValue('analytics_tag_manager_id')) ?>" placeholder="GTM-XXXXXXX">
                    <span class="hint">Found in your Tag Manager dashboard → Admin → Container Settings</span>
                </div>
                <div class="form-group">
                    <label>&nbsp;</label>
                    <div class="checkbox-group">
                        <input type="checkbox" name="tag_manager_enabled" id="tag_manager_enabled" <?= getSettingValue('analytics_tag_manager_enabled') === '1' ? 'checked' : '' ?>>
                        <label for="tag_manager_enabled">Enable Tag Manager</label>
                    </div>
                </div>
            </div>
            <button type="submit" name="save_analytics" class="btn btn-success"><i class="fas fa-save"></i> Save Tag Manager</button>
        </form>
    </div>

    <div class="settings-card">
        <h3><i class="fab fa-facebook"></i> Facebook Pixel</h3>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <input type="hidden" name="save_analytics" value="1">
            <div class="form-row">
                <div class="form-group">
                    <label>Facebook Pixel ID</label>
                    <input type="text" name="facebook_pixel_id" value="<?= htmlspecialchars(getSettingValue('analytics_facebook_pixel_id')) ?>" placeholder="XXXXXXXXXX">
                    <span class="hint">Found in Facebook Events Manager → Data Sources → Pixel</span>
                </div>
                <div class="form-group">
                    <label>&nbsp;</label>
                    <div class="checkbox-group">
                        <input type="checkbox" name="facebook_pixel_enabled" id="facebook_pixel_enabled" <?= getSettingValue('analytics_facebook_pixel_enabled') === '1' ? 'checked' : '' ?>>
                        <label for="facebook_pixel_enabled">Enable Facebook Pixel</label>
                    </div>
                </div>
            </div>
            <button type="submit" name="save_analytics" class="btn btn-success"><i class="fas fa-save"></i> Save Facebook Pixel</button>
        </form>
    </div>
</div>

<!-- Social Media Tab -->
<div id="social" class="tab-content">
    <div class="settings-card">
        <h3><i class="fas fa-share-alt"></i> Social Media Links</h3>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <div class="social-list">
                <?php
                $socialPlatforms = [
                    'facebook' => ['name' => 'Facebook', 'color' => '#1877F2', 'icon' => 'fab fa-facebook-f'],
                    'instagram' => ['name' => 'Instagram', 'color' => '#E4405F', 'icon' => 'fab fa-instagram'],
                    'twitter' => ['name' => 'Twitter / X', 'color' => '#000000', 'icon' => 'fab fa-x-twitter'],
                    'linkedin' => ['name' => 'LinkedIn', 'color' => '#0A66C2', 'icon' => 'fab fa-linkedin-in'],
                    'youtube' => ['name' => 'YouTube', 'color' => '#FF0000', 'icon' => 'fab fa-youtube'],
                    'whatsapp' => ['name' => 'WhatsApp', 'color' => '#25D366', 'icon' => 'fab fa-whatsapp'],
                    'telegram' => ['name' => 'Telegram', 'color' => '#0088CC', 'icon' => 'fab fa-telegram'],
                    'discord' => ['name' => 'Discord', 'color' => '#5865F2', 'icon' => 'fab fa-discord'],
                    'tiktok' => ['name' => 'TikTok', 'color' => '#000000', 'icon' => 'fab fa-tiktok'],
                    'github' => ['name' => 'GitHub', 'color' => '#333333', 'icon' => 'fab fa-github'],
                ];

                foreach ($socialPlatforms as $platform => $info):
                ?>
                <div class="social-item">
                    <div class="social-icon" style="background: <?= $info['color'] ?>;">
                        <i class="<?= $info['icon'] ?>"></i>
                    </div>
                    <div class="social-content">
                        <div class="social-header">
                            <span class="social-name"><?= $info['name'] ?></span>
                            <div class="checkbox-group social-toggle">
                                <input type="checkbox" name="social[<?= $platform ?>][enabled]" id="<?= $platform ?>_enabled" <?= getSettingValue('social_' . $platform . '_enabled') === '1' ? 'checked' : '' ?>>
                                <label for="<?= $platform ?>_enabled">Show</label>
                            </div>
                        </div>
                        <input type="url" name="social[<?= $platform ?>][url]" value="<?= htmlspecialchars(getSettingValue('social_' . $platform . '_url')) ?>" placeholder="https://<?= $platform ?>.com/yourpage">
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <button type="submit" name="save_social" class="btn btn-success" style="margin-top: 20px;"><i class="fas fa-save"></i> Save Social Links</button>
        </form>
    </div>

    <div class="settings-card">
        <h3><i class="fas fa-eye"></i> Social Icons Preview</h3>
        <div class="social-preview">
            <?php foreach ($socialPlatforms as $platform => $info):
                if (getSettingValue('social_' . $platform . '_enabled') === '1' && getSettingValue('social_' . $platform . '_url')):
            ?>
            <a href="<?= htmlspecialchars(getSettingValue('social_' . $platform . '_url')) ?>" target="_blank" style="background: <?= $info['color'] ?>;" title="<?= $info['name'] ?>">
                <i class="<?= $info['icon'] ?>"></i>
            </a>
            <?php endif; endforeach; ?>
        </div>
    </div>
</div>

<!-- SEO Settings Tab -->
<div id="seo" class="tab-content">
    <div class="settings-card">
        <h3><i class="fas fa-globe"></i> Meta Tags</h3>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <div class="form-row">
                <div class="form-group">
                    <label>Meta Title</label>
                    <input type="text" name="meta_title" value="<?= htmlspecialchars(getSettingValue('seo_meta_title')) ?>" placeholder="Your Site Title">
                </div>
                <div class="form-group">
                    <label>Meta Author</label>
                    <input type="text" name="meta_author" value="<?= htmlspecialchars(getSettingValue('seo_meta_author')) ?>" placeholder="Author Name">
                </div>
            </div>
            <div class="form-group">
                <label>Meta Description</label>
                <textarea name="meta_description" placeholder="Brief description of your website (150-160 characters recommended)"><?= htmlspecialchars(getSettingValue('seo_meta_description')) ?></textarea>
                <span class="hint">Recommended: 150-160 characters</span>
            </div>
            <div class="form-group">
                <label>Meta Keywords</label>
                <input type="text" name="meta_keywords" value="<?= htmlspecialchars(getSettingValue('seo_meta_keywords')) ?>" placeholder="keyword1, keyword2, keyword3">
                <span class="hint">Comma-separated keywords</span>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>OG Image URL</label>
                    <input type="url" name="og_image" value="<?= htmlspecialchars(getSettingValue('seo_og_image')) ?>" placeholder="https://example.com/og-image.jpg">
                    <span class="hint">Recommended size: 1200x630px</span>
                </div>
                <div class="form-group">
                    <label>Canonical URL</label>
                    <input type="url" name="canonical_url" value="<?= htmlspecialchars(getSettingValue('seo_canonical_url')) ?>" placeholder="https://yourdomain.com">
                    <span class="hint">Your website's main URL</span>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" name="robots_index" id="robots_index" <?= getSettingValue('seo_robots_index') !== '0' ? 'checked' : '' ?>>
                        <label for="robots_index">Allow Search Engines to Index</label>
                    </div>
                </div>
                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" name="robots_follow" id="robots_follow" <?= getSettingValue('seo_robots_follow') !== '0' ? 'checked' : '' ?>>
                        <label for="robots_follow">Follow Links</label>
                    </div>
                </div>
            </div>
            <button type="submit" name="save_seo" class="btn btn-success"><i class="fas fa-save"></i> Save SEO Settings</button>
        </form>
    </div>
</div>

<!-- SEO Tools Tab -->
<div id="tools" class="tab-content">
    <div class="settings-card">
        <h3><i class="fas fa-tools"></i> SEO Tools</h3>
        <p style="color: #666; margin-bottom: 20px;">Generate essential SEO files for your website. Click to download.</p>
        <div class="tools-grid">
            <div class="tool-card">
                <i class="fas fa-sitemap"></i>
                <h4>Sitemap Generator</h4>
                <p>Creates XML sitemap for search engines to crawl your site</p>
                <a href="?action=generate_sitemap" class="btn btn-success btn-sm"><i class="fas fa-download"></i> Download sitemap.xml</a>
            </div>
            <div class="tool-card">
                <i class="fas fa-robot"></i>
                <h4>Robots.txt Generator</h4>
                <p>Control search engine crawlers access to your site</p>
                <a href="?action=generate_robots" class="btn btn-info btn-sm"><i class="fas fa-download"></i> Download robots.txt</a>
            </div>
            <div class="tool-card">
                <i class="fas fa-brain"></i>
                <h4>LLMs.txt Generator</h4>
                <p>New standard for AI/LLM readable documentation</p>
                <a href="?action=generate_llms" class="btn btn-sm" style="background: #9333ea;"><i class="fas fa-download"></i> Download llms.txt</a>
            </div>
        </div>
    </div>

    <div class="settings-card">
        <h3><i class="fas fa-code"></i> Preview Generated Code</h3>
        <div class="form-row">
            <div class="form-group">
                <label>Code to add in &lt;head&gt;</label>
                <textarea readonly style="background: #f8f9fa; font-family: monospace; font-size: 12px; min-height: 200px;">
<?php if (getSettingValue('analytics_tag_manager_enabled') === '1' && getSettingValue('analytics_tag_manager_id')): ?>
<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','<?= htmlspecialchars(getSettingValue('analytics_tag_manager_id')) ?>');</script>
<?php endif; ?>

<?php if (getSettingValue('analytics_google_enabled') === '1' && getSettingValue('analytics_google_id')): ?>
<!-- Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=<?= htmlspecialchars(getSettingValue('analytics_google_id')) ?>"></script>
<script>window.dataLayer = window.dataLayer || []; function gtag(){dataLayer.push(arguments);} gtag('js', new Date()); gtag('config', '<?= htmlspecialchars(getSettingValue('analytics_google_id')) ?>');</script>
<?php endif; ?></textarea>
            </div>
            <div class="form-group">
                <label>Meta Tags Preview</label>
                <textarea readonly style="background: #f8f9fa; font-family: monospace; font-size: 12px; min-height: 200px;">
<!-- SEO Meta Tags -->
<title><?= htmlspecialchars(getSettingValue('seo_meta_title', 'Your Site Title')) ?></title>
<meta name="description" content="<?= htmlspecialchars(getSettingValue('seo_meta_description', '')) ?>">
<meta name="keywords" content="<?= htmlspecialchars(getSettingValue('seo_meta_keywords', '')) ?>">
<meta name="author" content="<?= htmlspecialchars(getSettingValue('seo_meta_author', '')) ?>">
<link rel="canonical" href="<?= htmlspecialchars(getSettingValue('seo_canonical_url', '')) ?>">
<!-- Open Graph -->
<meta property="og:title" content="<?= htmlspecialchars(getSettingValue('seo_meta_title', '')) ?>">
<meta property="og:description" content="<?= htmlspecialchars(getSettingValue('seo_meta_description', '')) ?>">
<meta property="og:image" content="<?= htmlspecialchars(getSettingValue('seo_og_image', '')) ?>">
<meta property="og:url" content="<?= htmlspecialchars(getSettingValue('seo_canonical_url', '')) ?>">

<!-- Robots -->
<meta name="robots" content="<?= getSettingValue('seo_robots_index') !== '0' ? 'index' : 'noindex' ?>, <?= getSettingValue('seo_robots_follow') !== '0' ? 'follow' : 'nofollow' ?>"></textarea>
            </div>
        </div>
    </div>
</div>

<!-- WhatsApp (Fast2SMS WABA) Tab -->
<div id="whatsapp" class="tab-content">
    <div class="settings-card">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
            <h3 style="margin-bottom: 0;"><i class="fab fa-whatsapp" style="color: #25D366;"></i> Fast2SMS WhatsApp Business API (WABA)</h3>
            <button type="button" class="btn btn-secondary btn-sm" id="btnSyncWaba" onclick="syncWabaTemplates()">
                <i class="fas fa-sync-alt" id="syncIcon"></i> Fetch WABA & Templates
            </button>
        </div>

        <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 24px;">
            Configure official Fast2SMS WhatsApp Business Cloud API integration for automated Lead alerts, client confirmations, and review notifications using Meta-approved templates.
            <a href="https://docs.fast2sms.com/reference/get-waba-template-details" target="_blank" rel="noopener noreferrer" style="color: var(--primary); font-weight: 600; text-decoration: none; margin-left: 6px;">
                <i class="fas fa-external-link-alt"></i> API Documentation
            </a>
        </p>

        <!-- Live WABA Connection Status Card -->
        <div id="wabaStatusBox" style="background: var(--bg-main); border: 1px solid var(--border-color); border-radius: var(--border-radius); padding: 20px; margin-bottom: 28px; display: none;">
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 16px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 12px; height: 12px; border-radius: 50%; background: #10b981; box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);"></div>
                    <strong style="color: var(--text-main); font-size: 15px;" id="wabaVerifiedName">Fast2SMS Connected</strong>
                    <span id="wabaBadge" style="background: rgba(16, 185, 129, 0.15); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.3); padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: 600;">ACTIVE</span>
                </div>
                <div style="font-size: 13px; color: var(--text-muted);">
                    Quality Rating: <strong id="wabaQuality" style="color: #10b981;">GREEN</strong>
                </div>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; font-size: 13px;">
                <div><span style="color: var(--text-muted);">WABA ID:</span> <strong id="lblWabaId" style="color: var(--text-main);">-</strong></div>
                <div><span style="color: var(--text-muted);">Phone Number ID:</span> <strong id="lblPhoneId" style="color: var(--text-main);">-</strong></div>
                <div><span style="color: var(--text-muted);">Sender Number:</span> <strong id="lblSenderNumber" style="color: var(--text-main);">-</strong></div>
            </div>
        </div>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <input type="hidden" name="save_whatsapp" value="1">

            <div class="form-row">
                <div class="form-group">
                    <label><i class="fas fa-key"></i> Fast2SMS API Authorization Key <span style="color: #ef4444;">*</span></label>
                    <input type="text" name="whatsapp_api_key" id="inputApiKey" value="<?= htmlspecialchars(getSettingValue('whatsapp_api_key')) ?>" placeholder="e.g. YOUR_FAST2SMS_DEV_API_KEY" required>
                    <span class="hint">Obtain your API Key from your Fast2SMS Panel → Dev API section.</span>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-phone-alt"></i> Admin WhatsApp Mobile Number <span style="color: #ef4444;">*</span></label>
                    <input type="text" name="whatsapp_admin_number" value="<?= htmlspecialchars(getSettingValue('whatsapp_admin_number', getSettingValue('contact_whatsapp', '8098830937'))) ?>" placeholder="e.g. 8098830937" required>
                    <span class="hint">10-digit Indian mobile number where incoming lead & review notifications will be sent.</span>
                </div>
            </div>

            <div class="form-row-3">
                <div class="form-group">
                    <label><i class="fas fa-paper-plane"></i> Sender WhatsApp Number</label>
                    <input type="text" name="whatsapp_sender_number" value="<?= htmlspecialchars(getSettingValue('whatsapp_sender_number', '8682848655')) ?>" placeholder="e.g. 8682848655">
                    <span class="hint">Registered sender number (+91 8682848655).</span>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-hashtag"></i> WABA Phone Number ID</label>
                    <input type="text" name="whatsapp_phone_number_id" id="inputPhoneId" value="<?= htmlspecialchars(getSettingValue('whatsapp_phone_number_id')) ?>" placeholder="e.g. 576293849102938">
                    <span class="hint">Meta Cloud API Phone Number ID.</span>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-building"></i> WhatsApp Business (WABA) ID</label>
                    <input type="text" name="whatsapp_waba_id" id="inputWabaId" value="<?= htmlspecialchars(getSettingValue('whatsapp_waba_id')) ?>" placeholder="e.g. 992837461928374">
                    <span class="hint">Your Meta WABA ID.</span>
                </div>
            </div>

            <div class="form-group">
                <div class="checkbox-group">
                    <input type="checkbox" id="whatsapp_enabled" name="whatsapp_enabled" <?= getSettingValue('whatsapp_enabled') === '1' ? 'checked' : '' ?>>
                    <label for="whatsapp_enabled" style="margin-bottom:0; cursor:pointer;">
                        <strong>Enable Automated WhatsApp Notifications</strong>
                    </label>
                </div>
                <span class="hint" style="margin-left: 30px;">When enabled, system events will instantly trigger WhatsApp template alerts.</span>
            </div>

            <hr style="border: 0; border-top: 1px solid var(--border-color); margin: 32px 0;">

            <h4 style="color: var(--text-main); font-size: 16px; margin-bottom: 20px;">
                <i class="fas fa-comments" style="color: var(--primary);"></i> Template Automation Mapping (Fast2SMS Message IDs)
            </h4>

            <div class="form-row-3">
                <div class="form-group">
                    <label>Lead Notification Template ID</label>
                    <input type="text" name="whatsapp_lead_template_id" id="inputLeadTpl" value="<?= htmlspecialchars(getSettingValue('whatsapp_lead_template_id')) ?>" placeholder="e.g. 8340">
                    <span class="hint">Fast2SMS template message_id for alerts sent to Admin.</span>
                </div>
                <div class="form-group">
                    <label>Client Welcome Template ID</label>
                    <input type="text" name="whatsapp_welcome_template_id" id="inputWelcomeTpl" value="<?= htmlspecialchars(getSettingValue('whatsapp_welcome_template_id')) ?>" placeholder="e.g. 8341">
                    <span class="hint">Template message_id sent to the client upon inquiry.</span>
                </div>
                <div class="form-group">
                    <label>Review Alert Template ID</label>
                    <input type="text" name="whatsapp_review_template_id" id="inputReviewTpl" value="<?= htmlspecialchars(getSettingValue('whatsapp_review_template_id')) ?>" placeholder="e.g. 8342">
                    <span class="hint">Template message_id sent when a review is submitted.</span>
                </div>
            </div>

            <div id="templatesListContainer" style="margin-top: 16px; display: none;">
                <label style="font-weight: 600; margin-bottom: 12px; display: block;">Discovered Fast2SMS Templates</label>
                <div id="templatesGrid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px; max-height: 280px; overflow-y: auto; padding: 12px; background: var(--bg-main); border: 1px solid var(--border-color); border-radius: var(--border-radius);"></div>
            </div>

            <div style="margin-top: 24px;">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save WhatsApp Settings</button>
            </div>
        </form>
    </div>

    <!-- Interactive WhatsApp Test Console Card -->
    <div class="settings-card" style="border-top: 4px solid #25D366;">
        <h3><i class="fas fa-paper-plane" style="color: #25D366;"></i> WhatsApp Live Test Console</h3>
        <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 20px;">
            Send an instant test WhatsApp message to verify API credentials and template delivery before enabling production alerts.
        </p>

        <form id="formTestWhatsApp" onsubmit="sendTestWhatsApp(event)">
            <div class="form-row-3">
                <div class="form-group">
                    <label>Recipient Mobile Number <span style="color: #ef4444;">*</span></label>
                    <input type="text" id="testToNumber" placeholder="e.g. 8098830937" required>
                </div>
                <div class="form-group">
                    <label>Template Message ID <span style="color: #ef4444;">*</span></label>
                    <input type="text" id="testMessageId" placeholder="e.g. 8340" required>
                </div>
                <div class="form-group">
                    <label>Sample Variables (Comma-separated)</label>
                    <input type="text" id="testVariables" placeholder="e.g. Rahul, Digital Marketing">
                </div>
            </div>
            <button type="submit" class="btn btn-primary" id="btnSendTest" style="background: #25D366; border-color: #25D366; color: #fff;">
                <i class="fab fa-whatsapp"></i> Send Test Message
            </button>
        </form>

        <div id="testOutputBox" style="margin-top: 20px; display: none;">
            <label style="font-weight: 600; font-size: 13px; color: var(--text-main); margin-bottom: 8px; display: block;">API Response Diagnostics</label>
            <pre id="testOutputLog" style="background: #0f172a; color: #38bdf8; padding: 16px; border-radius: 8px; font-family: monospace; font-size: 12px; overflow-x: auto; white-space: pre-wrap; max-height: 250px;"></pre>
        </div>
    </div>
</div>

<script>
function showTab(tabId) {
    document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    document.getElementById(tabId).classList.add('active');
    
    // Find matching button
    document.querySelectorAll('.tab-btn').forEach(btn => {
        if (btn.getAttribute('onclick') && btn.getAttribute('onclick').includes(tabId)) {
            btn.classList.add('active');
        }
    });
}

// Fetch WABA & Templates from Fast2SMS
async function syncWabaTemplates() {
    const apiKey = document.getElementById('inputApiKey').value.trim();
    if (!apiKey) {
        alert('Please enter your Fast2SMS API Key first.');
        document.getElementById('inputApiKey').focus();
        return;
    }

    const syncBtn = document.getElementById('btnSyncWaba');
    const syncIcon = document.getElementById('syncIcon');
    syncBtn.disabled = true;
    syncIcon.classList.add('fa-spin');

    try {
        const response = await fetch(`../../admin/api/whatsapp.php?action=fetch_templates&api_key=${encodeURIComponent(apiKey)}`);
        const result = await response.json();

        if (result.success && result.data && result.data.length > 0) {
            const waba = result.data[0];
            
            // Populate Status Box
            document.getElementById('wabaStatusBox').style.display = 'block';
            document.getElementById('wabaVerifiedName').textContent = waba.verified_name || 'Fast2SMS WABA';
            document.getElementById('wabaQuality').textContent = waba.quality_rating || 'GREEN';
            document.getElementById('lblWabaId').textContent = waba.waba_id || '-';
            document.getElementById('lblPhoneId').textContent = waba.phone_number_id || '-';
            document.getElementById('lblSenderNumber').textContent = waba.number || '-';

            // Auto fill inputs if empty
            if (waba.phone_number_id && !document.getElementById('inputPhoneId').value) {
                document.getElementById('inputPhoneId').value = waba.phone_number_id;
            }
            if (waba.waba_id && !document.getElementById('inputWabaId').value) {
                document.getElementById('inputWabaId').value = waba.waba_id;
            }

            // Populate Templates Grid
            const templates = waba.templates || [];
            const grid = document.getElementById('templatesGrid');
            grid.innerHTML = '';

            if (templates.length > 0) {
                document.getElementById('templatesListContainer').style.display = 'block';
                templates.forEach(tpl => {
                    const card = document.createElement('div');
                    card.style.cssText = 'background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 8px; padding: 12px; cursor: pointer; transition: all 0.2s;';
                    card.innerHTML = `
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                            <strong style="color: var(--text-main); font-size: 13px;">${tpl.template_name || 'Template'}</strong>
                            <span style="font-size: 11px; background: rgba(16, 185, 129, 0.15); color: #10b981; padding: 1px 6px; border-radius: 4px;">ID: ${tpl.message_id}</span>
                        </div>
                        <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 6px;">
                            Category: ${tpl.category || 'MARKETING'} | Lang: ${tpl.language || 'en'}
                        </div>
                        <div style="display: flex; gap: 4px; flex-wrap: wrap;">
                            <button type="button" class="btn btn-sm" style="padding: 2px 8px; font-size: 11px; background: var(--bg-main); border: 1px solid var(--border-color);" onclick="setTemplateId('inputLeadTpl', ${tpl.message_id})">Use for Leads</button>
                            <button type="button" class="btn btn-sm" style="padding: 2px 8px; font-size: 11px; background: var(--bg-main); border: 1px solid var(--border-color);" onclick="setTemplateId('inputWelcomeTpl', ${tpl.message_id})">Use for Welcome</button>
                            <button type="button" class="btn btn-sm" style="padding: 2px 8px; font-size: 11px; background: var(--bg-main); border: 1px solid var(--border-color);" onclick="setTemplateId('testMessageId', ${tpl.message_id})">Test This</button>
                        </div>
                    `;
                    grid.appendChild(card);
                });
            }

            alert(`Successfully fetched WABA details and ${templates.length} templates!`);
        } else {
            alert('Failed to fetch WABA details: ' + (result.error || 'Unknown error occurred.'));
        }
    } catch (err) {
        alert('Network or server error while connecting to Fast2SMS API: ' + err.message);
    } finally {
        syncBtn.disabled = false;
        syncIcon.classList.remove('fa-spin');
    }
}

function setTemplateId(inputId, id) {
    const el = document.getElementById(inputId);
    if (el) {
        el.value = id;
        el.scrollIntoView({ behavior: 'smooth', block: 'center' });
        el.style.borderColor = '#10b981';
        setTimeout(() => el.style.borderColor = '', 1500);
    }
}

// Send Test WhatsApp Message
async function sendTestWhatsApp(e) {
    e.preventDefault();
    const to = document.getElementById('testToNumber').value.trim();
    const msgId = document.getElementById('testMessageId').value.trim();
    const pId = document.getElementById('inputPhoneId').value.trim();
    const vars = document.getElementById('testVariables').value.trim();

    const btn = document.getElementById('btnSendTest');
    const outBox = document.getElementById('testOutputBox');
    const outLog = document.getElementById('testOutputLog');

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
    outBox.style.display = 'block';
    outLog.textContent = 'Connecting to Fast2SMS WhatsApp Gateway...';

    const formData = new FormData();
    formData.append('action', 'send_test');
    formData.append('to_number', to);
    formData.append('message_id', msgId);
    formData.append('phone_number_id', pId);
    formData.append('variables', vars);
    formData.append('csrf_token', '<?= $csrf_token ?>');

    try {
        const res = await fetch('../../admin/api/whatsapp.php', {
            method: 'POST',
            body: formData
        });
        const json = await res.json();
        outLog.textContent = JSON.stringify(json, null, 2);

        if (json.success) {
            outLog.style.color = '#34d399';
        } else {
            outLog.style.color = '#f87171';
        }
    } catch (err) {
        outLog.textContent = 'Error: ' + err.message;
        outLog.style.color = '#f87171';
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fab fa-whatsapp"></i> Send Test Message';
    }
}
</script>

<?php require_once __DIR__ . '/../../../../admin/includes/footer.php'; ?>