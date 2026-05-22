/** URL segments that are not college registration slugs (root /{slug} routes). */
export const RESERVED_ROOT_SEGMENTS = new Set([
  'admin',
  'api',
  'login',
  'register',
  'storage',
  'build',
  'sanctum',
  'vendor',
  'assets',
  'dashboard',
  'colleges',
  'students',
  'entry',
  'up',
  'health',
])

export function isAdminSpaPath(pathname = window.location.pathname) {
  return pathname === '/admin' || pathname.startsWith('/admin/')
}

export function isReservedSlug(slug) {
  return RESERVED_ROOT_SEGMENTS.has(String(slug || '').toLowerCase())
}

/** @param {'short'|'register'|'admin'} style */
export function collegeRegistrationPath(slug, style = 'short') {
  const s = String(slug || '').trim()
  if (!s) return ''
  const origin = typeof window !== 'undefined' ? window.location.origin : ''
  if (style === 'admin') return `${origin}/admin/register/${s}`
  if (style === 'register') return `${origin}/register/${s}`
  return `${origin}/${s}`
}

export function collegeRegisterRoute(slug) {
  if (isAdminSpaPath()) {
    return { name: 'register-college', params: { slug } }
  }
  return { name: 'register-college-root', params: { slug } }
}
