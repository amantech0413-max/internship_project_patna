<template>
  <div class="p-6 lg:p-8 max-w-xl">
    <h2 class="text-2xl font-bold">Notifications</h2>
    <form class="mt-4 bg-white p-4 rounded-xl border space-y-3" @submit.prevent="broadcast">
      <input v-model="form.title" required placeholder="Title" class="w-full border rounded-lg px-3 py-2" />
      <textarea v-model="form.message" required placeholder="Message" class="w-full border rounded-lg px-3 py-2" rows="4" />
      <select v-model="form.role" class="w-full border rounded-lg px-3 py-2">
        <option value="">All Users</option>
        <option value="student">Students Only</option>
      </select>
      <button type="submit" class="px-4 py-2 bg-blue-700 text-white rounded-lg">Broadcast</button>
    </form>
  </div>
</template>

<script setup lang="ts">
const { apiFetch } = useApi()
const form = reactive({ title: '', message: '', role: 'student' })

const broadcast = async () => {
  await apiFetch('/admin/notifications/broadcast', { method: 'POST', body: form })
  alert('Broadcast queued')
}
</script>
