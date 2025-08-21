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
                // sans: ['Figtree', ...defaultTheme.fontFamily.sans],
                // sans: ['Hahmlet', ...defaultTheme.fontFamily.sans],
                sans: ['Wix Madefor Text', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: '#25346D',
                primarylight: '#B8C1E5',
                secondary: '#D72C8A',
                secondarylight: '#FAE6F1',
                redpad: '#C1202A',
                greenpad: '#278E16',
                black1: '#1A1A1A',
                gray66: '#666666',
                grayD9: '#D9D9D9',
                gray99: '#999999',
                grayED: '#EDEDED',
                gray3F: '#3F3F3F',
            },
        },
    },

    plugins: [forms, typography],
};
