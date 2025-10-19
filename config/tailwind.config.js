/** @type {import('tailwindcss').Config} */
const path = require('path');

module.exports = {
  content: [
    path.resolve(__dirname, '..', '**/*.php'),
    path.resolve(__dirname, '..', 'assets/js/**/*.js'),
    path.resolve(__dirname, '..', 'template-parts/**/*.php'),
    path.resolve(__dirname, '..', 'inc/**/*.php'),
  ],
  theme: {
    extend: {
      colors: {
        'black': '#000000',
        'white': '#ffffff',
        'gray': {
          50: '#fafafa',
          100: '#f5f5f5',
          200: '#e0e0e0',
          300: '#c0c0c0',
          400: '#8a8a8a',
          500: '#6a6a6a',
          600: '#4a4a4a',
          700: '#2a2a2a',
          800: '#1a1a1a',
          900: '#0a0a0a',
        },
      },
      fontFamily: {
        primary: ['Outfit', 'Inter', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'sans-serif'],
        secondary: ['Inter', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'sans-serif'],
        japanese: ['"Noto Sans JP"', 'sans-serif'],
      },
      spacing: {
        '1': '0.25rem',
        '2': '0.5rem',
        '3': '0.75rem',
        '4': '1rem',
        '5': '1.25rem',
        '6': '1.5rem',
        '8': '2rem',
        '10': '2.5rem',
        '12': '3rem',
      },
      borderRadius: {
        'none': '0',
        'sm': '2px',
        'DEFAULT': '4px',
        'md': '4px',
        'lg': '6px',
        'xl': '8px',
        '2xl': '12px',
        'full': '9999px',
      },
      boxShadow: {
        'xs': '0 1px 2px rgba(0, 0, 0, 0.02)',
        'sm': '0 2px 4px rgba(0, 0, 0, 0.04)',
        'DEFAULT': '0 4px 8px rgba(0, 0, 0, 0.06)',
        'md': '0 4px 8px rgba(0, 0, 0, 0.06)',
        'lg': '0 8px 16px rgba(0, 0, 0, 0.08)',
        'xl': '0 16px 32px rgba(0, 0, 0, 0.12)',
      },
      transitionDuration: {
        'fast': '150ms',
        'DEFAULT': '250ms',
        'slow': '350ms',
      },
    },
  },
  plugins: [],
  // 未使用のユーティリティをPurge
  purge: {
    enabled: process.env.NODE_ENV === 'production',
    content: [
      path.resolve(__dirname, '..', '**/*.php'),
      path.resolve(__dirname, '..', 'assets/js/**/*.js'),
      path.resolve(__dirname, '..', 'template-parts/**/*.php'),
      path.resolve(__dirname, '..', 'inc/**/*.php'),
    ],
    options: {
      safelist: [
        // 動的に生成されるクラスを保護
        'grant-card',
        'stylish-header',
        'loading',
        'active',
        /^fa-/,
        /^filter-/,
        /^card-/,
      ],
    },
  },
};
