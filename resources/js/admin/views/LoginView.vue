<template>
  <div class="min-vh-100 d-flex align-items-center justify-content-center bg-primary bg-gradient p-3">
    <div class="card shadow-lg border-0" style="max-width:420px;width:100%">
      <div class="card-body p-4 p-md-5">
        <h1 class="h4 fw-bold text-primary">Staff Login</h1>
        <p class="text-muted small">M/s Bhagya Laxmi · Mohali, Chandigarh</p>
        <form class="mt-4" @submit.prevent="submit">
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input v-model="form.login" type="email" class="form-control" required autocomplete="username" placeholder="Enter your email" />
          </div>
          <div class="mb-3">
            <label class="form-label">Password</label>
            <input v-model="form.password" type="password" class="form-control" required autocomplete="current-password" placeholder="Enter your password" />
          </div>
          <div v-if="error" class="alert alert-danger py-2 small">{{ error }}</div>
          <button type="submit" class="btn btn-primary w-100" :disabled="loading">{{ loading ? 'Signing in...' : 'Login' }}</button>
        </form>
        <p class="text-center text-muted small mt-4 mb-0">
          Public student form:
          <router-link to="/register" class="text-decoration-none">Registration</router-link>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { apiFetch } from '@/api/client'
import { parseApiError } from '@/utils/apiHelpers'

const router = useRouter()
const auth = useAuthStore()
const form = reactive({ login: '', password: '' })
const loading = ref(false)
const error = ref('')

const submit = async () => {
  loading.value = true
  error.value = ''
  try {
    const res = await apiFetch('/auth/login', { method: 'POST', body: form })
    if (!res.data.user?.can_access_panel) {
      error.value = 'Staff access only.'
      return
    }
    auth.setSession({ token: res.data.token, user: res.data.user })
    try {
      const me = await apiFetch('/auth/me')
      auth.setSession({ token: res.data.token, user: me.data })
    } catch {
      /* use login payload */
    }
    await auth.loadAccess()
    if (!auth.isAdmin && auth.can('staff_entry') && !auth.can('student_view')) {
      router.push('/entry')
    } else {
      router.push('/dashboard')
    }
  } catch (e) {
    error.value = parseApiError(e)
  } finally {
    loading.value = false
  }
}
</script>
