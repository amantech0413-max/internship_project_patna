import { DataTable } from '@/utils/jquerySetup'
import { http } from '@/api/client'

import { DEFAULT_PER_PAGE, PER_PAGE_PRESETS } from '@/utils/pagination'

/** Default: 10 rows; dropdown 50, 100; custom number input beside control */
export const DEFAULT_LENGTH_MENU = PER_PAGE_PRESETS

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
  const enableCustomPageLength = config.enableCustomPageLength !== false

  const userInitComplete = config.tableOptions?.initComplete

  const dt = new DataTable(el, {
    processing: true,
    serverSide: true,
    paging: true,
    pageLength: config.pageLength ?? DEFAULT_PER_PAGE,
    lengthMenu: [lengthMenu, lengthMenu.map(String)],
    order: config.defaultOrder ?? [[0, 'desc']],
    searching: config.searching ?? false,
    autoWidth: false,
    layout: config.layout ?? {
      topStart: {
        pageLength: {
          menu: lengthMenu,
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
      const perPage = requestData.length || config.pageLength || DEFAULT_PER_PAGE
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
    initComplete(...args) {
      if (enableCustomPageLength) {
        const api = this.api()
        const container = api.table().container()
        const lengthCell = container?.querySelector('.dt-length')
        if (lengthCell && !lengthCell.querySelector('[data-dt-custom-len]')) {
          const input = document.createElement('input')
          input.type = 'number'
          input.min = '1'
          input.max = '500'
          input.placeholder = 'Custom'
          input.setAttribute('data-dt-custom-len', '1')
          input.className = 'form-control form-control-sm d-inline-block ms-1'
          input.style.width = '5rem'
          input.title = 'Custom entries per page'
          const apply = () => {
            const v = parseInt(input.value, 10)
            if (v > 0 && v <= 500) {
              api.page.len(v).draw(false)
            }
          }
          input.addEventListener('change', apply)
          input.addEventListener('keydown', (ev) => {
            if (ev.key === 'Enter') {
              ev.preventDefault()
              apply()
            }
          })
          lengthCell.appendChild(document.createTextNode(' '))
          lengthCell.appendChild(input)
        }
      }
      userInitComplete?.apply(this, args)
    },
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
  else if (s === 'completed') cls = 'text-bg-primary'
  else if (s === 'rejected') cls = 'text-bg-danger'
  else if (s === 'inactive') cls = 'text-bg-secondary'
  return `<span class="badge ${cls}">${s || '—'}</span>`
}
