<template>
  <div class="sidebar p-4 text-white h-100 d-flex flex-column">
    <div class="mb-4">
      <h2 class="h5 fw-bold mb-1">Bhagya Laxmi</h2>
      <p class="small text-white-50 mb-0">Internship Management</p>
    </div>

    <nav class="nav admin-nav flex-column flex-grow-1 overflow-y-auto pb-2">
      <template v-for="section in visibleSections" :key="section.title">
        <p class="small text-white-50 text-uppercase mt-3 mb-1 px-2">{{ section.title }}</p>
        <NuxtLink
          v-for="item in section.items"
          :key="item.to"
          :to="item.to"
          class="nav-link"
          active-class="active"
          no-prefetch
          @click="$emit('navigate')"
        >
          <i :class="`bi ${item.icon} me-2`" />{{ item.label }}
        </NuxtLink>
      </template>
    </nav>

    <button type="button" class="btn btn-light btn-sm mt-3" @click="logout">
      <i class="bi bi-box-arrow-right me-1" /> Logout
    </button>
  </div>
</template>

<script setup lang="ts">
import type { StaffPermissionKey } from '~/types/permissions'

defineEmits<{ navigate: [] }>()

const { clearSession, can } = useAuth()
const { isAdmin } = usePermissions()
const { apiFetch } = useApi()

type MenuItem = {
  to: string
  label: string
  icon: string
  permission?: StaffPermissionKey
  adminOnly?: boolean
}

type MenuSection = { title: string; items: MenuItem[] }

const menuSections: MenuSection[] = [
  {
    title: 'Main',
    items: [{ to: '/dashboard', label: 'Dashboard', icon: 'bi-speedometer2' }],
  },
  {
    title: 'College & Entry',
    items: [
      { to: '/colleges', label: 'Colleges', icon: 'bi-building', permission: 'college_manage' },
      { to: '/entry', label: 'Add Student', icon: 'bi-person-plus', permission: 'staff_entry' },
      { to: '/student-entries', label: 'Entry Records', icon: 'bi-journal-text', permission: 'staff_entry' },
      { to: '/import-logs', label: 'Import Logs', icon: 'bi-file-earmark-spreadsheet', permission: 'staff_entry' },
    ],
  },
  {
    title: 'Students',
    items: [
      { to: '/students', label: 'Students', icon: 'bi-people', permission: 'student_view' },
    ],
  },
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
  {
    title: 'Administration',
    items: [
      { to: '/staff-users', label: 'Staff Users', icon: 'bi-person-badge', permission: 'staff_manage' },
    ],
  },
]

const itemVisible = (item: MenuItem): boolean => {
  if (item.adminOnly && !isAdmin.value) return false
  if (item.permission) return can(item.permission)
  return true
}

const visibleSections = computed(() =>
  menuSections
    .map((section) => ({
      ...section,
      items: section.items.filter(itemVisible),
    }))
    .filter((section) => section.items.length > 0)
)

const logout = async () => {
  try {
    await apiFetch('/auth/logout', { method: 'POST' })
  } catch {}
  clearSession()
  navigateTo('/login')
}
</script>
