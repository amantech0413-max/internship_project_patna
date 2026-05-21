/** Restore staff session after refresh; refetch profile if token exists but user cache is missing */
export default defineNuxtPlugin(async () => {
  const { token, user, setSession, clearSession } = useAuth()
  const { apiFetch } = useApi()

  if (!token.value || user.value) {
    return
  }

  try {
    const res = await apiFetch<{
      success: boolean
      data: Record<string, unknown>
    }>('/auth/me')
    setSession({ token: token.value!, user: res.data })
  } catch {
    clearSession()
  }
})
