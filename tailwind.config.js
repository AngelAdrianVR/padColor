import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: '#D72C8A',
                primarylight: '#e955a6',
                secondary: '#25346D',
                secondarylight: '#25346D',
                redpad: '#C1202A',
                greenpad: '#278E16',
                black1: '#1A1A1A',
                gray66: '#666666',
                grayD9: '#D9D9D9',
                gray99: '#999999',
            },
        },
    },

    plugins: [forms, typography],
};
