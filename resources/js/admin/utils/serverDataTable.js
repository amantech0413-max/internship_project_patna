import { DataTable } from '@/utils/jquerySetup'
import { http } from '@/api/client'

/** Default: 10 rows; dropdown 50, 100; type any number for custom page size */
export const DEFAULT_LENGTH_MENU = [10, 50, 100]

/**
 * Server-side DataTable wired to Laravel API: { success, data: [], meta: { total, ... } }
 */
export function initServerDataTable(tableEl, config = {}) {
  if (!tableEl) return null

  const el =
    tableEl instanceof HTMLElement
      ? tableEl
      : typeof tableEl === 'string'
        ? document.querySelector(tableEl)
        : null

  if (!el) return null

  if (DataTable.isDataTable(el)) {
    DataTable.table(el).destroy()
  }

  const columnSortKeys = config.columnSortKeys || []
  const lengthMenu = config.lengthMenu ?? DEFAULT_LENGTH_MENU

  const dt = new DataTable(el, {
    processing: true,
    serverSide: true,
    paging: true,
    pageLength: config.pageLength ?? 10,
    lengthMenu,
    order: config.defaultOrder ?? [[0, 'desc']],
    searching: config.searching ?? false,
    autoWidth: false,
    layout: config.layout ?? {
      topStart: {
        pageLength: {
          menu: lengthMenu,
          input: true,
        },
      },
      topEnd: 'info',
      bottomStart: null,
      bottomEnd: 'paging',
    },
    language: {
      emptyTable: 'No records found',
      zeroRecords: 'No matching records found',
      processing: 'Loading...',
      lengthMenu: 'Show _MENU_ entries',
      info: 'Showing _START_ to _END_ of _TOTAL_ entries',
      infoEmpty: 'No entries',
      infoFiltered: '(filtered from _MAX_ total)',
      paginate: { next: 'Next', previous: 'Prev' },
      ...(config.language || {}),
    },
    ajax: (requestData, callback) => {
      const order = requestData.order?.[0]
      const sortBy =
        order != null && columnSortKeys[order.column]
          ? columnSortKeys[order.column]
          : config.defaultSortBy || columnSortKeys.find(Boolean) || 'created_at'
      const sortDir = order?.dir === 'asc' ? 'asc' : 'desc'
      const perPage = requestData.length || config.pageLength || 10
      const page = Math.floor(requestData.start / perPage) + 1
      const params = {
        page,
        per_page: perPage,
        sort_by: sortBy,
        sort_dir: sortDir,
        ...(config.getFilterParams?.() || {}),
      }

      http
        .get(config.url, { params })
        .then((res) => {
          const rows = Array.isArray(res?.data) ? res.data : []
          callback({
            draw: requestData.draw,
            recordsTotal: res?.meta?.total ?? rows.length,
            recordsFiltered: res?.meta?.total ?? rows.length,
            data: config.transformRows ? config.transformRows(rows) : rows,
          })
          config.onLoaded?.(rows, res?.meta)
        })
        .catch((err) => {
          callback({
            draw: requestData.draw,
            recordsTotal: 0,
            recordsFiltered: 0,
            data: [],
          })
          config.onError?.(err)
        })
    },
    columns: config.columns,
    columnDefs: config.columnDefs,
    ...config.tableOptions,
  })

  return dt
}

export function reloadDataTable(dt) {
  if (!dt) return
  if (typeof dt.ajax?.reload === 'function') {
    dt.ajax.reload(null, false)
  } else if (typeof dt.draw === 'function') {
    dt.draw(false)
  }
}

export function destroyDataTable(dt) {
  if (!dt) return
  if (typeof dt.destroy === 'function') {
    dt.destroy()
  }
}

export function formatDateTime(value) {
  if (!value) return '—'
  try {
    return new Date(String(value)).toLocaleString('en-IN', {
      day: '2-digit',
      month: 'short',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    })
  } catch {
    return String(value)
  }
}

export function statusBadge(status) {
  const s = String(status || '').toLowerCase()
  let cls = 'text-bg-secondary'
  if (s === 'pending') cls = 'text-bg-warning'
  else if (s === 'approved' || s === 'active') cls = 'text-bg-success'
  else if (s === 'rejected') cls = 'text-bg-danger'
  else if (s === 'inactive') cls = 'text-bg-secondary'
  return `<span class="badge ${cls}">${s || '—'}</span>`
}
