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

<script setup lang="ts">
definePageMeta({ prefetch: false })

const { apiDownload } = useApi()
const toast = useToast()
const downloading = ref(false)

const exportCsv = async () => {
  downloading.value = true
  try {
    await apiDownload('/admin/students/export', 'students_export.csv')
    toast.show('CSV downloaded successfully.', 'success')
  } catch (e: unknown) {
    toast.show((e as Error).message || 'Export failed', 'danger')
  } finally {
    downloading.value = false
  }
}
</script>
