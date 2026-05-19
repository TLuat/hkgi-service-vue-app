<template>
  <!-- On md+ screens, redirect to dedicated /kanban page -->
  <div v-if="mdAndUp" class="d-flex flex-column align-center justify-center pa-12 text-center">
    <v-icon size="56" color="primary" class="mb-3">mdi-view-column</v-icon>
    <div class="text-h6 mb-2">Đang chuyển đến Kanban...</div>
    <div class="text-body-2 text-medium-emphasis">Mở bảng Kanban toàn màn hình</div>
  </div>

  <!-- Mobile: inline kanban board -->
  <div v-else class="kanban-board pa-3">
    <div
      v-for="stage in KANBAN_STAGES"
      :key="stage"
      class="kanban-column"
    >
      <div class="d-flex align-center justify-space-between mb-2 px-1">
        <span class="text-subtitle-2 font-weight-bold text-truncate">{{ stage }}</span>
        <v-chip size="x-small" class="ml-1 flex-shrink-0">{{ byStage[stage]?.length ?? 0 }}</v-chip>
      </div>

      <div style="min-height:60px;">
        <TicketCard
          v-for="ticket in byStage[stage] ?? []"
          :key="ticket.id"
          :ticket="ticket"
          @open-detail="emit('open-detail', $event)"
        />
        <div
          v-if="!byStage[stage]?.length"
          class="text-caption text-medium-emphasis text-center py-4"
        >
          Trống
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import { useDisplay } from 'vuetify'
import TicketCard from './TicketCard.vue'
import { KANBAN_STAGES } from '../utils/roles'
import type { ServiceTicket, KanbanStage } from '../types'

const props = defineProps<{ tickets: ServiceTicket[] }>()
const emit  = defineEmits<{ (e: 'open-detail', ticket: ServiceTicket): void }>()

const { mdAndUp } = useDisplay()

onMounted(() => {
  if (mdAndUp.value) router.visit('/kanban')
})

const byStage = computed(() => {
  const map: Record<KanbanStage, ServiceTicket[]> = {} as Record<KanbanStage, ServiceTicket[]>
  for (const s of KANBAN_STAGES) map[s] = []
  for (const t of props.tickets) map[t.kanbanStage]?.push(t)
  return map
})
</script>