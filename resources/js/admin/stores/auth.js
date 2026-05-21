import { defineStore } from 'pinia'
import { computed, ref } from 'vue'

const TOKEN_KEY = 'bli_token'
const USER_KEY = 'bli_user_cache'

export const useAuthStore = defineStore('auth', () => {
  const token = ref(localStorage.getItem(TOKEN_KEY) || '')
  const user = ref(parseUser(localStorage.getItem(USER_KEY)))

  function parseUser(raw) {
    if (!raw) return null
    try {
      return JSON.parse(raw)
    } catch {
      return null
    }
  }

  const isStaff = computed(() =>
    ['super_admin', 'admin', 'college_coordinator'].includes(user.value?.role || '')
  )

  const isAdmin = computed(() =>
    ['super_admin', 'admin'].includes(user.value?.role || '')
  )

  const permissions = computed(() => user.value?.permissions || {})

  function can(key) {
    if (isAdmin.value) return true
    return !!permissions.value[key]
  }

  function setSession(payload) {
    token.value = payload.token
    user.value = payload.user
    localStorage.setItem(TOKEN_KEY, payload.token)
    localStorage.setItem(USER_KEY, JSON.stringify(payload.user))
  }

  function clearSession() {
    token.value = ''
    user.value = null
    localStorage.removeItem(TOKEN_KEY)
    localStorage.removeItem(USER_KEY)
  }

  return { token, user, isStaff, isAdmin, permissions, can, setSession, clearSession }
})
