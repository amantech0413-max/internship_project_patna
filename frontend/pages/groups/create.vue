<template>
  <div class="p-6 lg:p-8 max-w-2xl">
    <h2 class="text-2xl font-bold">Add Internship Group</h2>
    <p class="text-sm text-slate-500 mt-1">Create WhatsApp group externally, then paste invite link here.</p>

    <form class="mt-6 bg-white rounded-xl border p-6 space-y-4" @submit.prevent="submit">
      <input v-model="form.name" required placeholder="Group Name *" class="w-full border rounded-lg px-3 py-2" />
      <div class="grid grid-cols-2 gap-4">
        <input v-model="form.semester" placeholder="Semester" class="border rounded-lg px-3 py-2" />
        <input v-model="form.subject" placeholder="Subject" class="border rounded-lg px-3 py-2" />
      </div>
      <input v-model="form.college_name" placeholder="College Name" class="w-full border rounded-lg px-3 py-2" />
      <select v-model="form.internship_mode" required class="w-full border rounded-lg px-3 py-2">
        <option value="online">Online Internship</option>
        <option value="offline">Offline Internship</option>
      </select>
      <input
        v-model="form.whatsapp_group_link"
        placeholder="WhatsApp Group Invite Link (paste from WhatsApp)"
        class="w-full border rounded-lg px-3 py-2"
      />
      <div class="grid grid-cols-2 gap-4">
        <input v-model="form.start_date" type="date" class="border rounded-lg px-3 py-2" />
        <input v-model="form.end_date" type="date" class="border rounded-lg px-3 py-2" />
      </div>
      <select v-model="form.status" class="w-full border rounded-lg px-3 py-2">
        <option value="active">Active</option>
        <option value="completed">Completed</option>
        <option value="inactive">Inactive</option>
      </select>
      <div class="flex gap-3">
        <button type="submit" class="px-4 py-2 bg-blue-700 text-white rounded-lg">Save Group</button>
        <NuxtLink to="/groups" class="px-4 py-2 border rounded-lg">Cancel</NuxtLink>
      </div>
    </form>
  </div>
</template>

<script setup lang="ts">
const { apiFetch } = useApi()
const form = reactive({
  name: '',
  semester: '',
  subject: '',
  college_name: '',
  internship_mode: 'online',
  whatsapp_group_link: '',
  start_date: '',
  end_date: '',
  status: 'active',
})

const submit = async () => {
  await apiFetch('/admin/groups', { method: 'POST', body: form })
  navigateTo('/groups')
}
</script>
