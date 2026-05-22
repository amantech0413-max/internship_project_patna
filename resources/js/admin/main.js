import { createApp } from 'vue'
import { createPinia } from 'pinia'
import router from './router'
import App from './App.vue'
import DataTable from '@/components/DataTable.vue'
import PaginationBar from '@/components/PaginationBar.vue'
import ListToolbar from '@/components/ListToolbar.vue'
import { useAuthStore } from './stores/auth'
import { apiFetch } from './api/client'
import 'bootstrap/dist/js/bootstrap.bundle.min.js'
import '@/utils/jquerySetup'

const app = createApp(App)
const pinia = createPinia()
app.use(pinia)
app.use(router)
app.component('DataTable', DataTable)
app.component('PaginationBar', PaginationBar)
app.component('ListToolbar', ListToolbar)

const auth = useAuthStore()
if (auth.token) {
  Promise.all([
    apiFetch('/auth/me')
      .then((res) => {
        if (res?.data) {
          auth.setSession({ token: auth.token, user: res.data })
        }
      })
      .catch(() => {
        /* keep cached session; do not logout on transient /auth/me failure */
      }),
    auth.loadAccess(),
  ])
}

app.mount('#admin-app')
