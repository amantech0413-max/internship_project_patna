import type { StaffPermissionKey } from '~/types/permissions'

export const usePermissions = () => {
  const { user, isStaff } = useAuth()

  const permissions = computed(() => {
    return (user.value?.permissions as Record<string, boolean>) || {}
  })

  const can = (key: StaffPermissionKey): boolean => {
    if (!isStaff.value) return false
    const role = user.value?.role as string
    if (role === 'super_admin' || role === 'admin') return true
    return !!permissions.value[key]
  }

  const isAdmin = computed(() => {
    const role = user.value?.role as string
    return role === 'super_admin' || role === 'admin'
  })

  return { permissions, can, isAdmin }
}
