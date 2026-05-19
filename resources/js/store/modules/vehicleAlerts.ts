import type { Module } from 'vuex'
import type { RootState } from '../index'
import type { VehicleAlertData } from '../../types'

export interface VehicleAlertsState {
  alerts: Record<string, VehicleAlertData>
}

const vehicleAlerts: Module<VehicleAlertsState, RootState> = {
  namespaced: true,

  state: () => ({ alerts: {} }),

  mutations: {
    SET_ALERTS(state, alerts: Record<string, VehicleAlertData>) {
      state.alerts = alerts
    },
  },

  actions: {
    // Alerts come from Inertia page props (Dashboard controller), not a separate fetch.
    initFromPageProps({ commit }, alerts: Record<string, VehicleAlertData>) {
      commit('SET_ALERTS', alerts)
    },
  },

  getters: {
    getAlert: (state) => (licensePlate: string): VehicleAlertData | null => {
      const key = licensePlate.toUpperCase().replace(/\s/g, '')
      return state.alerts[key] ?? null
    },
  },
}

export default vehicleAlerts