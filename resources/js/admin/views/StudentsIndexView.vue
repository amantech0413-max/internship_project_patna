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
      <div class="card-body row g-2 align-items-end">
        <div class="col-md-3">
          <label class="form-label small text-muted mb-1">Search</label>
          <input
            v-model="filters.search"
            class="form-control"
            placeholder="Name, code, mobile..."
            @keyup.enter="applyFilters"
          />
        </div>
        <div class="col-md-2">
          <label class="form-label small text-muted mb-1">Mobile</label>
          <input
            v-model="filters.mobile"
            class="form-control"
            placeholder="10 digits"
            maxlength="10"
            @keyup.enter="applyFilters"
          />
        </div>
        <div class="col-md-2">
          <label class="form-label small text-muted mb-1">Status</label>
          <select v-model="filters.status" class="form-select">
            <option value="">All Status</option>
            <option value="pending">Pending</option>
            <option value="approved">Approved</option>
            <option value="rejected">Rejected</option>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label small text-muted mb-1">Mode</label>
          <select v-model="filters.internship_mode" class="form-select">
            <option value="">All Modes</option>
            <option value="online">Online</option>
            <option value="offline">Offline</option>
          </select>
        </div>
        <div class="col-md-3 d-flex gap-2">
          <button type="button" class="btn btn-dark flex-grow-1" @click="applyFilters">
            <i class="bi bi-funnel me-1" />Filter
          </button>
          <button type="button" class="btn btn-outline-secondary" title="Clear" @click="resetFilters">
            <i class="bi bi-x-lg" />
          </button>
        </div>
      </div>
    </div>

    <p v-if="loadError" class="alert alert-danger">{{ loadError }}</p>

    <div class="card table-card">
      <div class="card-body table-responsive">
        <table ref="tableRef" class="table table-striped table-hover w-100">
          <thead class="table-light">
            <tr>
              <th>Code</th>
              <th>Name</th>
              <th>Mobile</th>
              <th>College</th>
              <th>Mode</th>
              <th>Status</th>
              <th>Added by</th>
              <th>Register Date</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody />
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onBeforeUnmount, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { apiDownload } from '@/api/client'
import {
  initServerDataTable,
  reloadDataTable,
  destroyDataTable,
  formatDateTime,
  statusBadge,
} from '@/utils/serverDataTable'
import { alertError, confirmDelete, confirmDialog, promptText, toastSuccess } from '@/utils/swal'
import { parseApiError } from '@/utils/apiHelpers'
import { useToastStore } from '@/stores/toast'
import { apiFetch } from '@/api/client'

const router = useRouter()
const auth = useAuthStore()
const toast = useToastStore()
const exporting = ref(false)
const tableRef = ref(null)
let dt = null

const canCreate = computed(() => auth.can('student_create'))
const canEdit = computed(() => auth.can('student_edit'))
const canApprove = computed(() => auth.can('student_approve'))
const canExport = computed(() => auth.can('student_view'))
const canDelete = computed(() => auth.can('student_delete'))
const canManageStaff = computed(() => auth.can('staff_manage'))

const filters = reactive({ search: '', mobile: '', status: '', internship_mode: '' })

const getFilterParams = () => {
  const params = {}
  if (filters.search?.trim()) params.search = filters.search.trim()
  if (filters.mobile?.trim()) params.mobile = filters.mobile.trim()
  if (filters.status) params.status = filters.status
  if (filters.internship_mode) params.internship_mode = filters.internship_mode
  return params
}

const rowStatus = (row) => {
  const s = row?.status
  if (s && typeof s === 'object' && s.value) return s.value
  return String(s || '')
}

const escHtml = (value) =>
  String(value)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')

const renderAddedBy = (row) => {
  const u = row.added_by_user
  if (!u?.name) return '—'

  const canLink = canManageStaff.value && u.id && u.is_assignable_staff
  if (canLink) {
    return `<a href="#" class="text-decoration-none fw-medium" data-dt-action="view-staff" data-staff-id="${u.id}">${escHtml(u.name)}</a>`
  }

  return escHtml(u.name)
}

const renderActions = (row) => {
  const id = row.id
  const status = rowStatus(row)
  let html = ''
  if (canEdit.value) {
    html += `<button type="button" class="btn btn-sm btn-outline-primary me-1" data-dt-action="edit" data-id="${id}">Edit</button>`
  }
  if (canApprove.value && status === 'pending') {
    html += `<button type="button" class="btn btn-sm btn-outline-success me-1" data-dt-action="approve" data-id="${id}">Approve</button>`
    html += `<button type="button" class="btn btn-sm btn-outline-danger" data-dt-action="reject" data-id="${id}">Reject</button>`
  }
  if (!html) html = '<span class="text-muted small">View only</span>'
  return html
}

