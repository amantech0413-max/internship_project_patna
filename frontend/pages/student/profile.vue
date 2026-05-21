<template>
  <div class="p-6 max-w-2xl">
    <h2 class="text-2xl font-bold mb-4">My Profile</h2>
    <div class="bg-white rounded-xl border p-6 space-y-4">
      <FormField label="Name" v-model="form.name" />
      <FormField label="Father's Name" v-model="form.father_name" />
      <FormField label="Mobile" v-model="form.mobile" maxlength="10" />
      <FormField label="Email" v-model="form.email" type="email" />
      <FormField label="Address" v-model="form.address" />
      <FormField label="College" v-model="form.college_name" />
      <FormField label="Subject" v-model="form.subject" />
      <FormField label="Semester" v-model="form.semester" />
      <div>
        <label class="text-sm font-medium">Photo</label>
        <input type="file" accept="image/*" class="mt-1 w-full border rounded-lg px-3 py-2 text-sm" @change="photo = ($event.target as HTMLInputElement).files?.[0] || null" />
      </div>
      <p v-if="msg" class="text-sm text-green-700">{{ msg }}</p>
      <p v-if="err" class="text-sm text-red-600">{{ err }}</p>
      <button class="px-4 py-2 bg-emerald-700 text-white rounded-lg" @click="save">Save Profile</button>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ layout: 'student' })

const { apiFetch, apiForm } = useApi()
const form = reactive({ name: '', father_name: '', mobile: '', email: '', address: '', college_name: '', subject: '', semester: '' })
const photo = ref<File | null>(null)
const msg = ref('')
const err = ref('')

onMounted(async () => {
  const res = await apiFetch<{ success: boolean; data: Record<string, unknown> }>('/student/profile')
  const s = res.data
  Object.assign(form, {
    name: s.name, father_name: s.father_name, mobile: s.mobile, email: s.email || '',
    address: s.address || '', college_name: s.college_name || '', subject: s.subject || '', semester: s.semester || '',
  })
})

const save = async () => {
  const fd = new FormData()
  Object.entries(form).forEach(([k, v]) => v && fd.append(k, v))
  if (photo.value) fd.append('photo', photo.value)
  try {
    await apiForm('/student/profile/update', fd)
    msg.value = 'Profile updated'
    err.value = ''
  } catch (e: unknown) {
    err.value = (e as { data?: { message?: string } })?.data?.message || 'Failed'
  }
}
</script>
