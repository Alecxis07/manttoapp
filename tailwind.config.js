import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',

    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            colors: {
                steel: {
                    50: '#f4f6f8',
                    100: '#e4e9ef',
                    200: '#c9d3de',
                    300: '#a3b3c5',
                    400: '#768ea8',
                    500: '#5a728c',
                    600: '#475a72',
                    700: '#3a4a5d',
                    800: '#323f4f',
                    900: '#2c3643',
                    950: '#1a212b',
                },
                hazard: {
                    DEFAULT: '#e8a317',
                    soft: '#f5c86a',
                    deep: '#b87a0a',
                },
            },
            fontFamily: {
                sans: ['"Source Sans 3"', 'Figtree', ...defaultTheme.fontFamily.sans],
                display: ['"Barlow Condensed"', 'Impact', ...defaultTheme.fontFamily.sans],
                mono: ['"IBM Plex Mono"', ...defaultTheme.fontFamily.mono],
            },
            boxShadow: {
                panel: '0 1px 0 rgba(255,255,255,0.04) inset, 0 8px 24px rgba(15, 23, 42, 0.12)',
            },
        },
    },

    plugins: [forms, typography],
};
