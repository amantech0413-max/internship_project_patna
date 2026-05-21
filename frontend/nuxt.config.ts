export default defineNuxtConfig({
  compatibilityDate: '2025-05-21',
  devtools: { enabled: process.env.NODE_ENV !== 'production' },
  ssr: false,
  nitro: {
    preset: 'static',
  },
  app: {
    // Must match folder: public/admin/ → URLs like https://domain.com/admin/login
    baseURL: process.env.NUXT_APP_BASE_URL || '/admin/',
    buildAssetsDir: '_nuxt',
    head: {
      title: 'BLI Admin Panel',
      meta: [
        { name: 'description', content: 'M/s Bhagya Laxmi — Mohali, Chandigarh' },
        { name: 'viewport', content: 'width=device-width, initial-scale=1' },
      ],
    },
  },
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
})
