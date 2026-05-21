<template>
  <div class="max-w-3xl mx-auto w-full">
    <h2 class="text-xl sm:text-2xl font-bold">Add Student</h2>
    <p class="text-sm text-slate-500 mt-1">All fields · Default status: Approved</p>

    <div class="mt-4 sm:mt-6 bg-white rounded-xl border shadow-sm p-4 sm:p-6">
      <StudentForm
        :form="form"
        show-status
        @update:form="Object.assign(form, $event)"
        @photo-selected="photoFile = $event"
        @id-proof-selected="idProofFile = $event"
        @submit="submit"
      >
        <template #actions>
          <p v-if="error" class="text-sm text-red-600 whitespace-pre-line">{{ error }}</p>
          <p v-if="success" class="text-sm text-green-700">{{ success }}</p>
          <div class="flex flex-col sm:flex-row gap-3 pt-4">
            <button
              type="submit"
              class="w-full sm:w-auto px-6 py-3 bg-blue-700 text-white rounded-lg font-medium disabled:opacity-60"
              :disabled="loading"
            >
              {{ loading ? 'Saving...' : 'Create Student' }}
            </button>
            <NuxtLink to="/students" class="w-full sm:w-auto px-6 py-3 border rounded-lg text-center">Cancel</NuxtLink>
          </div>
        </template>
      </StudentForm>
    </div>
  </div>
</template>

<script setup lang="ts">
import { buildStudentFormData, emptyStudentForm } from '~/utils/studentForm'
import { parseApiError } from '~/composables/useApiHelpers'

const { apiForm } = useApi()
const form = reactive(emptyStudentForm())
const photoFile = ref<File | null>(null)
const idProofFile = ref<File | null>(null)
const loading = ref(false)
const error = ref('')
const success = ref('')

const submit = async () => {
  if (!form.name.trim() || form.name.trim().length < 2) {
    error.value = 'Student name is required (min 2 characters).'
    return
  }
  if (!/^\d{10}$/.test(form.mobile.replace(/\D/g, ''))) {
    error.value = 'Mobile must be 10 digits.'
    return
  }

  loading.value = true
  error.value = ''
  success.value = ''
  try {
    const res = await apiForm<{ success: boolean; data: Record<string, unknown> }>(
      '/admin/students',
      buildStudentFormData(form, photoFile.value, idProofFile.value)
    )
    success.value = `Created: ${res.data.student_code}`
    setTimeout(() => navigateTo(`/students/${res.data.id}`), 800)
  } catch (e: unknown) {
    error.value = parseApiError(e)
  } finally {
    loading.value = false
  }
}
</script>
