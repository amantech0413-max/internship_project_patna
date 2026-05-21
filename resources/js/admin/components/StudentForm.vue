<template>
  <form @submit.prevent="$emit('submit')">
    <h6 class="text-muted text-uppercase small border-bottom pb-2 mb-3">Personal</h6>
    <div class="row g-3 mb-4">
      <div class="col-md-6">
        <FormField label="Student Name *" :model-value="form.name" required :invalid="!!errors?.name" @update:model-value="set('name', $event)" />
      </div>
      <div class="col-md-6">
        <FormField label="Father's Name" :model-value="form.father_name" @update:model-value="set('father_name', $event)" />
      </div>
      <div class="col-md-6">
        <FormField label="Mobile *" :model-value="form.mobile" required maxlength="10" :invalid="!!errors?.mobile" @update:model-value="set('mobile', $event)" />
      </div>
      <div class="col-md-6">
        <FormField label="Email" :model-value="form.email" type="email" @update:model-value="set('email', $event)" />
      </div>
      <div class="col-12">
        <FormField label="Address" :model-value="form.address" @update:model-value="set('address', $event)" />
      </div>
    </div>

    <h6 class="text-muted text-uppercase small border-bottom pb-2 mb-3">College</h6>
    <div class="row g-3 mb-4">
      <div class="col-md-6">
        <FormField label="College Name" :model-value="form.college_name" @update:model-value="set('college_name', $event)" />
      </div>
      <div class="col-md-6">
        <FormField label="Subject" :model-value="form.subject" @update:model-value="set('subject', $event)" />
      </div>
      <div class="col-md-6">
        <FormField label="Semester" :model-value="form.semester" @update:model-value="set('semester', $event)" />
      </div>
      <div class="col-md-6">
        <FormField label="University Roll No" :model-value="form.university_roll_no" @update:model-value="set('university_roll_no', $event)" />
      </div>
      <div class="col-md-6">
        <FormField label="College Roll No" :model-value="form.college_roll_no" @update:model-value="set('college_roll_no', $event)" />
      </div>
    </div>

    <h6 class="text-muted text-uppercase small border-bottom pb-2 mb-3">Internship</h6>
    <div class="row g-3 mb-4">
      <div class="col-md-6">
        <label class="form-label">Mode *</label>
        <select v-model="form.internship_mode" required class="form-select">
          <option value="online">Online</option>
          <option value="offline">Offline</option>
        </select>
      </div>
      <div v-if="showStatus" class="col-md-6">
        <label class="form-label">Status</label>
        <select v-model="form.status" class="form-select">
          <option value="pending">Pending</option>
          <option value="approved">Approved</option>
          <option value="rejected">Rejected</option>
          <option value="completed">Completed</option>
        </select>
      </div>
    </div>

    <h6 class="text-muted text-uppercase small border-bottom pb-2 mb-3">Documents</h6>
    <div class="row g-3 mb-3">
      <div class="col-md-6">
        <label class="form-label">Photo</label>
        <input type="file" accept="image/*" class="form-control" @change="onPhoto" />
        <img v-if="photoPreviewUrl" :src="photoPreviewUrl" alt="" class="mt-2 rounded border" style="height:80px;width:80px;object-fit:cover" />
      </div>
      <div class="col-md-6">
        <label class="form-label">ID Proof</label>
        <input type="file" accept="image/*,.pdf" class="form-control" @change="onIdProof" />
      </div>
    </div>

    <div v-if="showRejection && form.status === 'rejected'" class="mb-3">
      <label class="form-label">Rejection Reason</label>
      <textarea v-model="form.rejection_reason" rows="2" class="form-control" />
    </div>

    <slot name="actions" />
  </form>
</template>

<script setup>
import FormField from './FormField.vue'

const props = defineProps({
  form: { type: Object, required: true },
  errors: { type: Object, default: () => ({}) },
  showStatus: Boolean,
  showRejection: Boolean,
  photoPreviewUrl: String,
})

const emit = defineEmits(['submit', 'update:form', 'photo-selected', 'id-proof-selected'])

const set = (key, value) => {
  emit('update:form', { ...props.form, [key]: value })
}

const onPhoto = (e) => emit('photo-selected', e.target.files?.[0] || null)
const onIdProof = (e) => emit('id-proof-selected', e.target.files?.[0] || null)
</script>
