/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ['./app/Views/**/*.php', './app/Controllers/**/*.php', './index.php', './assets/js/**/*.js'],
  theme: {
    extend: {
      colors: {
        // Indigo-violet palette. Surfaces stay white; the violet family carries
        // every accent. Contrast checked against WCAG AA — see README.
        brand: {
          50:  '#f1f0fc',
          100: '#e4e1f9',
          200: '#cbc6f3',
          300: '#ada5eb',
          400: '#8b80e0',
          500: '#5b4fc7', // primary: buttons, links, active states
          600: '#4a3fb0',
          700: '#3e3596', // hover / dark
          800: '#332b7a',
          900: '#241f57',
        },
        ink: {
          DEFAULT: '#221b45',              // near-black with a violet cast
          soft:  'rgba(34,27,69,0.72)',
          faint: 'rgba(34,27,69,0.65)',
        },
        canvas: '#f5f4fd',   // page background behind the white cards
        line: '#e3e1f1',
        success: '#0e7040',
        warning: '#8a5a08',
        danger:  '#b3261e',
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
