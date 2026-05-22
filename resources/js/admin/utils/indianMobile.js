/**
 * Normalize Indian mobile: 10 digits, or take last 10 when 91/+91/12-digit style.
 * @returns {string|null}
 */
export function normalizeIndianMobile(value) {
  const digits = String(value ?? '').replace(/\D/g, '')
  if (!digits) return null

  let normalized = digits
  if (normalized.length > 10) {
    normalized = normalized.slice(-10)
  } else if (normalized.length === 11 && normalized.startsWith('0')) {
    normalized = normalized.slice(1)
  }

  return /^\d{10}$/.test(normalized) ? normalized : null
}
