<template>
  <!-- Mobile overlay -->
  <div
    v-if="mobileOpen"
    class="fixed inset-0 z-40 bg-black/50 lg:hidden"
    @click="mobileOpen = false"
  />

  <aside
    class="fixed inset-y-0 left-0 z-50 w-64 max-w-[85vw] bg-blue-900 text-white shadow-lg transform transition-transform duration-200 lg:translate-x-0"
    :class="mobileOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
  >
    <div class="p-5 border-b border-blue-800 flex justify-between items-start">
      <div>
        <h1 class="text-lg font-bold leading-tight">Bhagya Laxmi</h1>
        <p class="text-xs text-blue-200 mt-1">Internship Admin</p>
      </div>
      <button type="button" class="lg:hidden p-1 rounded hover:bg-blue-800" @click="mobileOpen = false">✕</button>
    </div>
    <nav class="p-3 space-y-0.5 overflow-y-auto max-h-[calc(100vh-8rem)]">
      <NuxtLink
        v-for="item in links"
        :key="item.to"
        :to="item.to"
        no-prefetch
        class="block px-3 py-2.5 rounded-lg hover:bg-blue-800 transition text-sm"
        active-class="bg-blue-700"
        @click="mobileOpen = false"
      >
        {{ item.label }}
      </NuxtLink>
    </nav>
    <button
      class="absolute bottom-0 left-0 right-0 m-3 py-2.5 bg-blue-700 rounded-lg text-sm font-medium"
      @click="logout"
    >
      Logout
    </button>
  </aside>
</template>

<script setup lang="ts">
const mobileOpen = defineModel<boolean>('mobileOpen', { default: false })

const { clearSession } = useAuth()
const { apiFetch } = useApi()

const links = [
  { to: '/dashboard', label: 'Dashboard' },
  { to: '/students', label: 'Students' },
  { to: '/groups', label: 'Internship Groups' },
  { to: '/whatsapp', label: 'WhatsApp' },
  { to: '/reports', label: 'Reports' },
  { to: '/certificates', label: 'Certificates' },
  { to: '/notifications', label: 'Notifications' },
  { to: '/settings', label: 'Settings' },
]

const logout = async () => {
  try {
    await apiFetch('/auth/logout', { method: 'POST' })
  } catch {}
  clearSession()
  navigateTo('/login')
}
</script>
