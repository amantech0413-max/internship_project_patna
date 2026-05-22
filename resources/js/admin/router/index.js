import { createRouter, createWebHistory } from 'vue-router'
import { setupGuards } from './guards'

const routes = [
  {
    path: '/',
    component: () => import('@/layouts/BlankLayout.vue'),
    children: [
      { path: '', name: 'home', redirect: '/login' },
      { path: 'login', name: 'login', component: () => import('@/views/LoginView.vue') },
      { path: 'register', name: 'register', component: () => import('@/views/RegisterHomeView.vue') },
      {
        path: 'register/:slug',
        name: 'register-college',
        component: () => import('@/views/RegisterView.vue'),
      },
    ],
  },
  {
    path: '/',
    component: () => import('@/layouts/AdminLayout.vue'),
    meta: { requiresAuth: true },
    children: [
      { path: 'dashboard', name: 'dashboard', component: () => import('@/views/DashboardView.vue') },
      { path: 'colleges', name: 'colleges', component: () => import('@/views/CollegesView.vue') },
      { path: 'entry', name: 'entry', component: () => import('@/views/EntryView.vue') },
      { path: 'import-logs', name: 'import-logs', component: () => import('@/views/ImportLogsView.vue') },
      { path: 'students', name: 'students', component: () => import('@/views/StudentsIndexView.vue') },
      { path: 'students/create', name: 'student-create', component: () => import('@/views/StudentCreateView.vue') },
      { path: 'students/:id', name: 'student-edit', component: () => import('@/views/StudentEditView.vue') },
      { path: 'roles', name: 'roles', component: () => import('@/views/RolesView.vue') },
      { path: 'staff-users', name: 'staff-users', component: () => import('@/views/StaffUsersView.vue') },
      { path: 'bin', name: 'bin', component: () => import('@/views/BinView.vue') },
      { path: 'groups', name: 'groups', component: () => import('@/views/GroupsIndexView.vue') },
      { path: 'groups/create', name: 'group-create', component: () => import('@/views/GroupCreateView.vue') },
      { path: 'groups/:id', name: 'group-edit', component: () => import('@/views/GroupEditView.vue') },
      { path: 'whatsapp', name: 'whatsapp', component: () => import('@/views/WhatsappView.vue') },
      { path: 'reports', name: 'reports', component: () => import('@/views/ReportsView.vue') },
      { path: 'certificates', name: 'certificates', component: () => import('@/views/CertificatesView.vue') },
      { path: 'notifications', name: 'notifications', component: () => import('@/views/NotificationsView.vue') },
      { path: 'settings', name: 'settings', component: () => import('@/views/SettingsView.vue') },
    ],
  },
]

const router = createRouter({
  history: createWebHistory('/admin'),
  routes,
  linkActiveClass: 'active',
})

setupGuards(router)

export default router
