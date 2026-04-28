/**
 * BulkSMS Pro - Main JavaScript
 * Premium UI Framework
 */

'use strict';

const App = {
    // ==========================================
    // INITIALIZATION
    // ==========================================
    init() {
        this.initTheme();
        this.initSidebar();
        this.initDropdowns();
        this.initTooltips();
        this.initAlerts();
        this.initForms();
        this.initAnimations();
        this.initNotifications();
        this.initSearch();
        this.initConfirmDialogs();
    },

    // ==========================================
    // THEME MANAGEMENT
    // ==========================================
    initTheme() {
        const saved = localStorage.getItem('theme') || 'dark';
        document.documentElement.setAttribute('data-theme', saved);
        this.currentTheme = saved;

        document.querySelectorAll('.theme-switch, .theme-toggle').forEach(el => {
            el.addEventListener('click', () => this.toggleTheme());
        });
    },

    toggleTheme() {
        this.currentTheme = this.currentTheme === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', this.currentTheme);
        localStorage.setItem('theme', this.currentTheme);

        // Save to server if logged in
        fetch('/ajax/theme', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.getCsrfToken(),
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ theme: this.currentTheme })
        }).catch(() => {});
    },

    // ==========================================
    // SIDEBAR
    // ==========================================
    initSidebar() {
        const sidebar = document.querySelector('.app-sidebar');
        const toggle = document.querySelector('.header-toggle');
        const overlay = document.querySelector('.sidebar-overlay');
        const collapsed = localStorage.getItem('sidebar_collapsed') === 'true';

        if (sidebar && collapsed && window.innerWidth > 991) {
            sidebar.classList.add('collapsed');
            document.body.classList.add('sidebar-collapsed');
        }

        if (toggle) {
            toggle.addEventListener('click', () => {
                if (window.innerWidth <= 991) {
                    sidebar.classList.toggle('mobile-open');
                    overlay?.classList.toggle('active');
                } else {
                    sidebar.classList.toggle('collapsed');
                    document.body.classList.toggle('sidebar-collapsed');
                    localStorage.setItem('sidebar_collapsed', sidebar.classList.contains('collapsed'));
                }
            });
        }

        if (overlay) {
            overlay.addEventListener('click', () => {
                sidebar.classList.remove('mobile-open');
                overlay.classList.remove('active');
            });
        }

        // Close sidebar on mobile when a nav link is tapped
        if (sidebar) {
            sidebar.querySelectorAll('.nav-item[href]').forEach(link => {
                link.addEventListener('click', () => {
                    if (window.innerWidth <= 991) {
                        sidebar.classList.remove('mobile-open');
                        overlay?.classList.remove('active');
                    }
                });
            });
        }

        // Auto-close sidebar on resize to desktop
        window.addEventListener('resize', () => {
            if (window.innerWidth > 991 && sidebar) {
                sidebar.classList.remove('mobile-open');
                overlay?.classList.remove('active');
            }
        });

        // Active nav item
        const currentPath = window.location.pathname;
        document.querySelectorAll('.nav-item[href]').forEach(item => {
            const href = item.getAttribute('href');
            if (href === currentPath || (href !== '/' && currentPath.startsWith(href))) {
                item.classList.add('active');
            }
        });
    },

    // ==========================================
    // DROPDOWNS (Custom)
    // ==========================================
    initDropdowns() {
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.custom-dropdown')) {
                document.querySelectorAll('.custom-dropdown.open').forEach(d => d.classList.remove('open'));
            }
        });
    },

    // ==========================================
    // TOOLTIPS
    // ==========================================
    initTooltips() {
        if (typeof bootstrap !== 'undefined') {
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
                new bootstrap.Tooltip(el);
            });
        }
    },

    // ==========================================
    // AUTO-DISMISS ALERTS
    // ==========================================
    initAlerts() {
        document.querySelectorAll('.alert-auto-dismiss').forEach(alert => {
            setTimeout(() => {
                alert.style.transition = 'opacity 0.3s, transform 0.3s';
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-10px)';
                setTimeout(() => alert.remove(), 300);
            }, 5000);
        });
    },

    // ==========================================
    // FORM ENHANCEMENTS
    // ==========================================
    initForms() {
        // Character counter for SMS
        document.querySelectorAll('[data-sms-counter]').forEach(textarea => {
            const counter = document.querySelector(textarea.dataset.smsCounter);
            if (counter) {
                textarea.addEventListener('input', () => {
                    const text = textarea.value;
                    const isUnicode = /[^\x00-\x7F]/.test(text);
                    const maxLen = isUnicode ? 70 : 160;
                    const multipartLen = isUnicode ? 67 : 153;
                    const len = text.length;
                    const parts = len <= maxLen ? 1 : Math.ceil(len / multipartLen);
                    counter.innerHTML = `<span>${len}</span> chars | <span>${parts}</span> part(s) | ${isUnicode ? 'Unicode' : 'GSM'}`;
                });
            }
        });

        // File upload preview
        document.querySelectorAll('.file-upload-input').forEach(input => {
            input.addEventListener('change', function() {
                const label = this.closest('.file-upload')?.querySelector('.file-upload-label');
                if (label && this.files.length) {
                    label.textContent = this.files[0].name;
                }
            });
        });

        // Select all checkboxes
        document.querySelectorAll('.select-all-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const target = this.dataset.target;
                document.querySelectorAll(target).forEach(cb => {
                    cb.checked = this.checked;
                });
            });
        });
    },

    // ==========================================
    // SCROLL ANIMATIONS
    // ==========================================
    initAnimations() {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('fade-in');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });

        document.querySelectorAll('.animate-on-scroll').forEach(el => observer.observe(el));
    },

    // ==========================================
    // NOTIFICATIONS
    // ==========================================
    initNotifications() {
        this.checkNotifications();
        setInterval(() => this.checkNotifications(), 30000);
    },

    checkNotifications() {
        fetch('/ajax/notifications/unread-count', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            const badge = document.querySelector('.notification-badge-count');
            const dot = document.querySelector('.notification-dot');
            if (badge) badge.textContent = data.count || '';
            if (dot) dot.style.display = data.count > 0 ? 'block' : 'none';
        })
        .catch(() => {});
    },

    // ==========================================
    // SEARCH
    // ==========================================
    initSearch() {
        const searchInput = document.querySelector('.header-search input');
        let searchTimeout;
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    if (this.value.length >= 2) {
                        // Trigger search
                        App.performSearch(this.value);
                    }
                }, 300);
            });
        }
    },

    performSearch(query) {
        // Global search implementation
        console.log('Searching:', query);
    },

    // ==========================================
    // CONFIRM DIALOGS
    // ==========================================
    initConfirmDialogs() {
        document.addEventListener('click', (e) => {
            const btn = e.target.closest('[data-confirm]');
            if (btn) {
                e.preventDefault();
                const message = btn.dataset.confirm || 'Are you sure?';
                if (confirm(message)) {
                    if (btn.tagName === 'A') {
                        window.location.href = btn.href;
                    } else if (btn.form) {
                        btn.form.submit();
                    }
                }
            }
        });
    },

    // ==========================================
    // TOAST NOTIFICATIONS
    // ==========================================
    toast(message, type = 'info', duration = 4000) {
        let container = document.querySelector('.toast-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'toast-container';
            document.body.appendChild(container);
        }

        const icons = { success: 'check-circle-fill', danger: 'x-circle-fill', warning: 'exclamation-triangle-fill', info: 'info-circle-fill' };
        const colors = { success: 'var(--success)', danger: 'var(--danger)', warning: 'var(--warning)', info: 'var(--info)' };

        const toast = document.createElement('div');
        toast.className = 'toast-message';
        toast.innerHTML = `<i class="bi bi-${icons[type] || icons.info}" style="color:${colors[type] || colors.info};font-size:1.1rem"></i><span style="flex:1">${message}</span><i class="bi bi-x" style="cursor:pointer;opacity:0.5" onclick="this.parentElement.remove()"></i>`;
        container.appendChild(toast);

        setTimeout(() => {
            toast.style.transition = 'opacity 0.3s, transform 0.3s';
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(100%)';
            setTimeout(() => toast.remove(), 300);
        }, duration);
    },

    // ==========================================
    // AJAX HELPER
    // ==========================================
    ajax(url, options = {}) {
        const defaults = {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': this.getCsrfToken()
            }
        };

        if (options.method === 'POST' && options.body && !(options.body instanceof FormData)) {
            defaults.headers['Content-Type'] = 'application/json';
            options.body = JSON.stringify(options.body);
        }

        return fetch(url, { ...defaults, ...options, headers: { ...defaults.headers, ...options.headers } })
            .then(r => {
                if (!r.ok) throw new Error(`HTTP ${r.status}`);
                return r.json();
            });
    },

    // ==========================================
    // UTILITIES
    // ==========================================
    getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.content : '';
    },

    formatNumber(num) {
        return new Intl.NumberFormat().format(num);
    },

    formatCurrency(amount, currency = 'USD') {
        return new Intl.NumberFormat('en-US', { style: 'currency', currency }).format(amount);
    },

    copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            this.toast('Copied to clipboard', 'success', 2000);
        });
    },

    debounce(func, wait) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    },

    // ==========================================
    // DATA TABLE HELPER
    // ==========================================
    initDataTable(tableId, options = {}) {
        const table = document.getElementById(tableId);
        if (!table) return;

        const searchInput = options.searchInput;
        if (searchInput) {
            const input = document.querySelector(searchInput);
            if (input) {
                input.addEventListener('input', App.debounce(function() {
                    const filter = this.value.toLowerCase();
                    const rows = table.querySelectorAll('tbody tr');
                    rows.forEach(row => {
                        const text = row.textContent.toLowerCase();
                        row.style.display = text.includes(filter) ? '' : 'none';
                    });
                }, 200));
            }
        }
    },

    // ==========================================
    // MODAL HELPER
    // ==========================================
    showModal(id) {
        const modal = new bootstrap.Modal(document.getElementById(id));
        modal.show();
        return modal;
    },

    hideModal(id) {
        const modal = bootstrap.Modal.getInstance(document.getElementById(id));
        if (modal) modal.hide();
    },

    // ==========================================
    // LOADING STATE
    // ==========================================
    setLoading(element, loading = true) {
        if (loading) {
            element.dataset.originalHtml = element.innerHTML;
            element.innerHTML = '<span class="spinner"></span> Loading...';
            element.disabled = true;
        } else {
            element.innerHTML = element.dataset.originalHtml || element.innerHTML;
            element.disabled = false;
        }
    }
};

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => App.init());
