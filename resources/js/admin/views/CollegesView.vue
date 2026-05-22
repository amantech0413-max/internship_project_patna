<template>
  <div>
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
      <p class="text-muted mb-0">Manage colleges for student entries</p>
      <button type="button" class="btn btn-primary" @click="openForm()">
        <i class="bi bi-plus-lg me-1" /> Add College
      </button>
    </div>

    <div class="card table-card mb-3">
      <div class="card-body row g-2">
        <div class="col-md-6">
          <input v-model="filters.search" class="form-control" placeholder="Search college..." @keyup.enter="applyFilters" />
        </div>
        <div class="col-md-3">
          <select v-model="filters.status" class="form-select">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>
        <div class="col-md-3">
          <button class="btn btn-dark w-100" @click="applyFilters">
            <i class="bi bi-funnel me-1" />Filter
          </button>
        </div>
      </div>
    </div>

    <p v-if="error" class="alert alert-danger">{{ error }}</p>

    <div class="card table-card">
      <div class="card-body table-responsive">
        <table ref="tableRef" class="table table-striped table-hover w-100">
          <thead class="table-light">
            <tr>
              <th>College Name</th>
              <th>Slug</th>
              <th>Address</th>
              <th>Contact</th>
              <th>Mobile</th>
              <th>Status</th>
              <th>Students</th>
              <th>Register Date</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody />
        </table>
      </div>
    </div>

    <div id="collegeModal" ref="modalEl" class="modal fade" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <form @submit.prevent="save">
            <div class="modal-header">
              <h5 class="modal-title">{{ editingId ? 'Edit College' : 'Add College' }}</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" />
            </div>
            <div class="modal-body row g-3">
              <div class="col-12">
                <label class="form-label">College Name *</label>
                <input v-model="form.college_name" class="form-control" required />
              </div>
              <div class="col-12">
                <label class="form-label">Registration Slug</label>
                <input
                  v-model="form.slug"
                  class="form-control"
                  placeholder="Auto from name if empty (e.g. my-college-name)"
                  pattern="[a-z0-9]+(-[a-z0-9]+)*"
                />
                <p class="form-text mb-0">Used in URL: /admin/register/<strong>slug</strong>. Must be unique.</p>
              </div>
              <p v-if="registrationUrl" class="col-12 small text-muted mb-0">
                Registration link:
                <a :href="registrationUrl" target="_blank" rel="noopener">{{ registrationUrl }}</a>
              </p>
              <div class="col-12">
                <label class="form-label">Address</label>
                <textarea v-model="form.address" class="form-control" rows="2" />
              </div>
              <div class="col-md-6">
                <label class="form-label">Contact Person</label>
                <input v-model="form.contact_person" class="form-control" />
              </div>
              <div class="col-md-6">
                <label class="form-label">Mobile (10 digits)</label>
                <input v-model="form.mobile_number" class="form-control" maxlength="10" />
              </div>
              <div class="col-12">
                <label class="form-label">Status</label>
                <select v-model="form.status" class="form-select">
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                </select>
              </div>
              <p v-if="formError" class="text-danger small mb-0">{{ formError }}</p>
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
import { ref, reactive, computed, onMounted, onBeforeUnmount, nextTick } from 'vue'
import { apiFetch } from '@/api/client'
import { parseApiError } from '@/utils/apiHelpers'
import { Modal } from 'bootstrap'
import {
  initServerDataTable,
  reloadDataTable,
  destroyDataTable,
  formatDateTime,
  statusBadge,
} from '@/utils/serverDataTable'
import { alertError, confirmDelete, toastSuccess } from '@/utils/swal'

const modalEl = ref(null)
const tableRef = ref(null)
let modal = null
let dt = null

const filters = reactive({ search: '', status: '' })
const error = ref('')
const editingId = ref(null)
const saving = ref(false)
const formError = ref('')

const form = reactive({
  college_name: '',
  slug: '',
  address: '',
  contact_person: '',
  mobile_number: '',
  status: 'active',
})

