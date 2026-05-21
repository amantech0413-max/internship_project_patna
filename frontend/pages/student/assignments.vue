<template>
  <div class="p-6 space-y-6">
    <h2 class="text-2xl font-bold">Assignments / Tasks</h2>

    <form class="bg-white rounded-xl border p-5 space-y-3 max-w-lg" @submit.prevent="submit">
      <h3 class="font-semibold">Upload Task</h3>
      <input v-model="task.title" required placeholder="Title *" class="w-full border rounded-lg px-3 py-2" />
      <textarea v-model="task.description" placeholder="Description" rows="2" class="w-full border rounded-lg px-3 py-2" />
      <input type="file" class="w-full text-sm border rounded-lg px-3 py-2" @change="file = ($event.target as HTMLInputElement).files?.[0] || null" />
      <button type="submit" class="px-4 py-2 bg-emerald-700 text-white rounded-lg">Submit</button>
    </form>

    <div class="bg-white rounded-xl border">
      <h3 class="font-semibold p-4 border-b">Submitted</h3>
      <ul class="divide-y text-sm">
        <li v-for="a in list" :key="a.id as number" class="p-4">
          <p class="font-medium">{{ a.title }}</p>
          <p class="text-slate-500 text-xs">{{ a.status }} · {{ a.submitted_at }}</p>
        </li>
      </ul>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ layout: 'student' })

const { apiFetch } = useApi()
const task = reactive({ title: '', description: '' })
const file = ref<File | null>(null)
const list = ref<Record<string, unknown>[]>([])

const load = async () => {
  const res = await apiFetch<{ success: boolean; data: Record<string, unknown>[] }>('/student/assignments')
  list.value = Array.isArray(res.data) ? res.data : (res.data as { data?: Record<string, unknown>[] })?.data || []
}

const submit = async () => {
  const fd = new FormData()
  fd.append('title', task.title)
  if (task.description) fd.append('description', task.description)
  if (file.value) fd.append('file', file.value)
  await apiFetch('/student/assignments', { method: 'POST', body: fd })
  task.title = ''
  task.description = ''
  await load()
}

onMounted(load)
</script>
