import { defineStore } from 'pinia'
import { computed, ref } from 'vue'
import { apiFetch } from '@/api/client'

const TOKEN_KEY = 'bli_token'
const USER_KEY = 'bli_user_cache'
const ACCESS_KEY = 'bli_access_cache'

const FALLBACK_ROUTE_PERMISSIONS = {
  colleges: 'college_manage',
  entry: 'staff_entry',
  'import-logs': 'staff_entry',
  students: 'student_view',
  'student-create': 'student_create',
  'student-edit': 'student_edit',
  'staff-users': 'staff_manage',
  bin: 'bin_manage',
}

const FALLBACK_SUPER_ADMIN_ONLY = ['roles']

const FALLBACK_ADMIN_ONLY = [
  'groups',
  'group-create',
  'group-edit',
  'whatsapp',
  'reports',
  'certificates',
  'notifications',
  'settings',
]

const FALLBACK_MENU = [
  { title: 'Main', items: [{ to: '/dashboard', label: 'Dashboard', icon: 'bi-speedometer2' }] },
  {
    title: 'College & Entry',
    items: [
      { to: '/colleges', label: 'Colleges', icon: 'bi-building', permission: 'college_manage' },
      { to: '/entry', label: 'Add Student', icon: 'bi-person-plus', permission: 'staff_entry' },
      { to: '/import-logs', label: 'Import Logs', icon: 'bi-file-earmark-spreadsheet', permission: 'staff_entry' },
    ],
  },
  { title: 'Students', items: [{ to: '/students', label: 'Students', icon: 'bi-people', permission: 'student_view' }] },
  {
    title: 'Internship (Full)',
    items: [
      { to: '/groups', label: 'Internship Groups', icon: 'bi-collection', admin_only: true },
      { to: '/whatsapp', label: 'WhatsApp', icon: 'bi-whatsapp', admin_only: true },
      { to: '/reports', label: 'Reports', icon: 'bi-bar-chart', admin_only: true },
      { to: '/certificates', label: 'Certificates', icon: 'bi-award', admin_only: true },
      { to: '/notifications', label: 'Notifications', icon: 'bi-bell', admin_only: true },
      { to: '/settings', label: 'Settings', icon: 'bi-gear', admin_only: true },
    ],
  },
  {
    title: 'Administration',
    items: [
      { to: '/roles', label: 'Roles & Permissions', icon: 'bi-shield-lock', super_admin_only: true },
      { to: '/staff-users', label: 'Staff Users', icon: 'bi-person-badge', permission: 'staff_manage' },
      {
        to: '/bin',
        label: 'Recycle Bin',
        icon: 'bi-trash',
        permissions: ['bin_manage', 'bin_delete_permanent'],
      },
    ],
  },
]

function parseJson(raw) {
  if (!raw) return null
  try {
    return JSON.parse(raw)
  } catch {
    return null
  }
}

export const useAuthStore = defineStore('auth', () => {
  const token = ref(localStorage.getItem(TOKEN_KEY) || '')
  const user = ref(parseJson(localStorage.getItem(USER_KEY)))
  const access = ref(parseJson(localStorage.getItem(ACCESS_KEY)))

  const isStaff = computed(() => !!user.value?.can_access_panel)

  const isSuperAdmin = computed(() => !!user.value?.is_super_admin)

  const isAdmin = computed(() => !!user.value?.is_super_admin || user.value?.role === 'admin')

  const permissions = computed(() => user.value?.permissions || {})

  const permissionKeys = computed(() => access.value?.permissions?.keys ?? [])
  const permissionLabels = computed(() => access.value?.permissions?.labels ?? {})
  const permissionDefaults = computed(() => access.value?.permissions?.defaults ?? {})
  const assignableRoles = computed(() => access.value?.roles ?? [])

  const routePermissions = computed(() => access.value?.route_permissions ?? FALLBACK_ROUTE_PERMISSIONS)
  const adminOnlyRoutes = computed(() => access.value?.admin_only_routes ?? FALLBACK_ADMIN_ONLY)
  const superAdminOnlyRoutes = computed(
    () => access.value?.super_admin_only_routes ?? FALLBACK_SUPER_ADMIN_ONLY
  )
  const menuSections = computed(() => access.value?.menu ?? FALLBACK_MENU)
  const staffRoles = computed(() => access.value?.roles ?? [])

  function can(key) {
    if (isSuperAdmin.value || isAdmin.value) return true
    return !!permissions.value[key]
  }

  const itemVisible = (item) => {
    if (item.super_admin_only && !isSuperAdmin.value) return false
    if (item.admin_only && !isAdmin.value) return false
    if (item.permissions?.length) {
      return item.permissions.some((p) => can(p))
    }
    if (item.permission) return can(item.permission)
    return true
  }

  const visibleMenuSections = computed(() =>
    menuSections.value
      .map((section) => ({ ...section, items: section.items.filter(itemVisible) }))
      .filter((section) => section.items.length)
  )

  function setSession(payload) {
    token.value = payload.token
    user.value = payload.user
    localStorage.setItem(TOKEN_KEY, payload.token)
    localStorage.setItem(USER_KEY, JSON.stringify(payload.user))
  }

  function setAccess(payload) {
    access.value = payload
    localStorage.setItem(ACCESS_KEY, JSON.stringify(payload))
  }

  function clearSession() {
    token.value = ''
    user.value = null
    access.value = null
    localStorage.removeItem(TOKEN_KEY)
    localStorage.removeItem(USER_KEY)
    localStorage.removeItem(ACCESS_KEY)
  }

  async function loadAccess() {
    if (!token.value) return null
    try {
      const res = await apiFetch('/auth/access')
      if (res?.data) {
        setAccess(res.data)
      }
      return access.value
    } catch {
      return access.value
    }
  }

  return {
    token,
    user,
    access,
    isStaff,
    isSuperAdmin,
    isAdmin,
    permissions,
    staffRoles,
    permissionKeys,
    permissionLabels,
    permissionDefaults,
    assignableRoles,
    routePermissions,
    adminOnlyRoutes,
    superAdminOnlyRoutes,
    menuSections,
    visibleMenuSections,
    can,
    setSession,
    setAccess,
    clearSession,
    loadAccess,
  }
})