const registrationUrl = computed(() => {
  const s = String(form.slug || '').trim()
  return s ? `${window.location.origin}/admin/register/${s}` : ''
})

const getFilterParams = () => {
  const params = {}
  if (filters.search?.trim()) params.search = filters.search.trim()
  if (filters.status) params.status = filters.status
  return params
}

const initTable = () => {
  if (!tableRef.value) return
  dt = initServerDataTable(tableRef.value, {
    url: '/admin/colleges',
    pageLength: 10,
    defaultOrder: [[7, 'desc']],
    defaultSortBy: 'created_at',
    columnSortKeys: [
      'college_name',
      'slug',
      'address',
      'contact_person',
      'mobile_number',
      'status',
      'students_count',
      'created_at',
      null,
    ],
    getFilterParams,
    onError: (err) => {
      error.value = parseApiError(err) || 'Failed to load colleges.'
    },
    columns: [
      { data: 'college_name' },
      { data: 'slug', defaultContent: '—' },
      { data: 'address', defaultContent: '—' },
      { data: 'contact_person', defaultContent: '—' },
      { data: 'mobile_number', defaultContent: '—' },
      { data: 'status', render: (d) => statusBadge(d) },
      { data: 'students_count', defaultContent: '0' },
      { data: 'created_at', render: (d) => formatDateTime(d) },
      {
        data: null,
        orderable: false,
        searchable: false,
        render: (_d, _t, row) =>
          `<button type="button" class="btn btn-sm btn-outline-primary me-1" data-dt-action="edit" data-id="${row.id}">Edit</button>` +
          `<button type="button" class="btn btn-sm btn-outline-danger" data-dt-action="delete" data-id="${row.id}">Delete</button>`,
      },
    ],
  })
}

const onTableClick = async (e) => {
  const btn = e.target.closest('[data-dt-action]')
  if (!btn) return
  const id = Number(btn.dataset.id)
  if (btn.dataset.dtAction === 'edit') {
    try {
      const res = await apiFetch(`/admin/colleges/${id}`)
      openForm(res.data)
    } catch (err) {
      await alertError(parseApiError(err))
    }
    return
  }
  if (btn.dataset.dtAction === 'delete') {
    await remove(id)
  }
}

const applyFilters = () => reloadDataTable(dt)

onMounted(async () => {
  if (modalEl.value) modal = new Modal(modalEl.value)
  await nextTick()
  initTable()
  tableRef.value?.addEventListener('click', onTableClick)
})

onBeforeUnmount(() => {
  tableRef.value?.removeEventListener('click', onTableClick)
  destroyDataTable(dt)
})

const openForm = (row) => {
  formError.value = ''
  if (row) {
    editingId.value = row.id
    form.college_name = String(row.college_name || '')
    form.slug = String(row.slug || '')
    form.address = String(row.address || '')
    form.contact_person = String(row.contact_person || '')
    form.mobile_number = String(row.mobile_number || '')
    form.status = String(row.status || 'active')
  } else {
    editingId.value = null
    Object.assign(form, {
      college_name: '',
      slug: '',
      address: '',
      contact_person: '',
      mobile_number: '',
      status: 'active',
    })
  }
  modal?.show()
}

const save = async () => {
  saving.value = true
  formError.value = ''
  try {
    if (editingId.value) {
      await apiFetch(`/admin/colleges/${editingId.value}`, { method: 'PUT', body: { ...form } })
    } else {
      await apiFetch('/admin/colleges', { method: 'POST', body: { ...form } })
    }
    modal?.hide()
    toastSuccess(editingId.value ? 'College updated.' : 'College created.')
    reloadDataTable(dt)
  } catch (e) {
    const msg = parseApiError(e)
    formError.value = msg
    await alertError(msg)
  } finally {
    saving.value = false
  }
}

const remove = async (id) => {
  const ok = await confirmDelete('this college')
  if (!ok) return
  try {
    await apiFetch(`/admin/colleges/${id}`, { method: 'DELETE' })
    toastSuccess('College moved to recycle bin.')
    reloadDataTable(dt)
  } catch (e) {
    await alertError(parseApiError(e))
  }
}
</script>
