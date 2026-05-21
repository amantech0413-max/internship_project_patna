<template>
  <div>
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
      <p class="text-muted small mb-0">Full internship students — approve, edit, groups, documents</p>
      <div class="d-flex gap-2">
        <button
          v-if="canExport"
          type="button"
          class="btn btn-outline-secondary btn-sm"
          :disabled="exporting"
          @click="exportCsv"
        >
          <i class="bi bi-download me-1" />{{ exporting ? '...' : 'Export CSV' }}
        </button>
        <router-link v-if="canCreate" to="/students/create" class="btn btn-primary btn-sm">
          <i class="bi bi-plus-lg me-1" /> Add Full Student
        </router-link>
      </div>
    </div>

    <div class="card table-card mb-3">
      <div class="card-body row g-2">
        <div class="col-md-3">
          <input v-model="filters.search" class="form-control" placeholder="Search name, code, mobile..." @keyup.enter="refresh()" />
        </div>
        <div class="col-md-2">
          <input v-model="filters.mobile" class="form-control" placeholder="Mobile" maxlength="10" />
        </div>
        <div class="col-md-2">
          <select v-model="filters.status" class="form-select">
            <option value="">All Status</option>
            <option value="pending">Pending</option>
            <option value="approved">Approved</option>
            <option value="rejected">Rejected</option>
          </select>
        </div>
        <div class="col-md-2">
          <select v-model="filters.internship_mode" class="form-select">
            <option value="">All Modes</option>
            <option value="online">Online</option>
            <option value="offline">Offline</option>
          </select>
        </div>
        <div class="col-md-3">
          <button class="btn btn-dark w-100" :disabled="pending" @click="refresh()">{{ pending ? 'Loading...' : 'Apply' }}</button>
        </div>
      </div>
    </div>

    <p v-if="loadError" class="alert alert-danger">{{ loadError }}</p>

    <DataTable :columns="columns" :rows="rows">
      <template #actions="{ row }">
        <router-link v-if="canEdit" :to="`/students/${row.id}`" class="btn btn-sm btn-outline-primary me-1">Edit</router-link>
        <button
          v-if="canApprove && row.status === 'pending'"
          class="btn btn-sm btn-outline-success me-1"
          @click="approve(row.id)"
        >
          Approve
        </button>
        <button
          v-if="canApprove && row.status === 'pending'"
          class="btn btn-sm btn-outline-danger"
          @click="reject(row.id)"
        >
          Reject
        </button>
        <span v-if="!canEdit && !canApprove" class="text-muted small">View only</span>
      </template>
    </DataTable>

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
const { can } = useAuthStore()
const toast = useToastStore()
const exporting = ref(false)

const canCreate = computed(() => auth.can('student_create'))
const canEdit = computed(() => auth.can('student_edit'))
const canApprove = computed(() => auth.can('student_approve'))
const canExport = computed(() => auth.can('student_view'))

const filters = reactive({ search: '', mobile: '', status: '', internship_mode: '' })
const currentPage = ref(1)

const columns = [
  { key: 'student_code', label: 'Code' },
  { key: 'name', label: 'Name' },
  { key: 'mobile', label: 'Mobile' },
  { key: 'college_name', label: 'College' },
  { key: 'internship_mode', label: 'Mode' },
  { key: 'status', label: 'Status' },
]

const buildQuery = () => {
  const query = new URLSearchParams({ page: String(currentPage.value), per_page: '15' })
  Object.entries(filters).forEach(([k, v]) => v && query.set(k, v))
  return query
}

const fetchStudents = async () => {
  const res = await apiFetch(
    `/admin/students?${buildQuery()}`
  )
  return unwrapList(res)
}

const { data, pending, error, refresh } = useFetchData(fetchStudents)

const loadError = computed(() => (error.value ? parseApiError(error.value) : ''))
const meta = computed(() => data.value?.meta ?? null)
const rows = computed(() =>
  (data.value?.items ?? []).map((s) => ({
    id: s.id,
    student_code: s.student_code || '—',
    name: s.name || s.student_name,
    mobile: s.mobile || s.mobile_number,
    college_name: s.college_name || s.college?.college_name || '—',
    internship_mode: s.internship_mode || '—',
    status: s.status,
  }))
)

const goToPage = async (p) => {
  currentPage.value = p
  await refresh()
}

const approve = async (id) => {
  try {
    await apiFetch(`/admin/students/${id}/approve`, { method: 'POST' })
    await refresh()
  } catch (e) {
    alert(parseApiError(e))
  }
}

const exportCsv = async () => {
  exporting.value = true
  try {
    await apiDownload('/admin/students/export', 'students_export.csv')
    useToastStore().show('Export downloaded.', 'success')
  } catch (e) {
    useToastStore().show((e).message || 'Export failed', 'danger')
  } finally {
    exporting.value = false
  }
}

const reject = async (id) => {
  const reason = prompt('Rejection reason?')
  if (!reason) return
  try {
    await apiFetch(`/admin/students/${id}/reject`, { method: 'POST', body: { reason } })
    await refresh()
  } catch (e) {
    alert(parseApiError(e))
  }
}
</script>
