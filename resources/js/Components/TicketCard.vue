<template>
  <div :class="['kanban-card', kanbanCardClass(ticket.priority)]">

    <!-- ── Clickable body ────────────────────────────────────────────────── -->
    <div class="pa-3" @click="emit('open-detail', ticket)">

      <!-- License plate + priority chip -->
      <div class="d-flex align-center justify-space-between mb-1">
        <span class="text-h6 font-weight-bold">{{ ticket.licensePlate }}</span>
        <v-chip :color="PRIORITY_COLORS[ticket.priority]" size="x-small" label>
          {{ ticket.priority }}
        </v-chip>
      </div>

      <!-- Customer name -->
      <div class="text-body-2 font-weight-medium mb-1">{{ ticket.customerName }}</div>

      <!-- Model -->
      <div v-if="ticket.model" class="text-caption text-medium-emphasis mb-2">
        {{ ticket.model }}
      </div>

      <!-- Department chip + advisor -->
      <div class="d-flex align-center ga-1 flex-wrap mb-2">
        <v-chip size="x-small" label color="primary" variant="tonal">
          {{ ticket.department }}
        </v-chip>
        <span v-if="ticket.advisor" class="text-caption text-medium-emphasis">
          {{ ticket.advisor }}
        </span>
      </div>

      <!-- Check-in time -->
      <div class="text-caption text-medium-emphasis mb-1">
        <v-icon size="12" class="mr-1">mdi-login</v-icon>
        {{ formatDate(ticket.checkInAt) }}
      </div>

      <!-- Due time -->
      <div
        v-if="ticket.dueAt"
        class="text-caption mb-2"
        :class="overdue ? 'text-error font-weight-bold' : 'text-medium-emphasis'"
      >
        <v-icon size="12" class="mr-1">mdi-clock-outline</v-icon>
        Hẹn: {{ formatDate(ticket.dueAt) }}
        <v-icon v-if="overdue" size="12" color="error">mdi-alert-circle</v-icon>
      </div>

      <!-- Vehicle alert banner -->
      <VehicleAlertBanner v-if="ticket.vehicleAlert" :alert="ticket.vehicleAlert" />
    </div>

    <!-- ── Stage navigation buttons ──────────────────────────────────────── -->
    <div
      v-if="userCanMove"
      class="d-flex align-center px-2 pb-2"
      @click.stop
    >
      <v-btn
        v-if="prevStage"
        icon="mdi-chevron-left"
        size="x-small"
        variant="text"
        density="compact"
        :title="prevStage"
        :loading="moving"
        @click="moveStage(prevStage)"
      />
      <v-spacer />
      <v-btn
        v-if="nextStage"
        icon="mdi-chevron-right"
        size="x-small"
        variant="text"
        density="compact"
        :title="nextStage"
        :loading="moving"
        @click="moveStage(nextStage)"
      />
    </div>

  </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import VehicleAlertBanner from './VehicleAlertBanner.vue'
import { KANBAN_STAGES, canMoveKanban, kanbanCardClass, PRIORITY_COLORS, isTicketOverdue } from '../utils/roles'
import type { ServiceTicket, KanbanStage, SharedProps } from '../types'

const props = defineProps<{ ticket: ServiceTicket }>()
const emit  = defineEmits<{ 'open-detail': [ticket: ServiceTicket] }>()

const page        = usePage<SharedProps>()
const user        = computed(() => page.props.auth.user)
const userCanMove = computed(() => user.value ? canMoveKanban(user.value.role) : false)

const stageIndex = computed(() => KANBAN_STAGES.indexOf(props.ticket.kanbanStage))
const prevStage  = computed<KanbanStage | null>(() => KANBAN_STAGES[stageIndex.value - 1] ?? null)
const nextStage  = computed<KanbanStage | null>(() => KANBAN_STAGES[stageIndex.value + 1] ?? null)

const overdue = computed(() => isTicketOverdue(props.ticket))

const moving = ref(false)

function moveStage(stage: KanbanStage) {
  moving.value = true
  router.patch(
    `/tickets/${props.ticket.id}/stage`,
    { kanbanStage: stage },
    {
      preserveScroll: true,
      onFinish: () => { moving.value = false },
    },
  )
}

function formatDate(d: string): string {
  return new Date(d).toLocaleString('vi-VN', { dateStyle: 'short', timeStyle: 'short' })
}
</script>
