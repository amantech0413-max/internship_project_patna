<template>
  <div>
    <p class="text-muted small mb-3">
      Staff bulk entries — filter by college and date range. See who added how many students (name + mobile).
    </p>

    <div class="card table-card mb-3">
      <div class="card-body admin-filter-bar">
        <div class="admin-filter-fields">
          <div class="admin-filter-field">
            <label class="form-label small text-muted mb-1">College</label>
            <select v-model="filters.college_id" class="form-select">
              <option value="">All colleges</option>
              <option v-for="c in colleges" :key="c.id" :value="String(c.id)">{{ c.college_name }}</option>
            </select>
          </div>
          <div class="admin-filter-field admin-filter-field--narrow">
            <label class="form-label small text-muted mb-1">Date from</label>
            <input v-model="filters.date_from" type="date" class="form-control" />
          </div>
          <div class="admin-filter-field admin-filter-field--narrow">
            <label class="form-label small text-muted mb-1">Date to</label>
            <input v-model="filters.date_to" type="date" class="form-control" />
          </div>
          <div v-if="auth.isAdmin" class="admin-filter-field">
            <label class="form-label small text-muted mb-1">Added by (staff)</label>
            <select v-model="filters.created_by" class="form-select">
              <option value="">All staff</option>
              <option v-for="u in staffUsers" :key="u.id" :value="String(u.id)">{{ u.name }}</option>
            </select>
          </div>
        </div>
        <div class="admin-filter-actions">
          <button type="button" class="btn btn-dark text-nowrap" :disabled="loading" @click="loadReport(1)">
            <i class="bi bi-funnel me-1" />{{ loading ? '...' : 'Apply' }}
          </button>
        </div>
      </div>
    </div>

    <p v-if="error" class="alert alert-danger">{{ error }}</p>

    <div v-if="summary.length" class="row g-3 mb-4">
      <div v-for="row in summary" :key="row.created_by" class="col-md-4 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body py-3">
            <p class="small text-muted mb-1">Staff</p>
            <p class="fw-semibold mb-1">{{ row.staff_name }}</p>
            <p class="h4 mb-0 text-primary">{{ row.total }}</p>
            <p class="small text-muted mb-0">entries in range</p>
          </div>
        </div>
      </div>
    </div>

    <div class="card table-card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span class="fw-semibold">Entries</span>
        <span v-if="total !== null" class="badge text-bg-secondary">{{ total }} total</span>
      </div>
      <div class="px-3 py-2 border-bottom bg-body-tertiary">
        <TablePager
          layout="per-page-only"
          :meta="pagerMeta"
          :per-page="perPage"
          @update:per-page="onPerPage"
        />
      </div>
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead class="table-light">
            <tr>
              <th>Date</th>
              <th>Name</th>
              <th>Mobile</th>
              <th>College</th>
              <th>Added by</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading">
              <td colspan="5" class="text-center text-muted py-4">Loading...</td>
            </tr>
            <template v-else>
              <tr v-for="row in entries" :key="row.id">
                <td class="small">{{ formatDate(row.created_at) }}</td>
                <td>{{ row.student_name }}</td>
                <td>{{ row.mobile_number }}</td>
                <td>{{ row.college_name || row.college?.college_name || '—' }}</td>
                <td>
                  <a
                    v-if="canLinkStaff(row)"
                    href="#"
                    class="text-decoration-none fw-medium"
                    @click.prevent="openStaff(row)"
                  >{{ row.added_by_user?.name || '—' }}</a>
                  <span v-else>{{ row.added_by_user?.name || '—' }}</span>
                </td>
              </tr>
              <tr v-if="!entries.length">
                <td colspan="5" class="text-center text-muted py-4">No entries for selected filters</td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>
      <div class="px-3 pb-3">
        <TablePager
          v-if="entriesMeta"
          layout="pagination-only"
          :meta="entriesMeta"
          :per-page="perPage"
          @page="loadReport"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { apiFetch } from '@/api/client'
import { parseApiError, unwrapList } from '@/utils/apiHelpers'
import { DEFAULT_PER_PAGE, fallbackMeta } from '@/utils/pagination'
import TablePager from '@/components/TablePager.vue'

const auth = useAuthStore()
const router = useRouter()

const colleges = ref([])
const staffUsers = ref([])
const entries = ref([])
const entriesMeta = ref(null)
const summary = ref([])
const total = ref(null)
const loading = ref(false)
const error = ref('')
const currentPage = ref(1)
const perPage = ref(DEFAULT_PER_PAGE)

const filters = reactive({
  college_id: '',
  date_from: '',
  date_to: '',
  created_by: '',
})

const pagerMeta = computed(() => entriesMeta.value ?? fallbackMeta(perPage.value))

const canLinkStaff = (row) =>
  auth.can('staff_manage') && row.added_by_user?.id && row.added_by_user?.is_assignable_staff

const openStaff = (row) => {
  router.push(`/staff-users?edit=${row.added_by_user.id}`)
}

const formatDate = (val) => {
  if (!val) return '—'
  return new Date(String(val)).toLocaleString('en-IN', { dateStyle: 'medium', timeStyle: 'short' })
}

const loadReport = async (page = 1) => {
  currentPage.value = page
  loading.value = true
  error.value = ''
  try {
    const params = new URLSearchParams({
      page: String(page),
      per_page: String(perPage.value),
    })
    if (filters.college_id) params.set('college_id', filters.college_id)
    if (filters.date_from) params.set('date_from', filters.date_from)
    if (filters.date_to) params.set('date_to', filters.date_to)
    if (filters.created_by && auth.isAdmin) params.set('created_by', filters.created_by)

    const res = await apiFetch(`/admin/reports/bulk-entries?${params}`)
    const data = res.data || {}
    summary.value = data.summary || []
    total.value = data.total ?? 0
    entries.value = Array.isArray(data.entries) ? data.entries : []
    entriesMeta.value = data.meta ?? null
  } catch (e) {
    error.value = parseApiError(e)
    entries.value = []
    summary.value = []
    total.value = null
    entriesMeta.value = null
  } finally {
    loading.value = false
  }
}

const onPerPage = (n) => {
  perPage.value = n
  loadReport(1)
}

onMounted(async () => {
  const today = new Date().toISOString().slice(0, 10)
  filters.date_from = today
  filters.date_to = today

  try {
    const cRes = await apiFetch('/admin/colleges/dropdown')
    colleges.value = unwrapList(cRes).items
  } catch {
    colleges.value = []
  }

  if (auth.isAdmin && auth.can('staff_manage')) {
    try {
      const sRes = await apiFetch('/admin/staff-users?per_page=200')
      staffUsers.value = unwrapList(sRes).items
    } catch {
      staffUsers.value = []
    }
  }

  await loadReport(1)
})
</script>
