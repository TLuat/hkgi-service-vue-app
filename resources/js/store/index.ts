import { createStore } from 'vuex'
import ui from './modules/ui'
import vehicleAlerts from './modules/vehicleAlerts'
import type { UiState } from './modules/ui'
import type { VehicleAlertsState } from './modules/vehicleAlerts'

export interface RootState {
  ui: UiState
  vehicleAlerts: VehicleAlertsState
}

export const store = createStore<RootState>({
  modules: { ui, vehicleAlerts },
})

export default store