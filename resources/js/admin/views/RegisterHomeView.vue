<template>
  <RegisterPageShell
    hero-title="Internship Jun 2026 Registration"
    hero-description="Select your college below to open the student registration form."
  >
    <div class="register-card">
      <div class="register-card-head">
        <div class="register-card-head-icon"><i class="bi bi-building" /></div>
        <div>
          <h2>Select Your College</h2>
          <p>Choose your institution to continue registration</p>
        </div>
      </div>
      <p v-if="loadError" class="text-danger small px-3 pb-2 mb-0">{{ loadError }}</p>
      <p v-if="loading" class="text-muted small px-3 pb-3 mb-0">Loading colleges...</p>
      <ul v-else class="register-college-list">
        <li v-for="college in colleges" :key="college.slug">
          <router-link
            :to="collegeRegisterRoute(college.slug)"
            class="register-college-link"
          >
            <i class="bi bi-mortarboard" />
            <span>{{ college.short_name || college.name }}</span>
            <i class="bi bi-chevron-right" />
          </router-link>
        </li>
        <li v-if="!colleges.length && !loading" class="text-muted small px-3 py-3">
          No colleges available for registration right now.
        </li>
      </ul>
    </div>
  </RegisterPageShell>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { getPublicApi } from '@/api/client'
import RegisterPageShell from '@/components/RegisterPageShell.vue'
import { collegeRegisterRoute } from '@/utils/registrationPaths'

const colleges = ref([])
const loading = ref(true)
const loadError = ref('')

onMounted(async () => {
  try {
    const api = getPublicApi()
    const res = await api.get('/registration/colleges')
    colleges.value = res.data?.data || []
  } catch {
    loadError.value = 'Could not load colleges. Please try again later.'
  } finally {
    loading.value = false
  }
})
</script>
