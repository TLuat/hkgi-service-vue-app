import { useStore } from 'vuex'
import type { RootState } from '../store'

export function useConfirm() {
  const store = useStore<RootState>()

  function confirm(message: string): Promise<boolean> {
    return store.dispatch('ui/confirm', message)
  }

  return { confirm }
}