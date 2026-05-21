import { isStudentPortalPath } from '~/utils/routes'
import type { StaffPermissionKey } from '~/types/permissions'

const PUBLIC_PATHS = ['/login', '/register', '/student-login']

const STAFF_PREFIXES = [
  '/dashboard',
  '/colleges',
  '/entry',
  '/student-entries',
  '/import-logs',
  '/students',
  '/staff-users',
  '/groups',
  '/whatsapp',
  '/reports',
  '/certificates',
  '/notifications',
  '/settings',
]

const ROUTE_PERMISSIONS: Array<{ prefix: string; permission: StaffPermissionKey }> = [
  { prefix: '/colleges', permission: 'college_manage' },
  { prefix: '/entry', permission: 'staff_entry' },
  { prefix: '/student-entries', permission: 'staff_entry' },
  { prefix: '/import-logs', permission: 'staff_entry' },
  { prefix: '/students', permission: 'student_view' },
  { prefix: '/staff-users', permission: 'staff_manage' },
]

const ADMIN_ONLY_PREFIXES = [
  '/groups',
  '/whatsapp',
  '/reports',
  '/certificates',
  '/notifications',
  '/settings',
]

export default defineNuxtRouteMiddleware((to) => {
  const { token, isStaff, isStudent, can, clearSession, user } = useAuth()
  const { isAdmin } = usePermissions()

  const staffHome = () => {
    const role = user.value?.role as string
    if (role === 'super_admin' || role === 'admin') return '/dashboard'
    if (can('staff_entry') && !can('student_view')) return '/entry'
    return '/dashboard'
  }

  if (to.path === '/student-login') {
    return navigateTo('/register')
  }

  if (isStudentPortalPath(to.path)) {
    if (isStaff.value) {
      return navigateTo('/dashboard')
    }
    return navigateTo('/register')
  }

  if (to.path === '/student' || to.path === '/student/') {
    return navigateTo('/register')
  }

  if (PUBLIC_PATHS.includes(to.path)) {
    if (token.value && isStaff.value && to.path === '/login') {
      return navigateTo(staffHome())
    }
    return
  }

  if (!token.value) {
    return navigateTo('/login')
  }

  if (isStudent.value) {
    clearSession()
    return navigateTo('/login')
  }

  if (isStaff.value) {
    if (to.path === '/students/create' && !can('student_create')) {
      return navigateTo('/students')
    }

    for (const { prefix, permission } of ROUTE_PERMISSIONS) {
      if (to.path === prefix || to.path.startsWith(`${prefix}/`)) {
        if (!can(permission)) {
          return navigateTo(staffHome())
        }
        break
      }
    }

    if (ADMIN_ONLY_PREFIXES.some((p) => to.path.startsWith(p)) && !isAdmin.value) {
      return navigateTo(staffHome())
    }

    return
  }

  return navigateTo('/login')
})
