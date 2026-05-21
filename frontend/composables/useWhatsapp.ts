export const useWhatsapp = () => {
  const { apiFetch } = useApi()

  const fetchMessages = (filters: Record<string, string> = {}) => {
    const query = new URLSearchParams(filters).toString()
    return apiFetch<{ success: boolean; data: Record<string, unknown>[]; meta?: Record<string, unknown> }>(
      `/admin/whatsapp/messages?${query}`
    )
  }

  const previewStudents = (body: Record<string, unknown>) =>
    apiFetch<{ success: boolean; data: { count: number; students: Record<string, unknown>[] } }>(
      '/admin/whatsapp/preview-students',
      { method: 'POST', body }
    )

  const sendMessages = (body: Record<string, unknown>) =>
    apiFetch<{ success: boolean; data: Record<string, unknown>[]; message: string }>(
      '/admin/whatsapp/send-messages',
      { method: 'POST', body }
    )

  const retryFailed = (groupId?: number) =>
    apiFetch('/admin/whatsapp/messages/retry-failed', {
      method: 'POST',
      body: groupId ? { internship_group_id: groupId } : {},
    })

  const resendMessage = (messageId: number) =>
    apiFetch(`/admin/whatsapp/messages/${messageId}/resend`, { method: 'POST' })

  return { fetchMessages, previewStudents, sendMessages, retryFailed, resendMessage }
}
