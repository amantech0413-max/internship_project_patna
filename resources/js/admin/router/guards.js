import { useAuthStore } from '@/stores/auth'

const PUBLIC = ['login', 'register', 'register-college', 'register-college-root', 'home']

function staffHome(auth) {
  if (auth.isAdmin) return { name: 'dashboard' }
  if (auth.can('staff_entry') && !auth.can('student_view')) return { name: 'entry' }
  return { name: 'dashboard' }
}

export function setupGuards(router) {
  router.beforeEach((to) => {
    const auth = useAuthStore()

    if (PUBLIC.includes(to.name)) {
      if (auth.token && auth.isStaff && to.name === 'login') {
        return staffHome(auth)
      }
      return true
    }

    if (!auth.token || !auth.isStaff) {
      return { name: 'login', query: { redirect: to.fullPath } }
    }

    if (to.name === 'bin') {
      if (!auth.can('bin_manage') && !auth.can('bin_delete_permanent')) {
        return staffHome(auth)
      }
    } else {
      const perm = auth.routePermissions[to.name]
      if (perm && !auth.can(perm)) {
        return staffHome(auth)
      }
    }

    if (auth.superAdminOnlyRoutes?.includes(to.name) && !auth.isSuperAdmin) {
      return staffHome(auth)
    }

    if (auth.adminOnlyRoutes.includes(to.name) && !auth.isAdmin) {
      return staffHome(auth)
    }

    return true
  })
}
