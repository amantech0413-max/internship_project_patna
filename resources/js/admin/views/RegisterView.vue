<template>
  <div class="min-vh-100 d-flex align-items-center justify-content-center bg-light p-3">
    <div class="w-100" style="max-width: 720px">
      <div class="text-center mb-4">
        <h1 class="h4 fw-bold text-primary">{{ PROGRAM_TITLE }}</h1>
        <p v-if="college" class="fw-semibold text-dark mb-0 mt-2">{{ college.shortName }}</p>
      </div>

      <div v-if="success" class="card shadow border-success">
        <div class="card-body text-center p-4">
          <h2 class="h5 text-success">Registration Submitted!</h2>
          <p class="text-muted">{{ successMessage }}</p>
          <div v-if="registeredStudent" class="text-start bg-light rounded p-3 small mt-3">
            <p class="mb-1"><strong>College:</strong> {{ registeredStudent.college_name }}</p>
            <p class="mb-1"><strong>Registration No:</strong> {{ registeredStudent.registration_no }}</p>
            <p class="mb-1"><strong>Student Code:</strong> {{ registeredStudent.student_code }}</p>
            <p class="mb-0"><strong>Name:</strong> {{ registeredStudent.student_name || registeredStudent.name }}</p>
          </div>
          <button type="button" class="btn btn-primary mt-3" @click="resetForm">Register Another</button>
        </div>
      </div>

      <form v-else class="card shadow" @submit.prevent="submit">
        <div class="card-header bg-primary text-white">
          <strong>Student Registration Form</strong>
        </div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Name of the Student *</label>
              <input v-model="form.name" class="form-control" required />
              <div v-if="errors.name" class="invalid-feedback d-block">{{ errors.name }}</div>
            </div>
            <div class="col-md-6">
              <label class="form-label">Father's Name *</label>
              <input v-model="form.father_name" class="form-control" required />
              <div v-if="errors.father_name" class="invalid-feedback d-block">{{ errors.father_name }}</div>
            </div>
            <div class="col-md-6">
              <label class="form-label">College Name</label>
              <input :value="college?.shortName" class="form-control bg-light" readonly tabindex="-1" />
            </div>
            <div class="col-md-6">
              <label class="form-label">College Roll No *</label>
              <input v-model="form.college_roll_no" class="form-control" required />
              <div v-if="errors.college_roll_no" class="invalid-feedback d-block">{{ errors.college_roll_no }}</div>
            </div>
            <div class="col-md-6">
              <label class="form-label">Registration No *</label>
              <input v-model="form.registration_no" class="form-control" required />
              <div v-if="errors.registration_no" class="invalid-feedback d-block">{{ errors.registration_no }}</div>
            </div>
            <div class="col-md-6">
              <label class="form-label">University Roll No *</label>
              <input v-model="form.university_roll_no" class="form-control" required />
              <div v-if="errors.university_roll_no" class="invalid-feedback d-block">{{ errors.university_roll_no }}</div>
            </div>
            <div class="col-md-6">
              <label class="form-label">Subject *</label>
              <input v-model="form.subject" class="form-control" required />
              <div v-if="errors.subject" class="invalid-feedback d-block">{{ errors.subject }}</div>
            </div>
            <div class="col-md-6">
              <label class="form-label">Mobile No *</label>
              <input v-model="form.mobile" class="form-control" maxlength="10" inputmode="numeric" required />
              <div v-if="errors.mobile" class="invalid-feedback d-block">{{ errors.mobile }}</div>
            </div>
            <div class="col-md-6">
              <label class="form-label">Email ID <span class="text-muted fw-normal">(optional)</span></label>
              <input v-model="form.email" type="email" class="form-control" />
              <div v-if="errors.email" class="invalid-feedback d-block">{{ errors.email }}</div>
            </div>
          </div>
          <p v-if="error" class="alert alert-danger mt-3 mb-0 small">{{ error }}</p>
          <p v-if="fieldErrors" class="alert alert-danger mt-3 mb-0 small">{{ fieldErrors }}</p>
          <button type="submit" class="btn btn-primary w-100 mt-3" :disabled="loading">
            {{ loading ? 'Submitting...' : 'Submit Registration' }}
          </button>
          <p class="text-center small text-muted mt-3 mb-0 d-none" aria-hidden="true">
            <router-link to="/login">Staff Login</router-link>
          </p>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { computed, reactive, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { getPublicApi } from '@/api/client'
import { parseApiError } from '@/utils/apiHelpers'
import { PROGRAM_TITLE, collegeBySlug } from '@/config/registrationColleges'

const route = useRoute()
const router = useRouter()
const http = getPublicApi()

const slug = computed(() => String(route.params.slug || ''))
const college = computed(() => collegeBySlug(slug.value))

watch(
  () => slug.value,
  (s) => {
    if (!collegeBySlug(s)) {
      router.replace({ name: 'register' })
    }
  },
  { immediate: true }
)

const loading = ref(false)
const error = ref('')
const fieldErrors = ref('')
const success = ref(false)
const successMessage = ref('')
const registeredStudent = ref(null)
const errors = reactive({})

const initialForm = () => ({
  registration_no: '',
  name: '',
  father_name: '',
  university_roll_no: '',
  college_roll_no: '',
  subject: '',
  mobile: '',
  email: '',
})

const form = reactive(initialForm())

const validateClient = () => {
  Object.keys(errors).forEach((k) => delete errors[k])
  const required = [
    ['name', 'Student name is required (min 2 characters).'],
    ['father_name', "Father's name is required."],
    ['university_roll_no', 'University roll no is required.'],
    ['college_roll_no', 'College roll no is required.'],
    ['registration_no', 'Registration no is required.'],
    ['subject', 'Subject is required.'],
  ]
  for (const [key, msg] of required) {
    if (!String(form[key]).trim()) errors[key] = msg
  }
  if (form.name.trim() && form.name.trim().length < 2) {
    errors.name = 'Student name is required (min 2 characters).'
  }
  if (!/^\d{10}$/.test(form.mobile.replace(/\D/g, ''))) {
    errors.mobile = 'Mobile number must be exactly 10 digits.'
  }
  if (form.email.trim() && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email.trim())) {
    errors.email = 'Enter a valid email address.'
  }
  return Object.keys(errors).length === 0
}

