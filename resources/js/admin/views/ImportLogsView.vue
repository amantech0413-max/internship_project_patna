<template>
  <div>
    <div class="card table-card mb-3">
      <div class="card-body row g-2">
        <div class="col-md-6">
          <select v-model="collegeId" class="form-select">
            <option value="">All Colleges</option>
            <option v-for="c in colleges" :key="c.id" :value="c.id">{{ c.college_name }}</option>
          </select>
        </div>
        <div class="col-md-3">
          <button class="btn btn-dark w-100" :disabled="pending" @click="load(1)">Filter</button>
        </div>
      </div>
    </div>

    <p v-if="loadError" class="alert alert-danger">{{ loadError }}</p>

    <div class="card table-card">
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead class="table-light">
            <tr>
              <th>Date</th>
              <th>College</th>
              <th>File</th>
              <th>Total</th>
              <th>Success</th>
              <th>Failed</th>
              <th>Skipped</th>
              <th>By</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in rows" :key="row.id">
              <td>{{ formatDate(row.created_at) }}</td>
              <td>{{ (row.college )?.college_name || '—' }}</td>
              <td>{{ row.file_name || '—' }}</td>
              <td>{{ row.total_rows }}</td>
              <td class="text-success fw-medium">{{ row.success_count }}</td>
              <td class="text-danger fw-medium">{{ row.failed_count }}</td>
              <td>{{ row.skipped_count }}</td>
              <td>{{ (row.importer )?.name || '—' }}</td>
            </tr>
            <tr v-if="!rows.length && !pending">
              <td colspan="8" class="text-center text-muted py-4">No import logs</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <PaginationBar v-if="meta" :meta="meta" @page="goToPage" />
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
const colleges = ref([])
const collegeId = ref('')
const currentPage = ref(1)

const fetchLogs = async () => {
  const query = new URLSearchParams({ page: String(currentPage.value), per_page: '15' })
  if (collegeId.value) query.set('college_id', collegeId.value)
  const res = await apiFetch(
    `/admin/import-logs?${query}`
  )
  return unwrapList(res)
}

const { data, pending, error, refresh } = useFetchData(fetchLogs)

const loadError = computed(() => (error.value ? parseApiError(error.value) : ''))
const meta = computed(() => data.value?.meta ?? null)
const rows = computed(() => data.value?.items ?? [])

const load = async (page = 1) => {
  currentPage.value = page
  await refresh()
}

const goToPage = (p) => load(p)

const formatDate = (val) => {
  if (!val) return '—'
  return new Date(String(val)).toLocaleString('en-IN', { dateStyle: 'medium', timeStyle: 'short' })
}

onMounted(async () => {
  const res = await apiFetch('/admin/colleges/dropdown')
  colleges.value = res.data || []
})
</script>
