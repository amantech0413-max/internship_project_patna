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
          <input v-model="filters.search" class="form-control" placeholder="Search college..." @keyup.enter="load(1)" />
        </div>
        <div class="col-md-3">
          <select v-model="filters.status" class="form-select">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>
        <div class="col-md-3">
          <button class="btn btn-dark w-100" :disabled="loading" @click="load(1)">Filter</button>
        </div>
      </div>
    </div>

    <p v-if="error" class="alert alert-danger">{{ error }}</p>

    <div class="card table-card">
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead class="table-light">
            <tr>
              <th>College Name</th>
              <th>Address</th>
              <th>Contact</th>
              <th>Mobile</th>
              <th>Status</th>
              <th>Students</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in rows" :key="row.id as number">
              <td class="fw-medium">{{ row.college_name }}</td>
              <td>{{ row.address || '—' }}</td>
              <td>{{ row.contact_person || '—' }}</td>
              <td>{{ row.mobile_number || '—' }}</td>
              <td><span class="badge" :class="row.status === 'active' ? 'text-bg-success' : 'text-bg-secondary'">{{ row.status }}</span></td>
              <td>{{ row.students_count ?? 0 }}</td>
              <td>
                <button class="btn btn-sm btn-outline-primary me-1" @click="openForm(row)">Edit</button>
                <button class="btn btn-sm btn-outline-danger" @click="remove(row.id)">Delete</button>
              </td>
            </tr>
            <tr v-if="!rows.length && !loading">
              <td colspan="7" class="text-center text-muted py-4">No colleges found</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <PaginationBar v-if="meta" :meta="meta" @page="load" />

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

<script setup lang="ts">
import { Modal } from 'bootstrap'
import { parseApiError, unwrapList, type ApiMeta } from '~/composables/useApiHelpers'

definePageMeta({ prefetch: false })

const { apiFetch } = useApi()
const modalEl = ref<HTMLElement | null>(null)
let modal: Modal | null = null

const filters = reactive({ search: '', status: '' })
const rows = ref<Record<string, unknown>[]>([])
const meta = ref<ApiMeta | null>(null)
const loading = ref(false)
const error = ref('')
const editingId = ref<number | null>(null)
const saving = ref(false)
const formError = ref('')

const form = reactive({
  college_name: '',
  address: '',
  contact_person: '',
  mobile_number: '',
  status: 'active',
})

onMounted(() => {
  if (modalEl.value) modal = new Modal(modalEl.value)
  load(1)
})

const load = async (page = 1) => {
  loading.value = true
  error.value = ''
  try {
    const query = new URLSearchParams({ page: String(page), per_page: '15' })
    Object.entries(filters).forEach(([k, v]) => v && query.set(k, v))
    const res = await apiFetch<{ success: boolean; data: Record<string, unknown>[]; meta?: ApiMeta }>(`/admin/colleges?${query}`)
    const { items, meta: m } = unwrapList(res)
    rows.value = items
    meta.value = m ?? null
  } catch (e) {
    error.value = parseApiError(e)
  } finally {
    loading.value = false
  }
}

const openForm = (row?: Record<string, unknown>) => {
  formError.value = ''
  if (row) {
    editingId.value = row.id as number
    form.college_name = String(row.college_name || '')
    form.address = String(row.address || '')
    form.contact_person = String(row.contact_person || '')
    form.mobile_number = String(row.mobile_number || '')
    form.status = String(row.status || 'active')
  } else {
    editingId.value = null
    Object.assign(form, { college_name: '', address: '', contact_person: '', mobile_number: '', status: 'active' })
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
    await load(meta.value?.current_page || 1)
  } catch (e) {
    formError.value = parseApiError(e)
  } finally {
    saving.value = false
  }
}

const remove = async (id: unknown) => {
  if (!confirm('Delete this college?')) return
  try {
    await apiFetch(`/admin/colleges/${id}`, { method: 'DELETE' })
    await load(meta.value?.current_page || 1)
  } catch (e) {
    alert(parseApiError(e))
  }
}
</script>
