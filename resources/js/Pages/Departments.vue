<template>
  <Head title="Bộ phận" />

  <!-- ── Department tabs ───────────────────────────────────────────────────── -->
  <v-tabs
    v-model="activeTab"
    color="primary"
    border="b"
    bg-color="surface"
  >
    <v-tab v-for="dept in DEPARTMENTS" :key="dept" :value="dept">
      {{ dept }}
      <v-chip size="x-small" class="ml-2" variant="tonal">{{ byDept[dept].length }}</v-chip>
    </v-tab>
  </v-tabs>

  <v-tabs-window v-model="activeTab">
    <v-tabs-window-item v-for="dept in DEPARTMENTS" :key="dept" :value="dept">
      <v-container fluid class="pa-4">

        <!-- Stage summary chips -->
        <div class="d-flex flex-wrap ga-2 mb-4">
          <v-chip
            v-for="stage in KANBAN_STAGES"
            :key="stage"
            size="small"
            label
            :variant="stageCounts[dept]?.[stage] ? 'tonal' : 'outlined'"
            :color="stageCounts[dept]?.[stage] ? 'primary' : undefined"
          >
            {{ stage }}
            <strong class="ml-1">{{ stageCounts[dept]?.[stage] ?? 0 }}</strong>
          </v-chip>
        </div>

        <!-- Tickets table -->
        <v-card rounded="lg" :border="true">
          <v-data-table
            :items="byDept[dept]"
            :headers="headers"
            density="compact"
            hover
            :row-props="rowProps"
            @click:row="(_: Event, row: { item: ServiceTicket }) => openDetail(row.item)"
          >
            <template #item.priority="{ item }">
              <v-chip :color="PRIORITY_COLORS[item.priority]" size="x-small" label>
                {{ item.priority }}
              </v-chip>
            </template>

            <template #item.kanbanStage="{ item }">
              <v-chip size="x-small" label>{{ item.kanbanStage }}</v-chip>
            </template>

            <template #item.dueAt="{ item }">
              <span :class="isTicketOverdue(item) ? 'text-error font-weight-bold' : ''">
                {{ item.dueAt ? formatDate(item.dueAt) : '—' }}
              </span>
            </template>

            <template #no-data>
              <div class="d-flex flex-column align-center py-8 text-medium-emphasis">
                <v-icon size="40" class="mb-2">mdi-car-off</v-icon>
                <div>Không có phiếu nào</div>
              </div>
            </template>

            <template #item.bayId="{ item }">
              {{ item.bayId || '—' }}
            </template>
          </v-data-table>
        </v-card>

      </v-container>
    </v-tabs-window-item>
  </v-tabs-window>

  <!-- Ticket detail dialog (shared across tabs) -->
  <TicketDetailDialog v-model="detailDialog" :ticket="selectedTicket" />
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import AppLayout from '../Layouts/AppLayout.vue'
import TicketDetailDialog from '../Components/TicketDetailDialog.vue'
import { DEPARTMENTS, KANBAN_STAGES, PRIORITY_COLORS, isTicketOverdue } from '../utils/roles'
import type { ServiceTicket, DepartmentName, KanbanStage } from '../types'

defineOptions({ layout: AppLayout })

const props = defineProps<{ tickets: ServiceTicket[] }>()

const activeTab = ref<DepartmentName>(DEPARTMENTS[0])

// ── Pre-computed maps ─────────────────────────────────────────────────────────

const byDept = computed(() => {
  const map = {} as Record<DepartmentName, ServiceTicket[]>
  for (const d of DEPARTMENTS) map[d] = []
  for (const t of props.tickets) map[t.department]?.push(t)
  return map
})

const stageCounts = computed(() => {
  const result = {} as Record<DepartmentName, Record<KanbanStage, number>>
  for (const dept of DEPARTMENTS) {
    result[dept] = {} as Record<KanbanStage, number>
    for (const stage of KANBAN_STAGES) result[dept][stage] = 0
  }
  for (const t of props.tickets) {
    result[t.department]?.[t.kanbanStage] !== undefined && result[t.department][t.kanbanStage]++
  }
  return result
})

// ── Table ─────────────────────────────────────────────────────────────────────

const headers = [
  { title: 'Biển số',        key: 'licensePlate' },
  { title: 'Khách hàng',     key: 'customerName' },
  { title: 'Cố vấn',         key: 'advisor' },
  { title: 'Kỹ thuật viên',  key: 'technician' },
  { title: 'Bay',            key: 'bayId' },
  { title: 'Giai đoạn',      key: 'kanbanStage' },
  { title: 'Ưu tiên',        key: 'priority' },
  { title: 'Hạn xong',       key: 'dueAt' },
]

const PRIORITY_ROW_BG: Partial<Record<string, string>> = {
  'Khẩn': 'rgba(183, 28, 28, .1)',
  'Cao':  'rgba(244, 67, 54, .06)',
}

function rowProps({ item }: { item: ServiceTicket }) {
  const bg = PRIORITY_ROW_BG[item.priority]
  return bg ? { style: `background-color: ${bg};` } : {}
}

// ── Detail dialog ─────────────────────────────────────────────────────────────

const detailDialog   = ref(false)
const selectedTicket = ref<ServiceTicket | null>(null)

function openDetail(ticket: ServiceTicket) {
  selectedTicket.value = ticket
  detailDialog.value   = true
}

// ── Helpers ───────────────────────────────────────────────────────────────────


function formatDate(d: string): string {
  return new Date(d).toLocaleString('vi-VN', { dateStyle: 'short', timeStyle: 'short' })
}
</script>
