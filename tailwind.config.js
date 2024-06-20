const { iconsPlugin, getIconCollections } = require("@egoist/tailwindcss-icons")
module.exports = {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
    "./node_modules/flowbite/**/*.js"
  ],
  theme: {
    extend: {
      colors: {
        primary: {"50":"#eff6ff","100":"#dbeafe","200":"#bfdbfe","300":"#93c5fd","400":"#60a5fa","500":"#3b82f6","600":"#2563eb","700":"#1d4ed8","800":"#1e40af","900":"#1e3a8a","950":"#172554"},
        success: {"50":"#f0fdf4","100":"#dcfce7","200":"#bbf7d0","300":"#86efac","400":"#4ade80","500":"#22c55e","600":"#16a34a","700":"#15803d","800":"#166534","900":"#14532d","950":"#0d3328"},
      }
    },
  },
  plugins: [
      require('flowbite/plugin'),
      iconsPlugin({
        // Select the icon collections you want to use
        // You can also ignore this option to automatically discover all individual icon packages you have installed
        // If you install @iconify/json, you should explicitly specify the collections you want to use, like this:
        collections: getIconCollections(["mdi", "mdi-light"]),
        // If you want to use all icons from @iconify/json, you can do this:
        // collections: getIconCollections("all"),
        // and the more recommended way is to use `dynamicIconsPlugin`, see below.
      }),
  ],
}