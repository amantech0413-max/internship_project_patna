<template>
  <div class="p-6 space-y-6">
    <h2 class="text-2xl font-bold">My Internship Group</h2>
    <div v-if="loading" class="text-slate-500">Loading...</div>
    <template v-else>
      <div v-if="groupData?.group" class="bg-white rounded-xl border p-6">
        <h3 class="text-lg font-semibold">{{ (groupData.group as Record<string,string>).name }}</h3>
        <p class="text-sm text-slate-500 mt-2 capitalize">Mode: {{ groupData.internship_mode }}</p>
        <p class="text-sm mt-1">Semester: {{ (groupData.group as Record<string,string>).semester || '—' }}</p>
      </div>
      <p v-else class="text-slate-600">Abhi koi group assign nahi hua.</p>

      <div v-if="whatsapp?.deep_link" class="bg-green-50 border border-green-200 rounded-xl p-6">
        <p class="text-sm text-green-800 mb-3">WhatsApp group mein manually join karein:</p>
        <a
          :href="whatsapp.deep_link as string"
          target="_blank"
          rel="noopener"
          class="inline-block px-6 py-3 bg-green-600 text-white rounded-lg font-semibold"
        >
          Join WhatsApp Group
        </a>
      </div>
    </template>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ layout: 'student' })

const { apiFetch } = useApi()
const groupData = ref<Record<string, unknown> | null>(null)
const whatsapp = ref<Record<string, unknown> | null>(null)
const loading = ref(true)

onMounted(async () => {
  const [g, w] = await Promise.all([
    apiFetch<{ success: boolean; data: Record<string, unknown> }>('/student/group'),
    apiFetch<{ success: boolean; data: Record<string, unknown> }>('/student/whatsapp'),
  ])
  groupData.value = g.data
  whatsapp.value = w.data
  loading.value = false
})
</script>