const loadError = ref('')

const initTable = () => {
  if (!tableRef.value) return

  dt = initServerDataTable(tableRef.value, {
    url: '/admin/students',
    pageLength: 10,
    defaultOrder: [[7, 'desc']],
    defaultSortBy: 'created_at',
    columnSortKeys: [
      'student_code',
      'student_name',
      'mobile_number',
      'college_name',
      'internship_mode',
      'status',
      'created_by',
      'created_at',
      null,
    ],
    getFilterParams,
    onError: (err) => {
      loadError.value = parseApiError(err) || 'Failed to load students.'
    },
    columns: [
      { data: 'student_code', defaultContent: '—' },
      {
        data: 'name',
        render: (_d, _t, row) => row.name || row.student_name || '—',
      },
      {
        data: 'mobile',
        render: (_d, _t, row) => row.mobile || row.mobile_number || '—',
      },
      {
        data: 'college_name',
        render: (_d, _t, row) => row.college_name || row.college?.college_name || '—',
      },
      { data: 'internship_mode', defaultContent: '—' },
      {
        data: 'status',
        render: (data) => statusBadge(rowStatus({ status: data })),
      },
      {
        data: 'added_by_user',
        orderable: true,
        defaultContent: '—',
        render: (_d, _t, row) => renderAddedBy(row),
      },
      {
        data: 'created_at',
        render: (data) => formatDateTime(data),
      },
      {
        data: null,
        orderable: false,
        searchable: false,
        className: 'text-nowrap',
        render: (_d, _t, row) => renderActions(row),
      },
    ],
  })
}

const onTableClick = async (e) => {
  const staffLink = e.target.closest('[data-dt-action="view-staff"]')
  if (staffLink) {
    e.preventDefault()
    const staffId = staffLink.dataset.staffId
    if (staffId && canManageStaff.value) {
      router.push({ name: 'staff-users', query: { edit: staffId } })
    }
    return
  }

  const btn = e.target.closest('[data-dt-action]')
  if (!btn) return
  const id = btn.dataset.id
  const action = btn.dataset.dtAction

  if (action === 'edit') {
    router.push(`/students/${id}`)
    return
  }

  if (action === 'approve') {
    const ok = await confirmDialog('Approve student?', 'This will mark the student as approved.', 'Approve')
    if (!ok) return
    try {
      await apiFetch(`/admin/students/${id}/approve`, { method: 'POST' })
      toastSuccess('Student approved.')
      reloadDataTable(dt)
    } catch (err) {
      await alertError(parseApiError(err))
    }
    return
  }

  if (action === 'reject') {
    const reason = await promptText('Reject student', 'Rejection reason')
    if (!reason) return
    try {
      await apiFetch(`/admin/students/${id}/reject`, { method: 'POST', body: { reason } })
      toastSuccess('Student rejected.')
      reloadDataTable(dt)
    } catch (err) {
      await alertError(parseApiError(err))
    }
    return
  }

  if (action === 'delete') {
    const ok = await confirmDelete('this student')
    if (!ok) return
    try {
      await apiFetch(`/admin/students/${id}`, { method: 'DELETE' })
      toastSuccess('Student moved to recycle bin.')
      reloadDataTable(dt)
    } catch (err) {
      await alertError(parseApiError(err))
    }
  }
}

const applyFilters = () => reloadDataTable(dt)

const resetFilters = () => {
  filters.search = ''
  filters.mobile = ''
  filters.status = ''
  filters.internship_mode = ''
  applyFilters()
}

onMounted(async () => {
  await nextTick()
  initTable()
  tableRef.value?.addEventListener('click', onTableClick)
})

onBeforeUnmount(() => {
  tableRef.value?.removeEventListener('click', onTableClick)
  destroyDataTable(dt)
  dt = null
})

const exportCsv = async () => {
  exporting.value = true
  try {
    await apiDownload('/admin/students/export', 'students_export.csv')
    toast.show('Export downloaded.', 'success')
  } catch (e) {
    toast.show(parseApiError(e) || 'Export failed', 'danger')
  } finally {
    exporting.value = false
  }
}
</script>
