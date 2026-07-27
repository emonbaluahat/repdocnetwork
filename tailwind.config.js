/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        './views/**/*.php',
        './assets/js/**/*.js',
    ],
    darkMode: 'class',
    theme: {
        extend: {
            colors: {
                primary: {
                    50: '#EFF6FF',
                    100: '#DBEAFE',
                    200: '#BFDBFE',
                    300: '#93C5FD',
                    400: '#60A5FA',
                    500: '#3B82F6',
                    600: '#2563EB',
                    700: '#1D4ED8',
                    800: '#1E40AF',
                    900: '#1E3A8A',
                },
                surface: {
                    DEFAULT: '#FFFFFF',
                    secondary: '#F8F9FA',
                    dark: '#0F0F11',
                    'dark-secondary': '#141416',
                },
                border: {
                    DEFAULT: '#E5E7EB',
                    dark: '#2C2C30',
                },
                text: {
                    primary: '#111827',
                    secondary: '#6B7280',
                    tertiary: '#9CA3AF',
                    'dark-primary': '#F5F5F7',
                    'dark-secondary': '#98989D',
                    'dark-tertiary': '#636366',
                },
                success: {
                    50: '#ECFDF5',
                    100: '#D1FAE5',
                    400: '#34D399',
                    500: '#10B981',
                    600: '#059669',
                    700: '#047857',
                },
                warning: {
                    50: '#FFFBEB',
                    100: '#FEF3C7',
                    400: '#FBBF24',
                    500: '#F59E0B',
                    600: '#D97706',
                    700: '#B45309',
                },
                error: {
                    50: '#FEF2F2',
                    100: '#FEE2E2',
                    400: '#F87171',
                    500: '#EF4444',
                    600: '#DC2626',
                    700: '#B91C1C',
                },
                card: {
                    DEFAULT: '#FFFFFF',
                    dark: '#1A1A1E',
                },
                info: {
                    50: '#F0F9FF',
                    100: '#E0F2FE',
                    400: '#38BDF8',
                    500: '#0EA5E9',
                    600: '#0284C7',
                    700: '#0369A1',
                },
            },
            fontFamily: {
                sans: ['Inter', 'system-ui', '-apple-system', 'sans-serif'],
                bengali: ['Noto Sans Bengali', 'sans-serif'],
                mono: ['JetBrains Mono', 'monospace'],
            },
            spacing: {
                4.5: '18px',
                18: '72px',
                22: '88px',
                30: '120px',
            },
            maxWidth: {
                '8xl': '1280px',
            },
            minHeight: {
                'screen-minus-top': 'calc(100vh - 48px)',
            },
            width: {
                sidebar: '240px',
                'sidebar-collapsed': '56px',
                drawer: '400px',
                'drawer-wide': '600px',
                rail: '56px',
            },
            height: {
                topbar: '48px',
                statusbar: '28px',
            },
        },
    },
    plugins: [],
};
