<template>
  <div>
    <div class="alert alert-primary d-flex flex-column flex-sm-row align-items-start align-items-sm-center gap-2 mb-4">
      <div class="flex-grow-1">
        <strong>Step 1:</strong> Staff login &nbsp;→&nbsp; <strong>Step 2:</strong> Add student (college + name + contact)
      </div>
      <span v-if="staffName" class="badge text-bg-light text-dark">Logged in: {{ staffName }}</span>
    </div>

    <!-- Step indicator -->
    <div class="d-flex flex-wrap gap-2 mb-4">
      <button
        type="button"
        class="btn btn-sm"
        :class="step === 1 ? 'btn-primary' : 'btn-outline-primary'"
        @click="step = 1"
      >
        1. Select College
      </button>
      <button
        type="button"
        class="btn btn-sm"
        :class="step === 2 ? 'btn-primary' : 'btn-outline-secondary'"
        :disabled="!form.college_id"
        @click="goStep2"
      >
        2. Student Details
      </button>
    </div>

    <div class="row g-4">
      <!-- Step 1 -->
      <div v-show="step === 1" class="col-12 col-lg-5">
        <div class="card table-card h-100 shadow-sm">
          <div class="card-header bg-primary text-white fw-semibold">
            <i class="bi bi-building me-2" />Step 1 — Select College
          </div>
          <div class="card-body">
            <div class="mb-3">
              <label class="form-label">College *</label>
              <select
                v-model="form.college_id"
                class="form-select"
                :class="{ 'is-invalid': errors.college_id }"
                @change="errors.college_id = ''"
              >
                <option value="">Choose college...</option>
                <option v-for="c in colleges" :key="c.id" :value="c.id">{{ c.college_name }}</option>
              </select>
              <div v-if="errors.college_id" class="invalid-feedback d-block">{{ errors.college_id }}</div>
            </div>
            <button type="button" class="btn btn-primary w-100" @click="goStep2">
              Next: Student Name & Contact <i class="bi bi-arrow-right ms-1" />
            </button>
          </div>
        </div>
      </div>

      <!-- Step 2 -->
      <div v-show="step === 2" class="col-12 col-lg-5">
        <div class="card table-card h-100 shadow-sm">
          <div class="card-header bg-primary text-white fw-semibold">
            <i class="bi bi-person-plus me-2" />Step 2 — Student Entry
          </div>
          <div class="card-body">
            <p v-if="selectedCollegeName" class="small text-muted mb-3">
              College: <strong>{{ selectedCollegeName }}</strong>
              <button type="button" class="btn btn-link btn-sm p-0 ms-2" @click="step = 1">Change</button>
            </p>
            <form @submit.prevent="saveEntry">
              <div class="mb-3">
                <label class="form-label">Student Name *</label>
                <input
                  v-model="form.student_name"
                  class="form-control"
                  :class="{ 'is-invalid': errors.student_name }"
                  maxlength="255"
                  @input="errors.student_name = ''"
                />
                <div v-if="errors.student_name" class="invalid-feedback d-block">{{ errors.student_name }}</div>
              </div>
              <div class="mb-3">
                <label class="form-label">Contact (Mobile) *</label>
                <input
                  v-model="form.mobile_number"
                  class="form-control"
                  :class="{ 'is-invalid': errors.mobile_number }"
                  maxlength="10"
                  inputmode="numeric"
                  placeholder="10 digits"
                  @input="errors.mobile_number = ''"
                />
                <div v-if="errors.mobile_number" class="invalid-feedback d-block">{{ errors.mobile_number }}</div>
                <div class="form-text">Same mobile allowed for multiple students.</div>
              </div>
              <p class="small text-muted mb-3">
                <i class="bi bi-person-check me-1" />
                Added by: <strong>{{ staffName || 'Current staff' }}</strong> (saved automatically)
              </p>
              <div class="d-flex flex-column flex-sm-row gap-2">
                <button type="button" class="btn btn-outline-secondary" @click="step = 1">Back</button>
                <button type="submit" class="btn btn-primary flex-grow-1" :disabled="saving">
                  <i class="bi bi-check2-circle me-1" />{{ saving ? 'Saving...' : 'Save Student Entry' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Excel Import -->
      <div class="col-12 col-lg-7">
        <div class="card table-card h-100 shadow-sm">
          <div class="card-header bg-success text-white fw-semibold d-flex flex-wrap justify-content-between align-items-center gap-2">
            <span><i class="bi bi-file-earmark-spreadsheet me-2" />Excel Bulk Import</span>
            <button type="button" class="btn btn-sm btn-light" @click="downloadSample">
              <i class="bi bi-download me-1" />Sample Excel
            </button>
          </div>
          <div class="card-body">
            <div class="mb-3">
              <label class="form-label">College for all rows *</label>
              <select v-model="importCollegeId" class="form-select" required>
                <option value="">Choose college...</option>
                <option v-for="c in colleges" :key="'i-' + (c.id)" :value="c.id">{{ c.college_name }}</option>
              </select>
              <div class="form-text">Excel: Student Name & Mobile only — college from dropdown; added by current staff.</div>
            </div>
            <div class="mb-3">
              <label class="form-label">Upload Excel / CSV</label>
              <input type="file" class="form-control" accept=".xlsx,.xls,.csv" @change="onFileChange" />
            </div>
            <button
              type="button"
              class="btn btn-outline-success w-100 w-sm-auto"
              :disabled="!importFile || !importCollegeId || previewing"
              @click="runPreview"
            >
              {{ previewing ? 'Reading...' : 'Preview Import' }}
            </button>
            <div v-if="importSummary" class="alert alert-info mt-3 mb-0 small">
              Total: {{ importSummary.total }} · Valid: {{ validPreviewRows.length }} · Invalid: {{ importSummary.invalid_count }}
            </div>
          </div>
        </div>
      </div>
    </div>

    <div id="importPreviewModal" ref="previewModalEl" class="modal fade" tabindex="-1">
      <div class="modal-dialog modal-lg modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Import Preview</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" />
          </div>
          <div class="modal-body p-0">
            <div class="table-responsive">
              <table class="table table-sm table-striped mb-0">
                <thead class="table-light sticky-top">
                  <tr>
                    <th>Row</th>
                    <th>Student Name</th>
                    <th>Mobile</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="r in previewRows" :key="r.row" :class="{ 'table-danger': !r.valid }">
                    <td>{{ r.row }}</td>
                    <td>{{ r.student_name }}</td>
                    <td>{{ r.mobile_number }}</td>
                    <td><span class="badge" :class="r.valid ? 'text-bg-success' : 'text-bg-danger'">{{ r.valid ? 'OK' : r.error }}</span></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <div class="modal-footer flex-wrap gap-2">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-success" :disabled="importing || !validPreviewRows.length || !importToken" @click="confirmImport">
              {{ importing ? 'Importing...' : `Confirm Import (${validPreviewRows.length})` }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <div v-if="importResult" class="card table-card mt-4 border-success">
      <div class="card-body">
        <h3 class="h6 fw-semibold text-success">Import Summary</h3>
        <div class="d-flex flex-wrap gap-2 small">
          <span class="badge text-bg-success">Success: {{ importResult.success_count }}</span>
          <span class="badge text-bg-danger">Failed: {{ importResult.failed_count }}</span>
          <span class="badge text-bg-secondary">Skipped: {{ importResult.skipped_count }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { apiFetch, apiForm, apiDownload, getPublicApi } from '@/api/client'
import { parseApiError, unwrapList, useFetchData } from '@/utils/apiHelpers'
import { useToastStore } from '@/stores/toast'
import { normalizeIndianMobile } from '../utils/indianMobile'
import { Modal } from 'bootstrap'

const auth = useAuthStore()
const toast = useToastStore()

const step = ref(1)
const staffName = computed(() => String(auth.user?.name || ''))

const colleges = ref([])
const form = reactive({ college_id: '', student_name: '', mobile_number: '' })
const errors = reactive({})
const saving = ref(false)

const selectedCollegeName = computed(() => {
  const c = colleges.value.find((x) => String(x.id) === String(form.college_id))
  return c ? String(c.college_name) : ''
})

const importCollegeId = ref('')
const importFile = ref(null)
const importToken = ref('')
const previewing = ref(false)
const importing = ref(false)
const previewRows = ref([])
const importSummary = ref(null)
const importResult = ref(null)

const previewModalEl = ref(null)
let previewModal = null

const validPreviewRows = computed(() => previewRows.value.filter((r) => r.valid))

onMounted(async () => {
  if (previewModalEl.value) previewModal = new Modal(previewModalEl.value)
  await loadColleges()
})

const loadColleges = async () => {
  const res = await apiFetch('/admin/colleges/dropdown')
  colleges.value = res.data || []
}

const validateStep1 = () => {
  errors.college_id = form.college_id ? '' : 'Please select a college.'
  return !!form.college_id
}

const validateStep2 = () => {
  let ok = true
  if (!form.student_name.trim() || form.student_name.trim().length < 2) {
    errors.student_name = 'Student name is required (min 2 characters).'
    ok = false
  } else {
    errors.student_name = ''
  }
  const mobile = normalizeIndianMobile(form.mobile_number)
  if (!mobile) {
    errors.mobile_number =
      'Invalid mobile. Use 10 digits, or 91/+91 prefix (last 10 digits will be used).'
    ok = false
  } else {
    form.mobile_number = mobile
    errors.mobile_number = ''
  }
  if (!form.college_id) {
    errors.college_id = 'Please select a college first.'
    ok = false
  }
  return ok
}

const goStep2 = () => {
  if (!validateStep1()) return
  step.value = 2
}

const saveEntry = async () => {
  if (!validateStep2()) return
  saving.value = true
  try {
    await apiFetch('/admin/staff-students', {
      method: 'POST',
      body: {
        college_id: Number(form.college_id),
        student_name: form.student_name.trim(),
        mobile_number: normalizeIndianMobile(form.mobile_number) ?? form.mobile_number,
      },
    })
    useToastStore().show('Student entry saved. Added by: ' + staffName.value, 'success')
    form.student_name = ''
    form.mobile_number = ''
    step.value = 2
  } catch (e) {
    useToastStore().show(parseApiError(e), 'danger')
  } finally {
    saving.value = false
  }
}

const onFileChange = (e) => {
  importFile.value = (e.target).files?.[0] || null
  importSummary.value = null
  previewRows.value = []
  importToken.value = ''
}

const runPreview = async () => {
  if (!importFile.value || !importCollegeId.value) return
  previewing.value = true
  try {
    const fd = new FormData()
    fd.append('file', importFile.value)
    fd.append('college_id', importCollegeId.value)
    const res = await apiForm('/staff/student/import/upload', fd)
    previewRows.value = res.data.rows
    importToken.value = res.data.import_token
    importSummary.value = { total: res.data.total, invalid_count: res.data.invalid_count }
    previewModal?.show()
  } catch (e) {
    useToastStore().show(parseApiError(e), 'danger')
  } finally {
    previewing.value = false
  }
}

const confirmImport = async () => {
  importing.value = true
  try {
    const res = await apiFetch(
      '/staff/student/import/confirm',
      {
        method: 'POST',
        body: {
          college_id: Number(importCollegeId.value),
          import_token: importToken.value,
          rows: validPreviewRows.value.map((r) => ({
            student_name: r.student_name,
            mobile_number: r.mobile_number,
            row: r.row,
          })),
          file_name: importFile.value?.name,
        },
      }
    )
    importResult.value = res.data
    previewModal?.hide()
    useToastStore().show(res.message || 'Import completed.', 'success')
  } catch (e) {
    useToastStore().show(parseApiError(e), 'danger')
  } finally {
    importing.value = false
  }
}

const downloadSample = async () => {
  try {
    await apiDownload('/staff/student/import/sample', 'student_import_sample.xlsx')
    toast.show('Sample file downloaded.', 'info')
  } catch {
    toast.show('Could not download sample file.', 'danger')
  }
}
</script>
