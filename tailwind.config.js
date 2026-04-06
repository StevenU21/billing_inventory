import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
        './node_modules/flowbite/**/*.js',
    ],

    darkMode: 'class',

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            }, colors: {
                // CORRECCIÓN: Estos son los grays EXACTOS de Windmill (Old CSS)
                gray: {
                    50: '#f9fafb',
                    100: '#f4f5f7',
                    200: '#e5e7eb',
                    300: '#d5d6d7',
                    400: '#9e9e9e',
                    500: '#707275',
                    600: '#4c4f52',
                    700: '#24262d', // Este es el color de las tarjetas (Cards)
                    800: '#1a1c23', // Este es el fondo principal en dark mode
                    900: '#121317', // Este es el sidebar o fondos muy oscuros
                },
                purple: {
                    50: '#f3f0ff',
                    100: '#e9e5ff',
                    200: '#d3c6fa',
                    300: '#bda5f3',
                    400: '#9a6eeb',
                    500: '#7e3af2',
                    600: '#6c2bd9',
                    700: '#5521b5',
                    800: '#42198f',
                    900: '#2e1266',
                },
            },
        },
    },

    plugins: [
        forms,
        require('flowbite/plugin')
    ],
};
