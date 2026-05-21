export const useToast = () => {
  const message = useState<string | null>('toast_msg', () => null)
  const type = useState<'success' | 'danger' | 'info'>('toast_type', () => 'success')

  const show = (msg: string, t: 'success' | 'danger' | 'info' = 'success') => {
    message.value = msg
    type.value = t
    if (import.meta.client) {
      setTimeout(() => { message.value = null }, 4000)
    }
  }

  const clear = () => {
    message.value = null
  }

  return { message, type, show, clear }
}
