import { createApp } from 'vue'
import { createPinia } from 'pinia'
import router from './router'
import App from './App.vue'
import { useAuthStore } from './stores/auth'
import { apiFetch } from './api/client'
import 'bootstrap/dist/js/bootstrap.bundle.min.js'

const app = createApp(App)
const pinia = createPinia()
app.use(pinia)
app.use(router)

const auth = useAuthStore()
if (auth.token) {
  apiFetch('/auth/me')
    .then((res) => auth.setSession({ token: auth.token, user: res.data }))
    .catch(() => auth.clearSession())
}

app.mount('#admin-app')
