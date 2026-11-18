import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

export default defineConfig({
  plugins: [react()],
  preview: {
    host: '0.0.0.0',
    port: Number(process.env.PORT) || 4173,
    allowedHosts: ['.railway.app', 'projcontinnum.com.br', 'localhost', '127.0.0.1'],
    strictPort: false,
  },
})