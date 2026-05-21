import { apiFetch } from '@/api/client'

export function useWhatsapp() {
  const fetchMessages = (filters = {}) => {
    const query = new URLSearchParams(filters).toString()
    return apiFetch(`/admin/whatsapp/messages?${query}`)
  }

  const previewStudents = (body) =>
    apiFetch('/admin/whatsapp/preview-students', { method: 'POST', body })

  const sendMessages = (body) =>
    apiFetch('/admin/whatsapp/send-messages', { method: 'POST', body })

  const retryFailed = (groupId) =>
    apiFetch('/admin/whatsapp/messages/retry-failed', {
      method: 'POST',
      body: groupId ? { internship_group_id: groupId } : {},
    })

  const resendMessage = (messageId) =>
    apiFetch(`/admin/whatsapp/messages/${messageId}/resend`, { method: 'POST' })

  return { fetchMessages, previewStudents, sendMessages, retryFailed, resendMessage }
}
