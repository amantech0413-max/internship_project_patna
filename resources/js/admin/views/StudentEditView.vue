<template>
  <div>
    <div v-if="loadError" class="alert alert-danger">
      {{ loadError }}
      <router-link to="/students" class="alert-link ms-2">← Back to Students</router-link>
    </div>

    <div v-else-if="pending" class="text-muted py-5 text-center">Loading student...</div>

    <template v-else-if="!loadError && student">
      <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
        <div>
          <p class="text-muted small mb-1">Edit student</p>
          <h2 class="h5 page-title mb-1">{{ student.student_name || student.name }}</h2>
          <p class="text-muted small mb-2">{{ student.student_code }} · {{ student.registration_no || '—' }}</p>
          <span class="badge" :class="statusBadgeClass">{{ displayStatus }}</span>
        </div>
        <div class="d-flex flex-wrap gap-2">
          <button
            v-if="canApprove && displayStatus === 'pending'"
            type="button"
            class="btn btn-sm btn-success"
            @click="approve"
          >
            Approve
          </button>
          <button
            v-if="canApprove && displayStatus === 'pending'"
            type="button"
            class="btn btn-sm btn-outline-danger"
            @click="reject"
          >
            Reject
          </button>
          <button
            v-if="canSoftDelete"
            type="button"
            class="btn btn-sm btn-outline-warning"
            @click="softDelete"
          >
            Move to Bin
          </button>
          <button
            v-if="canPermanentDelete"
            type="button"
            class="btn btn-sm btn-danger"
            @click="permanentDelete"
          >
            Delete Forever
          </button>
        </div>
      </div>

      <div class="card table-card">
        <div class="card-body">
          <p v-if="!canEdit" class="alert alert-warning py-2 small mb-3">
            View only — you do not have permission to edit students.
          </p>
          <StudentForm
            :key="studentId"
            :form="form"
            show-status
            show-rejection
            show-registration-no
            :photo-preview-url="student.photo_url || null"
            @update:form="onFormPatch"
            @photo-selected="photoFile = $event"
            @id-proof-selected="idProofFile = $event"
            @submit="save"
          >
            <template #actions>
              <p v-if="error" class="text-danger small mb-2" style="white-space: pre-line">{{ error }}</p>
              <p v-if="saveOk" class="text-success small mb-2">Saved successfully.</p>
              <div class="d-flex flex-wrap gap-2 pt-2">
                <button
                  v-if="canEdit"
                  type="submit"
                  class="btn btn-primary"
                  :disabled="saving"
                >
                  {{ saving ? 'Saving...' : 'Save Changes' }}
                </button>
                <router-link to="/students" class="btn btn-outline-secondary">Back to list</router-link>
              </div>
            </template>
          </StudentForm>
        </div>
      </div>
    </template>

    <div v-else class="alert alert-warning">
      Student record could not be loaded.
      <router-link to="/students" class="alert-link ms-2">← Back to Students</router-link>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { apiFetch, apiForm } from '@/api/client'
import { parseApiError, useFetchData } from '@/utils/apiHelpers'
import { buildStudentFormData, studentToForm } from '@/utils/studentForm'
import StudentForm from '@/components/StudentForm.vue'
import { alertError, confirmDelete, confirmDialog, promptText, toastSuccess } from '@/utils/swal'

const auth = useAuthStore()
const router = useRouter()
const route = useRoute()

const canEdit = computed(() => auth.can('student_edit'))
const canApprove = computed(() => auth.can('student_approve'))
const canSoftDelete = computed(() => auth.can('student_delete'))
const canPermanentDelete = computed(() => auth.can('bin_delete_permanent'))

const form = reactive(studentToForm({}))
const photoFile = ref(null)
const idProofFile = ref(null)
const saving = ref(false)
const error = ref('')
const saveOk = ref(false)
const loadError = ref('')

const studentId = computed(() => route.params.id)

const loadStudent = async () => {
  const res = await apiFetch(`/admin/students/${studentId.value}`)
  const row = res?.data ?? res
  if (!row?.id) {
    throw new Error('Student data not found in response.')
  }
  Object.assign(form, studentToForm(row))
  return row
}

const { data: student, pending, refresh, error: fetchErr } = useFetchData(loadStudent)

watch(fetchErr, (e) => {
  loadError.value = e ? parseApiError(e) : ''
})

watch(studentId, () => {
  if (studentId.value) {
    refresh()
  }
})

const displayStatus = computed(() => {
  const s = student.value?.status
  if (s && typeof s === 'object' && s.value) return s.value
  return String(s || 'pending')
})

const statusBadgeClass = computed(() => {
  const s = displayStatus.value
  if (s === 'approved') return 'text-bg-success'
  if (s === 'rejected') return 'text-bg-danger'
  if (s === 'pending') return 'text-bg-warning'
  return 'text-bg-secondary'
})

const onFormPatch = (patch) => {
  Object.assign(form, patch)
}

const save = async () => {
  if (!canEdit.value) return
  if (!form.name.trim()) {
    error.value = 'Student name is required.'
    return
  }
  if (form.mobile && !/^\d{10}$/.test(String(form.mobile).replace(/\D/g, ''))) {
    error.value = 'Mobile must be 10 digits.'
    return
  }

  saving.value = true
  error.value = ''
  saveOk.value = false
  try {
    await apiForm(
      `/admin/students/${studentId.value}/update`,
      buildStudentFormData(form, photoFile.value, idProofFile.value)
    )
    await refresh()
    saveOk.value = true
    toastSuccess('Student updated.')
    photoFile.value = null
    idProofFile.value = null
  } catch (e) {
    error.value = parseApiError(e)
    await alertError(error.value)
  } finally {
    saving.value = false
  }
}

const approve = async () => {
  try {
    await apiFetch(`/admin/students/${studentId.value}/approve`, { method: 'POST' })
    toastSuccess('Student approved.')
    await refresh()
  } catch (e) {
    await alertError(parseApiError(e))
  }
}

const reject = async () => {
  const reason = await promptText('Reject student', 'Rejection reason')
  if (!reason) return
  try {
    await apiFetch(`/admin/students/${studentId.value}/reject`, {
      method: 'POST',
      body: { reason },
    })
    toastSuccess('Student rejected.')
    await refresh()
  } catch (e) {
    await alertError(parseApiError(e))
  }
}

const softDelete = async () => {
  const ok = await confirmDialog(
    'Move to recycle bin?',
    'This student will be hidden from lists until restored from Recycle Bin.',
    'Move to Bin'
  )
  if (!ok) return
  try {
    await apiFetch(`/admin/students/${studentId.value}`, { method: 'DELETE' })
    toastSuccess('Student moved to recycle bin.')
    router.push('/students')
  } catch (e) {
    await alertError(parseApiError(e))
  }
}

const permanentDelete = async () => {
  const ok = await confirmDelete('this student permanently')
  if (!ok) return
  const ok2 = await confirmDialog(
    'Permanent delete',
    'This cannot be undone. The student record will be removed forever.',
    'Delete Forever'
  )
  if (!ok2) return
  try {
    await apiFetch(`/admin/students/${studentId.value}/force`, { method: 'DELETE' })
    toastSuccess('Student permanently deleted.')
    router.push('/students')
  } catch (e) {
    await alertError(parseApiError(e))
  }
}
</script>
