<template>
  <div class="min-vh-100 d-flex align-items-center justify-content-center bg-primary bg-gradient p-3">
    <div class="card shadow-lg border-0" style="max-width:420px;width:100%">
      <div class="card-body p-4 p-md-5">
        <h1 class="h4 fw-bold text-primary">Staff Login</h1>
        <p class="text-muted small">M/s Bhagya Laxmi · Mohali, Chandigarh</p>

        <form class="mt-4" @submit.prevent="submit">
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input v-model="form.login" type="text" class="form-control" required />
          </div>
          <div class="mb-3">
            <label class="form-label">Password</label>
            <input v-model="form.password" type="password" class="form-control" required />
          </div>
          <div v-if="error" class="alert alert-danger py-2 small">{{ error }}</div>
          <button type="submit" class="btn btn-primary w-100" :disabled="loading">
            {{ loading ? 'Signing in...' : 'Login' }}
          </button>
        </form>

        <p class="text-center text-muted small mt-4 mb-0">
          Public student form (no login):
          <NuxtLink to="/register" class="text-decoration-none">Registration</NuxtLink>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { parseApiError } from '~/composables/useApiHelpers'

definePageMeta({ layout: 'blank' })

const { apiFetch } = useApi()
const { setSession } = useAuth()

const form = reactive({ login: 'admin@bhagyalaxmi.local', password: 'password' })
const loading = ref(false)
const error = ref('')

const submit = async () => {
  loading.value = true
  error.value = ''
  try {
    const res = await apiFetch<{
      success: boolean
      data: { token: string; user: Record<string, unknown> }
    }>('/auth/login', { method: 'POST', body: form })

    if (!['super_admin', 'admin', 'college_coordinator'].includes(res.data.user.role as string)) {
      error.value = 'Staff access only.'
      return
    }

    setSession({ token: res.data.token, user: res.data.user })
    const { can, user } = useAuth()
    const role = user.value?.role as string
    const isAdmin = role === 'super_admin' || role === 'admin'
    if (!isAdmin && can('staff_entry') && !can('student_view')) {
      navigateTo('/entry')
    } else {
      navigateTo('/dashboard')
    }
  } catch (e: unknown) {
    error.value = parseApiError(e)
  } finally {
    loading.value = false
  }
}
</script>
