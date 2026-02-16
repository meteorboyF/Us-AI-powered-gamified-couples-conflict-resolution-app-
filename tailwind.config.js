import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                // PDF Page 15: Pixel font for headers
                pixel: ['"VT323"', 'monospace'], 
            },
            colors: {
                // PDF Page 14: Neutrals
                parchment: '#F6E7C8',
                sand: '#E9D6AE',
                toast: '#6B3F2A',
                cocoa: '#2B1B12',
                
                // PDF Page 14: Accents
                rose: '#E85A9B',
                berry: '#9B2C6B',
                sky: '#78C2E8',
                leaf: '#58B368',
                gold: '#F2C14E',
                
                // PDF Page 14: Night Overlay
                navy: '#0F1B2D',
                moonlight: '#B9D6F2',

                // PDF Page 15: Status
                success: '#58B368',
                warning: '#F2C14E',
                danger: '#E05D5D',
            }
        },
    },
    plugins: [],
};