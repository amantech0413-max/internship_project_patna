<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-slate-100 py-6 px-3 sm:py-8 sm:px-4">
    <div class="max-w-3xl mx-auto w-full">
      <div class="text-center mb-6 sm:mb-8">
        <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-blue-900">M/s Bhagya Laxmi</h1>
        <p class="text-slate-600 mt-1 text-sm sm:text-base">Public Student Registration — Mohali, Chandigarh</p>
        <p class="text-xs sm:text-sm text-slate-500 mt-2">
          No login required · Submit your details · Admin will review your application
        </p>
      </div>

      <div v-if="success" class="bg-white rounded-2xl shadow-lg border border-green-200 p-6 sm:p-8 text-center">
        <div class="w-14 h-14 sm:w-16 sm:h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto text-2xl sm:text-3xl">✓</div>
        <h2 class="text-lg sm:text-xl font-bold text-green-800 mt-4">Registration Submitted!</h2>
        <p class="text-slate-600 mt-2 text-sm">{{ successMessage }}</p>
        <div v-if="registeredStudent" class="mt-6 bg-slate-50 rounded-xl p-4 text-left text-sm space-y-2">
          <p><span class="font-medium">Reference Code:</span> {{ registeredStudent.student_code }}</p>
          <p><span class="font-medium">Name:</span> {{ registeredStudent.student_name || registeredStudent.name }}</p>
          <p class="text-slate-600 text-xs mt-3">
            There is no student login. Our team will contact you after admin approval.
          </p>
        </div>
        <button
          type="button"
          class="mt-6 w-full sm:w-auto px-6 py-2.5 bg-blue-700 text-white rounded-lg font-medium"
          @click="resetForm"
        >
          Register Another Student
        </button>
      </div>

      <form
        v-else
        class="bg-white rounded-2xl shadow-lg border border-slate-200 overflow-hidden"
        novalidate
        @submit.prevent="submit"
      >
        <div class="bg-blue-900 text-white px-4 sm:px-6 py-4">
          <h2 class="text-base sm:text-lg font-semibold">Student Internship Registration</h2>
          <p class="text-blue-200 text-xs sm:text-sm mt-0.5">College is not required here — staff will link college when needed</p>
        </div>

        <div class="p-4 sm:p-6 space-y-5 sm:space-y-6">
          <section>
            <h3 class="text-sm font-semibold text-slate-700 uppercase tracking-wide mb-3">Personal Details</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <FormField
                  label="Student Name *"
                  v-model="form.name"
                  required
                  placeholder="Full name"
                  :invalid="!!errors.name"
                />
                <p v-if="errors.name" class="text-xs text-red-600 mt-1">{{ errors.name }}</p>
              </div>
              <div>
                <FormField label="Father's Name" v-model="form.father_name" placeholder="Father's name" />
              </div>
              <div>
                <FormField
                  label="Mobile Number *"
                  v-model="form.mobile"
                  required
                  maxlength="10"
                  inputmode="numeric"
                  placeholder="10 digits"
                  :invalid="!!errors.mobile"
                />
                <p v-if="errors.mobile" class="text-xs text-red-600 mt-1">{{ errors.mobile }}</p>
              </div>
              <div>
                <FormField
                  label="Email"
                  v-model="form.email"
                  type="email"
                  placeholder="Optional"
                  :invalid="!!errors.email"
                />
                <p v-if="errors.email" class="text-xs text-red-600 mt-1">{{ errors.email }}</p>
              </div>
              <div class="md:col-span-2">
                <FormField label="Address" v-model="form.address" placeholder="Optional" />
              </div>
            </div>
            <p class="text-xs text-slate-500 mt-2">
              Multiple students may share the same mobile number (siblings / same parent contact).
            </p>
          </section>

          <section>
            <h3 class="text-sm font-semibold text-slate-700 uppercase tracking-wide mb-3">Academic (Optional)</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <FormField label="Subject" v-model="form.subject" placeholder="e.g. CSE, BCA" />
              <FormField label="Semester" v-model="form.semester" placeholder="e.g. 5" />
              <FormField label="University Roll No" v-model="form.university_roll_no" />
              <FormField label="College Roll No" v-model="form.college_roll_no" />
            </div>
          </section>

          <section>
            <h3 class="text-sm font-semibold text-slate-700 uppercase tracking-wide mb-3">Internship</h3>
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Internship Mode *</label>
              <select
                v-model="form.internship_mode"
                required
                class="w-full border rounded-lg px-3 py-2.5"
                :class="errors.internship_mode ? 'border-red-500' : 'border-slate-300'"
              >
                <option value="online">Online Internship</option>
                <option value="offline">Offline Internship</option>
              </select>
              <p v-if="errors.internship_mode" class="text-xs text-red-600 mt-1">{{ errors.internship_mode }}</p>
            </div>
          </section>

          <section>
            <h3 class="text-sm font-semibold text-slate-700 uppercase tracking-wide mb-3">Documents (Optional)</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Passport Size Photo</label>
                <input type="file" accept="image/*" class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2" @change="onPhotoChange" />
              </div>
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">ID Proof</label>
                <input type="file" accept="image/*,.pdf" class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2" @change="onIdProofChange" />
              </div>
            </div>
          </section>

          <p v-if="error" class="text-sm text-red-600 bg-red-50 border border-red-200 rounded-lg px-4 py-3">{{ error }}</p>
          <p v-if="fieldErrors" class="text-sm text-red-600 bg-red-50 border border-red-200 rounded-lg px-4 py-3 whitespace-pre-line">{{ fieldErrors }}</p>

          <button
            type="submit"
            class="w-full py-3 bg-blue-700 hover:bg-blue-800 text-white font-semibold rounded-lg disabled:opacity-60 transition"
            :disabled="loading"
          >
            {{ loading ? 'Submitting...' : 'Submit Registration' }}
          </button>
        </div>

        <div class="px-4 sm:px-6 py-4 bg-slate-50 border-t text-center text-sm text-slate-500">
          Staff adding students?
          <NuxtLink to="/login" class="text-blue-700 font-medium hover:underline">Staff Login</NuxtLink>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ layout: 'blank' })

