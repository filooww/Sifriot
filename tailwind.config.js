import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Cozy color palette
                cozy: {
                    bg: {
                        primary: 'rgb(var(--color-bg-primary) / <alpha-value>)',
                        secondary: 'rgb(var(--color-bg-secondary) / <alpha-value>)',
                        tertiary: 'rgb(var(--color-bg-tertiary) / <alpha-value>)',
                        accent: 'rgb(var(--color-bg-accent) / <alpha-value>)',
                    },
                    text: {
                        primary: 'rgb(var(--color-text-primary) / <alpha-value>)',
                        secondary: 'rgb(var(--color-text-secondary) / <alpha-value>)',
                        tertiary: 'rgb(var(--color-text-tertiary) / <alpha-value>)',
                        accent: 'rgb(var(--color-text-accent) / <alpha-value>)',
                    },
                    border: {
                        light: 'rgb(var(--color-border-light) / <alpha-value>)',
                        medium: 'rgb(var(--color-border-medium) / <alpha-value>)',
                        dark: 'rgb(var(--color-border-dark) / <alpha-value>)',
                    },
                    accent: {
                        primary: 'rgb(var(--color-accent-primary) / <alpha-value>)',
                        secondary: 'rgb(var(--color-accent-secondary) / <alpha-value>)',
                        hover: 'rgb(var(--color-accent-hover) / <alpha-value>)',
                    },
                    hover: 'rgb(var(--color-hover) / <alpha-value>)',
                    active: 'rgb(var(--color-active) / <alpha-value>)',
                },
            },
        },
    },

    plugins: [forms],
};
