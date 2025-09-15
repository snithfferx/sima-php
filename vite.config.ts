import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite'

export default defineConfig({
  plugins: [
    laravel({
      input: [
          './resources/css/global.css',
          './resources/js/global.ts'
      ],
      refresh: true,
      hotFile: 'cache/vite/vite.hot',
    }),
    tailwindcss(),
  ],
  build: {
    manifest: true,
  },
});
// server: {
//     origin: 'http://localhost',
// },
