<template>
  <div class="p-6 space-y-6">
    <h2 class="text-2xl font-bold">My Internship</h2>

    <div v-if="loading" class="text-slate-500">Loading...</div>
    <template v-else>
      <div class="grid sm:grid-cols-2 gap-4">
        <div class="bg-white rounded-xl border p-5">
          <p class="text-sm text-slate-500">Status</p>
          <p class="text-xl font-bold capitalize text-emerald-700">{{ data?.status || '—' }}</p>
        </div>
        <div class="bg-white rounded-xl border p-5">
          <p class="text-sm text-slate-500">Internship Mode</p>
          <p class="text-xl font-bold capitalize">{{ data?.internship_mode || '—' }}</p>
        </div>
      </div>

      <div v-if="data?.student" class="bg-white rounded-xl border p-5 text-sm space-y-1">
        <p><strong>Student Code:</strong> {{ (data.student as Record<string,string>).student_code }}</p>
        <p><strong>College:</strong> {{ (data.student as Record<string,string>).college_name || '—' }}</p>
        <p><strong>Semester:</strong> {{ (data.student as Record<string,string>).semester || '—' }}</p>
      </div>

      <div class="flex flex-wrap gap-3">
        <NuxtLink to="/student/group" class="px-4 py-2 bg-emerald-700 text-white rounded-lg text-sm">View Group & WhatsApp</NuxtLink>
        <NuxtLink to="/student/profile" class="px-4 py-2 border rounded-lg text-sm">Edit Profile</NuxtLink>
      </div>
    </template>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ layout: 'student' })

const { apiFetch } = useApi()
const data = ref<Record<string, unknown> | null>(null)
const loading = ref(true)

onMounted(async () => {
  const res = await apiFetch<{ success: boolean; data: Record<string, unknown> }>('/student/internship')
  data.value = res.data
  loading.value = false
})
</script>
