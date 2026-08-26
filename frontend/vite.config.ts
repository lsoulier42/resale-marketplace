/// <reference types="vitest/config" />
import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

// https://vite.dev/config/
export default defineConfig({
  plugins: [react()],
  server: {
    port: 5173,
    proxy: {
      // API Symfony (app Docker sur :8081) — cookies de session partagés (même hôte)
      '/api': {
        target: 'http://localhost:8081',
        changeOrigin: true,
      },
      // Fichiers uploadés (public/uploads)
      '/uploads': {
        target: 'http://localhost:8081',
        changeOrigin: true,
      },
    },
  },
  test: {
    environment: 'jsdom',
    globals: true,
    setupFiles: './src/test/setup.ts',
    css: false,
  },
})
