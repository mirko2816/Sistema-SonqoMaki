import Alpine from 'alpinejs';

Alpine.data('mobileNavigation', () => ({
    isOpen: false,

    open() {
        this.isOpen = true;
        this.$nextTick(() => setTimeout(() => this.$refs.closeButton?.focus(), 0));
    },

    close() {
        if (! this.isOpen) {
            return;
        }

        this.isOpen = false;
        this.$nextTick(() => setTimeout(() => this.$refs.menuButton?.focus(), 0));
    },

    trapFocus(event) {
        const focusable = [...this.$refs.panel.querySelectorAll(
            'button:not([disabled]), a[href], [tabindex]:not([tabindex="-1"])',
        )];

        if (focusable.length === 0) {
            return;
        }

        const first = focusable[0];
        const last = focusable[focusable.length - 1];

        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (! event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    },
}));

Alpine.data('routineTemplateEditor', (searchUrl, initialItems = []) => ({
    query: '', results: [], loading: false, submitting: false,
    items: (Array.isArray(initialItems) ? initialItems : []).map((item, index) => ({ ...item, key: `stored-${item.id || index}-${crypto.randomUUID()}` })),
    init() { this.search(); },
    async search() {
        this.loading = true;
        try {
            const response = await fetch(`${searchUrl}?search=${encodeURIComponent(this.query)}`, { headers: { Accept: 'application/json' } });
            this.results = response.ok ? (await response.json()).data : [];
        } finally { this.loading = false; }
    },
    add(source) {
        this.items.push({ ...source, id: null, source_exercise_id: source.id, key: `new-${crypto.randomUUID()}` });
        this.$nextTick(() => document.getElementById(`copy-name-${this.items.at(-1).key}`)?.focus());
    },
    remove(index) { this.items.splice(index, 1); },
    move(index, delta) {
        const target = index + delta;
        if (target < 0 || target >= this.items.length) return;
        [this.items[index], this.items[target]] = [this.items[target], this.items[index]];
        this.items = [...this.items];
    },
    prepareSubmit() { this.submitting = true; },
}));

window.Alpine = Alpine;

Alpine.start();
