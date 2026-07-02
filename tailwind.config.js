import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans:    ['Inter', ...defaultTheme.fontFamily.sans],
                heading: ['Poppins', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: {
                    DEFAULT: '#2F6B3C',
                    dark:    '#1F4A28',
                    light:   '#4E8C5C',
                },
                secondary: {
                    DEFAULT: '#8B5E3C',
                    light:   '#B08968',
                },
                accent:  '#C9A227',
                surface: '#F7F5F0',
                ink:     '#1F2A1F',
            },
            boxShadow: {
                'card':  '0 1px 3px 0 rgba(47,107,60,0.08), 0 1px 2px -1px rgba(47,107,60,0.06)',
                'card-hover': '0 4px 16px 0 rgba(47,107,60,0.12), 0 2px 6px -1px rgba(47,107,60,0.08)',
                'sidebar': '4px 0 16px 0 rgba(47,107,60,0.08)',
            },
            backgroundImage: {
                'gradient-herbal': 'linear-gradient(135deg, #2F6B3C 0%, #1F4A28 100%)',
                'gradient-herbal-light': 'linear-gradient(135deg, #4E8C5C 0%, #2F6B3C 100%)',
            },
            animation: {
                'fade-in': 'fadeIn 0.2s ease-out',
                'slide-in': 'slideIn 0.25s ease-out',
                'pulse-dot': 'pulseDot 2s cubic-bezier(0.4, 0, 0.6, 1) infinite',
            },
            keyframes: {
                fadeIn: {
                    '0%':   { opacity: '0', transform: 'translateY(-4px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
                slideIn: {
                    '0%':   { opacity: '0', transform: 'translateX(-8px)' },
                    '100%': { opacity: '1', transform: 'translateX(0)' },
                },
                pulseDot: {
                    '0%, 100%': { opacity: '1' },
                    '50%': { opacity: '.4' },
                },
            },
        },
    },

    plugins: [forms],
};
