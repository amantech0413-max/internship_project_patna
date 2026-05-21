import { useToastStore } from '@/stores/toast'

export function useToast() {
  const store = useToastStore()
  return { show: store.show, message: store.message, type: store.type, clear: () => { store.visible = false } }
}
