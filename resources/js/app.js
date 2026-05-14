import './bootstrap';

// Alpine.js plugins
import focus from '@alpinejs/focus'
import collapse from '@alpinejs/collapse'

// Apply dark mode immediately on page load (before Alpine initializes)
function applyDarkMode() {
    const isDark = localStorage.getItem('darkMode') === 'true' ||
                   (!localStorage.getItem('darkMode') && window.matchMedia('(prefers-color-scheme: dark)').matches);
    if (isDark) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
}

// Apply theme immediately
applyDarkMode();

// Reapply theme on Livewire navigation
document.addEventListener('livewire:navigated', applyDarkMode);

// Livewire v3 includes Alpine, so we extend it instead of importing it
document.addEventListener('livewire:init', () => {
    // Get Alpine from Livewire
    const Alpine = window.Alpine;

    // Register plugins
    Alpine.plugin(focus)
    Alpine.plugin(collapse)

    // Dark mode functionality
    Alpine.store('darkMode', {
        init() {
            this.on = localStorage.getItem('darkMode') === 'true' ||
                     (!localStorage.getItem('darkMode') && window.matchMedia('(prefers-color-scheme: dark)').matches);
            this.updateTheme();
        },

        on: false,

        toggle() {
            this.on = !this.on;
            localStorage.setItem('darkMode', this.on);
            this.updateTheme();
        },

        updateTheme() {
            if (this.on) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        }
    });

    // Notification system
    Alpine.store('notifications', {
        items: [],

        add(message, type = 'info') {
            const id = Date.now();
            this.items.push({ id, message, type });

            // Auto-remove after 5 seconds
            setTimeout(() => {
                this.remove(id);
            }, 5000);
        },

        remove(id) {
            this.items = this.items.filter(item => item.id !== id);
        },

        clear() {
            this.items = [];
        }
    });

    // Listen for Livewire notify events
    Livewire.on('notify', (event) => {
        Alpine.store('notifications').add(event.message, event.type || 'info');
    });
});
