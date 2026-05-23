<template>
  <footer class="register-footer landing-footer">
    <div class="register-footer-inner">
      <span class="register-footer-trust register-footer-left">
        <i class="bi bi-shield-check" /> Secure • Reliable • Future Ready
      </span>
      <div class="register-footer-center">
        <span>© {{ year }} M/s Bhagya Laxmi. All rights reserved.</span>
        <div v-if="hasSupport" class="register-footer-support">
          <a
            v-if="supportPhone"
            :href="`tel:${supportPhoneTel}`"
            class="register-footer-support-item"
          >
            <i class="bi bi-telephone-fill" />
            <span>{{ supportPhone }}</span>
          </a>
          <a
            v-if="supportEmail"
            :href="`mailto:${supportEmail}`"
            class="register-footer-support-item"
          >
            <i class="bi bi-envelope-fill" />
            <span>{{ supportEmail }}</span>
          </a>
        </div>
      </div>
      <a :href="privacyUrl" class="register-footer-link register-footer-right">Privacy Policy</a>
    </div>
  </footer>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { fetchPublicSiteSettings } from '@/composables/useSiteSettings'

const year = new Date().getFullYear()
const privacyUrl = `${typeof window !== 'undefined' ? window.location.origin : ''}/privacy-policy`

const supportPhone = ref('')
const supportEmail = ref('')

const hasSupport = computed(() => Boolean(supportPhone.value || supportEmail.value))

const supportPhoneTel = computed(() => String(supportPhone.value || '').replace(/\s+/g, ''))

onMounted(async () => {
  const s = await fetchPublicSiteSettings()
  supportPhone.value = String(s.support_contact_number || '').trim()
  supportEmail.value = String(s.support_email || '').trim()
})
</script>
