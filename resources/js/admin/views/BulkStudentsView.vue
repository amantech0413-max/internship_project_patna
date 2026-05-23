<template>
  <div>
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
      <p class="text-muted small mb-0">Staff bulk entries — name, mobile, college</p>
      <button v-if="canCreate" type="button" class="btn btn-primary btn-sm" @click="openCreate">
        <i class="bi bi-plus-lg me-1" />Add Entry
      </button>
    </div>

    <div class="card table-card mb-3">
      <div class="card-body admin-filter-bar">
        <div class="admin-filter-fields">
          <div class="admin-filter-field">
            <label class="form-label small text-muted mb-1">Search</label>
            <input v-model="filters.search" class="form-control" placeholder="Name or mobile..." @keyup.enter="load(1)" />
          </div>
          <div class="admin-filter-field">
            <label class="form-label small text-muted mb-1">College</label>
            <select v-model="filters.college_id" class="form-select">
              <option value="">All</option>
              <option v-for="c in colleges" :key="c.id" :value="String(c.id)">{{ c.college_name }}</option>
            </select>
          </div>
          <div class="admin-filter-field admin-filter-field--narrow">
            <label class="form-label small text-muted mb-1">From</label>
            <input v-model="filters.date_from" type="date" class="form-control" />
          </div>
          <div class="admin-filter-field admin-filter-field--narrow">
            <label class="form-label small text-muted mb-1">To</label>
            <input v-model="filters.date_to" type="date" class="form-control" />
          </div>
          <div v-if="auth.isAdmin" class="admin-filter-field">
            <label class="form-label small text-muted mb-1">Added by</label>
            <select v-model="filters.created_by" class="form-select">
              <option value="">All staff</option>
              <option v-for="u in staffUsers" :key="u.id" :value="String(u.id)">{{ u.name }}</option>
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
              <th>Name</th>
              <th>Mobile</th>
              <th>College</th>
              <th v-if="auth.isAdmin">Added by</th>
              <th v-if="canEdit || canDelete">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in rows" :key="row.id">
              <td class="small">{{ formatDate(row.created_at) }}</td>
              <td>{{ row.student_name }}</td>
              <td>{{ row.mobile_number }}</td>
              <td>{{ row.college?.college_name || '—' }}</td>
              <td v-if="auth.isAdmin">
                <a
                  v-if="canLinkStaff(row)"
                  href="#"
                  class="text-decoration-none"
                  @click.prevent="openStaff(row)"
                >{{ row.added_by_user?.name || '—' }}</a>
                <span v-else>{{ row.added_by_user?.name || '—' }}</span>
              </td>
              <td v-if="canEdit || canDelete" class="text-nowrap">
                <button
                  v-if="canEdit"
                  type="button"
                  class="btn btn-sm btn-icon-action btn-outline-primary"
                  title="Edit entry"
                  aria-label="Edit entry"
                  @click="openEdit(row)"
                >
                  <i class="bi bi-pencil" />
                </button>
                <button
                  v-if="canDelete"
                  type="button"
                  class="btn btn-sm btn-icon-action btn-outline-danger"
                  title="Delete entry"
                  aria-label="Delete entry"
                  @click="remove(row)"
                >
                  <i class="bi bi-trash" />
                </button>
              </td>
            </tr>
            <tr v-if="!rows.length && !pending">
              <td :colspan="auth.isAdmin ? 6 : 5" class="text-center text-muted py-4">No records</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

      <div class="px-3 pb-3">
        <TablePager v-if="meta" layout="pagination-only" :meta="meta" :per-page="perPage" @page="load" />
      </div>

    <div ref="modalEl" class="modal fade" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <form @submit.prevent="save">
            <div class="modal-header">
              <h5 class="modal-title">{{ editingId ? 'Edit entry' : 'Add entry' }}</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" />
            </div>
            <div class="modal-body row g-3">
              <div class="col-12">
                <label class="form-label">College *</label>
                <select v-model="form.college_id" class="form-select" required>
                  <option value="">Choose...</option>
                  <option v-for="c in colleges" :key="'f-' + c.id" :value="String(c.id)">{{ c.college_name }}</option>
                </select>
              </div>
              <div class="col-12">
                <label class="form-label">Student Name *</label>
                <input v-model="form.student_name" class="form-control" required />
              </div>
              <div class="col-12">
                <label class="form-label">Mobile *</label>
                <input v-model="form.mobile_number" class="form-control" maxlength="15" required />
              </div>
              <p v-if="formError" class="col-12 text-danger small mb-0">{{ formError }}</p>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary" :disabled="saving">{{ saving ? 'Saving...' : 'Save' }}</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { Modal } from 'bootstrap'
