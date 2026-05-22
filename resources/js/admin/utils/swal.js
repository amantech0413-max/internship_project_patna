import Swal from 'sweetalert2'

export const Toast = Swal.mixin({
  toast: true,
  position: 'top-end',
  showConfirmButton: false,
  timer: 3200,
  timerProgressBar: true,
})

export function toastSuccess(message) {
  return Toast.fire({ icon: 'success', title: message })
}

export function toastError(message) {
  return Toast.fire({ icon: 'error', title: message })
}

export function alertError(message, title = 'Error') {
  return Swal.fire({ icon: 'error', title, text: message })
}

export function alertSuccess(message, title = 'Success') {
  return Swal.fire({ icon: 'success', title, text: message })
}

export function alertInfo(message, title = 'Notice') {
  return Swal.fire({ icon: 'info', title, text: message })
}

export async function confirmDialog(title, text, confirmText = 'Yes') {
  const result = await Swal.fire({
    title,
    text,
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: confirmText,
    cancelButtonText: 'Cancel',
    reverseButtons: true,
  })
  return result.isConfirmed
}

export async function confirmDelete(itemLabel = 'this record') {
  return confirmDialog('Delete?', `Are you sure you want to delete ${itemLabel}?`, 'Delete')
}

export async function promptText(title, inputLabel = 'Reason', placeholder = '') {
  const result = await Swal.fire({
    title,
    input: 'text',
    inputLabel,
    inputPlaceholder: placeholder,
    showCancelButton: true,
    confirmButtonText: 'Submit',
    inputValidator: (value) => (!value?.trim() ? 'This field is required' : undefined),
  })
  return result.isConfirmed ? String(result.value).trim() : null
}
