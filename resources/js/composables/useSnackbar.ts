import { useStore } from 'vuex'
import type { RootState } from '../store'

export function useSnackbar() {
  const store = useStore<RootState>()

  return {
    showSuccess: (message: string) => store.dispatch('ui/showSuccess', message),
    showError:   (message: string) => store.dispatch('ui/showError',   message),
  }
}