import { useAuthStore } from '@/stores/auth'
import { apiFetch } from '@/api/client'
import { parseApiError, unwrapList } from '@/utils/apiHelpers'
import { useToastStore } from '@/stores/toast'
import { confirmDelete } from '@/utils/swal'
import TablePager from '@/components/TablePager.vue'
import { DEFAULT_PER_PAGE, fallbackMeta } from '@/utils/pagination'
import { normalizeIndianMobile } from '../utils/indianMobile'

const auth = useAuthStore()
const router = useRouter()
const toast = useToastStore()

const canCreate = computed(() => auth.can('bulk_student_create'))
const canEdit = computed(() => auth.can('bulk_student_edit'))
const canDelete = computed(() => auth.can('bulk_student_delete'))
const canManageStaff = computed(() => auth.can('staff_manage'))

const colleges = ref([])
const staffUsers = ref([])
const rows = ref([])
const meta = ref(null)
const pending = ref(false)
const loadError = ref('')
const currentPage = ref(1)
const perPage = ref(DEFAULT_PER_PAGE)

const pagerMeta = computed(() => meta.value ?? fallbackMeta(perPage.value))

const filters = reactive({
  search: '',
  college_id: '',
  date_from: '',
  date_to: '',
  created_by: '',
})

const modalEl = ref(null)
let modal = null
const editingId = ref(null)
const saving = ref(false)
const formError = ref('')
const form = reactive({ college_id: '', student_name: '', mobile_number: '' })

const buildQuery = (page) => {
  const q = new URLSearchParams({
    page: String(page),
    per_page: String(perPage.value),
    sort_by: 'created_at',
    sort_dir: 'desc',
  })
  if (!auth.isAdmin) q.set('mine', '1')
  if (filters.search.trim()) q.set('search', filters.search.trim())
  if (filters.college_id) q.set('college_id', filters.college_id)
  if (filters.date_from) q.set('date_from', filters.date_from)
  if (filters.date_to) q.set('date_to', filters.date_to)
  if (filters.created_by && auth.isAdmin) q.set('created_by', filters.created_by)
  return q
}

const onPerPage = (n) => {
  perPage.value = n
  load(1)
}

const load = async (page = 1) => {
  currentPage.value = page
  pending.value = true
  loadError.value = ''
  try {
    const res = await apiFetch(`/admin/bulk-students?${buildQuery(page)}`)
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

const formatDate = (val) => {
  if (!val) return '—'
  return new Date(String(val)).toLocaleString('en-IN', { dateStyle: 'short', timeStyle: 'short' })
}

const canLinkStaff = (row) => canManageStaff.value && row.added_by_user?.id && row.added_by_user?.is_assignable_staff
const openStaff = (row) => router.push(`/staff-users?edit=${row.added_by_user.id}`)

const resetForm = () => {
  form.college_id = ''
  form.student_name = ''
  form.mobile_number = ''
  formError.value = ''
  editingId.value = null
}

const openCreate = () => {
  resetForm()
  modal?.show()
}

const openEdit = (row) => {
  editingId.value = row.id
  form.college_id = String(row.college_id || row.college?.id || '')
  form.student_name = row.student_name
  form.mobile_number = row.mobile_number
  formError.value = ''
  modal?.show()
}

const save = async () => {
  const mobile = normalizeIndianMobile(form.mobile_number)
  if (!form.college_id || !form.student_name.trim() || !mobile) {
    formError.value = 'College, name, and valid mobile are required.'
    return
  }
  saving.value = true
  formError.value = ''
  try {
    const body = {
      college_id: Number(form.college_id),
      student_name: form.student_name.trim(),
      mobile_number: mobile,
    }
    if (editingId.value) {
      await apiFetch(`/admin/bulk-students/${editingId.value}`, { method: 'PUT', body })
      toast.show('Updated.', 'success')
    } else {
      await apiFetch('/admin/bulk-students', { method: 'POST', body })
      toast.show('Created.', 'success')
    }
    modal?.hide()
    await load(currentPage.value)
  } catch (e) {
    formError.value = parseApiError(e)
  } finally {
    saving.value = false
  }
}

const remove = async (row) => {
  const ok = await confirmDelete(`entry for ${row.student_name}`)
  if (!ok) return
  try {
    await apiFetch(`/admin/bulk-students/${row.id}`, { method: 'DELETE' })
    toast.show('Deleted.', 'success')
    await load(currentPage.value)
  } catch (e) {
    toast.show(parseApiError(e), 'danger')
  }
}

onMounted(async () => {
  if (modalEl.value) modal = new Modal(modalEl.value)
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
  await load(1)
})
</script>