const { publicFetch } = usePublicApi()

const loading = ref(false)
const error = ref('')
const fieldErrors = ref('')
const success = ref(false)
const successMessage = ref('')
const registeredStudent = ref<Record<string, string> | null>(null)
const errors = reactive<Record<string, string>>({})

const photoFile = ref<File | null>(null)
const idProofFile = ref<File | null>(null)

const initialForm = () => ({
  name: '',
  father_name: '',
  university_roll_no: '',
  college_roll_no: '',
  subject: '',
  semester: '',
  mobile: '',
  email: '',
  internship_mode: 'online',
  address: '',
})

const form = reactive(initialForm())

const onPhotoChange = (e: Event) => {
  photoFile.value = (e.target as HTMLInputElement).files?.[0] ?? null
}

const onIdProofChange = (e: Event) => {
  idProofFile.value = (e.target as HTMLInputElement).files?.[0] ?? null
}

const validateClient = (): boolean => {
  Object.keys(errors).forEach((k) => delete errors[k])
  let ok = true

  if (!form.name.trim() || form.name.trim().length < 2) {
    errors.name = 'Student name is required (min 2 characters).'
    ok = false
  }
  if (!/^\d{10}$/.test(form.mobile.replace(/\D/g, ''))) {
    errors.mobile = 'Mobile number must be exactly 10 digits.'
    ok = false
  }
  if (form.email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
    errors.email = 'Enter a valid email address.'
    ok = false
  }
  if (!['online', 'offline'].includes(form.internship_mode)) {
    errors.internship_mode = 'Please select internship mode.'
    ok = false
  }

  return ok
}

const buildFormData = () => {
  const fd = new FormData()
  const mobile = form.mobile.replace(/\D/g, '')
  fd.append('name', form.name.trim())
  fd.append('mobile', mobile)
  fd.append('internship_mode', form.internship_mode)
  ;['father_name', 'university_roll_no', 'college_roll_no', 'subject', 'semester', 'email', 'address'].forEach((key) => {
    const val = form[key as keyof typeof form]
    if (val) fd.append(key, String(val))
  })
  if (photoFile.value) fd.append('photo', photoFile.value)
  if (idProofFile.value) fd.append('id_proof', idProofFile.value)
  return fd
}

const parseErrors = (err: unknown) => {
  const e = err as { data?: { message?: string; errors?: Record<string, string[]> } }
  error.value = e?.data?.message || 'Registration failed. Please try again.'
  if (e?.data?.errors) {
    fieldErrors.value = Object.entries(e.data.errors)
      .map(([k, v]) => `${k}: ${v.join(', ')}`)
      .join('\n')
    Object.entries(e.data.errors).forEach(([k, v]) => {
      errors[k] = v[0]
    })
  }
}

const submit = async () => {
  if (!validateClient()) return

  loading.value = true
  error.value = ''
  fieldErrors.value = ''

  try {
    const res = await publicFetch<{
      success: boolean
      message: string
      data: Record<string, string>
    }>('/auth/register', {
      method: 'POST',
      body: buildFormData(),
    })

    success.value = true
    successMessage.value = res.message || 'Registration submitted. Awaiting admin approval.'
    registeredStudent.value = res.data
  } catch (err) {
    parseErrors(err)
  } finally {
    loading.value = false
  }
}

const resetForm = () => {
  Object.assign(form, initialForm())
  photoFile.value = null
  idProofFile.value = null
  success.value = false
  registeredStudent.value = null
  error.value = ''
  fieldErrors.value = ''
  Object.keys(errors).forEach((k) => delete errors[k])
}
</script>
