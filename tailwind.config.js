/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ['./app/Views/**/*.php', './app/Controllers/**/*.php', './index.php', './assets/js/**/*.js'],
  theme: {
    extend: {
      colors: {
        // LinkedIn-inspired palette
        brand: {
          50:  '#e8f1fb',
          100: '#cfe3f7',
          200: '#9fc7ef',
          300: '#6fabe7',
          400: '#3f8fdf',
          500: '#0a66c2', // LinkedIn blue
          600: '#0959a8',
          700: '#004182', // hover / dark blue
          800: '#00306b',
          900: '#002550',
        },
        ink: {
          DEFAULT: '#191919',
          soft: 'rgba(0,0,0,0.60)',
          faint: 'rgba(0,0,0,0.45)',
        },
        canvas: '#f4f2ee',   // LinkedIn feed background
        line: '#e0dfdc',
        success: '#057642',
        warning: '#915907',
        danger:  '#b24020',
      },
      fontFamily: {
        sans: ['-apple-system', 'BlinkMacSystemFont', '"Segoe UI"', 'Roboto', '"Helvetica Neue"', 'Arial', 'sans-serif'],
      },
      boxShadow: {
        card: '0 0 0 1px rgba(0,0,0,.08), 0 2px 4px rgba(0,0,0,.06)',
        pop:  '0 4px 12px rgba(0,0,0,.15)',
        nav:  '0 1px 2px rgba(0,0,0,.10)',
      },
      borderRadius: {
        card: '0.5rem',
      },
      maxWidth: {
        shell: '1128px',
      },
      keyframes: {
        'fade-in':  { '0%': { opacity: 0 }, '100%': { opacity: 1 } },
        'slide-up': { '0%': { opacity: 0, transform: 'translateY(8px)' }, '100%': { opacity: 1, transform: 'translateY(0)' } },
      },
      animation: {
        'fade-in': 'fade-in .18s ease-out',
        'slide-up': 'slide-up .22s ease-out',
      },
    },
  },
  plugins: [],
};
