<template>
  <div class="register-page landing-page">
    <header class="register-header">
      <div class="register-header-inner">
        <div class="register-brand">
          <img v-if="siteSettings.organization_logo_url" :src="siteSettings.organization_logo_url" alt="" class="register-logo-img" />
          <div v-else class="register-logo">BL</div>
          <div>
            <div class="register-brand-name">{{ siteSettings.organization_name || 'M/s Bhagya Laxmi' }}</div>
            <div class="register-brand-addr">{{ siteSettings.organization_address || '' }}</div>
          </div>
        </div>
        <div class="register-badge">
          <i class="bi bi-mortarboard-fill" />
          <div>
            <strong>Internship Jun 2026</strong>
            <span>Registration Program</span>
          </div>
        </div>
      </div>
    </header>

    <section class="landing-hero">
      <div class="landing-hero-inner">
        <div class="landing-hero-grid">
          <div class="landing-hero-copy">
            <span class="register-hero-tag"><i class="bi bi-mortarboard-fill" /> Welcome to</span>
            <h1>Internship Jun 2026 Registration</h1>
            <div class="landing-hero-rule" aria-hidden="true" />
            <p class="register-hero-desc">
              Select your college below to open the student registration form and begin your journey with us.
            </p>
          </div>

          <div class="register-card landing-college-card">
            <div class="register-card-head">
              <div class="register-card-head-icon"><i class="bi bi-building" /></div>
              <div>
                <h2>Select Your College</h2>
                <p>Choose your institution to continue registration</p>
              </div>
            </div>
            <p v-if="loadError" class="text-danger small px-3 pb-2 mb-0">{{ loadError }}</p>
            <p v-if="loading" class="text-muted small px-3 pb-3 mb-0">Loading colleges...</p>
            <template v-else>
              <div class="landing-college-search">
                <div class="landing-college-search-wrap">
                  <i class="bi bi-search" aria-hidden="true" />
                  <input
                    v-model="collegeSearch"
                    type="search"
                    class="landing-college-search-input"
                    placeholder="Search college..."
                    autocomplete="off"
                  />
                  <button
                    v-if="collegeSearch"
                    type="button"
                    class="landing-college-search-clear"
                    aria-label="Clear search"
                    @click="clearCollegeSearch"
                  >
                    <i class="bi bi-x-lg" />
                  </button>
                </div>
              </div>
              <div class="landing-college-scroll" role="list">
                <ul class="register-college-list mb-0">
                  <li v-for="college in filteredColleges" :key="college.slug" role="listitem">
                    <router-link :to="collegeRegisterRoute(college.slug)" class="register-college-link">
                      <i class="bi bi-mortarboard" />
                      <span>{{ collegeLabel(college) }}</span>
                      <i class="bi bi-chevron-right" />
                    </router-link>
                  </li>
                  <li v-if="!filteredColleges.length" class="text-muted small px-3 py-3">
                    <template v-if="collegeSearch.trim()">No college matches "{{ collegeSearch.trim() }}".</template>
                    <template v-else>No colleges available for registration right now.</template>
                  </li>
                </ul>
              </div>
            </template>
          </div>
        </div>
      </div>
    </section>

    <section class="landing-stats">
      <div class="landing-stats-inner">
        <div v-for="stat in stats" :key="stat.label" class="landing-stat-card">
          <div class="landing-stat-icon" :class="stat.tone">
            <i :class="`bi ${stat.icon}`" />
          </div>
          <div class="landing-stat-value">{{ stat.value }}</div>
          <div class="landing-stat-label">{{ stat.label }}</div>
          <div class="landing-stat-sub">{{ stat.sub }}</div>
        </div>
      </div>
    </section>

    <section class="landing-why">
      <h2 class="landing-why-title">Why Join Our Internship Program?</h2>
      <div class="register-features landing-features">
        <div class="register-feature">
          <div class="register-feature-icon blue"><i class="bi bi-briefcase-fill" /></div>
          <h3>Professional Experience</h3>
          <p>Gain hands-on industry exposure with structured internship guidance.</p>
        </div>
        <div class="register-feature">
          <div class="register-feature-icon purple"><i class="bi bi-lightbulb-fill" /></div>
          <h3>Skill Development</h3>
          <p>Enhance your practical knowledge and workplace readiness.</p>
        </div>
        <div class="register-feature">
          <div class="register-feature-icon green"><i class="bi bi-people-fill" /></div>
          <h3>Expert Mentorship</h3>
          <p>Learn from experienced professionals throughout your journey.</p>
        </div>
      </div>
    </section>

    <RegisterFooter />
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { getPublicApi } from '@/api/client'
import { collegeRegisterRoute } from '@/utils/registrationPaths'
import { fetchPublicSiteSettings } from '@/composables/useSiteSettings'
import RegisterFooter from '@/components/RegisterFooter.vue'
import '../../../css/register.css'

const siteSettings = ref({})
const colleges = ref([])
const collegeSearch = ref('')
const loading = ref(true)
const loadError = ref('')
const collegeLabel = (c) => String(c?.short_name || c?.name || '').trim()

const filteredColleges = computed(() => {
  const q = collegeSearch.value.trim().toLowerCase()
  if (!q) return colleges.value
  return colleges.value.filter((c) => {
    const label = collegeLabel(c).toLowerCase()
    const slug = String(c.slug || '').toLowerCase()
    return label.includes(q) || slug.includes(q.replace(/\s+/g, '-'))
  })
})

const clearCollegeSearch = () => {
  collegeSearch.value = ''
}

const stats = [
  { icon: 'bi-people-fill', value: '1000+', label: 'Students Registered', sub: 'And Growing', tone: 'blue' },
  { icon: 'bi-building', value: '10+', label: 'Partner Colleges', sub: 'Across Bihar', tone: 'purple' },
  { icon: 'bi-briefcase-fill', value: '500+', label: 'Internships Offered', sub: 'Every Year', tone: 'green' },
  { icon: 'bi-person-check-fill', value: '50+', label: 'Expert Mentors', sub: 'Guiding Students', tone: 'orange' },
]

onMounted(async () => {
  siteSettings.value = await fetchPublicSiteSettings()
  try {
    const api = getPublicApi()
    const res = await api.get('/registration/colleges')
    colleges.value = Array.isArray(res.data) ? res.data : []
  } catch {
    loadError.value = 'Could not load colleges. Please try again later.'
  } finally {
    loading.value = false
  }
})
</script>
