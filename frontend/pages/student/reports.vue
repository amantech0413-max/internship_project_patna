<template>
  <div class="p-6 space-y-6">
    <h2 class="text-2xl font-bold">Daily Reports</h2>

    <form class="bg-white rounded-xl border p-5 space-y-3 max-w-lg" @submit.prevent="submit">
      <h3 class="font-semibold">Submit Report</h3>
      <input v-model="report.report_date" type="date" required class="w-full border rounded-lg px-3 py-2" />
      <textarea v-model="report.work_summary" required placeholder="Work summary *" rows="3" class="w-full border rounded-lg px-3 py-2" />
      <textarea v-model="report.learnings" placeholder="Learnings (optional)" rows="2" class="w-full border rounded-lg px-3 py-2" />
      <button type="submit" class="px-4 py-2 bg-emerald-700 text-white rounded-lg">Submit</button>
    </form>

    <div class="bg-white rounded-xl border overflow-hidden">
      <h3 class="font-semibold p-4 border-b">Previous Reports</h3>
      <ul class="divide-y text-sm">
        <li v-for="r in list" :key="r.id as number" class="p-4">
          <p class="font-medium">{{ r.report_date }}</p>
          <p class="text-slate-600 mt-1">{{ r.work_summary }}</p>
        </li>
        <li v-if="!list.length" class="p-4 text-slate-500">No reports yet</li>
      </ul>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ layout: 'student' })

const { apiFetch } = useApi()
const report = reactive({ report_date: new Date().toISOString().slice(0, 10), work_summary: '', learnings: '' })
const list = ref<Record<string, unknown>[]>([])

const load = async () => {
  const res = await apiFetch<{ success: boolean; data: Record<string, unknown>[] }>('/student/daily-reports')
  list.value = Array.isArray(res.data) ? res.data : (res.data as { data?: Record<string, unknown>[] })?.data || []
}

const submit = async () => {
  await apiFetch('/student/daily-reports', { method: 'POST', body: report })
  report.work_summary = ''
  report.learnings = ''
  await load()
}

onMounted(load)
</script>
