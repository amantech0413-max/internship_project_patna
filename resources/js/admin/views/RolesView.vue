<template>
  <div>
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
      <p class="text-muted small mb-0">Create roles and assign permissions. Staff users inherit access from their role.</p>
      <button type="button" class="btn btn-primary btn-sm" @click="openForm()">
        <i class="bi bi-plus-lg me-1" /> Add Role
      </button>
    </div>

    <div class="card table-card mb-3">
      <div class="card-body row g-2 align-items-end">
        <div class="col-md-9">
          <input v-model="filters.search" class="form-control" placeholder="Search role name..." @keyup.enter="applyFilters" />
        </div>
        <div class="col-md-3">
          <button type="button" class="btn btn-dark w-100" @click="applyFilters">
            <i class="bi bi-funnel me-1" />Filter
          </button>
        </div>
      </div>
    </div>

    <div class="card table-card">
      <div class="card-body table-responsive">
        <table ref="tableRef" class="table table-striped table-hover w-100">
          <thead class="table-light">
            <tr>
              <th>Role Name</th>
              <th>Slug</th>
              <th>Permissions</th>
              <th>Staff Count</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody />
        </table>
      </div>
    </div>

    <div v-if="showModal" class="modal show d-block" tabindex="-1" style="background: rgba(0,0,0,.5)">
      <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
          <form @submit.prevent="save">
            <div class="modal-header">
              <h5 class="modal-title">{{ editingId ? 'Edit Role' : 'Add Role' }}</h5>
              <button type="button" class="btn-close" @click="closeForm" />
            </div>
            <div class="modal-body">
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">Role Name *</label>
                  <input v-model="form.name" class="form-control" required />
                </div>
                <div class="col-md-6">
                  <label class="form-label">Slug (optional)</label>
                  <input v-model="form.slug" class="form-control" placeholder="auto-from-name" />
                </div>
                <div class="col-12">
                  <label class="form-label">Description</label>
                  <textarea v-model="form.description" class="form-control" rows="2" />
                </div>
              </div>
              <hr />
              <h6 class="fw-semibold mb-2">Permissions *</h6>
              <div class="row g-2">
                <div v-for="p in permissionList" :key="p.key" class="col-md-6">
                  <div class="form-check">
                    <input
                      :id="`perm-${p.key}`"
                      v-model="form.permission_keys"
                      class="form-check-input"
                      type="checkbox"
                      :value="p.key"
                    />
                    <label class="form-check-label" :for="`perm-${p.key}`">{{ p.label }}</label>
                  </div>
                </div>
              </div>
              <p v-if="formError" class="text-danger small mt-3 mb-0">{{ formError }}</p>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" @click="closeForm">Cancel</button>
              <button type="submit" class="btn btn-primary" :disabled="saving">
                {{ saving ? 'Saving...' : 'Save' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, onBeforeUnmount, nextTick } from 'vue'
import { apiFetch } from '@/api/client'
import { parseApiError } from '@/utils/apiHelpers'
import {
  initServerDataTable,
  reloadDataTable,
  destroyDataTable,
} from '@/utils/serverDataTable'
import { alertError, confirmDelete, toastSuccess } from '@/utils/swal'

const tableRef = ref(null)
let dt = null
const filters = reactive({ search: '' })
const permissionList = ref([])
const showModal = ref(false)
const editingId = ref(null)
const saving = ref(false)
const formError = ref('')

const form = reactive({
  name: '',
  slug: '',
  description: '',
  permission_keys: [],
})

const getFilterParams = () => {
  const params = {}
  if (filters.search?.trim()) params.search = filters.search.trim()
  return params
}

const loadPermissions = async () => {
  const res = await apiFetch('/admin/roles/permissions')
  permissionList.value = res.data || []
}

const initTable = () => {
  if (!tableRef.value) return
  dt = initServerDataTable(tableRef.value, {
    url: '/admin/roles',
    pageLength: 10,
    defaultOrder: [[0, 'asc']],
    defaultSortBy: 'name',
    columnSortKeys: ['name', 'slug', null, 'users_count', null],
    getFilterParams,
    onError: (err) => {
      formError.value = parseApiError(err) || 'Failed to load roles.'
    },
    columns: [
      { data: 'name' },
      { data: 'slug' },
      {
        data: 'permission_keys',
        orderable: false,
        render: (data) => {
          const keys = data || []
          return keys.length ? keys.map((k) => `<span class="badge text-bg-light border me-1">${k}</span>`).join('') : '—'
        },
      },
      { data: 'users_count', defaultContent: '0' },
      {
        data: null,
        orderable: false,
        render: (_d, _t, row) => {
          if (row.is_system) return '<span class="text-muted small">System</span>'
          return (
            `<button type="button" class="btn btn-sm btn-outline-primary me-1" data-dt-action="edit" data-id="${row.id}">Edit</button>` +
            `<button type="button" class="btn btn-sm btn-outline-danger" data-dt-action="delete" data-id="${row.id}">Delete</button>`
          )
        },
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
      const res = await apiFetch(`/admin/roles/${id}`)
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

const openForm = (row) => {
  formError.value = ''
  if (row?.id) {
    editingId.value = row.id
    form.name = row.name || ''
    form.slug = row.slug || ''
    form.description = row.description || ''
    form.permission_keys = [...(row.permission_keys || [])]
  } else {
    editingId.value = null
    form.name = ''
    form.slug = ''
    form.description = ''
    form.permission_keys = []
  }
  showModal.value = true
}

const closeForm = () => {
  showModal.value = false
  editingId.value = null
}

const save = async () => {
  saving.value = true
  formError.value = ''
  const body = {
    name: form.name,
    slug: form.slug || undefined,
    description: form.description || null,
    permission_keys: form.permission_keys,
  }
  try {
    if (editingId.value) {
      await apiFetch(`/admin/roles/${editingId.value}`, { method: 'PUT', body })
      toastSuccess('Role updated.')
    } else {
      await apiFetch('/admin/roles', { method: 'POST', body })
      toastSuccess('Role created.')
    }
    closeForm()
    reloadDataTable(dt)
    await loadPermissions()
  } catch (e) {
    formError.value = parseApiError(e)
  } finally {
    saving.value = false
  }
}

const remove = async (id) => {
  const ok = await confirmDelete('this role')
  if (!ok) return
  try {
    await apiFetch(`/admin/roles/${id}`, { method: 'DELETE' })
    toastSuccess('Role deleted.')
    reloadDataTable(dt)
  } catch (e) {
    await alertError(parseApiError(e))
  }
}

onMounted(async () => {
  await loadPermissions()
  await nextTick()
  initTable()
  tableRef.value?.addEventListener('click', onTableClick)
})

onBeforeUnmount(() => {
  tableRef.value?.removeEventListener('click', onTableClick)
  destroyDataTable(dt)
})
</script>
