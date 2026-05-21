<template>
  <div class="p-6 lg:p-8">
    <h2 class="text-2xl font-bold">Certificates & Offer Letters</h2>
    <p class="text-slate-500 text-sm mt-2">
      Generate or upload certificates from the student detail page. Auto-generation uses queued jobs.
    </p>
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

</script>
