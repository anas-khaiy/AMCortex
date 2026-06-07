/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
  ],
  theme: {
    extend: {
      colors: {
        'amc-pink': '#FF004C',
        'amc-dark': '#1A1A1A',
      },
    },
  },
  plugins: [],
}