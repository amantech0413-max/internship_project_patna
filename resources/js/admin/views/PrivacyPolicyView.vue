<template>
  <div class="register-page privacy-policy-page">
    <header class="privacy-policy-topbar">
      <div class="privacy-policy-topbar-inner">
        <router-link :to="{ name: 'landing' }" class="btn btn-sm btn-outline-primary">
          <i class="bi bi-arrow-left me-1" />Back to Home
        </router-link>
      </div>
    </header>

    <main class="privacy-policy-main">
      <div class="register-card privacy-policy-card">
        <div v-if="loading" class="text-muted py-4 text-center">Loading...</div>
        <div v-else-if="loadError" class="alert alert-danger">{{ loadError }}</div>
        <div v-else-if="contentHtml" class="rich-content privacy-policy-content" v-html="contentHtml" />
        <p v-else class="text-muted mb-0">Privacy policy content is not available yet. Please check back later.</p>
      </div>
    </main>

    <RegisterFooter />
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { getPublicApi } from '@/api/client'
import { parseApiError } from '@/utils/apiHelpers'
import RegisterFooter from '@/components/RegisterFooter.vue'
import '../../../css/register.css'

const contentHtml = ref('')
const loading = ref(true)
const loadError = ref('')

onMounted(async () => {
  try {
    const res = await getPublicApi().get('/registration/privacy-policy')
    contentHtml.value = res.data?.content_html || ''
  } catch (e) {
    loadError.value = parseApiError(e) || 'Could not load privacy policy.'
  } finally {
    loading.value = false
  }
})
</script>
