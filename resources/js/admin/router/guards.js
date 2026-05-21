import { useAuthStore } from '@/stores/auth'

const PUBLIC = ['login', 'register', 'home']

const PERM_ROUTES = {
  colleges: 'college_manage',
  entry: 'staff_entry',
  'student-entries': 'staff_entry',
  'import-logs': 'staff_entry',
  students: 'student_view',
  'student-create': 'student_create',
  'student-edit': 'student_edit',
  'staff-users': 'staff_manage',
}

const ADMIN_ONLY = ['groups', 'group-create', 'group-edit', 'whatsapp', 'reports', 'certificates', 'notifications', 'settings']

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

    const perm = PERM_ROUTES[to.name]
    if (perm && !auth.can(perm)) {
      return staffHome(auth)
    }

    if (ADMIN_ONLY.includes(to.name) && !auth.isAdmin) {
      return staffHome(auth)
    }

    return true
  })
}
