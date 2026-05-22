import { ref } from 'vue'
import { getPublicApi } from '@/api/client'

const cache = ref(null)
let loading = null

export function clearSiteSettingsCache() {
  cache.value = null
  loading = null
}

export async function fetchPublicSiteSettings(force = false) {
  if (cache.value && !force) return cache.value
  if (loading) return loading

  loading = (async () => {
    try {
      const res = await getPublicApi().get('/registration/settings')
      cache.value = res.data || {}
      return cache.value
    } catch {
      cache.value = {
        organization_name: 'M/s Bhagya Laxmi',
        organization_address: 'A-1, Patliputra Industrial Area, Patna-800013, Bihar, India',
        organization_logo_url: null,
        registration_fee_amount: 0,
        upi_id: null,
        upi_qr_url: null,
      }
      return cache.value
    } finally {
      loading = null
    }
  })()

  return loading
}
