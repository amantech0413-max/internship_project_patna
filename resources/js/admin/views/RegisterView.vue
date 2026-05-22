<template>
  <RegisterPageShell
    :branding="siteSettings"
    :college-short-name="college?.short_name"
    hero-title="Internship Jun 2026 Registration"
    hero-description="Begin your professional journey with us. Fill out the form carefully to apply for the internship program."
    :show-features="true"
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
                <input :value="college?.short_name || college?.name" class="reg-input" readonly tabindex="-1" />
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

        <div class="reg-payment-section mt-4 pt-4 border-top">
          <h3 class="reg-payment-title"><i class="bi bi-wallet2" />Payment</h3>

          <div class="reg-payment-toolbar">
            <div v-if="feeAmount > 0" class="reg-fee-badge">
              <span class="reg-fee-label">Registration Fee</span>
              <strong class="reg-fee-amount">{{ formatFeeDisplay(feeAmount) }}</strong>
            </div>
            <div class="form-check reg-offline-check mb-0">
              <input
                id="paymentOffline"
                v-model="form.payment_mode_offline"
                class="form-check-input"
                type="checkbox"
                @change="onOfflineToggle"
              />
              <label class="form-check-label" for="paymentOffline">Payment mode offline</label>
            </div>
          </div>

          <div v-if="!form.payment_mode_offline" class="reg-payment-online">
            <div
              v-if="siteSettings.upi_qr_url || siteSettings.upi_id"
              class="reg-payment-scan-card"
            >
              <p class="reg-payment-scan-heading">Scan &amp; pay via UPI</p>
              <div v-if="siteSettings.upi_qr_url && !qrImageFailed" class="reg-qr-card">
                <div class="reg-qr-hover">
                  <img
                    :src="siteSettings.upi_qr_url"
                    alt="UPI QR Code"
                    class="reg-qr-img"
                    @error="qrImageFailed = true"
                  />
                  <div class="reg-qr-overlay">
                    <button type="button" class="btn btn-light btn-sm" @click="downloadQr">
                      <i class="bi bi-download me-1" />Download QR
                    </button>
                  </div>
                </div>
              </div>
              <p v-else-if="siteSettings.upi_qr_url && qrImageFailed" class="reg-qr-missing small text-muted mb-2">
                QR image could not be loaded. Please use UPI ID below or contact support.
              </p>

              <div v-if="siteSettings.upi_id" class="reg-upi-card">
                <span class="reg-upi-label">UPI ID</span>
                <div class="reg-upi-value-row">
                  <code class="reg-upi-id">{{ siteSettings.upi_id }}</code>
                  <button type="button" class="btn btn-sm btn-outline-primary reg-upi-copy" title="Copy UPI ID" @click="copyUpi">
                    <i class="bi bi-clipboard me-1" />Copy
                  </button>
                </div>
              </div>
            </div>

            <div class="reg-payment-fields">
              <p class="reg-payment-fields-heading">After payment, submit details</p>
              <div class="row g-3">
                <div class="col-md-6">
                  <div class="reg-field" :class="{ 'has-error': errors.transaction_id }">
                    <label>Transaction ID *</label>
                    <div class="reg-input-wrap">
                      <i class="bi bi-receipt reg-input-icon" />
                      <input
                        v-model="form.transaction_id"
                        class="reg-input"
                        placeholder="Enter UPI / bank transaction ID"
                        required
                      />
                    </div>
                    <div v-if="errors.transaction_id" class="reg-field-error">{{ errors.transaction_id }}</div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="reg-field" :class="{ 'has-error': errors.payment_screenshot }">
                    <label>Payment Screenshot *</label>
                    <input
                      type="file"
                      class="reg-file-input"
                      accept="image/jpeg,image/png,image/webp,image/gif"
                      required
                      @change="onScreenshotChange"
                    />
                    <div v-if="screenshotPreview" class="reg-screenshot-preview-wrap">
                      <img :src="screenshotPreview" alt="Payment screenshot preview" class="reg-screenshot-preview" />
                    </div>
                    <div v-if="errors.payment_screenshot" class="reg-field-error">{{ errors.payment_screenshot }}</div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <p v-else class="reg-offline-note mb-0">
            <i class="bi bi-info-circle me-1" />
            Offline payment selected. Your payment will remain <strong>Pending</strong> until verified by admin.
          </p>
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
import { computed, reactive, ref, watch, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { getPublicApi } from '@/api/client'
import { parseApiError } from '@/utils/apiHelpers'
import { fetchPublicSiteSettings } from '@/composables/useSiteSettings'
import { useToastStore } from '@/stores/toast'
import RegisterPageShell from '@/components/RegisterPageShell.vue'

const route = useRoute()
const router = useRouter()
const http = getPublicApi()
const toast = useToastStore()

const siteSettings = ref({})
const slug = computed(() => String(route.params.slug || ''))
const college = ref(null)

const feeAmount = computed(() => Number(siteSettings.value.registration_fee_amount || 0))
const qrImageFailed = ref(false)

const formatFeeDisplay = (n) => {
  const val = Math.max(0, Number(n) || 0)
  const text =
    val === 0
      ? '0'
      : Number.isInteger(val)
        ? String(Math.round(val))
        : val.toLocaleString('en-IN', { maximumFractionDigits: 2, minimumFractionDigits: 0 })
  return `₹ ${text} /-`
}

onMounted(async () => {
  siteSettings.value = await fetchPublicSiteSettings()
})

watch(
  () => siteSettings.value.upi_qr_url,
  () => {
    qrImageFailed.value = false
  }
)

const loadCollege = async (s) => {
  if (!s) {
    college.value = null
    router.replace({ name: 'landing' })
    return
  }
  try {
    const res = await http.get(`/registration/colleges/${encodeURIComponent(s)}`)
    college.value = res.data || null
    if (!college.value) {
      router.replace({ name: 'landing' })
    }
  } catch {
    college.value = null
    router.replace({ name: 'landing' })
  }
}

watch(() => slug.value, loadCollege, { immediate: true })

const loading = ref(false)
const error = ref('')
const fieldErrors = ref('')
const success = ref(false)
const successMessage = ref('')
const registeredStudent = ref(null)
const errors = reactive({})
const screenshotFile = ref(null)
const screenshotPreview = ref('')

const initialForm = () => ({
  registration_no: '',
  name: '',
  father_name: '',
  university_roll_no: '',
  college_roll_no: '',
  subject: '',
  mobile: '',
  email: '',
  payment_mode_offline: false,
  transaction_id: '',
})

const form = reactive(initialForm())

const onOfflineToggle = () => {
  if (form.payment_mode_offline) {
    form.transaction_id = ''
    screenshotFile.value = null
    screenshotPreview.value = ''
    delete errors.transaction_id
    delete errors.payment_screenshot
  }
}

const onScreenshotChange = (e) => {
  const file = e.target.files?.[0]
  screenshotFile.value = file || null
  if (screenshotPreview.value) URL.revokeObjectURL(screenshotPreview.value)
  screenshotPreview.value = file ? URL.createObjectURL(file) : ''
  delete errors.payment_screenshot
}

const copyUpi = async () => {
  const id = siteSettings.value.upi_id
  if (!id) return
  try {
    await navigator.clipboard.writeText(id)
    toast.show('UPI ID copied to clipboard.', 'success')
  } catch {
    toast.show('Could not copy. Please copy manually.', 'danger')
  }
}

const downloadQr = async () => {
  const url = siteSettings.value.upi_qr_url
  if (!url) return
  try {
    const res = await fetch(url)
    const blob = await res.blob()
    const a = document.createElement('a')
    a.href = URL.createObjectURL(blob)
    a.download = 'upi-qr-code.png'
    a.click()
    URL.revokeObjectURL(a.href)
  } catch {
    window.open(url, '_blank')
  }
}

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
  if (!form.payment_mode_offline) {
    if (!form.transaction_id.trim()) {
      errors.transaction_id = 'Transaction ID is required for online payment.'
    }
    if (!screenshotFile.value) {
      errors.payment_screenshot = 'Payment screenshot is required for online payment.'
    }
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
    const fd = new FormData()
    fd.append('college_slug', slug.value)
    fd.append('registration_no', form.registration_no.trim())
    fd.append('name', form.name.trim())
    fd.append('father_name', form.father_name.trim())
    fd.append('university_roll_no', form.university_roll_no.trim())
    fd.append('college_roll_no', form.college_roll_no.trim())
    fd.append('subject', form.subject.trim())
    fd.append('mobile', form.mobile.replace(/\D/g, ''))
    if (form.email.trim()) fd.append('email', form.email.trim())
    fd.append('payment_mode_offline', form.payment_mode_offline ? '1' : '0')
    if (!form.payment_mode_offline) {
      fd.append('transaction_id', form.transaction_id.trim())
      if (screenshotFile.value) fd.append('payment_screenshot', screenshotFile.value)
    }

    const body = await http.post('/auth/register', fd)
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
  screenshotFile.value = null
  if (screenshotPreview.value) URL.revokeObjectURL(screenshotPreview.value)
  screenshotPreview.value = ''
  success.value = false
  registeredStudent.value = null
  error.value = ''
  fieldErrors.value = ''
  Object.keys(errors).forEach((k) => delete errors[k])
}
</script>
