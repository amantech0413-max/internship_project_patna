<template>
  <div>
    <div class="card table-card mb-3">
      <div class="card-body admin-filter-bar">
        <div class="admin-filter-fields">
          <div class="admin-filter-field">
            <label class="form-label small text-muted mb-1">College</label>
            <select v-model="collegeId" class="form-select">
              <option value="">All Colleges</option>
              <option v-for="c in colleges" :key="c.id" :value="c.id">{{ c.college_name }}</option>
            </select>
          </div>
        </div>
        <div class="admin-filter-actions">
          <button type="button" class="btn btn-dark text-nowrap" :disabled="pending" @click="load(1)">
            <i class="bi bi-funnel me-1" />Filter
          </button>
        </div>
      </div>
    </div>

    <p v-if="loadError" class="alert alert-danger">{{ loadError }}</p>

    <div class="card table-card">
      <div class="px-3 py-2 border-bottom bg-body-tertiary">
        <TablePager layout="per-page-only" :meta="pagerMeta" :per-page="perPage" @update:per-page="onPerPage" />
      </div>
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
            <tr v-if="pending">
              <td colspan="8" class="text-center text-muted py-4">Loading...</td>
            </tr>
            <template v-else>
              <tr v-for="row in rows" :key="row.id">
                <td>{{ formatDate(row.created_at) }}</td>
                <td>{{ row.college?.college_name || '—' }}</td>
                <td>{{ row.file_name || '—' }}</td>
                <td>{{ row.total_rows }}</td>
                <td class="text-success fw-medium">{{ row.success_count }}</td>
                <td class="text-danger fw-medium">{{ row.failed_count }}</td>
                <td>{{ row.skipped_count }}</td>
                <td>{{ row.importer?.name || '—' }}</td>
              </tr>
              <tr v-if="!rows.length">
                <td colspan="8" class="text-center text-muted py-4">No import logs</td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>
      <div class="px-3 pb-3">
        <TablePager v-if="meta" layout="pagination-only" :meta="meta" :per-page="perPage" @page="load" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { apiFetch } from '@/api/client'
import { parseApiError, unwrapList } from '@/utils/apiHelpers'
import TablePager from '@/components/TablePager.vue'
import { DEFAULT_PER_PAGE, fallbackMeta } from '@/utils/pagination'

const colleges = ref([])
const collegeId = ref('')
const currentPage = ref(1)
const perPage = ref(DEFAULT_PER_PAGE)
const rows = ref([])
const meta = ref(null)
const pending = ref(false)
const loadError = ref('')

const pagerMeta = computed(() => meta.value ?? fallbackMeta(perPage.value))

const load = async (page = 1) => {
  currentPage.value = page
  pending.value = true
  loadError.value = ''
  try {
    const query = new URLSearchParams({
      page: String(page),
      per_page: String(perPage.value),
    })
    if (collegeId.value) query.set('college_id', collegeId.value)
    const res = await apiFetch(`/admin/import-logs?${query}`)
    const { items, meta: m } = unwrapList(res)
    rows.value = items
    meta.value = m
  } catch (e) {
    loadError.value = parseApiError(e)
    rows.value = []
    meta.value = null
  } finally {
    pending.value = false
  }
}

const onPerPage = (n) => {
  perPage.value = n
  load(1)
}

const formatDate = (val) => {
  if (!val) return '—'
  return new Date(String(val)).toLocaleString('en-IN', { dateStyle: 'medium', timeStyle: 'short' })
}

onMounted(async () => {
  try {
    const res = await apiFetch('/admin/colleges/dropdown')
    colleges.value = unwrapList(res).items
  } catch {
    colleges.value = []
  }
  await load(1)
})
</script>
