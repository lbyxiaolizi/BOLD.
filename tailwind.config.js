module.exports = {
  content: [
    './*.php',
    './inc/**/*.php',
    './assets/js/**/*.js'
  ],
  darkMode: ['class', '.dark-mode'],
  theme: {
    extend: {}
  },
  plugins: [require('@tailwindcss/typography')]
};
