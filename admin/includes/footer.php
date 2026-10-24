<?php
/**
 * Professional Admin Footer
 * Works with includes/header.php and css/theme-professional.css
 */

// Relative path back to /admin, computed by the header. Falls back to the
// current directory so a footer included without the header still renders.
if (!isset($base_path) || !is_string($base_path)) {
    $base_path = '';
}
$api_search_url = $base_path . 'api/search.php';
$api_notif_url  = $base_path . 'api/notifications.php';
?>
 </main>
 </div>

 <script>
 // ═══════════════════════════════════════════════════════════════
 // Sidebar Toggle
 // ═══════════════════════════════════════════════════════════════

 function toggleSidebar() {
 const sidebar = document.getElementById('sidebar');
 const overlay = document.getElementById('sidebarOverlay');
 const toggle = document.getElementById('menuToggle');

 if (!sidebar) return;

 const isActive = sidebar.classList.contains('active');
 sidebar.classList.toggle('active');

 if (overlay) {
 overlay.classList.toggle('active');
 }

 if (toggle) {
 const icon = toggle.querySelector('i');
 if (icon) {
 icon.className = isActive ? 'fas fa-bars' : 'fas fa-times';
 }
 toggle.setAttribute('aria-expanded', String(!isActive));
 }

 // Lock the page behind the open drawer, but never trap the desktop layout.
 document.body.style.overflow = isActive ? '' : 'hidden';
 }

 // ═══════════════════════════════════════════════════════════════
 // Theme Toggle
 // ═══════════════════════════════════════════════════════════════
 // The initial theme is applied in <head> to avoid a flash; this only
 // handles the user switching it. The header is the single source of truth
 // for which icon shows in which state.

 function syncThemeIcons(theme) {
 document.querySelectorAll('.theme-icon-moon').forEach(function (el) {
 el.style.display = theme === 'dark' ? 'none' : 'inline-flex';
 });
 document.querySelectorAll('.theme-icon-sun').forEach(function (el) {
 el.style.display = theme === 'dark' ? 'inline-flex' : 'none';
 });
 }

 function toggleTheme() {
 const html = document.documentElement;
 const currentTheme = html.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
 const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

 html.setAttribute('data-theme', newTheme);
 try {
 localStorage.setItem('theme', newTheme);
 } catch (e) {}

 syncThemeIcons(newTheme);
 }

 // Set the correct icon for whatever theme the head script resolved.
 (function () {
 syncThemeIcons(document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light');
 })();

 // ═══════════════════════════════════════════════════════════════
 // Mobile Search
 // ═══════════════════════════════════════════════════════════════

 function toggleMobileSearch() {
 const box = document.getElementById('searchBox');
 if (!box) return;

 const isOpen = box.classList.contains('search-open');
 box.classList.toggle('search-open', !isOpen);

 if (!isOpen) {
 const input = document.getElementById('searchInput');
 if (input) input.focus();
 }
 }

 // ═══════════════════════════════════════════════════════════════
 // Search  (admin/api/search.php)
 // ═══════════════════════════════════════════════════════════════
 // The endpoint already matched both admin pages and leads; it was simply
 // never called. Arrow keys move the highlight, Enter opens, Esc closes.

 var searchState = {
 timer: null,
 items: [],
 activeIndex: -1,
 open: false
 };

 function searchBaseUrl() {
 return <?= json_encode($api_search_url) ?>;
 }

 function notifBaseUrl() {
 return <?= json_encode($api_notif_url) ?>;
 }

 function escapeHtml(value) {
 return String(value === null || value === undefined ? '' : value)
 .replace(/&/g, '&amp;')
 .replace(/</g, '&lt;')
 .replace(/>/g, '&gt;')
 .replace(/"/g, '&quot;')
 .replace(/'/g, '&#039;');
 }

 function closeSearch() {
 const results = document.getElementById('searchResults');
 const input = document.getElementById('searchInput');

 if (results) results.classList.remove('active');
 if (input) input.setAttribute('aria-expanded', 'false');

 searchState.open = false;
 searchState.activeIndex = -1;
 searchState.items = [];
 }

 function renderSearch(payload) {
 const results = document.getElementById('searchResults');
 if (!results) return;

 searchState.items = [];
 searchState.activeIndex = -1;

 var pages = (payload && payload.pages) || [];
 var leads = (payload && payload.leads) || [];
 var html = '';
 var basePath = <?= json_encode($base_path) ?>;

 if (pages.length) {
 html += '<div class="search-group">Pages</div>';
 pages.forEach(function (page) {
 var index = searchState.items.length;
 searchState.items.push({ url: basePath + page.url, label: page.title });

 html += '<a class="search-item" role="option" href="' + escapeHtml(basePath + page.url) + '" data-index="' + index + '">'
 + '<i class="fas ' + escapeHtml(page.icon || 'fa-file') + '"></i>'
 + '<span class="search-item-body">'
 + '<span class="search-item-title">' + escapeHtml(page.title) + '</span>'
 + (page.desc ? '<span class="search-item-desc">' + escapeHtml(page.desc) + '</span>' : '')
 + '</span></a>';
 });
 }

 if (leads.length) {
 html += '<div class="search-group">Leads</div>';
 leads.forEach(function (lead) {
 var index = searchState.items.length;
 searchState.items.push({ url: basePath + 'leads?highlight=' + encodeURIComponent(lead.id), label: lead.name });

 html += '<a class="search-item" role="option" href="' + escapeHtml(basePath + 'leads?highlight=' + encodeURIComponent(lead.id)) + '" data-index="' + index + '">'
 + '<i class="fas fa-user"></i>'
 + '<span class="search-item-body">'
 + '<span class="search-item-title">' + escapeHtml(lead.name) + '</span>'
 + '<span class="search-item-desc">' + escapeHtml(lead.status_label || '') + (lead.email ? ' · ' + escapeHtml(lead.email) : '') + '</span>'
 + '</span></a>';
 });
 }

 if (!html) {
 html = '<div class="search-empty">No matches</div>';
 }

 results.innerHTML = html;
 results.classList.add('active');
 searchState.open = true;

 var input = document.getElementById('searchInput');
 if (input) input.setAttribute('aria-expanded', 'true');
 }

 function highlightSearchItem(direction) {
 var items = document.querySelectorAll('#searchResults .search-item');
 if (!items.length) return;

 if (searchState.activeIndex >= 0) {
 items[searchState.activeIndex].classList.remove('active');
 }

 if (direction > 0) {
 searchState.activeIndex = searchState.activeIndex + 1;
 } else {
 searchState.activeIndex = searchState.activeIndex - 1;
 }

 if (searchState.activeIndex >= items.length) searchState.activeIndex = 0;
 if (searchState.activeIndex < 0) searchState.activeIndex = items.length - 1;

 var active = items[searchState.activeIndex];
 active.classList.add('active');
 active.scrollIntoView({ block: 'nearest' });
 }

 function runSearch(query) {
 var results = document.getElementById('searchResults');
 if (!results) return;

 if (query.trim() === '') {
 closeSearch();
 return;
 }

 fetch(searchBaseUrl() + '?q=' + encodeURIComponent(query), {
 headers: { 'X-Requested-With': 'XMLHttpRequest' }
 })
 .then(function (response) { return response.ok ? response.json() : null; })
 .then(function (payload) { renderSearch(payload || { pages: [], leads: [] }); })
 .catch(function () {
 results.innerHTML = '<div class="search-empty">Search unavailable</div>';
 results.classList.add('active');
 });
 }

 // ═══════════════════════════════════════════════════════════════
 // Notifications  (admin/api/notifications.php)
 // ═══════════════════════════════════════════════════════════════

 var notifState = { loaded: false, busy: false };

 function notifCountEl() {
 return document.getElementById('notifCount');
 }

 function setNotifCount(count) {
 var badge = notifCountEl();
 if (!badge) return;

 count = parseInt(count, 10) || 0;
 badge.textContent = count > 99 ? '99+' : String(count);
 badge.hidden = count === 0;
 }

 function toggleNotifications(event) {
 const dropdown = document.getElementById('notifDropdown');
 const btn = document.getElementById('notifBtn');
 if (!dropdown) return;

 if (event && typeof event.stopPropagation === 'function') {
 event.stopPropagation();
 }

 const willOpen = !dropdown.classList.contains('active');
 dropdown.classList.toggle('active', willOpen);

 if (btn) btn.setAttribute('aria-expanded', String(willOpen));

 if (willOpen && !notifState.loaded) {
 loadNotifications();
 }
 }

 function renderNotifications(payload) {
 const list = document.getElementById('notifList');
 if (!list) return;

 const items = (payload && payload.notifications) || [];

 if (!items.length) {
 list.innerHTML = '<div class="notif-empty">Nothing yet</div>';
 return;
 }

 var basePath = <?= json_encode($base_path) ?>;
 var html = '';

 items.forEach(function (item) {
 var target = item.link || '';
 // Links arrive as "leads.php" style paths relative to /admin.
 if (target) target = basePath + target.replace(/\.php$/, '');

 html += '<a class="notif-item' + (item.is_read ? '' : ' unread') + '" href="' + escapeHtml(target) + '" data-notif-id="' + escapeHtml(item.id) + '">'
 + '<i class="fas ' + escapeHtml(item.icon || 'fa-bell') + '"></i>'
 + '<span class="notif-item-body">'
 + '<span class="notif-item-title">' + escapeHtml(item.title) + '</span>'
 + '<span class="notif-item-text">' + escapeHtml(item.details) + '</span>'
 + '<span class="notif-item-time">' + escapeHtml(item.time_ago || '') + '</span>'
 + '</span></a>';
 });

 list.innerHTML = html;
 }

 function loadNotifications() {
 const list = document.getElementById('notifList');
 if (list) list.innerHTML = '<div class="notif-empty">Loading…</div>';

 return fetch(notifBaseUrl(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
 .then(function (response) { return response.ok ? response.json() : null; })
 .then(function (payload) {
 notifState.loaded = true;
 renderNotifications(payload);
 if (payload) setNotifCount(payload.unread_count);
 })
 .catch(function () {
 if (list) list.innerHTML = '<div class="notif-empty">Could not load</div>';
 });
 }

 function markNotificationRead(id) {
 if (!id || notifState.busy) return Promise.resolve();
 notifState.busy = true;

 return fetch(notifBaseUrl() + '?action=read_one&id=' + encodeURIComponent(id), {
 headers: { 'X-Requested-With': 'XMLHttpRequest' }
 })
 .then(function () { return loadNotifications(); })
 .catch(function () {})
 .then(function () { notifState.busy = false; });
 }

 function markAllNotificationsRead() {
 if (notifState.busy) return;
 notifState.busy = true;

 return fetch(notifBaseUrl() + '?action=read_all', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
 .then(function () { return loadNotifications(); })
 .catch(function () {})
 .then(function () { notifState.busy = false; });
 }

 // ═══════════════════════════════════════════════════════════════
 // Global listeners
 // ═══════════════════════════════════════════════════════════════

 document.addEventListener('keydown', function (e) {
 // Cmd/Ctrl + K focuses search.
 if ((e.metaKey || e.ctrlKey) && (e.key === 'k' || e.key === 'K')) {
 e.preventDefault();
 const search = document.getElementById('searchInput');
 if (search) {
 search.focus();
 search.select();
 }
 return;
 }

 const searchOpen = searchState.open;

 if (e.key === 'Escape') {
 if (searchOpen) {
 closeSearch();
 return;
 }
 const sidebar = document.getElementById('sidebar');
 if (sidebar && sidebar.classList.contains('active')) {
 toggleSidebar();
 return;
 }
 const dropdown = document.getElementById('notifDropdown');
 if (dropdown) {
 dropdown.classList.remove('active');
 const btn = document.getElementById('notifBtn');
 if (btn) btn.setAttribute('aria-expanded', 'false');
 }
 return;
 }

 if (searchOpen) {
 if (e.key === 'ArrowDown') {
 e.preventDefault();
 highlightSearchItem(1);
 } else if (e.key === 'ArrowUp') {
 e.preventDefault();
 highlightSearchItem(-1);
 } else if (e.key === 'Enter') {
 const items = document.querySelectorAll('#searchResults .search-item');
 if (searchState.activeIndex >= 0 && items[searchState.activeIndex]) {
 e.preventDefault();
 window.location.href = items[searchState.activeIndex].getAttribute('href');
 }
 }
 }
 });

 // Search input wiring.
 document.addEventListener('DOMContentLoaded', function () {
 const input = document.getElementById('searchInput');
 if (input) {
 input.addEventListener('input', function () {
 clearTimeout(searchState.timer);
 const value = input.value;
 searchState.timer = setTimeout(function () { runSearch(value); }, 200);
 });

 input.addEventListener('focus', function () {
 if (input.value.trim() !== '') runSearch(input.value);
 });
 }
 });

 // Click-outside closes search and the notification dropdown.
 document.addEventListener('click', function (e) {
 const sidebar = document.getElementById('sidebar');
 const overlay = document.getElementById('sidebarOverlay');
 const toggle = document.getElementById('menuToggle');

 if (window.innerWidth <= 1024 &&
 sidebar &&
 overlay &&
 sidebar.classList.contains('active') &&
 !sidebar.contains(e.target) &&
 (!toggle || !toggle.contains(e.target))) {
 toggleSidebar();
 }

 if (searchState.open) {
 const box = document.getElementById('searchBox');
 if (box && !box.contains(e.target)) {
 closeSearch();
 }
 }

 const dropdown = document.getElementById('notifDropdown');
 if (dropdown && dropdown.classList.contains('active')) {
 const btn = document.getElementById('notifBtn');
 if (!dropdown.contains(e.target)) {
 dropdown.classList.remove('active');
 if (btn) btn.setAttribute('aria-expanded', 'false');
 }
 }
 });

 // Clicking a notification marks it read and navigates.
 document.addEventListener('click', function (e) {
 const item = e.target.closest ? e.target.closest('[data-notif-id]') : null;
 if (!item) return;

 const id = item.getAttribute('data-notif-id');
 if (!id) return;

 e.preventDefault();
 markNotificationRead(id).then(function () {
 window.location.href = item.getAttribute('href');
 });
 });

 // Poll the unread count so the badge stays honest without a reload.
 (function () {
 if (document.getElementById('notifCount')) {
 loadNotifications();
 setInterval(function () {
 const dropdown = document.getElementById('notifDropdown');
 // Refresh the list only while it is open; otherwise just the count.
 if (dropdown && dropdown.classList.contains('active')) {
 loadNotifications();
 } else {
 fetch(notifBaseUrl(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
 .then(function (r) { return r.ok ? r.json() : null; })
 .then(function (payload) { if (payload) setNotifCount(payload.unread_count); })
 .catch(function () {});
 }
 }, 60000);
 }
 })();

 // ═══════════════════════════════════════════════════════════════
 // Auto-hide alerts
 // ═══════════════════════════════════════════════════════════════

 document.querySelectorAll('.alert[data-auto-hide]').forEach(function (alert) {
 setTimeout(function () {
 alert.style.opacity = '0';
 alert.style.transform = 'translateY(-10px)';
 setTimeout(function () {
 alert.remove();
 }, 200);
 }, 5000);
 });

 // ═══════════════════════════════════════════════════════════════
 // Confirm destructive actions
 // ═══════════════════════════════════════════════════════════════

 document.querySelectorAll('[data-confirm]').forEach(function (el) {
 el.addEventListener('click', function (e) {
 const message = el.getAttribute('data-confirm') || 'Are you sure?';
 if (!confirm(message)) {
 e.preventDefault();
 e.stopPropagation();
 }
 });
 });

 // ═══════════════════════════════════════════════════════════════
 // Dropdowns
 // ═══════════════════════════════════════════════════════════════

 document.querySelectorAll('.dropdown').forEach(function (dropdown) {
 const trigger = dropdown.querySelector('.dropdown-trigger');
 if (trigger) {
 trigger.addEventListener('click', function (e) {
 e.stopPropagation();
 dropdown.classList.toggle('active');
 });
 }
 });

 // ═══════════════════════════════════════════════════════════════
 // Tabs
 // ═══════════════════════════════════════════════════════════════

 document.querySelectorAll('.tabs').forEach(function (tabs) {
 tabs.addEventListener('click', function (e) {
 if (e.target.classList.contains('tab')) {
 tabs.querySelectorAll('.tab').forEach(function (t) {
 t.classList.remove('active');
 });

 e.target.classList.add('active');

 const target = e.target.getAttribute('data-tab');
 if (target) {
 const container = tabs.closest('.card, .page-content');
 if (container) {
 container.querySelectorAll('.tab-content').forEach(function (content) {
 content.classList.remove('active');
 });

 const targetContent = container.querySelector('#' + target);
 if (targetContent) {
 targetContent.classList.add('active');
 }
 }
 }
 }
 });
 });

 // ═══════════════════════════════════════════════════════════════
 // Clickable table rows
 // ═══════════════════════════════════════════════════════════════

 document.querySelectorAll('tr[data-href]').forEach(function (row) {
 row.style.cursor = 'pointer';
 row.addEventListener('click', function (e) {
 if (!e.target.closest('a, button')) {
 window.location.href = row.getAttribute('data-href');
 }
 });
 });

 // ═══════════════════════════════════════════════════════════════
 // Form validation
 // ═══════════════════════════════════════════════════════════════

 document.querySelectorAll('form[data-validate]').forEach(function (form) {
 form.addEventListener('submit', function (e) {
 let isValid = true;
 form.querySelectorAll('.form-input[required], .form-textarea[required]').forEach(function (input) {
 if (!input.value.trim()) {
 isValid = false;
 input.classList.add('error');
 const formGroup = input.closest('.form-group');
 if (formGroup) {
 let error = formGroup.querySelector('.form-error');
 if (!error) {
 error = document.createElement('div');
 error.className = 'form-error';
 error.innerHTML = '<i class="fas fa-exclamation-circle"></i> This field is required';
 formGroup.appendChild(error);
 }
 }
 }
 });

 if (!isValid) {
 e.preventDefault();
 }
 });

 form.querySelectorAll('.form-input, .form-textarea').forEach(function (input) {
 input.addEventListener('input', function () {
 this.classList.remove('error');
 const formGroup = this.closest('.form-group');
 if (formGroup) {
 const error = formGroup.querySelector('.form-error');
 if (error) error.remove();
 }
 });
 });
 });

 // ═══════════════════════════════════════════════════════════════
 // Button loading states
 // ═══════════════════════════════════════════════════════════════

 document.querySelectorAll('.btn[data-loading]').forEach(function (btn) {
 btn.addEventListener('click', function () {
 const originalText = btn.innerHTML;
 const loadingText = btn.getAttribute('data-loading') || '<i class="fas fa-spinner fa-spin"></i>';
 btn.innerHTML = loadingText;
 btn.disabled = true;

 setTimeout(function () {
 btn.innerHTML = originalText;
 btn.disabled = false;
 }, 3000);
 });
 });
 </script>

 </body>
 </html>
