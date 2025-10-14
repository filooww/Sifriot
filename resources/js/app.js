import './bootstrap';

// Alpine.js with plugins
import Alpine from 'alpinejs'
import focus from '@alpinejs/focus'
import collapse from '@alpinejs/collapse'

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

window.Alpine = Alpine
Alpine.start()
