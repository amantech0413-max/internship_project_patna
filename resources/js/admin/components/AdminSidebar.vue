<template>
  <div class="sidebar p-4 text-white h-100 d-flex flex-column">
    <div class="mb-4">
      <h2 class="h5 fw-bold mb-1">Bhagya Laxmi</h2>
      <p class="small text-white-50 mb-0">Internship Management</p>
    </div>
    <nav class="nav admin-nav flex-column flex-grow-1 overflow-y-auto pb-2">
      <template v-for="section in visibleSections" :key="section.title">
        <p class="small text-white-50 text-uppercase mt-3 mb-1 px-2">{{ section.title }}</p>
        <router-link
          v-for="item in section.items"
          :key="item.to"
          :to="item.to"
          class="nav-link"
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
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { apiFetch } from '@/api/client'

defineEmits(['navigate'])

const router = useRouter()
const auth = useAuthStore()

const menuSections = [
  { title: 'Main', items: [{ to: '/dashboard', label: 'Dashboard', icon: 'bi-speedometer2' }] },
  {
    title: 'College & Entry',
    items: [
      { to: '/colleges', label: 'Colleges', icon: 'bi-building', permission: 'college_manage' },
      { to: '/entry', label: 'Add Student', icon: 'bi-person-plus', permission: 'staff_entry' },
      { to: '/student-entries', label: 'Entry Records', icon: 'bi-journal-text', permission: 'staff_entry' },
      { to: '/import-logs', label: 'Import Logs', icon: 'bi-file-earmark-spreadsheet', permission: 'staff_entry' },
    ],
  },
  { title: 'Students', items: [{ to: '/students', label: 'Students', icon: 'bi-people', permission: 'student_view' }] },
  {
    title: 'Internship (Full)',
    items: [
      { to: '/groups', label: 'Internship Groups', icon: 'bi-collection', adminOnly: true },
      { to: '/whatsapp', label: 'WhatsApp', icon: 'bi-whatsapp', adminOnly: true },
      { to: '/reports', label: 'Reports', icon: 'bi-bar-chart', adminOnly: true },
      { to: '/certificates', label: 'Certificates', icon: 'bi-award', adminOnly: true },
      { to: '/notifications', label: 'Notifications', icon: 'bi-bell', adminOnly: true },
      { to: '/settings', label: 'Settings', icon: 'bi-gear', adminOnly: true },
    ],
  },
  { title: 'Administration', items: [{ to: '/staff-users', label: 'Staff Users', icon: 'bi-person-badge', permission: 'staff_manage' }] },
]

const itemVisible = (item) => {
  if (item.adminOnly && !auth.isAdmin) return false
  if (item.permission) return auth.can(item.permission)
  return true
}

const visibleSections = computed(() =>
  menuSections.map((s) => ({ ...s, items: s.items.filter(itemVisible) })).filter((s) => s.items.length)
)

const logout = async () => {
  try {
    await apiFetch('/auth/logout', { method: 'POST' })
  } catch {}
  auth.clearSession()
  router.push('/login')
}
</script>
