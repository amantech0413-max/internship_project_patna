function parseUserCache(raw: string | null | undefined): Record<string, unknown> | null {
  if (!raw) return null
  try {
    return JSON.parse(raw) as Record<string, unknown>
  } catch {
    return null
  }
}

export const useAuth = () => {
  const token = useCookie<string | null>('bli_token', { maxAge: 60 * 60 * 24 * 7 })
  const userCache = useCookie<string | null>('bli_user_cache', { maxAge: 60 * 60 * 24 * 7 })
  const user = useState<Record<string, unknown> | null>('bli_user', () => parseUserCache(userCache.value))

  const setSession = (payload: { token: string; user: Record<string, unknown> }) => {
    token.value = payload.token
    user.value = payload.user
    userCache.value = JSON.stringify(payload.user)
  }

  const clearSession = () => {
    token.value = null
    user.value = null
    userCache.value = null
  }

  const isStaff = computed(() => {
    const role = user.value?.role as string | undefined
    return ['super_admin', 'admin', 'college_coordinator'].includes(role || '')
  })

  const isStudent = computed(() => user.value?.role === 'student')

  const studentProfile = computed(() => user.value?.student as Record<string, unknown> | undefined)

  const isStudentApproved = computed(() => studentProfile.value?.status === 'approved')

  const permissions = computed(
    () => (user.value?.permissions as Record<string, boolean>) || {}
  )

  const can = (key: string): boolean => {
    const role = user.value?.role as string | undefined
    if (role === 'super_admin' || role === 'admin') return true
    return !!permissions.value[key]
  }

  return {
    token,
    user,
    setSession,
    clearSession,
    isStaff,
    isStudent,
    studentProfile,
    isStudentApproved,
    permissions,
    can,
  }
}
