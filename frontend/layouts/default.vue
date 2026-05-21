<template>
  <div v-if="showShell" class="admin-wrapper">
    <aside class="admin-sidebar-col" :class="{ 'mobile-open': menuOpen }">
      <AdminSidebar @navigate="closeMenu" />
    </aside>
    <div v-if="menuOpen" class="admin-overlay d-lg-none" aria-hidden="true" @click="closeMenu" />

    <main class="admin-main">
      <div class="d-flex justify-content-between align-items-center mb-3 mb-md-4 gap-2">
        <div class="d-flex align-items-center gap-2 min-w-0">
          <button
            type="button"
            class="btn btn-outline-primary d-lg-none flex-shrink-0"
            aria-label="Open menu"
            @click="menuOpen = true"
          >
            <i class="bi bi-list fs-5" />
          </button>
          <h1 class="h5 h4-md page-title mb-0 text-truncate">{{ pageTitle }}</h1>
        </div>
        <span class="text-muted small d-none d-md-inline flex-shrink-0">M/s Bhagya Laxmi · Mohali</span>
      </div>
      <slot />
    </main>
  </div>
  <div v-else>
    <slot />
  </div>
</template>

<script setup lang="ts">
const route = useRoute()
const { token, isStaff } = useAuth()
const menuOpen = ref(false)

const showShell = computed(() => !!token.value && isStaff.value)

const pageTitle = computed(() => {
  if (route.path.startsWith('/students/')) return 'Student'
  if (route.path.startsWith('/groups/')) return 'Group'
  const map: Record<string, string> = {
    '/dashboard': 'Dashboard',
    '/colleges': 'Colleges',
    '/entry': 'Add Student (Staff)',
    '/student-entries': 'Entry Records',
    '/import-logs': 'Import Logs',
    '/students': 'Internship Students',
    '/groups': 'Internship Groups',
    '/whatsapp': 'WhatsApp',
    '/reports': 'Reports',
    '/certificates': 'Certificates',
    '/notifications': 'Notifications',
    '/settings': 'Settings',
    '/staff-users': 'Staff Users',
  }
  return map[route.path] || 'Admin Panel'
})

const closeMenu = () => {
  menuOpen.value = false
}

watch(menuOpen, (open) => {
  if (import.meta.client) {
    document.body.classList.toggle('admin-menu-open', open)
  }
})

watch(() => route.path, () => {
  closeMenu()
})

onUnmounted(() => {
  if (import.meta.client) {
    document.body.classList.remove('admin-menu-open')
  }
})
</script>
