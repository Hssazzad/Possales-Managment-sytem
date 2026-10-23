/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './resources/**/*.blade.php',
    './resources/**/*.js',
    './resources/**/*.jsx',
  ],
  theme: {
    extend: {
      colors: {
        // Professional Government Style Palette
        'primary': {
          50: '#f0f4f8',
          100: '#d0dce8',
          200: '#a0b8d0',
          300: '#7094b8',
          400: '#5080a8',
          500: '#2c5aa0',
          600: '#254890',
          700: '#1a3a52',
          800: '#0f2435',
          900: '#050c11',
        },
        'success': {
          50: '#f0f9f4',
          500: '#27ae60',
          600: '#229954',
          700: '#1d7e4e',
        },
        'danger': {
          50: '#ffe8e8',
          500: '#e74c3c',
          600: '#d43a2a',
          700: '#bf2e1f',
        },
        'warning': {
          50: '#fff3cd',
          500: '#ffc107',
          600: '#e0a800',
        },
      },
      spacing: {
        'card': '2rem',
        'field': '1.8rem',
        'input': '0.8rem',
        'header': '1.4rem',
      },
      fontSize: {
        'title': '1.4rem',
        'subtitle': '1.1rem',
        'label': '1rem',
        'input': '0.95rem',
        'helper': '0.8rem',
      },
      fontWeight: {
        'heading': '700',
        'label': '700',
        'normal': '600',
      },
      borderRadius: {
        'input': '5px',
        'card': '8px',
      },
      boxShadow: {
        'card': '0 2px 8px rgba(0, 0, 0, 0.08)',
        'input': '0 1px 3px rgba(0, 0, 0, 0.05)',
        'hover': '0 4px 12px rgba(0, 0, 0, 0.12)',
      },
      backgroundImage: {
        'gradient-header': 'linear-gradient(135deg, #2c5aa0 0%, #1a3a52 100%)',
      },
    },
  },
  plugins: [],
}
