/** Default rows per page for all admin list tables */
export const DEFAULT_PER_PAGE = 10

export const PER_PAGE_PRESETS = [10, 50, 100]

export function clampPerPage(value, fallback = DEFAULT_PER_PAGE) {
  const n = parseInt(value, 10)
  if (!Number.isFinite(n) || n < 1) return fallback
  return Math.min(500, n)
}

export function fallbackMeta(perPage = DEFAULT_PER_PAGE) {
  return {
    total: 0,
    current_page: 1,
    last_page: 1,
    per_page: perPage,
  }
}