const submit = async () => {
  if (!college.value) return
  error.value = ''
  fieldErrors.value = ''
  if (!validateClient()) return

  loading.value = true
  try {
    const payload = {
      college_slug: slug.value,
      registration_no: form.registration_no.trim(),
      name: form.name.trim(),
      father_name: form.father_name.trim(),
      university_roll_no: form.university_roll_no.trim(),
      college_roll_no: form.college_roll_no.trim(),
      subject: form.subject.trim(),
      mobile: form.mobile.replace(/\D/g, ''),
    }
    if (form.email.trim()) payload.email = form.email.trim()

    const res = await http.post('/auth/register', payload)
    const body = res.data
    success.value = true
    successMessage.value = body.message || 'Submitted successfully.'
    registeredStudent.value = body.data
  } catch (e) {
    const body = e.response?.data
    error.value = body?.message || parseApiError(e)
    if (body?.errors) {
      Object.entries(body.errors).forEach(([k, v]) => {
        errors[k] = Array.isArray(v) ? v[0] : v
      })
      fieldErrors.value = Object.entries(body.errors)
        .map(([k, v]) => `${k}: ${(Array.isArray(v) ? v : [v]).join(', ')}`)
        .join('\n')
    }
  } finally {
    loading.value = false
  }
}

const resetForm = () => {
  Object.assign(form, initialForm())
  success.value = false
  registeredStudent.value = null
  error.value = ''
  fieldErrors.value = ''
  Object.keys(errors).forEach((k) => delete errors[k])
}
</script>
