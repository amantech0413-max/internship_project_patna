<template>
  <div>
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
      <p class="text-muted small mb-0">
        Create staff logins (email + password) and assign permissions for entry, students, colleges, etc.
      </p>
      <button type="button" class="btn btn-primary btn-sm" @click="openForm()">
        <i class="bi bi-plus-lg me-1" /> Add Staff User
      </button>
    </div>

    <p v-if="loadError" class="alert alert-danger">{{ loadError }}</p>

    <DataTable :columns="columns" :rows="rows">
      <template #actions="{ row }">
        <button type="button" class="btn btn-sm btn-outline-primary me-1" @click="openForm(row)">Edit</button>
        <button type="button" class="btn btn-sm btn-outline-danger" @click="remove(row.id)">Delete</button>
      </template>
    </DataTable>

    <PaginationBar v-if="meta" :meta="meta" @page="goToPage" />

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
                <div class="col-12">
                  <div class="form-check">
                    <input id="active" v-model="form.is_active" class="form-check-input" type="checkbox" />
                    <label class="form-check-label" for="active">Active account</label>
                  </div>
                </div>
              </div>

              <hr class="my-4" />
              <h6 class="fw-semibold mb-3">Permissions</h6>
              <div class="row g-2">
                <div v-for="key in permissionKeys" :key="key" class="col-md-6">
                  <div class="form-check">
                    <input
                      :id="`perm-${key}`"
                      v-model="form.permissions[key]"
                      class="form-check-input"
                      type="checkbox"
                    />
                    <label class="form-check-label" :for="`perm-${key}`">{{ permissionLabels[key] || key }}</label>
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

<script setup lang="ts">
import { parseApiError, unwrapList, type ApiMeta } from '~/composables/useApiHelpers'
import type { StaffPermissionKey } from '~/types/permissions'

definePageMeta({ prefetch: false })

const { apiFetch } = useApi()
const toast = useToast()

const columns = [
  { key: 'name', label: 'Name' },
  { key: 'email', label: 'Email' },
  { key: 'phone', label: 'Phone' },
  { key: 'active', label: 'Status' },
]

const currentPage = ref(1)
const permissionKeys = ref<StaffPermissionKey[]>([])
const permissionLabels = ref<Record<string, string>>({})
const permissionDefaults = ref<Record<string, boolean>>({})

const showModal = ref(false)
const editingId = ref<number | null>(null)
const saving = ref(false)
const formError = ref('')

const form = reactive({
  name: '',
  email: '',
  phone: '',
  password: '',
  is_active: true,
  permissions: {} as Record<string, boolean>,
})

const fetchList = async () => {
  const res = await apiFetch<{ success: boolean; data: Record<string, unknown>[]; meta?: ApiMeta }>(
    `/admin/staff-users?page=${currentPage.value}&per_page=15`
  )
  return unwrapList(res)
}

const { data, pending, error, refresh } = await useAsyncData('staff-users-list', fetchList, { server: false })

const loadError = computed(() => (error.value ? parseApiError(error.value) : ''))
const meta = computed(() => data.value?.meta ?? null)
const rows = computed(() =>
  (data.value?.items ?? []).map((u) => ({
    id: u.id,
    name: u.name,
    email: u.email,
    phone: u.phone || '—',
    active: u.is_active ? 'Active' : 'Inactive',
  }))
)

const loadPermissionMeta = async () => {
  try {
    const res = await apiFetch<{
      success: boolean
      data: { keys: StaffPermissionKey[]; labels: Record<string, string>; defaults: Record<string, boolean> }
    }>('/admin/staff-users/permission-keys')
    permissionKeys.value = res.data.keys
    permissionLabels.value = res.data.labels
    permissionDefaults.value = res.data.defaults
  } catch (e) {
    console.error(e)
  }
}

onMounted(() => {
  loadPermissionMeta()
})

const resetPermissions = () => {
  const base: Record<string, boolean> = {}
  for (const key of permissionKeys.value) {
    base[key] = permissionDefaults.value[key] ?? false
  }
  form.permissions = base
}

const openForm = async (row?: { id: number }) => {
  formError.value = ''
  if (!permissionKeys.value.length) {
    await loadPermissionMeta()
  }
  if (row?.id) {
    editingId.value = row.id
    try {
      const res = await apiFetch<{ success: boolean; data: Record<string, unknown> }>(`/admin/staff-users/${row.id}`)
      const u = res.data
      form.name = String(u.name ?? '')
      form.email = String(u.email ?? '')
      form.phone = String(u.phone ?? '')
      form.password = ''
      form.is_active = !!u.is_active
      form.permissions = { ...(u.permissions as Record<string, boolean>) }
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
    resetPermissions()
  }
  showModal.value = true
}

const closeForm = () => {
  showModal.value = false
  editingId.value = null
  formError.value = ''
}

const save = async () => {
  saving.value = true
  formError.value = ''
  const body = {
    name: form.name,
    email: form.email,
    phone: form.phone || null,
    is_active: form.is_active,
    permissions: form.permissions,
    ...(form.password ? { password: form.password } : {}),
  }
  try {
    if (editingId.value) {
      await apiFetch(`/admin/staff-users/${editingId.value}`, { method: 'PUT', body })
      toast.show('Staff user updated.', 'success')
    } else {
      await apiFetch('/admin/staff-users', { method: 'POST', body })
      toast.show('Staff user created.', 'success')
    }
    closeForm()
    await refresh()
  } catch (e) {
    formError.value = parseApiError(e)
  } finally {
    saving.value = false
  }
}

const remove = async (id: unknown) => {
  if (!confirm('Delete this staff user?')) return
  try {
    await apiFetch(`/admin/staff-users/${id}`, { method: 'DELETE' })
    toast.show('Staff user deleted.', 'success')
    await refresh()
  } catch (e) {
    alert(parseApiError(e))
  }
}

const goToPage = async (p: number) => {
  currentPage.value = p
  await refresh()
}
</script>
