<template>
  <div>
    <h2 class="h6 text-muted text-uppercase mb-2">College & Staff Entry</h2>
    <div class="row g-3 mb-4">
      <div v-for="c in collegeCards" :key="c.label" class="col-6 col-md-3">
        <div class="card card-stat h-100">
          <div class="card-body">
            <p class="text-muted small mb-1">{{ c.label }}</p>
            <h3 class="fw-bold text-primary mb-0">{{ c.value }}</h3>
          </div>
        </div>
      </div>
    </div>

    <h2 class="h6 text-muted text-uppercase mb-2">Internship (Full System)</h2>
    <div class="row g-3 mb-4">
      <div v-for="c in internCards" :key="c.label" class="col-6 col-md-4 col-xl-2">
        <div class="card card-stat h-100">
          <div class="card-body py-3">
            <p class="text-muted small mb-1">{{ c.label }}</p>
            <h4 class="fw-bold mb-0">{{ c.value }}</h4>
          </div>
        </div>
      </div>
    </div>

    <div class="card table-card">
      <div class="card-body">
        <h3 class="h6 fw-semibold">Quick Actions</h3>
        <div class="d-flex flex-wrap gap-2 mt-2">
          <NuxtLink to="/entry" class="btn btn-primary btn-sm">Staff Entry</NuxtLink>
          <NuxtLink to="/colleges" class="btn btn-outline-primary btn-sm">Colleges</NuxtLink>
          <NuxtLink to="/students" class="btn btn-outline-primary btn-sm">All Students</NuxtLink>
          <NuxtLink to="/students/create" class="btn btn-outline-secondary btn-sm">Add Full Student</NuxtLink>
          <NuxtLink to="/groups" class="btn btn-outline-secondary btn-sm">Groups</NuxtLink>
          <NuxtLink to="/whatsapp" class="btn btn-outline-success btn-sm">WhatsApp</NuxtLink>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ prefetch: false })

const { apiFetch } = useApi()

const { data } = await useAsyncData('dashboard', async () => {
  const res = await apiFetch<{ success: boolean; data: Record<string, number> }>('/admin/dashboard')
  return res.data
}, { server: false })

const collegeCards = computed(() => [
  { label: 'Colleges', value: data.value?.total_colleges ?? 0 },
  { label: 'Active Colleges', value: data.value?.active_colleges ?? 0 },
  { label: 'Staff Entries', value: data.value?.staff_entries ?? 0 },
  { label: 'Imports Today', value: data.value?.imports_today ?? 0 },
])

const internCards = computed(() => [
  { label: 'Total Students', value: data.value?.total_students ?? 0 },
  { label: 'Online', value: data.value?.online_interns ?? 0 },
  { label: 'Offline', value: data.value?.offline_interns ?? 0 },
  { label: 'Active Groups', value: data.value?.active_groups ?? 0 },
  { label: 'Pending', value: data.value?.pending_approvals ?? 0 },
  { label: 'Approved', value: data.value?.approved_students ?? 0 },
])
</script>
