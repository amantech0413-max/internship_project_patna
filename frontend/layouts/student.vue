<template>
  <div class="min-h-screen bg-slate-50">
    <header class="bg-emerald-800 text-white shadow">
      <div class="max-w-5xl mx-auto px-4 py-4 flex flex-wrap justify-between items-center gap-3">
        <div>
          <h1 class="font-bold">Student Portal</h1>
          <p class="text-emerald-200 text-xs">{{ studentName }} · {{ studentCode }}</p>
        </div>
        <nav class="flex flex-wrap gap-2 text-sm">
          <NuxtLink to="/student/dashboard" class="px-3 py-1.5 rounded-lg hover:bg-emerald-700" active-class="bg-emerald-700">Dashboard</NuxtLink>
          <NuxtLink to="/student/profile" class="px-3 py-1.5 rounded-lg hover:bg-emerald-700" active-class="bg-emerald-700">Profile</NuxtLink>
          <NuxtLink to="/student/group" class="px-3 py-1.5 rounded-lg hover:bg-emerald-700" active-class="bg-emerald-700">Group</NuxtLink>
          <NuxtLink to="/student/reports" class="px-3 py-1.5 rounded-lg hover:bg-emerald-700" active-class="bg-emerald-700">Reports</NuxtLink>
          <NuxtLink to="/student/assignments" class="px-3 py-1.5 rounded-lg hover:bg-emerald-700" active-class="bg-emerald-700">Tasks</NuxtLink>
          <button class="px-3 py-1.5 rounded-lg border border-emerald-600" @click="logout">Logout</button>
        </nav>
      </div>
    </header>
    <main class="max-w-5xl mx-auto">
      <slot />
    </main>
  </div>
</template>

<script setup lang="ts">
const { user, clearSession, studentProfile } = useAuth()
const { apiFetch } = useApi()

const studentName = computed(() => String(studentProfile.value?.name || user.value?.name || ''))
const studentCode = computed(() => String(studentProfile.value?.student_code || ''))

const logout = async () => {
  try { await apiFetch('/auth/logout', { method: 'POST' }) } catch {}
  clearSession()
  navigateTo('/student-login')
}
</script>
