<template>
  <div>
    <p class="text-muted small mb-3">Quick staff entries linked to colleges (name + mobile only).</p>

    <div class="card table-card mb-3">
      <div class="card-body row g-2 align-items-end">
        <div class="col-md-4">
          <label class="form-label small">Search</label>
          <input v-model="filters.search" class="form-control" placeholder="Name or mobile..." @keyup.enter="load(1)" />
        </div>
        <div class="col-md-4">
          <label class="form-label small">College</label>
          <select v-model="filters.college_id" class="form-select">
            <option value="">All Colleges</option>
            <option v-for="c in colleges" :key="c.id" :value="c.id">{{ c.college_name }}</option>
          </select>
        </div>
        <div class="col-md-2">
          <button class="btn btn-dark w-100" :disabled="pending" @click="load(1)">Filter</button>
        </div>
        <div class="col-md-2">
          <router-link to="/entry" class="btn btn-primary w-100">+ Staff Entry</router-link>
        </div>
      </div>
    </div>

    <p v-if="loadError" class="alert alert-danger">{{ loadError }}</p>

    <div class="card table-card">
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Student Name</th>
              <th>Mobile</th>
              <th>College</th>
              <th>Added By</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(row, idx) in rows" :key="row.id">
              <td>{{ rowIndex(idx) }}</td>
              <td class="fw-medium">{{ row.student_name }}</td>
              <td>{{ row.mobile_number }}</td>
              <td>{{ row.college?.college_name || '—' }}</td>
              <td>{{ row.added_by_user?.name || row.creator?.name || '—' }}</td>
              <td>{{ formatDate(row.created_at) }}</td>
            </tr>
            <tr v-if="!rows.length && !pending">
              <td colspan="6" class="text-center text-muted py-4">No entries found</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

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
const colleges = ref([])
const filters = reactive({ search: '', college_id: '' })
const currentPage = ref(1)

const fetchList = async () => {
  const query = new URLSearchParams({ page: String(currentPage.value), per_page: '15' })
  if (filters.search) query.set('search', filters.search)
  if (filters.college_id) query.set('college_id', filters.college_id)
  const res = await apiFetch(
    `/admin/staff-students?${query}`
  )
  return unwrapList(res)
}

const { data, pending, error, refresh } = useFetchData(fetchList)

const loadError = computed(() => (error.value ? parseApiError(error.value) : ''))
const meta = computed(() => data.value?.meta ?? null)
const rows = computed(() => data.value?.items ?? [])

const load = async (page = 1) => {
  currentPage.value = page
  await refresh()
}

const goToPage = (p) => load(p)

const rowIndex = (idx) => ((meta.value?.current_page || 1) - 1) * (meta.value?.per_page || 15) + idx + 1

const formatDate = (val) => {
  if (!val) return '—'
  return new Date(String(val)).toLocaleString('en-IN', { dateStyle: 'medium', timeStyle: 'short' })
}

onMounted(async () => {
  const res = await apiFetch('/admin/colleges/dropdown')
  colleges.value = res.data || []
})
</script>
