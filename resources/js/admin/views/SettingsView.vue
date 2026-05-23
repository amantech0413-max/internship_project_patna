<template>
  <div>
    <p class="text-muted small mb-3">Organization branding, UPI payment details, registration fee, and privacy policy.</p>

    <div class="card table-card shadow-sm mb-4" style="max-width: 42rem">
      <div class="card-body">
        <form @submit.prevent="save">
          <div class="mb-3">
            <label class="form-label">Organization Name</label>
            <input v-model="form.organization_name" class="form-control" required />
          </div>
          <div class="mb-3">
            <label class="form-label">Address</label>
            <textarea v-model="form.organization_address" class="form-control" rows="2" />
          </div>
          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label">Support Helpline Number</label>
              <input
                v-model="form.support_contact_number"
                class="form-control"
                placeholder="e.g. 9876543210"
                maxlength="30"
              />
              <div class="form-text">Shown in site footer when filled.</div>
            </div>
            <div class="col-md-6">
              <label class="form-label">Support Email</label>
              <input
                v-model="form.support_email"
                type="email"
                class="form-control"
                placeholder="support@example.com"
              />
              <div class="form-text">Shown in site footer when filled.</div>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Registration Fee (₹)</label>
            <input v-model="form.registration_fee_amount" type="number" min="0" step="0.01" class="form-control" />
            <div class="form-text">Shown on student registration form (read-only for students).</div>
          </div>
          <div class="mb-3">
            <label class="form-label">UPI ID</label>
            <input v-model="form.upi_id" class="form-control" placeholder="example@upi" />
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label">Logo</label>
              <div v-if="preview.logo" class="mb-2">
                <img :src="preview.logo" alt="Logo" class="img-thumbnail" style="max-height: 80px" @error="onLogoImgError" />
                <div v-if="stored.logo" class="form-text">File: {{ stored.logo }}</div>
              </div>
              <input type="file" class="form-control" accept="image/*" @change="onLogo" />
              <button v-if="preview.logo" type="button" class="btn btn-sm btn-outline-danger mt-2" @click="removeLogo">
                Remove logo
              </button>
            </div>
            <div class="col-md-6">
              <label class="form-label">UPI QR Code</label>
              <div v-if="preview.qr" class="mb-2">
                <img :src="preview.qr" alt="QR" class="img-thumbnail" style="max-height: 120px" @error="onQrImgError" />
                <div v-if="stored.qr" class="form-text">File: {{ stored.qr }}</div>
              </div>
              <input type="file" class="form-control" accept="image/*" @change="onQr" />
              <button v-if="preview.qr" type="button" class="btn btn-sm btn-outline-danger mt-2" @click="removeQr">
                Remove QR
              </button>
            </div>
          </div>

          <p v-if="error" class="text-danger small">{{ error }}</p>
          <button type="submit" class="btn btn-primary" :disabled="saving">
            {{ saving ? 'Saving...' : 'Save Settings' }}
          </button>
        </form>
      </div>
    </div>

    <div class="card table-card shadow-sm" style="max-width: 56rem">
      <div class="card-body">
        <h2 class="h6 fw-bold mb-2">Privacy Policy</h2>
        <p class="text-muted small mb-3">
          Content shown at <code>/privacy-policy</code>. Use the editor for formatting, lists, links, images, and video.
        </p>
        <RichTextEditor v-model="form.privacy_policy_html" placeholder="Write your privacy policy..." />
        <button type="button" class="btn btn-primary mt-3" :disabled="savingPrivacy" @click="savePrivacy">
          {{ savingPrivacy ? 'Saving...' : 'Save Privacy Policy' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import RichTextEditor from '@/components/RichTextEditor.vue'
import { apiFetch } from '@/api/client'
import { parseApiError } from '@/utils/apiHelpers'
import { useToastStore } from '@/stores/toast'
import { clearSiteSettingsCache } from '@/composables/useSiteSettings'

const toast = useToastStore()
const saving = ref(false)
const savingPrivacy = ref(false)
const error = ref('')

const form = reactive({
  organization_name: '',
  organization_address: '',
  upi_id: '',
  registration_fee_amount: '0',
  privacy_policy_html: '',
  support_contact_number: '',
  support_email: '',
})

const preview = reactive({ logo: '', qr: '' })
const stored = reactive({ logo: '', qr: '' })
const logoFile = ref(null)
const qrFile = ref(null)
const removeLogoFlag = ref(false)
const removeQrFlag = ref(false)

const load = async () => {
  try {
    const res = await apiFetch('/admin/settings')
    const d = res.data || {}
    form.organization_name = d.organization_name || ''
    form.organization_address = d.organization_address || ''
    form.upi_id = d.upi_id || ''
    form.registration_fee_amount = String(d.registration_fee_amount ?? 0)
    stored.logo = d.organization_logo || ''
    stored.qr = d.upi_qr || ''
    preview.logo = d.organization_logo_url || ''
    preview.qr = d.upi_qr_url || ''
    form.privacy_policy_html = d.privacy_policy_html || ''
    form.support_contact_number = d.support_contact_number || ''
    form.support_email = d.support_email || ''
  } catch (e) {
    error.value = parseApiError(e)
  }
}

const onLogo = (e) => {
  logoFile.value = e.target.files?.[0] || null
  removeLogoFlag.value = false
  if (logoFile.value) preview.logo = URL.createObjectURL(logoFile.value)
}

const onQr = (e) => {
  qrFile.value = e.target.files?.[0] || null
  removeQrFlag.value = false
  if (qrFile.value) preview.qr = URL.createObjectURL(qrFile.value)
}

const removeLogo = () => {
  logoFile.value = null
  removeLogoFlag.value = true
  preview.logo = ''
}

const removeQr = () => {
  qrFile.value = null
  removeQrFlag.value = true
  preview.qr = ''
}

const save = async () => {
  saving.value = true
  error.value = ''
  try {
    const fd = new FormData()
    fd.append('organization_name', form.organization_name)
    fd.append('organization_address', form.organization_address)
    fd.append('upi_id', form.upi_id)
    fd.append('registration_fee_amount', form.registration_fee_amount)
    fd.append('support_contact_number', form.support_contact_number)
    fd.append('support_email', form.support_email)
    if (logoFile.value) fd.append('organization_logo', logoFile.value)
    if (qrFile.value) fd.append('upi_qr', qrFile.value)
    if (removeLogoFlag.value) fd.append('remove_logo', '1')
    if (removeQrFlag.value) fd.append('remove_upi_qr', '1')

    await apiFetch('/admin/settings', { method: 'POST', body: fd })
    clearSiteSettingsCache()
    toast.show('Settings saved.', 'success')
    logoFile.value = null
    qrFile.value = null
    removeLogoFlag.value = false
    removeQrFlag.value = false
    await load()
  } catch (e) {
    error.value = parseApiError(e)
    toast.show(error.value, 'danger')
  } finally {
    saving.value = false
  }
}

const onLogoImgError = () => {
  preview.logo = ''
  error.value = 'Logo image could not be loaded. Re-upload or run: php artisan storage:link'
}

const onQrImgError = () => {
  preview.qr = ''
  error.value = 'QR image could not be loaded. Re-upload or run: php artisan storage:link'
}

const savePrivacy = async () => {
  savingPrivacy.value = true
  error.value = ''
  try {
    const fd = new FormData()
    fd.append('privacy_policy_html', form.privacy_policy_html || '')
    await apiFetch('/admin/settings', { method: 'POST', body: fd })
    clearSiteSettingsCache()
    toast.show('Privacy policy saved.', 'success')
    await load()
  } catch (e) {
    error.value = parseApiError(e)
    toast.show(error.value, 'danger')
  } finally {
    savingPrivacy.value = false
  }
}

onMounted(load)
</script>
