const STUDENT_STATUS_ICONS = {
  pending: { icon: 'hourglass-split', colorClass: 'text-warning', title: 'Pending' },
  approved: { icon: 'check-circle-fill', colorClass: 'text-success', title: 'Approved' },
  rejected: { icon: 'x-circle-fill', colorClass: 'text-danger', title: 'Rejected' },
  completed: { icon: 'patch-check-fill', colorClass: 'text-primary', title: 'Completed' },
}

function escAttr(value) {
  return String(value)
    .replace(/&/g, '&amp;')
    .replace(/"/g, '&quot;')
    .replace(/</g, '&lt;')
}

/** @returns {{ icon: string, colorClass: string, title: string }} */
export function getStudentStatusMeta(status) {
  const s = String(status || '').toLowerCase()
  return STUDENT_STATUS_ICONS[s] || { icon: 'question-circle', colorClass: 'text-secondary', title: s || 'Unknown' }
}

export function studentStatusIcon(status) {
  const cfg = getStudentStatusMeta(status)
  const title = escAttr(cfg.title)
  return (
    `<span class="status-icon-badge ${cfg.colorClass}" title="${title}" aria-label="${title}">` +
    `<i class="bi bi-${cfg.icon}"></i></span>`
  )
}

/** HTML for DataTables / list icon-only action buttons */
export function dtIconButton({ action, icon, btnClass, title, id, slug, extraAttrs = '' }) {
  const slugAttr = slug ? ` data-slug="${escAttr(slug)}"` : ''
  const extra = extraAttrs ? ` ${extraAttrs.trim()}` : ''
  const safeTitle = escAttr(title)
  const safeId = id !== undefined && id !== null ? String(id) : ''
  return (
    `<button type="button" class="btn btn-sm btn-icon-action ${btnClass}" ` +
    `data-dt-action="${action}" data-id="${safeId}"${slugAttr}${extra} ` +
    `title="${safeTitle}" aria-label="${safeTitle}">` +
    `<i class="bi bi-${icon}"></i></button>`
  )
}
