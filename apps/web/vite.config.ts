import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import tailwindcss from '@tailwindcss/vite';

// Base relativo para servir desde apps/web/dist/ (XAMPP) o public/clinica/ tras sync.
export default defineConfig({
  plugins: [react(), tailwindcss()],
  base: './',
  server: {
    port: 5173,
    strictPort: false,
    host: '127.0.0.1',
    open: '/',
  },
  build: {
    outDir: 'dist',
    emptyOutDir: true,
  },
});
