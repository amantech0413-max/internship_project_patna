export default defineNuxtConfig({
  compatibilityDate: '2025-05-21',
  devtools: { enabled: true },
  modules: ['@nuxtjs/tailwindcss'],
  tailwindcss: {
    config: {
      corePlugins: {
        preflight: false,
      },
    },
  },
  css: [
    'bootstrap/dist/css/bootstrap.min.css',
    'bootstrap-icons/font/bootstrap-icons.css',
    '~/assets/css/app.css',
  ],
  runtimeConfig: {
    public: {
      apiBase: process.env.NUXT_PUBLIC_API_BASE || 'http://127.0.0.1:8000/api/v1',
      appName: 'M/s Bhagya Laxmi Internship Management',
    },
  },
  app: {
    head: {
      title: 'BLI Admin Panel',
      meta: [
        { name: 'description', content: 'M/s Bhagya Laxmi — Mohali, Chandigarh' },
        { name: 'viewport', content: 'width=device-width, initial-scale=1' },
      ],
    },
  },
})
