<template>
  <div class="sidebar p-4 text-white h-100 d-flex flex-column">
    <div class="mb-4">
      <h2 class="h5 fw-bold mb-1">Bhagya Laxmi</h2>
      <p class="small text-white-50 mb-0">Internship Management</p>
    </div>
    <nav class="nav admin-nav flex-column flex-grow-1 pb-2">
      <template v-for="section in auth.visibleMenuSections" :key="section.title">
        <p class="small text-white-50 text-uppercase mt-3 mb-1 px-2">{{ section.title }}</p>
        <router-link
          v-for="item in section.items"
          :key="item.to"
          :to="item.to"
          class="nav-link"
          :class="{ active: isMenuActive(item) }"
          @click="$emit('navigate')"
        >
          <i :class="`bi ${item.icon} me-2`" />{{ item.label }}
        </router-link>
      </template>
    </nav>
    <button type="button" class="btn btn-light btn-sm mt-3" @click="logout">
      <i class="bi bi-box-arrow-right me-1" /> Logout
    </button>
  </div>
</template>

<script setup>
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { apiFetch } from '@/api/client'

defineEmits(['navigate'])

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()

/** Highlight parent menu for nested routes (e.g. student edit → Students). */
const isMenuActive = (item) => {
  const name = route.name
  const key = item.route

  if (key === 'students') {
    return name === 'students' || name === 'student-edit'
  }

  if (key) {
    return name === key
  }

  return route.path === item.to || route.path.startsWith(`${item.to}/`)
}

const logout = async () => {
  try {
    await apiFetch('/auth/logout', { method: 'POST' })
  } catch {}
  auth.clearSession()
  router.push('/login')
}
</script>
