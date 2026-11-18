/**
 * Maintenance Request System — Custom JavaScript
 */
document.addEventListener('DOMContentLoaded', function () {

    /* ── Theme Switcher (Light / Dark Mode) ───────────────────── */
    initThemeSwitcher();

    /* ── Sidebar Toggle (Mobile) ─────────────────────────────── */
    const sidebar      = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarOverlay = document.getElementById('sidebarOverlay');

    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function (e) {
            e.preventDefault();
            sidebar.classList.toggle('show');
            if (sidebarOverlay) sidebarOverlay.classList.toggle('show');
        });
    }

    if (sidebarOverlay && sidebar) {
        sidebarOverlay.addEventListener('click', function () {
            sidebar.classList.remove('show');
            sidebarOverlay.classList.remove('show');
        });
    }

    /* ── File Upload Preview ──────────────────────────────────── */
    document.querySelectorAll('input[type="file"]').forEach(function (input) {
        input.addEventListener('change', function () {
            const previewArea = document.querySelector('.file-preview-area');
            if (!previewArea) return;

            const placeholder = previewArea.querySelector('.preview-placeholder');
            let img = previewArea.querySelector('img');
            let removeBtn = previewArea.querySelector('.remove-preview');

            if (this.files && this.files[0]) {
                const file = this.files[0];

                // Only preview images
                if (!file.type.startsWith('image/')) {
                    if (placeholder) {
                        // The file name is set with textContent, so HTML in a name is shown as text, not run.
                        placeholder.innerHTML = '<i class="bi bi-file-earmark"></i><span></span>';
                        placeholder.querySelector('span').textContent = file.name;
                    }
                    return;
                }

                const reader = new FileReader();
                reader.onload = function (e) {
                    if (!img) {
                        img = document.createElement('img');
                        previewArea.appendChild(img);
                    }
                    img.src = e.target.result;
                    img.alt = 'Preview';
                    if (placeholder) placeholder.style.display = 'none';

                    // Add remove button
                    if (!removeBtn) {
                        removeBtn = document.createElement('button');
                        removeBtn.type = 'button';
                        removeBtn.className = 'btn btn-sm btn-danger remove-preview';
                        removeBtn.innerHTML = '<i class="bi bi-x-lg"></i>';
                        previewArea.appendChild(removeBtn);

                        removeBtn.addEventListener('click', function (ev) {
                            ev.stopPropagation();
                            input.value = '';
                            img.remove();
                            removeBtn.remove();
                            if (placeholder) {
                                placeholder.style.display = '';
                                placeholder.innerHTML = '<i class="bi bi-cloud-arrow-up"></i><span>Click or drag to upload image</span>';
                            }
                        });
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    });

    /* ── Form Validation Enhancement ─────────────────────────── */
    document.querySelectorAll('form.needs-validation').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            form.classList.add('was-validated');

            // Custom messages for specific fields
            form.querySelectorAll('[required]').forEach(function (field) {
                if (!field.validity.valid) {
                    field.classList.add('is-invalid');
                } else {
                    field.classList.remove('is-invalid');
                    field.classList.add('is-valid');
                }
            });

            // Password match validation
            const pass = form.querySelector('input[name="password"]');
            const confirmPass = form.querySelector('input[name="confirm_password"]');
            if (pass && confirmPass && confirmPass.value !== '' && pass.value !== confirmPass.value) {
                e.preventDefault();
                confirmPass.classList.add('is-invalid');
                let feedback = confirmPass.parentElement.querySelector('.invalid-feedback');
                if (!feedback) {
                    feedback = document.createElement('div');
                    feedback.className = 'invalid-feedback';
                    confirmPass.parentElement.appendChild(feedback);
                }
                feedback.textContent = 'Passwords do not match.';
            }
        });
    });

    /* ── Confirm Dialogs ─────────────────────────────────────── */
    document.querySelectorAll('[data-confirm]').forEach(function (el) {
        el.addEventListener('click', function (e) {
            const message = this.getAttribute('data-confirm') || 'Are you sure?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });

    /* ── Auto-Dismiss Flash Messages ─────────────────────────── */
    document.querySelectorAll('.flash-message').forEach(function (flash) {
        setTimeout(function () {
            flash.classList.add('fade-out');
            setTimeout(function () {
                flash.remove();
            }, 400);
        }, 5000);
    });

    /* ── Table Search / Filter ────────────────────────────────── */
    document.querySelectorAll('.table-search-input').forEach(function (input) {
        input.addEventListener('input', function () {
            const query = this.value.toLowerCase().trim();
            const tableId = this.getAttribute('data-table');
            const table = document.getElementById(tableId);
            if (!table) return;

            const rows = table.querySelectorAll('tbody tr');
            rows.forEach(function (row) {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(query) ? '' : 'none';
            });

            // Update visible count
            const countEl = document.getElementById(tableId + '-count');
            if (countEl) {
                const visible = table.querySelectorAll('tbody tr:not([style*="display: none"])').length;
                countEl.textContent = visible;
            }
        });
    });

    /* ── Bootstrap Tooltip Initialization ─────────────────────── */
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(function (el) {
        new bootstrap.Tooltip(el);
    });

    /* ── Character Counter for Textareas ─────────────────────── */
    document.querySelectorAll('textarea[data-maxlength]').forEach(function (textarea) {
        const max = parseInt(textarea.getAttribute('data-maxlength'), 10);
        if (isNaN(max)) return;

        // Create counter element
        const counter = document.createElement('div');
        counter.className = 'char-counter';
        textarea.parentNode.insertBefore(counter, textarea.nextSibling);

        function updateCounter() {
            const remaining = max - textarea.value.length;
            counter.textContent = remaining + ' characters remaining';
            counter.classList.remove('warn', 'danger');
            if (remaining <= 0) {
                counter.classList.add('danger');
                textarea.value = textarea.value.substring(0, max);
                counter.textContent = '0 characters remaining';
            } else if (remaining <= Math.floor(max * 0.1)) {
                counter.classList.add('warn');
            }
        }

        updateCounter();
        textarea.addEventListener('input', updateCounter);
    });

    /* ── Print Button Handler ────────────────────────────────── */
    document.querySelectorAll('.btn-print, [data-action="print"]').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            window.print();
        });
    });

    /* ── Password Show/Hide Toggle ───────────────────────────── */
    document.querySelectorAll('.password-toggle').forEach(function (toggle) {
        toggle.addEventListener('click', function () {
            const input = this.closest('.input-group').querySelector('input');
            const icon = this.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        });
    });

    /**
     * Theme Switcher implementation
     */
    function applyTheme(theme) {
        document.documentElement.setAttribute('data-bs-theme', theme);
        document.documentElement.setAttribute('data-theme', theme);

        const icons = document.querySelectorAll('#themeToggleIcon, .theme-toggle-icon');
        const btns = document.querySelectorAll('#themeToggle, .theme-toggle-btn');

        icons.forEach(function (icon) {
            if (theme === 'dark') {
                icon.className = 'bi bi-sun-fill text-warning';
            } else {
                icon.className = 'bi bi-moon-stars-fill text-primary';
            }
        });

        btns.forEach(function (btn) {
            btn.setAttribute('title', theme === 'dark' ? 'Switch to Light Mode' : 'Switch to Dark Mode');
            btn.setAttribute('aria-label', theme === 'dark' ? 'Switch to Light Mode' : 'Switch to Dark Mode');
        });

        // Notify charts and other custom components
        window.dispatchEvent(new CustomEvent('themeChanged', { detail: { theme: theme } }));
    }

    function initThemeSwitcher() {
        const savedTheme = localStorage.getItem('mrs-theme');
        const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        const currentTheme = savedTheme || (prefersDark ? 'dark' : 'light');

        applyTheme(currentTheme);

        document.querySelectorAll('#themeToggle, .theme-toggle-btn').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                const activeTheme = document.documentElement.getAttribute('data-bs-theme') || 'light';
                const newTheme = activeTheme === 'dark' ? 'light' : 'dark';
                localStorage.setItem('mrs-theme', newTheme);
                applyTheme(newTheme);
            });
        });

        if (window.matchMedia) {
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function (e) {
                if (!localStorage.getItem('mrs-theme')) {
                    applyTheme(e.matches ? 'dark' : 'light');
                }
            });
        }
    }
});
