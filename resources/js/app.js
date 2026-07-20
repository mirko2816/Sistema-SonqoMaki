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

window.Alpine = Alpine;

Alpine.start();
