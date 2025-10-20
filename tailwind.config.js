import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './app/View/Components/**/*.php',
    ],

    safelist: [
        // Codeforces rank colors
        'text-gray-600',
        'text-green-600',
        'text-cyan-600',
        'text-blue-600',
        'text-purple-600',
        'text-orange-600',
        'text-red-600',
        'bg-gray-100',
        'bg-green-100',
        'bg-cyan-100',
        'bg-blue-100',
        'bg-purple-100',
        'bg-orange-100',
        'bg-red-100',
        'text-gray-800',
        'text-green-800',
        'text-cyan-800',
        'text-blue-800',
        'text-purple-800',
        'text-orange-800',
        'text-red-800',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
};
