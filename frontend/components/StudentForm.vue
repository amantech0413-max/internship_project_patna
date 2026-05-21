<template>
  <form class="space-y-6" @submit.prevent="$emit('submit')">
    <section class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <h3 class="sm:col-span-2 text-sm font-semibold text-slate-700 uppercase tracking-wide border-b pb-2">Personal</h3>
      <FormField label="Student Name *" :model-value="form.name" required @update:model-value="set('name', $event)" />
      <FormField label="Father's Name" :model-value="form.father_name" @update:model-value="set('father_name', $event)" />
      <FormField label="Mobile *" :model-value="form.mobile" required maxlength="10" @update:model-value="set('mobile', $event)" />
      <FormField label="Email" :model-value="form.email" type="email" @update:model-value="set('email', $event)" />
      <FormField label="Address" :model-value="form.address" class="sm:col-span-2" @update:model-value="set('address', $event)" />
      <p class="sm:col-span-2 text-xs text-slate-500">Same mobile allowed for siblings.</p>
    </section>

    <section class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <h3 class="sm:col-span-2 text-sm font-semibold text-slate-700 uppercase tracking-wide border-b pb-2">College</h3>
      <FormField label="College Name" :model-value="form.college_name" @update:model-value="set('college_name', $event)" />
      <FormField label="Subject" :model-value="form.subject" @update:model-value="set('subject', $event)" />
      <FormField label="Semester" :model-value="form.semester" @update:model-value="set('semester', $event)" />
      <FormField label="University Roll No" :model-value="form.university_roll_no" @update:model-value="set('university_roll_no', $event)" />
      <FormField label="College Roll No" :model-value="form.college_roll_no" @update:model-value="set('college_roll_no', $event)" />
    </section>

    <section class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <h3 class="sm:col-span-2 text-sm font-semibold text-slate-700 uppercase tracking-wide border-b pb-2">Internship</h3>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Mode *</label>
        <select v-model="form.internship_mode" required class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-base">
          <option value="online">Online</option>
          <option value="offline">Offline</option>
        </select>
      </div>
      <div v-if="showStatus">
        <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
        <select v-model="form.status" class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-base">
          <option value="pending">Pending</option>
          <option value="approved">Approved</option>
          <option value="rejected">Rejected</option>
          <option value="completed">Completed</option>
        </select>
      </div>
    </section>

    <section class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <h3 class="sm:col-span-2 text-sm font-semibold text-slate-700 uppercase tracking-wide border-b pb-2">Documents</h3>
      <div>
        <label class="block text-sm font-medium mb-1">Photo</label>
        <input type="file" accept="image/*" class="w-full text-sm border rounded-lg px-3 py-2" @change="onPhoto" />
        <img v-if="photoPreviewUrl" :src="photoPreviewUrl" alt="" class="mt-2 h-20 w-20 object-cover rounded-lg border" />
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">ID Proof</label>
        <input type="file" accept="image/*,.pdf" class="w-full text-sm border rounded-lg px-3 py-2" @change="onIdProof" />
      </div>
    </section>

    <p class="text-xs text-slate-500 sm:col-span-2">Student code is generated automatically. Students do not get login credentials.</p>

    <div v-if="showRejection && form.status === 'rejected'" class="sm:col-span-2">
      <label class="block text-sm font-medium mb-1">Rejection Reason</label>
      <textarea v-model="form.rejection_reason" rows="2" class="w-full border rounded-lg px-3 py-2 text-base" />
    </div>

    <slot name="actions" />
  </form>
</template>

<script setup lang="ts">
const props = defineProps<{
  form: Record<string, string>
  showStatus?: boolean
  showRejection?: boolean
  photoPreviewUrl?: string | null
}>()

const emit = defineEmits<{
  submit: []
  'update:form': [value: Record<string, string>]
  'photo-selected': [file: File | null]
  'id-proof-selected': [file: File | null]
}>()

const form = computed({
  get: () => props.form,
  set: (v) => emit('update:form', v),
})

const set = (key: string, value: string) => {
  emit('update:form', { ...props.form, [key]: value })
}

const onPhoto = (e: Event) => {
  emit('photo-selected', (e.target as HTMLInputElement).files?.[0] ?? null)
}

const onIdProof = (e: Event) => {
  emit('id-proof-selected', (e.target as HTMLInputElement).files?.[0] ?? null)
}
</script>
