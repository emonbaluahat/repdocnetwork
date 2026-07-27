const RepDoc = {
    debounce(fn, delay = 300) {
        let timer;
        return function (...args) {
            clearTimeout(timer);
            timer = setTimeout(() => fn.apply(this, args), delay);
        };
    },

    formatDate(dateStr) {
        if (!dateStr) return '';
        const date = new Date(dateStr);
        return date.toLocaleDateString('bn-BD', {
            year: 'numeric', month: 'short', day: 'numeric',
        });
    },

    formatDateTime(dateStr) {
        if (!dateStr) return '';
        const date = new Date(dateStr);
        return date.toLocaleDateString('bn-BD', {
            year: 'numeric', month: 'short', day: 'numeric',
            hour: '2-digit', minute: '2-digit',
        });
    },

    formatCurrency(amount) {
        return '৳ ' + Number(amount).toLocaleString('bn-BD');
    },

    pluralize(count, singular, plural) {
        return count === 1 ? singular : plural;
    },

    truncate(text, length = 100) {
        if (!text || text.length <= length) return text;
        return text.substring(0, length) + '...';
    },

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    },

    async fetchJson(url, options = {}) {
        const defaults = {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
        };

        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (csrfToken) {
            defaults.headers['X-CSRF-TOKEN'] = csrfToken.getAttribute('content');
        }

        const merged = { ...defaults, ...options };
        if (merged.body && typeof merged.body === 'object' && !(merged.body instanceof FormData)) {
            merged.body = JSON.stringify(merged.body);
            merged.headers['Content-Type'] = 'application/json';
        }

        const response = await fetch(url, merged);
        if (!response.ok) {
            const err = await response.json().catch(() => ({ message: `HTTP ${response.status}` }));
            throw err;
        }
        return response.json();
    },

    async postJson(url, data = {}) {
        return this.fetchJson(url, {
            method: 'POST',
            body: data instanceof FormData ? data : JSON.stringify(data),
            headers: data instanceof FormData ? {} : { 'Content-Type': 'application/json' },
        });
    },

    showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 z-50 p-4 rounded-lg border text-sm font-bengali shadow-lg animate-slide-in-right`;
        const colors = {
            success: 'bg-success-50 border-success-200 text-success-700',
            error: 'bg-error-50 border-error-200 text-error-700',
            warning: 'bg-warning-50 border-warning-200 text-warning-700',
            info: 'bg-info-50 border-info-200 text-info-700',
        };
        toast.className += ' ' + (colors[type] || colors.info);
        toast.textContent = message;
        document.body.appendChild(toast);
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transition = 'opacity 0.2s';
            setTimeout(() => toast.remove(), 200);
        }, 5000);
    },

    showLoading(container) {
        const skeleton = document.createElement('div');
        skeleton.className = 'animate-pulse space-y-3 p-4';
        skeleton.innerHTML = `
            <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-3/4"></div>
            <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-1/2"></div>
            <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-5/6"></div>
        `;
        skeleton.dataset.skeleton = 'true';
        container.innerHTML = '';
        container.appendChild(skeleton);
    },

    hideLoading(container) {
        const skeleton = container.querySelector('[data-skeleton]');
        if (skeleton) skeleton.remove();
    },
};
