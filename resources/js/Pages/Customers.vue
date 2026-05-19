<template>
  <v-container fluid class="pa-4">

  <Head title="Khách hàng" />

    <!-- Search bar -->
    <v-text-field
      v-model="search"
      prepend-inner-icon="mdi-magnify"
      placeholder="Tìm theo tên khách, biển số, SĐT..."
      density="compact"
      hide-details
      clearable
      class="mb-4"
      style="max-width: 400px;"
    />

    <!-- Customer table -->
    <v-card rounded="lg" :border="true">
      <v-data-table
        :items="filteredCustomers"
        :headers="headers"
        density="compact"
        hover
        item-value="key"
        @click:row="(_: Event, row: { item: CustomerRow }) => openHistory(row.item)"
      >
        <template #item.lastVisit="{ item }">
          {{ formatDate(item.lastVisit) }}
        </template>

        <template #item.totalVisits="{ item }">
          <v-chip size="x-small" label color="primary" variant="tonal">
            {{ item.totalVisits }}
          </v-chip>
        </template>
      </v-data-table>
    </v-card>

  </v-container>

  <!-- ── CustomerHistoryDialog ──────────────────────────────────────────────── -->
  <v-dialog v-model="historyDialog" max-width="500" scrollable>
    <v-card v-if="selectedCustomer">
      <v-toolbar color="primary" density="compact">
        <v-toolbar-title>{{ selectedCustomer.customerName }}</v-toolbar-title>
        <v-btn icon="mdi-close" @click="historyDialog = false" />
      </v-toolbar>

      <v-card-text>
        <!-- Customer meta -->
        <div class="d-flex ga-2 flex-wrap mb-4">
          <v-chip size="small" prepend-icon="mdi-card-account-phone" label>
            {{ selectedCustomer.phoneNumber }}
          </v-chip>
          <v-chip size="small" prepend-icon="mdi-car" label>
            {{ selectedCustomer.licensePlate || '—' }}
          </v-chip>
          <v-chip v-if="selectedCustomer.model" size="small" label variant="outlined">
            {{ selectedCustomer.model }}
          </v-chip>
        </div>

        <div class="text-caption text-medium-emphasis mb-3">
          {{ selectedCustomer.totalVisits }} lần đến dịch vụ
        </div>

        <!-- Visit timeline -->
        <v-timeline density="compact" align="start" truncate-line="both">
          <v-timeline-item
            v-for="(ticket, i) in selectedCustomer.tickets"
            :key="i"
            dot-color="primary"
            size="x-small"
          >
            <div class="d-flex align-start justify-space-between flex-wrap ga-1">
              <div>
                <div class="text-body-2 font-weight-bold">
                  {{ formatDate(ticket.checkInAt) }}
                </div>
                <div v-if="ticket.department" class="text-caption text-medium-emphasis">
                  {{ ticket.department }}
                </div>
              </div>
              <v-chip
                v-if="ticket.kanbanStage"
                size="x-small"
                label
                variant="outlined"
              >
                {{ ticket.kanbanStage }}
              </v-chip>
            </div>
          </v-timeline-item>
        </v-timeline>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import AppLayout from '../Layouts/AppLayout.vue'

defineOptions({ layout: AppLayout })

// ── Types ─────────────────────────────────────────────────────────────────────

interface CustomerTicket {
  customerName: string
  licensePlate: string
  phoneNumber: string
  model: string
  checkInAt: string
  createdAt: string
  department?: string
  kanbanStage?: string
}

interface CustomerRow {
  key: string
  customerName: string
  phoneNumber: string
  licensePlate: string
  model: string
  lastVisit: string
  totalVisits: number
  tickets: CustomerTicket[]
}

const props = defineProps<{ tickets: CustomerTicket[] }>()

// ── Group into unique customer rows ───────────────────────────────────────────

const customerRows = computed<CustomerRow[]>(() => {
  const map = new Map<string, CustomerRow>()

  for (const t of props.tickets) {
    // Key by license plate if present, otherwise by phone+name
    const key = t.licensePlate?.trim()
      || `${t.phoneNumber?.trim()}__${t.customerName?.trim()}`

    if (!map.has(key)) {
      map.set(key, {
        key,
        customerName: t.customerName,
        phoneNumber:  t.phoneNumber,
        licensePlate: t.licensePlate,
        model:        t.model,
        lastVisit:    t.checkInAt,
        totalVisits:  0,
        tickets:      [],
      })
    }

    const row = map.get(key)!
    row.totalVisits++
    row.tickets.push(t)
    if (t.checkInAt > row.lastVisit) {
      row.lastVisit = t.checkInAt
    }
  }

  // Sort each customer's tickets newest-first
  for (const row of map.values()) {
    row.tickets.sort((a, b) => b.checkInAt.localeCompare(a.checkInAt))
  }

  // Sort rows by most recent visit
  return [...map.values()].sort((a, b) => b.lastVisit.localeCompare(a.lastVisit))
})

// ── Search filter ─────────────────────────────────────────────────────────────

const search = ref('')

const filteredCustomers = computed<CustomerRow[]>(() => {
  const q = search.value.trim().toLowerCase()
  if (!q) return customerRows.value
  return customerRows.value.filter(c =>
    c.customerName.toLowerCase().includes(q) ||
    c.licensePlate.toLowerCase().includes(q) ||
    c.phoneNumber.includes(q)
  )
})

// ── Table headers ─────────────────────────────────────────────────────────────

const headers = [
  { title: 'Tên khách hàng', key: 'customerName' },
  { title: 'SĐT',            key: 'phoneNumber' },
  { title: 'Biển số',        key: 'licensePlate' },
  { title: 'Model',          key: 'model' },
  { title: 'Lần gần nhất',   key: 'lastVisit' },
  { title: 'Tổng số lần',    key: 'totalVisits', align: 'center' as const },
]

// ── History dialog ────────────────────────────────────────────────────────────

const historyDialog    = ref(false)
const selectedCustomer = ref<CustomerRow | null>(null)

function openHistory(customer: CustomerRow) {
  selectedCustomer.value = customer
  historyDialog.value    = true
}

// ── Helpers ───────────────────────────────────────────────────────────────────

function formatDate(d: string): string {
  return new Date(d).toLocaleString('vi-VN', { dateStyle: 'short', timeStyle: 'short' })
}
</script>
