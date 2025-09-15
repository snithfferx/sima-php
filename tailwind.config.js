import defaultTheme from 'tailwindcss/defaultTheme';
/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        './resources/css/global.css',
        './resources/**/*.{js,ts,jsx,tsx,md,mdx,astro,html,svelte,vue,php}',
        './node_modules/flowbite/**/*.js'
    ],
    theme: {
        fontFamily: {
            sans: ['Graphik', 'sans-serif'],
            serif: ['Merriweather', 'serif','Figtree', ...defaultTheme.fontFamily.sans],
        },
        extend: {
            colors: {
                primary: { DEFAULT: '#1982c4', 100: '#051a27', 200: '#0a344e', 300: '#0f4e74', 400: '#14679b', 500: '#1982c4', 600: '#31a0e4', 700: '#65b7eb', 800: '#98cff2', 900: '#cce7f8' },
                secondary: { DEFAULT: '#6a4c93', 100: '#150f1e', 200: '#2a1f3b', 300: '#402e59', 400: '#553d76', 500: '#6a4c93', 600: '#8768b1', 700: '#a58ec5', 800: '#c3b4d8', 900: '#e1d9ec' },
                danger: { DEFAULT: '#ff595e', 100: '#440002', 200: '#890005', 300: '#cd0007', 400: '#ff121a', 500: '#ff595e', 600: '#ff787d', 700: '#ff9a9d', 800: '#ffbcbe', 900: '#ffddde' },
                warning: { DEFAULT: '#ffca3a', 100: '#3e2e00', 200: '#7c5b00', 300: '#bb8900', 400: '#f9b700', 500: '#ffca3a', 600: '#ffd560', 700: '#ffdf88', 800: '#ffeaaf', 900: '#fff4d7' },
                success: { DEFAULT: '#8ac926', 100: '#1c2808', 200: '#38510f', 300: '#537917', 400: '#6fa11f', 500: '#8ac926', 600: '#a4dc49', 700: '#bbe577', 800: '#d2eea4', 900: '#e8f6d2' },
                info: { DEFAULT: '#00aeef', 100: '#002330', 200: '#004660', 300: '#006990', 400: '#008dc0', 500: '#00aeef', 600: '#27c5ff', 700: '#5dd4ff', 800: '#93e2ff', 900: '#c9f1ff' }, 
                gray: { DEFAULT: '#e8e6f3', 100: '#241f40', 200: '#483e80', 300: '#7367b6', 400: '#aea7d5', 500: '#e8e6f3', 600: '#edecf6', 700: '#f2f1f8', 800: '#f6f5fa', 900: '#fbfafd' },
                black: { DEFAULT: '#1a1a1d', 100: '#050506', 200: '#0b0b0c', 300: '#101012', 400: '#151518', 500: '#1a1a1d', 600: '#46464d', 700: '#71717d', 800: '#a0a0a9', 900: '#cfcfd4' }
            }
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
        require('flowbite/plugin')
    ],
    darkMode: 'class',
}