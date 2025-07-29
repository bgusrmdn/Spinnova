/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ["./*.html", "./src/**/*.{html,js}"],
  theme: {
    extend: {
      colors: {
        'slot-gold': '#FFD700',
        'slot-red': '#DC143C',
        'slot-green': '#228B22',
        'slot-blue': '#4169E1',
        'slot-purple': '#8A2BE2',
      },
      animation: {
        'spin-slow': 'spin 3s linear infinite',
        'bounce-slow': 'bounce 2s infinite',
        'pulse-fast': 'pulse 1s infinite',
      }
    },
  },
  plugins: [],
}

