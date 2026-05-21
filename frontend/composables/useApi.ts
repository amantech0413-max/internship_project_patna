export const useApi = () => {
  const config = useRuntimeConfig()
  const { token, clearSession, isStudent } = useAuth()

  const buildHeaders = (extra: Record<string, string> = {}, isFormData = false): Record<string, string> => {
    const headers: Record<string, string> = {
      Accept: 'application/json',
      ...extra,
    }

    if (!isFormData) {
      headers['Content-Type'] = 'application/json'
    }

    if (token.value) {
      headers.Authorization = `Bearer ${token.value}`
    }

    return headers
  }

  const handle401 = async () => {
    clearSession()
    await navigateTo(isStudent.value ? '/student-login' : '/login')
  }

  const apiFetch = async <T>(path: string, options: Record<string, unknown> = {}): Promise<T> => {
    const isFormData = options.body instanceof FormData

    try {
      return await $fetch<T>(`${config.public.apiBase}${path}`, {
        ...options,
        headers: buildHeaders((options.headers as Record<string, string>) || {}, isFormData),
      })
    } catch (error: unknown) {
      const err = error as { statusCode?: number }
      if (err?.statusCode === 401) {
        await handle401()
      }
      throw error
    }
  }

  /** Download file (CSV, Excel) with Bearer token — for Nuxt admin */
  const apiDownload = async (path: string, filename: string) => {
    const res = await fetch(`${config.public.apiBase}${path}`, {
      method: 'GET',
      headers: buildHeaders({ Accept: 'text/csv, application/octet-stream, application/json' }),
    })

    if (res.status === 401) {
      await handle401()
      throw new Error('Unauthenticated')
    }

    if (!res.ok) {
      let message = 'Download failed'
      try {
        const json = await res.json()
        message = json.message || message
      } catch {}
      throw new Error(message)
    }

    const blob = await res.blob()
    const url = URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = filename
    a.click()
    URL.revokeObjectURL(url)
  }

  const apiForm = async <T>(path: string, formData: FormData): Promise<T> => {
    return apiFetch<T>(path, { method: 'POST', body: formData })
  }

  return { apiFetch, apiForm, apiDownload }
}
