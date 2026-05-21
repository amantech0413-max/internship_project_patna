import { onMounted, ref } from 'vue'

export function unwrapList(res) {
  const raw = res?.data
  if (Array.isArray(raw)) {
    return { items: raw, meta: res.meta }
  }
  if (raw?.data && Array.isArray(raw.data)) {
    return { items: raw.data, meta: res.meta }
  }
  return { items: [], meta: res.meta }
}

export function parseApiError(e) {
  const body = e?.response?.data ?? e?.data
  if (body?.errors) {
    return Object.entries(body.errors)
      .map(([k, v]) => `${k}: ${(Array.isArray(v) ? v : [v]).join(', ')}`)
      .join('\n')
  }
  return body?.message || e?.message || 'Request failed'
}

export function useFetchData(fetcher) {
  const data = ref(null)
  const pending = ref(true)
  const error = ref(null)

  const refresh = async () => {
    pending.value = true
    error.value = null
    try {
      data.value = await fetcher()
    } catch (e) {
      error.value = e
    } finally {
      pending.value = false
    }
  }

  onMounted(refresh)

  return { data, pending, error, refresh }
}
