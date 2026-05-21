import axios from 'axios'
import { useAuthStore } from '@/stores/auth'
import router from '@/router'

const apiBase = window.__BLI__?.apiBase || '/api/v1'

export const http = axios.create({
  baseURL: apiBase,
  headers: { Accept: 'application/json' },
})

http.interceptors.request.use((config) => {
  const auth = useAuthStore()
  if (auth.token) {
    config.headers.Authorization = `Bearer ${auth.token}`
  }
  if (config.data instanceof FormData) {
    delete config.headers['Content-Type']
  } else if (!config.headers['Content-Type']) {
    config.headers['Content-Type'] = 'application/json'
  }
  return config
})

http.interceptors.response.use(
  (r) => r.data,
  async (error) => {
    if (error.response?.status === 401) {
      const auth = useAuthStore()
      auth.clearSession()
      await router.push({ name: 'login' })
    }
    return Promise.reject(error)
  }
)

export async function apiFetch(path, options = {}) {
  const method = (options.method || 'GET').toLowerCase()
  const config = {
    url: path,
    method,
    params: options.params,
    data: options.body,
  }
  return http.request(config)
}

export async function apiForm(path, formData) {
  return http.post(path, formData)
}

export async function apiDownload(path, filename) {
  const auth = useAuthStore()
  const res = await fetch(`${apiBase}${path}`, {
    headers: {
      Accept: 'text/csv, application/octet-stream, application/json',
      Authorization: auth.token ? `Bearer ${auth.token}` : '',
    },
  })
  if (res.status === 401) {
    auth.clearSession()
    await router.push({ name: 'login' })
    throw new Error('Unauthenticated')
  }
  if (!res.ok) {
    const json = await res.json().catch(() => ({}))
    throw new Error(json.message || 'Download failed')
  }
  const blob = await res.blob()
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = filename
  a.click()
  URL.revokeObjectURL(url)
}

export function getPublicApi() {
  return axios.create({
    baseURL: apiBase,
    headers: { Accept: 'application/json' },
  })
}
