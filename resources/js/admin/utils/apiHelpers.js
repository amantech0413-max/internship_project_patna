import { onMounted, ref } from 'vue'

export function unwrapList(res) {
  if (!res) {
    return { items: [], meta: null }
  }

  // Already normalized by caller
  if (Array.isArray(res.items)) {
    return { items: res.items, meta: res.meta ?? null }
  }

  // Standard envelope from apiFetch: { success, data: [...], meta }
  if (res.success === true && Array.isArray(res.data)) {
    return { items: res.data, meta: res.meta ?? null }
  }

  const payload = res.data !== undefined ? res : { data: res, meta: res.meta }
  let items = payload.data
  let meta = payload.meta ?? null

  if (items && typeof items === 'object' && !Array.isArray(items)) {
    if (Array.isArray(items.data)) {
      meta = items.meta ?? meta
      items = items.data
    } else {
      items = Object.values(items).filter((row) => row && typeof row === 'object' && 'id' in row)
    }
  }

  if (!Array.isArray(items)) {
    items = []
  }

  return { items, meta }
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
  let loadedOnce = false

  const refresh = async () => {
    if (!loadedOnce) {
      pending.value = true
    }
    error.value = null
    try {
      data.value = await fetcher()
      loadedOnce = true
    } catch (e) {
      error.value = e
      if (!loadedOnce) {
        data.value = null
      }
    } finally {
      pending.value = false
    }
  }

  onMounted(refresh)

  return { data, pending, error, refresh }
}
