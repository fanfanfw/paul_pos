import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import daisyui from 'daisyui';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Plus Jakarta Sans', ...defaultTheme.fontFamily.sans],
                mono: ['JetBrains Mono', ...defaultTheme.fontFamily.mono],
            },
        },
    },

    plugins: [forms, daisyui],

    daisyui: {
        themes: [
            {
                kasirku: {
                    primary: '#1e40af',
                    'primary-content': '#ffffff',
                    secondary: '#64748b',
                    'secondary-content': '#ffffff',
                    accent: '#f59e0b',
                    'accent-content': '#1c1917',
                    neutral: '#1e293b',
                    'neutral-content': '#f8fafc',
                    'base-100': '#f8fafc',
                    'base-200': '#f1f5f9',
                    'base-300': '#e2e8f0',
                    'base-content': '#0f172a',
                    info: '#0284c7',
                    success: '#16a34a',
                    warning: '#ea580c',
                    error: '#dc2626',
                },
            },
        ],
        defaultTheme: 'kasirku',
    },
};
