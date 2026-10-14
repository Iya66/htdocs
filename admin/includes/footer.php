<?php
/**
 * Professional Admin Footer
 * Works with header-pro.php and theme-professional.css
 */
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
 toggle.setAttribute('aria-expanded', !isActive);
 }

 document.body.style.overflow = isActive ? '' : 'hidden';
 }

 // ═══════════════════════════════════════════════════════════════
 // Theme Toggle
 // ═══════════════════════════════════════════════════════════════

 function toggleTheme() {
 const html = document.documentElement;
 const currentTheme = html.getAttribute('data-theme');
 const newTheme = currentTheme === 'light' ? 'dark' : 'light';
 html.setAttribute('data-theme', newTheme);
 localStorage.setItem('theme', newTheme);

 // Update icon
 const toggle = document.getElementById('themeToggle');
 if (toggle) {
 const moonIcon = toggle.querySelector('.theme-icon-moon');
 const sunIcon = toggle.querySelector('.theme-icon-sun');
 if (newTheme === 'dark') {
 if (moonIcon) moonIcon.style.display = 'inline';
 if (sunIcon) sunIcon.style.display = 'none';
 } else {
 if (moonIcon) moonIcon.style.display = 'none';
 if (sunIcon) sunIcon.style.display = 'inline';
 }
 }
 }

 // Load saved theme
 (function() {
 const savedTheme = localStorage.getItem('theme') || 'light';
 document.documentElement.setAttribute('data-theme', savedTheme);
 })();

 // ═══════════════════════════════════════════════════════════════
 // Keyboard Shortcuts
 // ═══════════════════════════════════════════════════════════════

 document.addEventListener('keydown', function(e) {
 // Cmd/Ctrl + K for search
 if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
 e.preventDefault();
 const search = document.getElementById('searchInput');
 if (search) search.focus();
 }

 // Escape to close sidebar
 if (e.key === 'Escape') {
 const sidebar = document.getElementById('sidebar');
 if (sidebar && sidebar.classList.contains('active')) {
 toggleSidebar();
 }
 }
 });

 // ═══════════════════════════════════════════════════════════════
 // Notifications
 // ═══════════════════════════════════════════════════════════════

 function toggleNotifications() {
 // TODO: Implement notification dropdown
 console.log('Notifications panel');
 }

 // ═══════════════════════════════════════════════════════════════
 // Click outside to close sidebar (mobile)
 // ═══════════════════════════════════════════════════════════════

 document.addEventListener('click', function(e) {
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
 });

 // ═══════════════════════════════════════════════════════════════
 // Auto-hide alerts
 // ═══════════════════════════════════════════════════════════════

 document.querySelectorAll('.alert[data-auto-hide]').forEach(function(alert) {
 setTimeout(function() {
 alert.style.opacity = '0';
 alert.style.transform = 'translateY(-10px)';
 setTimeout(function() {
 alert.remove();
 }, 200);
 }, 5000);
 });

 // ═══════════════════════════════════════════════════════════════
 // Confirm delete actions
 // ═══════════════════════════════════════════════════════════════

 document.querySelectorAll('[data-confirm]').forEach(function(el) {
 el.addEventListener('click', function(e) {
 const message = el.getAttribute('data-confirm') || 'Are you sure?';
 if (!confirm(message)) {
 e.preventDefault();
 e.stopPropagation();
 }
 });
 });

 // ═══════════════════════════════════════════════════════════════
 // Dropdown toggle
 // ═══════════════════════════════════════════════════════════════

 document.querySelectorAll('.dropdown').forEach(function(dropdown) {
 const trigger = dropdown.querySelector('.dropdown-trigger');
 if (trigger) {
 trigger.addEventListener('click', function(e) {
 e.stopPropagation();
 dropdown.classList.toggle('active');
 });
 }
 });

 // Close dropdowns on click outside
 document.addEventListener('click', function() {
 document.querySelectorAll('.dropdown.active').forEach(function(dropdown) {
 dropdown.classList.remove('active');
 });
 });

 // ═══════════════════════════════════════════════════════════════
 // Tab switching
 // ═══════════════════════════════════════════════════════════════

 document.querySelectorAll('.tabs').forEach(function(tabs) {
 tabs.addEventListener('click', function(e) {
 if (e.target.classList.contains('tab')) {
 // Remove active from all tabs in this group
 tabs.querySelectorAll('.tab').forEach(function(t) {
 t.classList.remove('active');
 });

 // Add active to clicked tab
 e.target.classList.add('active');

 // Get target content
 const target = e.target.getAttribute('data-tab');
 if (target) {
 // Hide all tab content in this container
 const container = tabs.closest('.card, .page-content');
 if (container) {
 container.querySelectorAll('.tab-content').forEach(function(content) {
 content.classList.remove('active');
 });

 // Show target content
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
 // Table row click
 // ═══════════════════════════════════════════════════════════════

 document.querySelectorAll('tr[data-href]').forEach(function(row) {
 row.style.cursor = 'pointer';
 row.addEventListener('click', function(e) {
 // Don't navigate if clicking on a button or link
 if (!e.target.closest('a, button')) {
 window.location.href = row.getAttribute('data-href');
 }
 });
 });

 // ═══════════════════════════════════════════════════════════════
 // Form validation visual feedback
 // ═══════════════════════════════════════════════════════════════

 document.querySelectorAll('form[data-validate]').forEach(function(form) {
 form.addEventListener('submit', function(e) {
 let isValid = true;
 form.querySelectorAll('.form-input[required], .form-textarea[required]').forEach(function(input) {
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

 // Clear error on input
 form.querySelectorAll('.form-input, .form-textarea').forEach(function(input) {
 input.addEventListener('input', function() {
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
 // Loading states for buttons
 // ═══════════════════════════════════════════════════════════════

 document.querySelectorAll('.btn[data-loading]').forEach(function(btn) {
 btn.addEventListener('click', function() {
 const originalText = btn.innerHTML;
 const loadingText = btn.getAttribute('data-loading') || '<i class="fas fa-spinner fa-spin"></i>';
 btn.innerHTML = loadingText;
 btn.disabled = true;

 // Reset after 3 seconds (adjust as needed)
 setTimeout(function() {
 btn.innerHTML = originalText;
 btn.disabled = false;
 }, 3000);
 });
 });

 // ═══════════════════════════════════════════════════════════════
 // Smooth reveal on load
 // ═══════════════════════════════════════════════════════════════

 document.addEventListener('DOMContentLoaded', function() {
 // Trigger body load
 setTimeout(function() {
 document.body.classList.add('loaded');
 }, 50);
 });
 </script>

 </body>
 </html>
