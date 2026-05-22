<template>
  <div class="admin-wrapper">
    <aside class="admin-sidebar-col" :class="{ 'mobile-open': menuOpen }">
      <AdminSidebar @navigate="menuOpen = false" />
    </aside>
    <div v-if="menuOpen" class="admin-overlay d-lg-none" @click="menuOpen = false" />
    <main class="admin-main">
      <div class="d-flex justify-content-between align-items-center mb-3 mb-md-4 gap-2">
        <div class="d-flex align-items-center gap-2 min-w-0">
          <button type="button" class="btn btn-outline-primary d-lg-none flex-shrink-0" aria-label="Open menu" @click="menuOpen = true">
            <i class="bi bi-list fs-5" />
          </button>
          <h1 class="h5 page-title mb-0 text-truncate">{{ title }}</h1>
        </div>
        <span class="text-muted small d-none d-md-inline">M/s Bhagya Laxmi · Mohali</span>
      </div>
      <router-view />
    </main>
  </div>
</template>

<script setup>
import { computed, ref, watch, onUnmounted } from 'vue'
import { useRoute } from 'vue-router'
import AdminSidebar from '@/components/AdminSidebar.vue'

const route = useRoute()
const menuOpen = ref(false)

const titles = {
  dashboard: 'Dashboard',
  colleges: 'Colleges',
  entry: 'Add Student',
  'import-logs': 'Import Logs',
  students: 'Internship Students',
  'student-create': 'Add Student',
  'student-edit': 'Edit Student',
  groups: 'Internship Groups',
  'group-create': 'Create Group',
  'group-edit': 'Group',
  whatsapp: 'WhatsApp',
  reports: 'Reports',
  certificates: 'Certificates',
  notifications: 'Notifications',
  settings: 'Settings',
  roles: 'Roles & Permissions',
  'staff-users': 'Staff Users',
  bin: 'Recycle Bin',
}

const title = computed(() => titles[route.name] || 'Admin Panel')

watch(menuOpen, (open) => document.body.classList.toggle('admin-menu-open', open))
watch(() => route.path, () => { menuOpen.value = false })
onUnmounted(() => document.body.classList.remove('admin-menu-open'))
</script>
