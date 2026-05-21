<template>
  <div class="card table-card shadow-sm">
    <div class="card-body">
      <h2 class="h5 fw-bold">Reports & Export</h2>
      <p class="text-muted small">Export student data as CSV (uses your login token via API).</p>
      <button type="button" class="btn btn-dark mt-3" :disabled="downloading" @click="exportCsv">
        <i class="bi bi-download me-1" />{{ downloading ? 'Exporting...' : 'Export Students CSV' }}
      </button>
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
const auth = useAuthStore()
const downloading = ref(false)

const exportCsv = async () => {
  downloading.value = true
  try {
    await apiDownload('/admin/students/export', 'students_export.csv')
    useToastStore().show('CSV downloaded successfully.', 'success')
  } catch (e) {
    useToastStore().show((e).message || 'Export failed', 'danger')
  } finally {
    downloading.value = false
  }
}
</script>
