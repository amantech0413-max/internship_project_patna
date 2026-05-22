import { createRouter, createWebHistory } from 'vue-router'
import { setupGuards } from './guards'
import { isAdminSpaPath, isReservedSlug } from '@/utils/registrationPaths'

const useAdminBase = isAdminSpaPath()

const adminBlankChildren = [
  { path: '', name: 'home', redirect: '/login' },
  { path: 'login', name: 'login', component: () => import('@/views/LoginView.vue') },
  { path: 'register', name: 'register', component: () => import('@/views/LandingView.vue') },
  {
    path: 'register/:slug',
    name: 'register-college',
    component: () => import('@/views/RegisterView.vue'),
  },
]

const publicBlankChildren = [
  { path: '', name: 'landing', component: () => import('@/views/LandingView.vue') },
  { path: 'register', redirect: { name: 'landing' } },
  {
    path: 'register/:slug',
    name: 'register-college',
    component: () => import('@/views/RegisterView.vue'),
  },
  {
    path: ':slug',
    name: 'register-college-root',
    component: () => import('@/views/RegisterView.vue'),
    beforeEnter: (to) => {
      if (isReservedSlug(to.params.slug)) {
        return { name: 'register' }
      }
      return true
    },
  },
]

const adminPanelChildren = [
  { path: 'dashboard', name: 'dashboard', component: () => import('@/views/DashboardView.vue') },
  { path: 'colleges', name: 'colleges', component: () => import('@/views/CollegesView.vue') },
  { path: 'entry', name: 'entry', component: () => import('@/views/EntryView.vue') },
  { path: 'import-logs', name: 'import-logs', component: () => import('@/views/ImportLogsView.vue') },
  { path: 'bulk-students', name: 'bulk-students', component: () => import('@/views/BulkStudentsView.vue') },
  { path: 'students', name: 'students', component: () => import('@/views/StudentsIndexView.vue') },
      { path: 'students/:id', name: 'student-edit', component: () => import('@/views/StudentEditView.vue') },
      { path: 'payments', name: 'payments', component: () => import('@/views/PaymentsIndexView.vue') },
  { path: 'roles', name: 'roles', component: () => import('@/views/RolesView.vue') },
  { path: 'staff-users', name: 'staff-users', component: () => import('@/views/StaffUsersView.vue') },
  { path: 'bin', name: 'bin', component: () => import('@/views/BinView.vue') },
  { path: 'whatsapp', name: 'whatsapp', component: () => import('@/views/WhatsappView.vue') },
  { path: 'reports', name: 'reports', component: () => import('@/views/ReportsView.vue') },
  { path: 'certificates', name: 'certificates', component: () => import('@/views/CertificatesView.vue') },
  { path: 'settings', name: 'settings', component: () => import('@/views/SettingsView.vue') },
]

const routes = useAdminBase
  ? [
      {
        path: '/',
        component: () => import('@/layouts/BlankLayout.vue'),
        children: adminBlankChildren,
      },
      {
        path: '/',
        component: () => import('@/layouts/AdminLayout.vue'),
        meta: { requiresAuth: true },
        children: adminPanelChildren,
      },
    ]
  : [
      {
        path: '/',
        component: () => import('@/layouts/BlankLayout.vue'),
        children: publicBlankChildren,
      },
    ]

const router = createRouter({
  history: createWebHistory(useAdminBase ? '/admin' : '/'),
  routes,
  linkActiveClass: 'active',
})

setupGuards(router)

export default router
