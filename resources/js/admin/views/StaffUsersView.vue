<template>
  <div>
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
      <p class="text-muted small mb-0">
        Create staff logins and assign a role — access comes from that role&apos;s permissions.
      </p>
      <button type="button" class="btn btn-primary btn-sm" @click="openForm()">
        <i class="bi bi-plus-lg me-1" /> Add Staff User
      </button>
    </div>

    <div class="card table-card mb-3">
      <div class="card-body row g-2 align-items-end">
        <div class="col-md-8">
          <label class="form-label small text-muted mb-1">Search</label>
          <input
            v-model="filters.search"
            class="form-control"
            placeholder="Name, email, phone..."
            @keyup.enter="applyFilters"
          />
        </div>
        <div class="col-md-4">
          <button type="button" class="btn btn-dark w-100" @click="applyFilters">
            <i class="bi bi-funnel me-1" />Filter
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
              <th>Name</th>
              <th>Email</th>
              <th>Phone</th>
              <th>Role</th>
              <th>Status</th>
              <th>Register Date</th>
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
              <h5 class="modal-title">{{ editingId ? 'Edit Staff User' : 'Add Staff User' }}</h5>
              <button type="button" class="btn-close" @click="closeForm" />
            </div>
            <div class="modal-body">
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">Full Name *</label>
                  <input v-model="form.name" class="form-control" required />
                </div>
                <div class="col-md-6">
                  <label class="form-label">Email (login) *</label>
                  <input v-model="form.email" type="email" class="form-control" required />
                </div>
                <div class="col-md-6">
                  <label class="form-label">Phone (10 digits)</label>
                  <input v-model="form.phone" class="form-control" maxlength="10" />
                </div>
                <div class="col-md-6">
                  <label class="form-label">{{ editingId ? 'New Password (optional)' : 'Password *' }}</label>
                  <input v-model="form.password" type="password" class="form-control" :required="!editingId" minlength="6" />
                </div>
                <div class="col-md-6">
                  <label class="form-label">Role *</label>
                  <select v-model="form.role_id" class="form-select" required>
                    <option value="">Select role...</option>
                    <option v-for="r in staffRoles" :key="r.id" :value="Number(r.id)">{{ r.name }}</option>
                  </select>
                </div>
                <div class="col-md-6 d-flex align-items-end">
                  <div class="form-check mb-2">
                    <input id="active" v-model="form.is_active" class="form-check-input" type="checkbox" />
                    <label class="form-check-label" for="active">Active account</label>
                  </div>
                </div>
                <div v-if="selectedRoleKeys.length" class="col-12">
                  <p class="small text-muted mb-1">This role includes:</p>
                  <span v-for="k in selectedRoleKeys" :key="k" class="badge text-bg-light border me-1 mb-1">{{ k }}</span>
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
import { ref, reactive, computed, onMounted, onBeforeUnmount, nextTick, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { apiFetch } from '@/api/client'
import { parseApiError } from '@/utils/apiHelpers'
import { useToastStore } from '@/stores/toast'
import {
  initServerDataTable,
  reloadDataTable,
  destroyDataTable,
  formatDateTime,
  statusBadge,
} from '@/utils/serverDataTable'
import { alertError, confirmDelete, toastSuccess } from '@/utils/swal'

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()
const toast = useToastStore()
const tableRef = ref(null)
let dt = null

const filters = reactive({ search: '' })
const staffRoles = computed(() => auth.staffRoles)

const showModal = ref(false)
const editingId = ref(null)
const saving = ref(false)
const formError = ref('')
const loadError = ref('')

const form = reactive({
  name: '',
  email: '',
  phone: '',
  password: '',
  is_active: true,
  role_id: '',
})

const selectedRoleKeys = computed(() => {
  const role = staffRoles.value.find((r) => String(r.id) === String(form.role_id))
  return role?.permission_keys || []
})

const getFilterParams = () => {
  const params = {}
  if (filters.search?.trim()) params.search = filters.search.trim()
  return params
}

const initTable = () => {
  if (!tableRef.value) return
  dt = initServerDataTable(tableRef.value, {
    url: '/admin/staff-users',
    pageLength: 10,
    defaultOrder: [[5, 'desc']],
    defaultSortBy: 'created_at',
    columnSortKeys: ['name', 'email', 'phone', null, 'is_active', 'created_at', null],
    getFilterParams,
    onError: (err) => {
      loadError.value = parseApiError(err) || 'Failed to load staff users.'
    },
    columns: [
      { data: 'name' },
      { data: 'email' },
      { data: 'phone', defaultContent: '—' },
      {
        data: 'role_label',
        orderable: false,
        defaultContent: '—',
      },
      {
        data: 'is_active',
        render: (d) => statusBadge(d ? 'active' : 'inactive'),
      },
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
      const res = await apiFetch(`/admin/staff-users/${id}`)
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

watch(
  () => route.query.edit,
  (editId) => {
    if (route.name === 'staff-users' && editId) {
      openStaffById(editId)
    }
  }
)

onMounted(async () => {
  if (!auth.staffRoles.length) {
    await auth.loadAccess()
  }
  await nextTick()
  initTable()
  tableRef.value?.addEventListener('click', onTableClick)
  if (route.query.edit) {
    await openStaffById(route.query.edit)
  }
})

onBeforeUnmount(() => {
  tableRef.value?.removeEventListener('click', onTableClick)
  destroyDataTable(dt)
})

const openStaffById = async (id) => {
  const staffId = Number(id)
  if (!staffId) return
  formError.value = ''
  if (!auth.staffRoles.length) {
    await auth.loadAccess()
  }
  editingId.value = staffId
  try {
    const res = await apiFetch(`/admin/staff-users/${staffId}`)
    const u = res.data
    form.name = String(u.name ?? '')
    form.email = String(u.email ?? '')
    form.phone = String(u.phone ?? '')
    form.password = ''
    form.is_active = !!u.is_active
    form.role_id = Number(u.role_id || u.role_detail?.id || 0) || ''
    showModal.value = true
  } catch (e) {
    await alertError(parseApiError(e))
    if (route.query.edit) {
      router.replace({ name: 'staff-users', query: {} })
    }
  }
}

const openForm = async (row) => {
  formError.value = ''
  if (!auth.staffRoles.length) {
    await auth.loadAccess()
  }
  if (row?.id) {
    editingId.value = row.id
    try {
      const res = await apiFetch(`/admin/staff-users/${row.id}`)
      const u = res.data
      form.name = String(u.name ?? '')
      form.email = String(u.email ?? '')
      form.phone = String(u.phone ?? '')
      form.password = ''
      form.is_active = !!u.is_active
      form.role_id = Number(u.role_id || u.role_detail?.id || 0) || ''
    } catch (e) {
      formError.value = parseApiError(e)
      return
    }
  } else {
    editingId.value = null
    form.name = ''
    form.email = ''
    form.phone = ''
    form.password = ''
    form.is_active = true
    form.role_id = Number(staffRoles.value[0]?.id || 0) || ''
  }
  showModal.value = true
}

const closeForm = () => {
  showModal.value = false
  editingId.value = null
  formError.value = ''
  if (route.query.edit) {
    router.replace({ name: 'staff-users', query: {} })
  }
}

const save = async () => {
  saving.value = true
  formError.value = ''
  const body = {
    name: form.name,
    email: form.email,
    phone: form.phone || null,
    is_active: form.is_active,
    role_id: form.role_id,
    ...(form.password ? { password: form.password } : {}),
  }
  try {
    if (editingId.value) {
      await apiFetch(`/admin/staff-users/${editingId.value}`, { method: 'PUT', body })
    } else {
      await apiFetch('/admin/staff-users', { method: 'POST', body })
    }
    closeForm()
    toastSuccess(editingId.value ? 'Staff user updated.' : 'Staff user created.')
    reloadDataTable(dt)
  } catch (e) {
    formError.value = parseApiError(e)
  } finally {
    saving.value = false
  }
}

const remove = async (id) => {
  const ok = await confirmDelete('this staff user')
  if (!ok) return
  try {
    await apiFetch(`/admin/staff-users/${id}`, { method: 'DELETE' })
    toastSuccess('Staff user deleted.')
    reloadDataTable(dt)
  } catch (e) {
    await alertError(parseApiError(e))
  }
}
</script>
