<template>
  <div class="max-w-3xl mx-auto w-full">
    <div v-if="loadError" class="bg-red-50 border border-red-200 rounded-xl p-6 text-center">
      <p class="text-red-700">{{ loadError }}</p>
      <router-link to="/students" class="inline-block mt-4 text-blue-700 font-medium">← Back to Students</router-link>
    </div>

    <div v-else-if="pending" class="text-slate-500 py-12 text-center">Loading student...</div>

    <template v-else-if="student">
      <div class="flex flex-col gap-4 mb-6">
        <div>
          <h2 class="text-xl sm:text-2xl font-bold">Edit Student</h2>
          <p class="text-sm text-slate-500 mt-1 break-all">
            {{ student.student_code }}
          </p>
          <span class="inline-block mt-2 px-2.5 py-1 rounded text-xs font-medium uppercase" :class="statusClass">
            {{ student.status }}
          </span>
        </div>
        <div class="flex flex-wrap gap-2">
          <button
            v-if="canApprove && student.status === 'pending'"
            type="button"
            class="flex-1 sm:flex-none px-4 py-2.5 bg-green-700 text-white rounded-lg text-sm font-medium"
            @click="approve"
          >
            Approve
          </button>
          <button
            v-if="canApprove && student.status === 'pending'"
            type="button"
            class="flex-1 sm:flex-none px-4 py-2.5 bg-red-600 text-white rounded-lg text-sm font-medium"
            @click="reject"
          >
            Reject
          </button>
        </div>
      </div>

      <div class="bg-white rounded-xl border shadow-sm p-4 sm:p-6">
        <p v-if="!canEdit" class="text-sm text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2 mb-4">
          View only — you do not have permission to edit students.
        </p>
        <StudentForm
          :form="form"
          show-status
          show-rejection
          :photo-preview-url="(student.photo_url) || null"
          @update:form="Object.assign(form, $event)"
          @photo-selected="photoFile = $event"
          @id-proof-selected="idProofFile = $event"
          @submit="save"
        >
          <template #actions>
            <p v-if="error" class="text-sm text-red-600 whitespace-pre-line">{{ error }}</p>
            <p v-if="saveOk" class="text-sm text-green-700">Saved successfully.</p>
            <div class="flex flex-col sm:flex-row gap-3 pt-4">
              <button
                v-if="canEdit"
                type="submit"
                class="w-full sm:w-auto px-6 py-3 bg-blue-700 text-white rounded-lg font-medium disabled:opacity-60"
                :disabled="saving"
              >
                {{ saving ? 'Saving...' : 'Save Changes' }}
              </button>
              <router-link to="/students" class="w-full sm:w-auto px-6 py-3 border rounded-lg text-center">Back</router-link>
            </div>
          </template>
        </StudentForm>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { apiFetch, apiForm, apiDownload, getPublicApi } from '@/api/client'
import { parseApiError, unwrapList, useFetchData } from '@/utils/apiHelpers'
import { useToastStore } from '@/stores/toast'
import { buildStudentFormData, emptyStudentForm, studentToForm } from '@/utils/studentForm'
const auth = useAuthStore()
const route = useRoute()
const { can } = useAuthStore()

const canEdit = computed(() => auth.can('student_edit'))
const canApprove = computed(() => auth.can('student_approve'))

const form = reactive(studentToForm({}))
const photoFile = ref(null)
const idProofFile = ref(null)
const saving = ref(false)
const error = ref('')
const saveOk = ref(false)
const loadError = ref('')

const { data: student, pending, refresh, error: fetchErr } = useFetchData(async () => {
  const res = await apiFetch(`/admin/students/${route.params.id}`)
  Object.assign(form, studentToForm(res.data))
  return res.data
})

watch(fetchErr, (e) => {
  if (e) loadError.value = parseApiError(e)
})

const statusClass = computed(() => {
  const s = student.value?.status
  if (s === 'approved') return 'bg-green-100 text-green-800'
  if (s === 'rejected') return 'bg-red-100 text-red-800'
  return 'bg-yellow-100 text-yellow-800'
})

const save = async () => {
  if (!form.name.trim()) {
    error.value = 'Student name is required.'
    return
  }
  if (form.mobile && !/^\d{10}$/.test(form.mobile.replace(/\D/g, ''))) {
    error.value = 'Mobile must be 10 digits.'
    return
  }

  saving.value = true
  error.value = ''
  saveOk.value = false
  try {
    await apiForm(`/admin/students/${route.params.id}/update`, buildStudentFormData(form, photoFile.value, idProofFile.value))
    await refresh()
    saveOk.value = true
    photoFile.value = null
    idProofFile.value = null
  } catch (e) {
    error.value = parseApiError(e)
  } finally {
    saving.value = false
  }
}

const approve = async () => {
  try {
    await apiFetch(`/admin/students/${route.params.id}/approve`, { method: 'POST' })
    await refresh()
  } catch (e) {
    alert(parseApiError(e))
  }
}

const reject = async () => {
  const reason = prompt('Rejection reason?')
  if (!reason) return
  try {
    await apiFetch(`/admin/students/${route.params.id}/reject`, { method: 'POST', body: { reason } })
    await refresh()
  } catch (e) {
    alert(parseApiError(e))
  }
}
</script>
