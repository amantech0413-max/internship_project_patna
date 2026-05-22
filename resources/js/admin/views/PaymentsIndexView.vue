<template>
  <div>
    <p class="text-muted small mb-3">Student registration payments. Status can be updated — records cannot be deleted.</p>

    <div class="card table-card mb-3">
      <div class="card-body admin-filter-bar">
        <div class="admin-filter-fields">
          <div class="admin-filter-field">
            <label class="form-label small text-muted mb-1">Search</label>
            <input v-model="filters.search" class="form-control" placeholder="Name, reg no, txn..." @keyup.enter="load(1)" />
          </div>
          <div class="admin-filter-field admin-filter-field--narrow">
            <label class="form-label small text-muted mb-1">Status</label>
            <select v-model="filters.status" class="form-select">
              <option value="">All</option>
              <option v-for="s in statusOptions" :key="s.value" :value="s.value">{{ s.label }}</option>
            </select>
          </div>
        </div>
        <div class="admin-filter-actions">
          <button type="button" class="btn btn-dark text-nowrap" :disabled="pending" @click="load(1)">Filter</button>
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
              <th>Student</th>
              <th>College</th>
              <th>Amount</th>
              <th>Txn ID</th>
              <th>Mode</th>
              <th>Status</th>
              <th>Screenshot</th>
              <th v-if="canChangeStatus">Action</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="pending">
              <td :colspan="canChangeStatus ? 9 : 8" class="text-center text-muted py-4">Loading...</td>
            </tr>
            <template v-else>
              <tr v-for="row in rows" :key="row.id">
                <td class="small">{{ formatDate(row.created_at) }}</td>
                <td>
                  <div class="fw-medium">{{ row.student?.student_name || '—' }}</div>
                  <div class="small text-muted">{{ row.student?.registration_no }}</div>
                </td>
                <td>{{ row.student?.college_name || '—' }}</td>
                <td>₹ {{ formatAmount(row.amount) }}</td>
                <td class="small">{{ row.transaction_id || '—' }}</td>
                <td>
                  <span class="badge" :class="row.payment_mode_offline ? 'text-bg-secondary' : 'text-bg-info'">
                    {{ row.payment_mode_offline ? 'Offline' : 'Online' }}
                  </span>
                </td>
                <td><span class="badge" :class="statusClass(row.status)">{{ row.status_label || row.status }}</span></td>
                <td>
                  <a v-if="row.screenshot_url" :href="row.screenshot_url" target="_blank" rel="noopener" class="small">View</a>
                  <span v-else class="text-muted small">—</span>
                </td>
                <td v-if="canChangeStatus">
                  <select
                    class="form-select form-select-sm"
                    :value="row.status"
                    style="min-width: 7rem"
                    @change="changeStatus(row, $event.target.value)"
                  >
                    <option v-for="s in statusOptions" :key="s.value" :value="s.value">{{ s.label }}</option>
                  </select>
                </td>
              </tr>
              <tr v-if="!rows.length">
                <td :colspan="canChangeStatus ? 9 : 8" class="text-center text-muted py-4">No payments</td>
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
import { computed, onMounted, reactive, ref } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { apiFetch } from '@/api/client'
import { parseApiError, unwrapList } from '@/utils/apiHelpers'
import { useToastStore } from '@/stores/toast'
import TablePager from '@/components/TablePager.vue'
import { DEFAULT_PER_PAGE, fallbackMeta } from '@/utils/pagination'

const auth = useAuthStore()
const toast = useToastStore()

const canChangeStatus = computed(() => auth.isAdmin || auth.can('payment_status'))

const statusOptions = [
  { value: 'pending', label: 'Pending' },
  { value: 'credited', label: 'Credited' },
  { value: 'failed', label: 'Failed' },
  { value: 'cancelled', label: 'Cancelled' },
  { value: 'refund', label: 'Refund' },
]

const rows = ref([])
const meta = ref(null)
const pending = ref(false)
const loadError = ref('')
const perPage = ref(DEFAULT_PER_PAGE)
const filters = reactive({ search: '', status: '' })

const pagerMeta = computed(() => meta.value ?? fallbackMeta(perPage.value))

const statusClass = (s) => {
  const m = {
    pending: 'text-bg-warning',
    credited: 'text-bg-success',
    failed: 'text-bg-danger',
    cancelled: 'text-bg-secondary',
    refund: 'text-bg-info',
  }
  return m[s] || 'text-bg-secondary'
}

const formatDate = (val) => {
  if (!val) return '—'
  return new Date(String(val)).toLocaleString('en-IN', { dateStyle: 'short', timeStyle: 'short' })
}

const formatAmount = (n) => Number(n || 0).toLocaleString('en-IN', { minimumFractionDigits: 2 })

const load = async (page = 1) => {
  pending.value = true
  loadError.value = ''
  try {
    const q = new URLSearchParams({ page: String(page), per_page: String(perPage.value) })
    if (filters.search.trim()) q.set('search', filters.search.trim())
    if (filters.status) q.set('status', filters.status)
    const res = await apiFetch(`/admin/payments?${q}`)
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

const changeStatus = async (row, status) => {
  if (row.status === status) return
  try {
    await apiFetch(`/admin/payments/${row.id}/status`, { method: 'PATCH', body: { status } })
    toast.show('Payment status updated.', 'success')
    await load(meta.value?.current_page || 1)
  } catch (e) {
    toast.show(parseApiError(e), 'danger')
    await load(meta.value?.current_page || 1)
  }
}

onMounted(() => load(1))
</script>
