<template>
  <Head title="Kanban" />

  <!-- ── Filter bar ──────────────────────────────────────────────────────── -->
  <div class="d-flex align-center gap-3 px-3 py-2 flex-wrap border-b">
    <v-chip-group v-model="departmentFilter" mandatory color="primary">
      <v-chip value="all" filter size="small">Tất cả</v-chip>
      <v-chip
        v-for="dept in DEPARTMENTS"
        :key="dept"
        :value="dept"
        filter
        size="small"
      >
        {{ dept }}
      </v-chip>
    </v-chip-group>

    <v-text-field
      v-model="search"
      prepend-inner-icon="mdi-magnify"
      placeholder="Biển số, khách hàng..."
      density="compact"
      hide-details
      clearable
      style="max-width: 240px;"
    />
  </div>

  <!-- ── Kanban board ───────────────────────────────────────────────────── -->
  <div class="kanban-board px-3 py-2">
    <div
      v-for="stage in KANBAN_STAGES"
      :key="stage"
      class="kanban-column"
    >
      <!-- Column header -->
      <div class="d-flex align-center justify-space-between mb-2 px-1">
        <span
          class="text-subtitle-2 font-weight-bold text-truncate"
          :title="stage"
        >
          {{ stage }}
        </span>
        <v-badge
          :content="byStage[stage].length"
          color="primary"
          inline
        />
      </div>

      <!-- Cards -->
      <div>
        <TicketCard
          v-for="ticket in byStage[stage]"
          :key="ticket.id"
          :ticket="ticket"
          @open-detail="openDetail"
        />
        <div
          v-if="!byStage[stage].length"
          class="d-flex flex-column align-center py-6 text-medium-emphasis"
        >
          <v-icon size="32" class="mb-1">mdi-car-off</v-icon>
          <span class="text-caption">Không có phiếu</span>
        </div>
      </div>
    </div>
  </div>

  <!-- ── Ticket detail dialog ───────────────────────────────────────────── -->
  <TicketDetailDialog v-model="detailDialog" :ticket="selectedTicket" />
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import { useStore } from 'vuex'
import AppLayout from '../Layouts/AppLayout.vue'
import TicketCard from '../Components/TicketCard.vue'
import TicketDetailDialog from '../Components/TicketDetailDialog.vue'
import { KANBAN_STAGES, DEPARTMENTS } from '../utils/roles'
import type { ServiceTicket, KanbanStage, VehicleAlertData } from '../types'
import type { RootState } from '../store'

defineOptions({ layout: AppLayout })

const props = defineProps<{
  tickets: ServiceTicket[]
  vehicleAlerts: Record<string, VehicleAlertData>
}>()

const store = useStore<RootState>()
store.dispatch('vehicleAlerts/initFromPageProps', props.vehicleAlerts)

// ── Filters ──────────────────────────────────────────────────────────────────

const departmentFilter = ref<string>('all')
const search = ref('')

const filteredTickets = computed(() => {
  const q = search.value.trim().toLowerCase()
  return props.tickets.filter(t => {
    const matchDept = departmentFilter.value === 'all' || t.department === departmentFilter.value
    const matchSearch = !q
      || t.licensePlate.toLowerCase().includes(q)
      || t.customerName.toLowerCase().includes(q)
    return matchDept && matchSearch
  })
})

const byStage = computed(() => {
  const map = {} as Record<KanbanStage, ServiceTicket[]>
  for (const s of KANBAN_STAGES) map[s] = []
  for (const t of filteredTickets.value) map[t.kanbanStage]?.push(t)
  return map
})

// ── Detail dialog ────────────────────────────────────────────────────────────

const detailDialog   = ref(false)
const selectedTicket = ref<ServiceTicket | null>(null)

function openDetail(ticket: ServiceTicket) {
  selectedTicket.value = ticket
  detailDialog.value   = true
}
</script>
