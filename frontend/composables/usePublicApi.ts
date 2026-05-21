/**
 * API calls for public pages (no auth required).
 */
export const usePublicApi = () => {
  const config = useRuntimeConfig()

  const publicFetch = async <T>(path: string, options: Record<string, unknown> = {}): Promise<T> => {
    return await $fetch<T>(`${config.public.apiBase}${path}`, {
      ...options,
      headers: {
        Accept: 'application/json',
        ...(options.headers as Record<string, string> || {}),
      },
    })
  }

  return { publicFetch }
}
