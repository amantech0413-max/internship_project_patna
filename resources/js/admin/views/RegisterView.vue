<template>
  <RegisterPageShell
    :college-short-name="college?.shortName"
    hero-title="Internship Jun 2026 Registration"
    hero-description="Begin your professional journey with us. Fill out the form carefully to apply for the internship program."
  >
    <div v-if="success" class="register-card">
      <div class="register-success">
        <div class="register-success-icon"><i class="bi bi-check-lg" /></div>
        <h2 class="h5 text-success fw-bold">Registration Submitted!</h2>
        <p class="text-muted">{{ successMessage }}</p>
        <div v-if="registeredStudent" class="register-success-details">
          <p class="mb-1"><strong>College:</strong> {{ registeredStudent.college_name }}</p>
          <p class="mb-1"><strong>Registration No:</strong> {{ registeredStudent.registration_no }}</p>
          <p class="mb-1"><strong>Student Code:</strong> {{ registeredStudent.student_code }}</p>
          <p class="mb-0"><strong>Name:</strong> {{ registeredStudent.student_name || registeredStudent.name }}</p>
        </div>
        <button type="button" class="reg-submit" style="max-width: 280px" @click="resetForm">
          <i class="bi bi-plus-circle" /> Register Another
        </button>
      </div>
    </div>

    <form v-else class="register-card" @submit.prevent="submit">
      <div class="register-card-head">
        <div class="register-card-head-icon"><i class="bi bi-person-fill" /></div>
        <div>
          <h2>Student Information</h2>
          <p>Please fill in your details carefully</p>
        </div>
      </div>
      <div class="register-card-body">
        <div class="row g-3 g-md-4">
          <div class="col-md-6">
            <div class="reg-field" :class="{ 'has-error': errors.name }">
              <label>Name of the Student *</label>
              <div class="reg-input-wrap">
                <i class="bi bi-person reg-input-icon" />
                <input v-model="form.name" class="reg-input" placeholder="Enter your full name" required />
              </div>
              <div v-if="errors.name" class="reg-field-error">{{ errors.name }}</div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="reg-field" :class="{ 'has-error': errors.father_name }">
              <label>Father's Name *</label>
              <div class="reg-input-wrap">
                <i class="bi bi-person-badge reg-input-icon" />
                <input v-model="form.father_name" class="reg-input" placeholder="Enter father's name" required />
              </div>
              <div v-if="errors.father_name" class="reg-field-error">{{ errors.father_name }}</div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="reg-field">
              <label>College Name</label>
              <div class="reg-input-wrap">
                <i class="bi bi-building reg-input-icon" />
                <input :value="college?.shortName" class="reg-input" readonly tabindex="-1" />
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="reg-field" :class="{ 'has-error': errors.college_roll_no }">
              <label>College Roll No *</label>
              <div class="reg-input-wrap">
                <i class="bi bi-hash reg-input-icon" />
                <input v-model="form.college_roll_no" class="reg-input" placeholder="Enter college roll number" required />
              </div>
              <div v-if="errors.college_roll_no" class="reg-field-error">{{ errors.college_roll_no }}</div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="reg-field" :class="{ 'has-error': errors.registration_no }">
              <label>Registration No *</label>
              <div class="reg-input-wrap">
                <i class="bi bi-card-text reg-input-icon" />
                <input v-model="form.registration_no" class="reg-input" placeholder="Enter registration number" required />
              </div>
              <div v-if="errors.registration_no" class="reg-field-error">{{ errors.registration_no }}</div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="reg-field" :class="{ 'has-error': errors.university_roll_no }">
              <label>University Roll No *</label>
              <div class="reg-input-wrap">
                <i class="bi bi-mortarboard reg-input-icon" />
                <input v-model="form.university_roll_no" class="reg-input" placeholder="Enter university roll number" required />
              </div>
              <div v-if="errors.university_roll_no" class="reg-field-error">{{ errors.university_roll_no }}</div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="reg-field" :class="{ 'has-error': errors.subject }">
              <label>Subject *</label>
              <div class="reg-input-wrap">
                <i class="bi bi-journal-text reg-input-icon" />
                <input v-model="form.subject" class="reg-input" placeholder="Enter your subject" required />
              </div>
              <div v-if="errors.subject" class="reg-field-error">{{ errors.subject }}</div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="reg-field" :class="{ 'has-error': errors.mobile }">
              <label>Mobile No *</label>
              <div class="reg-input-wrap">
                <i class="bi bi-telephone reg-input-icon" />
                <input
                  v-model="form.mobile"
                  class="reg-input"
                  placeholder="10 digit mobile number"
                  maxlength="10"
                  inputmode="numeric"
                  required
                />
              </div>
              <div v-if="errors.mobile" class="reg-field-error">{{ errors.mobile }}</div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="reg-field" :class="{ 'has-error': errors.email }">
              <label>Email ID <span class="text-muted fw-normal">(optional)</span></label>
              <div class="reg-input-wrap">
                <i class="bi bi-envelope reg-input-icon" />
                <input v-model="form.email" type="email" class="reg-input" placeholder="Enter email address" />
              </div>
              <div v-if="errors.email" class="reg-field-error">{{ errors.email }}</div>
            </div>
          </div>
        </div>

        <p v-if="error" class="alert alert-danger mt-3 mb-0 small">{{ error }}</p>
        <p v-if="fieldErrors" class="alert alert-danger mt-3 mb-0 small">{{ fieldErrors }}</p>

        <button type="submit" class="reg-submit" :disabled="loading">
          <i class="bi bi-send-fill" />
          {{ loading ? 'Submitting...' : 'Submit Application' }}
        </button>
      </div>
    </form>
  </RegisterPageShell>
</template>

<script setup>
import { computed, reactive, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { getPublicApi } from '@/api/client'
import { parseApiError } from '@/utils/apiHelpers'
import { collegeBySlug } from '@/config/registrationColleges'
import RegisterPageShell from '@/components/RegisterPageShell.vue'

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
