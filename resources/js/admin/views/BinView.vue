<template>
  <div>
    <p class="text-muted small mb-3">
      Restore soft-deleted colleges and students, or permanently remove items from the bin.
    </p>

    <div class="card table-card mb-3">
      <div class="card-body row g-2 align-items-end">
        <div class="col-md-3">
          <label class="form-label small text-muted mb-1">Type</label>
          <select v-model="filters.type" class="form-select">
            <option value="all">All</option>
            <option value="college">Colleges</option>
            <option value="student">Students</option>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label small text-muted mb-1">Search</label>
          <input
            v-model="filters.search"
            class="form-control"
            placeholder="Name, slug, code..."
            @keyup.enter="applyFilters"
          />
        </div>
        <div class="col-md-3">
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
              <th>Type</th>
              <th>Name</th>
              <th>Details</th>
              <th>Deleted At</th>
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
import { reactive, ref, computed, onMounted, onBeforeUnmount, nextTick } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { apiFetch } from '@/api/client'
import { parseApiError } from '@/utils/apiHelpers'
import {
  initServerDataTable,
  reloadDataTable,
  destroyDataTable,
  formatDateTime,
} from '@/utils/serverDataTable'
import { alertError, confirmDelete, confirmDialog, toastSuccess } from '@/utils/swal'
import { dtIconButton } from '@/utils/dtActions'

const auth = useAuthStore()
const tableRef = ref(null)
let dt = null
const loadError = ref('')

const canRestore = computed(() => auth.can('bin_manage'))
const canForceDelete = computed(() => auth.can('bin_delete_permanent'))

const filters = reactive({ search: '', type: 'all' })

const getFilterParams = () => {
  const params = { type: filters.type }
  if (filters.search?.trim()) params.search = filters.search.trim()
  return params
}

const renderActions = (row) => {
  const typeAttr = `data-type="${String(row.type || '').replace(/"/g, '&quot;')}"`
  let html = ''
  if (canRestore.value) {
    html += dtIconButton({
      action: 'restore',
      icon: 'arrow-counterclockwise',
      btnClass: 'btn-outline-success',
      title: 'Restore',
      id: row.id,
      extraAttrs: typeAttr,
    })
  }
  if (canForceDelete.value) {
    html += dtIconButton({
      action: 'force-delete',
      icon: 'trash',
      btnClass: 'btn-outline-danger',
      title: 'Delete permanently',
      id: row.id,
      extraAttrs: typeAttr,
    })
  }
  if (!html) html = '<span class="text-muted small">—</span>'
  return html
}

const initTable = () => {
  if (!tableRef.value) return
  dt = initServerDataTable(tableRef.value, {
    url: '/admin/bin',
    pageLength: 10,
    defaultOrder: [[3, 'desc']],
    defaultSortBy: 'deleted_at',
    columnSortKeys: ['type', 'title', 'subtitle', 'deleted_at', null],
    getFilterParams,
    onError: (err) => {
      loadError.value = parseApiError(err) || 'Failed to load recycle bin.'
    },
    columns: [
      {
        data: 'type',
        render: (d) => (d === 'college' ? 'College' : 'Student'),
      },
      { data: 'title', defaultContent: '—' },
      { data: 'subtitle', defaultContent: '—' },
      { data: 'deleted_at', render: (d) => formatDateTime(d) },
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
  const btn = e.target.closest('[data-dt-action]')
  if (!btn) return
  const type = btn.dataset.type
  const id = Number(btn.dataset.id)
  const action = btn.dataset.dtAction

  if (action === 'restore') {
    const ok = await confirmDialog('Restore item?', 'This will move the record back to the active list.', 'Restore')
    if (!ok) return
    try {
      await apiFetch('/admin/bin/restore', { method: 'POST', body: { type, id } })
      toastSuccess('Item restored.')
      reloadDataTable(dt)
    } catch (err) {
      await alertError(parseApiError(err))
    }
    return
  }

  if (action === 'force-delete') {
    const ok = await confirmDelete('this item permanently')
    if (!ok) return
    try {
      await apiFetch('/admin/bin/force', { method: 'DELETE', body: { type, id } })
      toastSuccess('Item permanently deleted.')
      reloadDataTable(dt)
    } catch (err) {
      await alertError(parseApiError(err))
    }
  }
}

const applyFilters = () => reloadDataTable(dt)

onMounted(async () => {
  await nextTick()
  initTable()
  tableRef.value?.addEventListener('click', onTableClick)
})

onBeforeUnmount(() => {
  tableRef.value?.removeEventListener('click', onTableClick)
  destroyDataTable(dt)
})
</script>
