<?php
// Settings Navigation Component - Modern SaaS Segmented Bar
$settings_nav_items = [
    ['url' => 'index', 'icon' => 'fa-user-cog', 'label' => 'Admin Profile'],
    ['url' => 'general', 'icon' => 'fa-sliders-h', 'label' => 'Site Settings'],
    ['url' => 'homepage', 'icon' => 'fa-home', 'label' => 'Homepage & Content'],
    ['url' => 'mailsettings', 'icon' => 'fa-envelope', 'label' => 'Mail Settings'],
    ['url' => 'integration', 'icon' => 'fa-plug', 'label' => 'SEO & Integrations'],
    ['url' => 'module', 'icon' => 'fa-cubes', 'label' => 'Modules'],
];

$current_settings_page = basename($_SERVER['PHP_SELF']);
$current_settings_clean = preg_replace('/\.php$/', '', $current_settings_page);
?>

<style>
.saas-settings-nav {
    display: flex;
    align-items: center;
    gap: 4px;
    background: var(--bg-surface, #ffffff);
    border: 1px solid var(--border-light, rgba(0,0,0,0.08));
    border-radius: var(--radius-xl, 12px);
    padding: 5px;
    margin-bottom: 28px;
    overflow-x: auto;
    box-shadow: var(--shadow-xs, 0 1px 2px rgba(0,0,0,0.04));
    -webkit-overflow-scrolling: touch;
}
.saas-settings-nav::-webkit-scrollbar {
    display: none;
}
.saas-settings-tab {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    border-radius: var(--radius-lg, 8px);
    color: var(--text-secondary, #6b7280);
    font-size: 13.5px;
    font-weight: 500;
    text-decoration: none;
    white-space: nowrap;
    transition: all 180ms cubic-bezier(0.16, 1, 0.3, 1);
    border: 1px solid transparent;
}
.saas-settings-tab:hover {
    color: var(--text-primary, #111827);
    background: var(--bg-hover, #f3f4f6);
}
.saas-settings-tab.active {
    color: var(--primary, #4f46e5);
    background: var(--primary-soft, rgba(79, 70, 229, 0.08));
    font-weight: 600;
    border-color: rgba(79, 70, 229, 0.18);
    box-shadow: 0 1px 2px rgba(79, 70, 229, 0.06);
}
.saas-settings-tab i {
    font-size: 14px;
    opacity: 0.85;
}
.saas-settings-tab.active i {
    opacity: 1;
    color: var(--primary, #4f46e5);
}
</style>

<nav class="saas-settings-nav" aria-label="Settings Navigation">
    <?php foreach ($settings_nav_items as $item): ?>
        <a href="<?= $base_path ?>settings/<?= $item['url'] ?>" 
           class="saas-settings-tab <?= $current_settings_clean === $item['url'] ? 'active' : '' ?>"
           <?= $current_settings_clean === $item['url'] ? 'aria-current="page"' : '' ?>>
            <i class="fas <?= $item['icon'] ?>" aria-hidden="true"></i>
            <span><?= $item['label'] ?></span>
        </a>
    <?php endforeach; ?>
</nav>
