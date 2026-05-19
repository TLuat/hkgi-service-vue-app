import type { Module } from 'vuex'
import type { RootState } from '../index'

export interface UiState {
  snackbar: { show: boolean; message: string; color: string }
  confirmDialog: {
    show: boolean
    message: string
    resolve: ((value: boolean) => void) | null
  }
}

const ui: Module<UiState, RootState> = {
  namespaced: true,

  state: () => ({
    snackbar:      { show: false, message: '', color: 'success' },
    confirmDialog: { show: false, message: '', resolve: null },
  }),

  mutations: {
    SHOW_SNACKBAR(state, payload: { message: string; color: string }) {
      state.snackbar.message = payload.message
      state.snackbar.color   = payload.color
      state.snackbar.show    = true
    },
    HIDE_SNACKBAR(state) {
      state.snackbar.show = false
    },
    SHOW_CONFIRM(state, payload: { message: string; resolve: (v: boolean) => void }) {
      state.confirmDialog.message = payload.message
      state.confirmDialog.resolve = payload.resolve
      state.confirmDialog.show    = true
    },
    RESOLVE_CONFIRM(state, value: boolean) {
      state.confirmDialog.resolve?.(value)
      state.confirmDialog.show    = false
      state.confirmDialog.resolve = null
    },
  },

  actions: {
    showSuccess({ commit }, message: string) {
      commit('SHOW_SNACKBAR', { message, color: 'success' })
    },
    showError({ commit }, message: string) {
      commit('SHOW_SNACKBAR', { message, color: 'error' })
    },
    confirm({ commit }, message: string): Promise<boolean> {
      return new Promise((resolve) => {
        commit('SHOW_CONFIRM', { message, resolve })
      })
    },
  },
}

export default ui