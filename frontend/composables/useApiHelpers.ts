export type ApiMeta = {
  current_page: number
  last_page: number
  per_page: number
  total: number
}

export function unwrapList<T = Record<string, unknown>>(res: {
  success?: boolean
  data?: T[] | { data?: T[] }
  meta?: ApiMeta
}): { items: T[]; meta?: ApiMeta } {
  const raw = res.data
  if (Array.isArray(raw)) {
    return { items: raw, meta: res.meta }
  }
  if (raw && typeof raw === 'object' && Array.isArray((raw as { data?: T[] }).data)) {
    return { items: (raw as { data: T[] }).data, meta: res.meta }
  }
  return { items: [], meta: res.meta }
}

export function parseApiError(e: unknown): string {
  const err = e as {
    data?: { message?: string; errors?: Record<string, string[]> }
    response?: { _data?: { message?: string; errors?: Record<string, string[]> } }
    statusMessage?: string
    message?: string
  }
  const body = err?.data ?? err?.response?._data
  if (body?.errors) {
    return Object.entries(body.errors)
      .map(([k, v]) => `${k}: ${(Array.isArray(v) ? v : [v]).join(', ')}`)
      .join('\n')
  }
  return body?.message || err?.statusMessage || err?.message || 'Request failed'
}
