import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

// https://vite.dev/config/
export default defineConfig({
  plugins: [vue()],
  server: {
    host: '127.0.0.1',  // Force IPv4
    port: 5174,          // Use safe port
    strictPort: true     // Fail if port is unavailable
  }
})
