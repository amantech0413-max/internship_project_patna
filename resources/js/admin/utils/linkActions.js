import { collegeRegistrationPath } from '@/utils/registrationPaths'
import { toastError, toastSuccess } from '@/utils/swal'

export async function copyText(text) {
  if (!text) return false
  try {
    await navigator.clipboard.writeText(text)
    toastSuccess('Link copied to clipboard.')
    return true
  } catch {
    toastError('Could not copy link.')
    return false
  }
}

export async function copyCollegeShortLink(slug) {
  return copyText(collegeRegistrationPath(slug, 'short'))
}

export async function shareCollegeShortLink(slug, title = 'College registration link') {
  const url = collegeRegistrationPath(slug, 'short')
  if (!url) return

  if (navigator.share) {
    try {
      await navigator.share({ title, url, text: title })
      return
    } catch (e) {
      if (e?.name === 'AbortError') return
    }
  }

  await copyText(url)
}

export { dtIconButton } from '@/utils/dtActions'
