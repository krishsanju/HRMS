import { defineConfig } from 'vitest/config';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
  plugins: [vue()],
  test: {
    globals: true,
    environment: 'jsdom',
    coverage: {
      provider: 'v8',
      reporter: ['text', 'json', 'html'],
      reportsDirectory: './build/coverage/frontend',
      include: ['resources/js/**/*.{js,vue}'],
      exclude: [
        'resources/js/main.js',
        'resources/js/app.js',
        'resources/js/bootstrap.js',
        'resources/js/router/index.js',
        'resources/js/api/index.js',
        'resources/js/store/index.js',
      ],
    },
  },
});