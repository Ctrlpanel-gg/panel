/** @type {import('tailwindcss').Config} */
module.exports = {
  darkMode: 'class',
  content: [
    "./themes/default/views/**/*.blade.php",
    "./themes/default/js/**/*.js",
    "./themes/default/css/**/*.css",
    "./themes/default/new-css/**/*.css",
  ],
  theme: {
    extend: {
      colors: {
        primary: {
          50: '#f8fafc',
          100: '#f1f5f9',
          200: '#e2e8f0',
          300: '#cbd5e1',
          400: '#94a3b8',
          500: '#64748b',
          600: '#475569',
          700: '#334155',
          800: '#1e293b',
          900: '#0f172a',
          950: '#020617',
        },
        accent: {
          blue: '#3b82f6',
          amber: '#f59e0b',
          emerald: '#10b981',
          red: '#ef4444',
        },
        surface: {
          DEFAULT: 'rgb(24 24 27 / 0.5)',
          border: 'rgb(39 39 42 / 0.5)',
        }
      },
      backgroundImage: {
        'gradient-radial': 'radial-gradient(var(--tw-gradient-stops))',
      }
    },
  },
  plugins: [
    require('@tailwindcss/forms'),
    require('@tailwindcss/typography'),
  ],
}
