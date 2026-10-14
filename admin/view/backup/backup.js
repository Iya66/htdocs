/**
 * Backup Manager JS - OTP, Generate, Download, Delete, Restore
 */
const BK = {
    csrf: '',
    controllerUrl: '',
    cooldown: 0,
    cooldownTimer: null,

    init(csrf, url) {
        this.csrf = csrf;
        this.controllerUrl = url;
        this.loadBackups();
        this.setupOtpInputs();
    },

    // ─── API CALL ────────────────────────────────────────
    async api(action, data = {}, isFormData = false) {
        let body;
        if (isFormData) {
            body = data;
            body.append('action', action);
            body.append('csrf_token', this.csrf);
        } else {
            body = new URLSearchParams({ action, csrf_token: this.csrf, ...data });
        }
        const resp = await fetch(this.controllerUrl, { method: 'POST', body });
        return resp.json();
    },

    // ─── NOTIFICATIONS ──────────────────────────────────
    notify(msg, type = 'success') {
        let box = document.getElementById('bk-notify');
        if (!box) {
            box = document.createElement('div');
            box.id = 'bk-notify';
            box.style.cssText = 'position:fixed;top:24px;right:24px;z-index:9999;display:flex;flex-direction:column;gap:10px;';
            document.body.appendChild(box);
        }
        const el = document.createElement('div');
        const icon = type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle';
        const bg = type === 'success' ? 'var(--success)' : type === 'error' ? 'var(--danger)' : 'var(--info)';
        el.style.cssText = `background:${bg};color:#fff;padding:14px 20px;border-radius:12px;font-size:14px;font-weight:500;display:flex;align-items:center;gap:10px;box-shadow:0 8px 24px rgba(0,0,0,.2);animation:slideUp .3s ease;min-width:280px;`;
        el.innerHTML = `<i class="fas fa-${icon}"></i> ${msg}`;
        box.appendChild(el);
        setTimeout(() => { el.style.opacity = '0'; el.style.transform = 'translateX(100px)'; el.style.transition = '.3s'; setTimeout(() => el.remove(), 300); }, 4000);
    },

    // ─── MODAL MANAGEMENT ────────────────────────────────
    openModal(id) { document.getElementById(id).classList.add('active'); },
    closeModal(id) { document.getElementById(id).classList.remove('active'); },

    // ─── STEP 1: OPEN GENERATE FLOW ──────────────────────
    openGenerateModal() {
        document.getElementById('gen-step-1').style.display = 'block';
        document.getElementById('gen-step-2').style.display = 'none';
        document.getElementById('gen-step-3').style.display = 'none';
        this.updateSteps(1);
        this.openModal('generateModal');
    },

    updateSteps(n) {
        document.querySelectorAll('#generateModal .step-dot').forEach((d, i) => {
            d.classList.remove('active', 'done');
            if (i < n - 1) d.classList.add('done');
            if (i === n - 1) d.classList.add('active');
        });
    },

    // ─── SEND OTP ────────────────────────────────────────
    async sendOtp() {
        const btn = document.getElementById('sendOtpBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
        try {
            const r = await this.api('send_otp');
            if (r.success) {
                this.notify('OTP sent to your email!');
                document.getElementById('gen-step-1').style.display = 'none';
                document.getElementById('gen-step-2').style.display = 'block';
                this.updateSteps(2);
                this.startCooldown();
                document.querySelector('.otp-inputs input')?.focus();
            } else {
                this.notify(r.message, 'error');
            }
        } catch (e) { this.notify('Network error', 'error'); }
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-paper-plane"></i> Send OTP';
    },

    startCooldown() {
        this.cooldown = 60;
        const el = document.getElementById('resendTimer');
        const btn = document.getElementById('resendBtn');
        if (btn) btn.disabled = true;
        clearInterval(this.cooldownTimer);
        this.cooldownTimer = setInterval(() => {
            this.cooldown--;
            if (el) el.textContent = this.cooldown + 's';
            if (this.cooldown <= 0) {
                clearInterval(this.cooldownTimer);
                if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-redo"></i> Resend OTP'; }
                if (el) el.textContent = '';
            }
        }, 1000);
    },

    async resendOtp() {
        if (this.cooldown > 0) return;
        const btn = document.getElementById('resendBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        try {
            const r = await this.api('send_otp');
            if (r.success) { this.notify('New OTP sent!'); this.startCooldown(); }
            else this.notify(r.message, 'error');
        } catch (e) { this.notify('Network error', 'error'); }
        btn.innerHTML = '<i class="fas fa-redo"></i> Resend OTP';
    },

    // ─── OTP INPUT HANDLING ──────────────────────────────
    setupOtpInputs() {
        document.querySelectorAll('.otp-inputs input').forEach((inp, i, all) => {
            inp.addEventListener('input', () => {
                inp.value = inp.value.replace(/\D/g, '').slice(0, 1);
                if (inp.value && i < all.length - 1) all[i + 1].focus();
                if (i === all.length - 1 && inp.value) this.verifyOtp();
            });
            inp.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && !inp.value && i > 0) all[i - 1].focus();
            });
            inp.addEventListener('paste', (e) => {
                e.preventDefault();
                const text = (e.clipboardData.getData('text') || '').replace(/\D/g, '').slice(0, 6);
                text.split('').forEach((ch, j) => { if (all[j]) all[j].value = ch; });
                if (text.length === 6) this.verifyOtp();
            });
        });
    },

    async verifyOtp() {
        const inputs = document.querySelectorAll('.otp-inputs input');
        const otp = Array.from(inputs).map(i => i.value).join('');
        if (otp.length !== 6) return;

        const btn = document.getElementById('verifyOtpBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verifying...';
        try {
            const r = await this.api('verify_otp', { otp });
            if (r.success) {
                this.notify('OTP verified!');
                document.getElementById('gen-step-2').style.display = 'none';
                document.getElementById('gen-step-3').style.display = 'block';
                this.updateSteps(3);
            } else {
                this.notify(r.message, 'error');
                inputs.forEach(i => i.value = '');
                inputs[0]?.focus();
            }
        } catch (e) { this.notify('Network error', 'error'); }
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check"></i> Verify OTP';
    },

    // ─── GENERATE BACKUP ─────────────────────────────────
    async generateBackup() {
        const type = document.querySelector('input[name="backup_type"]:checked')?.value || 'full';
        const name = document.getElementById('customName')?.value || 'backup';
        const btn = document.getElementById('generateBtn');
        const progress = document.getElementById('genProgress');

        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
        if (progress) { progress.style.display = 'block'; this.animateProgress(); }

        try {
            const r = await this.api('generate', { backup_type: type, custom_name: name });
            if (r.success) {
                this.notify(r.message);
                this.closeModal('generateModal');
                this.loadBackups();
                // Auto-download
                if (r.filename) {
                    setTimeout(() => { window.location.href = this.controllerUrl + '?action=download&file=' + encodeURIComponent(r.filename); }, 500);
                }
            } else {
                this.notify(r.message, 'error');
            }
        } catch (e) { this.notify('Backup generation failed', 'error'); }

        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-download"></i> Generate Backup';
        if (progress) progress.style.display = 'none';
    },

    animateProgress() {
        const fill = document.querySelector('#genProgress .fill');
        if (!fill) return;
        fill.style.width = '0%';
        let w = 0;
        const iv = setInterval(() => {
            w += Math.random() * 15;
            if (w > 90) w = 90;
            fill.style.width = w + '%';
            if (w >= 90) clearInterval(iv);
        }, 400);
    },

    // ─── LOAD BACKUPS ────────────────────────────────────
    async loadBackups() {
        try {
            const r = await fetch(this.controllerUrl + '?action=list');
            const data = await r.json();
            if (data.success) this.renderBackups(data.backups);
        } catch (e) { console.error('Failed to load backups'); }
    },

    renderBackups(backups) {
        const tbody = document.getElementById('backupList');
        const countEl = document.getElementById('backupCount');
        const sizeEl = document.getElementById('totalSize');
        if (!tbody) return;

        if (countEl) countEl.textContent = backups.length;
        let totalSize = 0;
        backups.forEach(b => totalSize += b.size);
        if (sizeEl) sizeEl.textContent = this.formatBytes(totalSize);

        if (!backups.length) {
            tbody.innerHTML = '<tr><td colspan="5"><div class="empty-state"><i class="fas fa-box-open"></i><p>No backups found. Generate your first backup!</p></div></td></tr>';
            return;
        }

        tbody.innerHTML = backups.map(b => `
            <tr>
                <td><div class="file-icon"><i class="fas fa-file-archive"></i><div><div class="fname">${this.esc(b.filename)}</div><div class="fmeta">${b.created_ago}</div></div></div></td>
                <td><span class="type-badge ${b.type}">${b.type}</span></td>
                <td>${b.size_formatted}</td>
                <td>${b.created}</td>
                <td><div class="td-actions">
                    <a href="${this.controllerUrl}?action=download&file=${encodeURIComponent(b.filename)}" class="btn btn-sm btn-primary" title="Download"><i class="fas fa-download"></i></a>
                    <button class="btn btn-sm btn-edit" onclick="BK.confirmRestore('${this.esc(b.filename)}')" title="Restore"><i class="fas fa-undo"></i></button>
                    <button class="btn btn-sm btn-delete" onclick="BK.confirmDelete('${this.esc(b.filename)}')" title="Delete"><i class="fas fa-trash"></i></button>
                </div></td>
            </tr>
        `).join('');
    },

    // ─── DELETE ──────────────────────────────────────────
    confirmDelete(file) {
        document.getElementById('deleteFileName').textContent = file;
        document.getElementById('deleteFileInput').value = file;
        this.openModal('deleteModal');
    },

    async deleteBackup() {
        const file = document.getElementById('deleteFileInput').value;
        const btn = document.getElementById('deleteBtn');
        btn.disabled = true;
        try {
            const r = await this.api('delete', { file });
            if (r.success) { this.notify('Backup deleted'); this.loadBackups(); }
            else this.notify(r.message, 'error');
        } catch (e) { this.notify('Delete failed', 'error'); }
        btn.disabled = false;
        this.closeModal('deleteModal');
    },

    // ─── RESTORE FROM LIST ───────────────────────────────
    confirmRestore(file) {
        document.getElementById('restoreFileName').textContent = file;
        document.getElementById('restoreFileInput').value = file;
        this.openModal('restoreModal');
    },

    async restoreBackup() {
        const file = document.getElementById('restoreFileInput').value;
        const btn = document.getElementById('restoreBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Restoring...';
        try {
            const r = await this.api('restore', { file });
            if (r.success) { this.notify(r.message); }
            else this.notify(r.message, 'error');
        } catch (e) { this.notify('Restore failed', 'error'); }
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-undo"></i> Restore Now';
        this.closeModal('restoreModal');
    },

    // ─── UPLOAD & RESTORE ────────────────────────────────
    openUploadModal() { this.openModal('uploadModal'); },

    async uploadRestore() {
        const fileInput = document.getElementById('uploadFile');
        if (!fileInput.files.length) { this.notify('Select a file first', 'error'); return; }

        const fd = new FormData();
        fd.append('backup_file', fileInput.files[0]);
        const btn = document.getElementById('uploadBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';

        try {
            const r = await this.api('upload_restore', fd, true);
            if (r.success) { this.notify(r.message); this.loadBackups(); }
            else this.notify(r.message, 'error');
        } catch (e) { this.notify('Upload failed', 'error'); }
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-upload"></i> Upload & Restore';
        this.closeModal('uploadModal');
    },

    // ─── HELPERS ─────────────────────────────────────────
    esc(s) { const d = document.createElement('div'); d.textContent = s; return d.innerHTML; },
    formatBytes(b) { const u = ['B','KB','MB','GB']; let i = 0; while (b >= 1024 && i < u.length-1) { b /= 1024; i++; } return b.toFixed(1) + ' ' + u[i]; }
};
