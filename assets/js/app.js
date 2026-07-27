document.addEventListener('alpine:init', () => {
    Alpine.data('dropdown', () => ({
        open: false,
        toggle() { this.open = !this.open; },
        close() { this.open = false; },
    }));

    Alpine.data('modal', () => ({
        show: false,
        open() { this.show = true; document.body.style.overflow = 'hidden'; },
        close() { this.show = false; document.body.style.overflow = ''; },
    }));

    Alpine.data('toast', () => ({
        visible: true,
        dismiss() { this.visible = false; },
        init() {
            setTimeout(() => { this.visible = false; }, 5000);
        }
    }));
});

document.addEventListener('DOMContentLoaded', () => {
    document.addEventListener('keydown', (e) => {
        if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
            e.preventDefault();
            const searchInput = document.querySelector('[data-command-palette]');
            if (searchInput) searchInput.focus();
        }
    });
});

function debounce(fn, delay = 300) {
    let timer;
    return function (...args) {
        clearTimeout(timer);
        timer = setTimeout(() => fn.apply(this, args), delay);
    };
}

function fetchJson(url, options = {}) {
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

    return fetch(url, merged)
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => Promise.reject(err)).catch(() => {
                    return Promise.reject({ message: `HTTP ${response.status}` });
                });
            }
            return response.json();
        });
}
