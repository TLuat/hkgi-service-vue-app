<template>
  <div>
    <Head title="Tổng quan" />

    <!-- ── Tab bar ─────────────────────────────────────────────────────────── -->
    <v-tabs
      v-model="activeTab"
      bg-color="surface"
      color="primary"
      border="b"
      class="px-1"
    >
      <v-tab
        v-for="tab in visibleTabs"
        :key="tab.key"
        :value="tab.key"
        :prepend-icon="tab.icon"
        min-width="0"
      >
        {{ tab.label }}
      </v-tab>
    </v-tabs>

    <!-- ── Tab windows ─────────────────────────────────────────────────────── -->
    <v-tabs-window v-model="activeTab">

      <!-- Tổng quan -->
      <v-tabs-window-item value="dashboard">
        <v-container fluid class="pa-4">
          <v-row dense class="mb-4">
            <v-col v-for="card in summaryCards" :key="card.label" cols="6" sm="4" md="2">
              <SummaryCard v-bind="card" />
            </v-col>
          </v-row>

          <div class="d-flex align-center ga-2 mb-3 flex-wrap">
            <v-text-field
              v-model="search"
              prepend-inner-icon="mdi-magnify"
              label="Tìm phiếu..."
              density="compact"
              clearable
              hide-details
              style="max-width:280px;"
            />
            <v-select
              v-model="filterStage"
              :items="['Tất cả', ...KANBAN_STAGES]"
              label="Giai đoạn"
              density="compact"
              hide-details
              style="max-width:200px;"
            />
            <v-spacer />
            <v-btn
              v-if="canCreateTicketForUser"
              color="primary"
              prepend-icon="mdi-plus"
              @click="createDialog = true"
            >
              Tạo phiếu mới
            </v-btn>
          </div>

          <v-card rounded="lg" :border="true">
            <v-data-table
              :items="filteredTickets"
              :headers="ticketHeaders"
              density="compact"
              item-value="id"
              hover
              @click:row="onTicketRowClick"
            >
              <template #item.kanbanStage="{ item }">
                <v-chip size="x-small" label>{{ item.kanbanStage }}</v-chip>
              </template>
              <template #item.priority="{ item }">
                <v-chip :color="PRIORITY_COLORS[item.priority]" size="x-small" label>{{ item.priority }}</v-chip>
              </template>
              <template #item.checkInAt="{ item }">{{ formatDate(item.checkInAt) }}</template>
              <template #item.dueAt="{ item }">{{ item.dueAt ? formatDate(item.dueAt) : '—' }}</template>
            </v-data-table>
          </v-card>
        </v-container>
      </v-tabs-window-item>

      <!-- Tiếp nhận -->
      <v-tabs-window-item value="intake">
        <PendingIntakeBoard
          :pending-intakes="pendingIntakes"
          :vehicle-models="vehicleModels"
          :advisor-users="accountUsers"
        />
      </v-tabs-window-item>

      <!-- Kanban -->
      <v-tabs-window-item value="kanban">
        <KanbanBoard :tickets="tickets" @open-detail="openDetail" />
      </v-tabs-window-item>

      <!-- Bộ phận -->
      <v-tabs-window-item value="departments">
        <v-container fluid class="pa-4">
          <v-row class="mb-4">
            <v-col v-for="dept in DEPARTMENTS" :key="dept" cols="12" sm="6" md="3">
              <v-card rounded="lg" :border="true" class="pa-3">
                <div class="text-subtitle-1 font-weight-bold mb-1">{{ dept }}</div>
                <div class="text-h5 font-weight-bold">{{ countByDept(dept) }}</div>
                <div class="text-body-2 text-medium-emphasis">phiếu đang mở</div>
              </v-card>
            </v-col>
          </v-row>
          <v-card rounded="lg" :border="true">
            <v-data-table
              :items="tickets"
              :headers="deptHeaders"
              density="compact"
              hover
              @click:row="onTicketRowClick"
            >
              <template #item.priority="{ item }">
                <v-chip :color="PRIORITY_COLORS[item.priority]" size="x-small" label>{{ item.priority }}</v-chip>
              </template>
              <template #item.kanbanStage="{ item }">
                <v-chip size="x-small" label>{{ item.kanbanStage }}</v-chip>
              </template>
            </v-data-table>
          </v-card>
        </v-container>
      </v-tabs-window-item>

      <!-- Khách hàng -->
      <v-tabs-window-item value="customers">
        <v-container fluid class="pa-4">
          <v-text-field
            v-model="customerSearch"
            prepend-inner-icon="mdi-magnify"
            label="Tìm theo biển số, tên, SĐT..."
            density="compact"
            clearable
            hide-details
            class="mb-4"
            style="max-width:400px;"
          />
          <v-card rounded="lg" :border="true">
            <v-data-table
              :items="filteredCustomers"
              :headers="customerHeaders"
              density="compact"
              item-value="id"
              hover
              @click:row="onTicketRowClick"
            >
              <template #item.vehicleAlert="{ item }">
                <v-icon v-if="item.vehicleAlert" color="warning" size="small">mdi-alert</v-icon>
              </template>
              <template #item.priority="{ item }">
                <v-chip :color="PRIORITY_COLORS[item.priority]" size="x-small" label>{{ item.priority }}</v-chip>
              </template>
              <template #item.checkInAt="{ item }">{{ formatDate(item.checkInAt) }}</template>
            </v-data-table>
          </v-card>
        </v-container>
      </v-tabs-window-item>

      <!-- Tài khoản -->
      <v-tabs-window-item value="accounts">
        <AccountsPanel :users="accountUsers" />
      </v-tabs-window-item>

    </v-tabs-window>

    <!-- ── Shared dialogs ──────────────────────────────────────────────────── -->
    <TicketDetailDialog v-model="detailDialog" :ticket="selectedTicket" @delete="deleteTicket" />
    <CreateTicketDialog
      v-model="createDialog"
      :vehicle-models="vehicleModels"
      :advisor-users="accountUsers"
      :technician-users="accountUsers"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import { useStore } from 'vuex'
import AppLayout from '../Layouts/AppLayout.vue'
import SummaryCard from '../Components/SummaryCard.vue'
import TicketDetailDialog from '../Components/TicketDetailDialog.vue'
import CreateTicketDialog from '../Components/CreateTicketDialog.vue'
import PendingIntakeBoard from '../Components/PendingIntakeBoard.vue'
import KanbanBoard from '../Components/KanbanBoard.vue'
import AccountsPanel from '../Components/AccountsPanel.vue'
import {
  KANBAN_STAGES, canCreateTicket, availableSectionsForRole,
  PRIORITY_COLORS, DEPARTMENTS,
} from '../utils/roles'
import { useConfirm } from '../composables/useConfirm'
import type { RootState } from '../store'
import type {
  ServiceTicket, DashboardSummary, VehicleAlertData, PendingIntake,
  AppUser, AppSettings, ActivityLogEntry, DepartmentName,
} from '../types'
import type { SharedProps } from '../types'

defineOptions({ layout: AppLayout })

const page    = usePage<SharedProps>()
const store   = useStore<RootState>()
const confirm = useConfirm()

const props = defineProps<{
  tickets: ServiceTicket[]
  summary: DashboardSummary
  pendingIntakes: PendingIntake[]
  vehicleAlerts: Record<string, VehicleAlertData>
  activityLogs: ActivityLogEntry[]
  vehicleModels: string[]
  accountUsers: AppUser[]
  settings: AppSettings | null
}>()

onMounted(() => store.dispatch('vehicleAlerts/initFromPageProps', props.vehicleAlerts))

// ── Tabs ──────────────────────────────────────────────────────────────────────

const TAB_DEFS = [
  { key: 'dashboard',   label: 'Tổng quan',  icon: 'mdi-view-dashboard'  },
  { key: 'intake',      label: 'Tiếp nhận',  icon: 'mdi-car-arrow-right' },
  { key: 'kanban',      label: 'Kanban',     icon: 'mdi-view-column'     },
  { key: 'departments', label: 'Bộ phận',    icon: 'mdi-garage'          },
  { key: 'customers',   label: 'Khách hàng', icon: 'mdi-account-group'   },
  { key: 'accounts',    label: 'Tài khoản',  icon: 'mdi-account-cog'     },
] as const

const activeTab = ref('dashboard')

const visibleTabs = computed(() => {
  const user = page.props.auth.user
  if (!user) return []
  const sections = availableSectionsForRole(user.role, user.sections)
  return TAB_DEFS.filter(t => (sections as string[]).includes(t.key))
})

watch(visibleTabs, (tabs) => {
  if (tabs.length && !tabs.find(t => t.key === activeTab.value)) {
    activeTab.value = tabs[0].key
  }
}, { immediate: true })

// ── Dashboard tab ─────────────────────────────────────────────────────────────

const search         = ref('')
const filterStage    = ref('Tất cả')
const detailDialog   = ref(false)
const createDialog   = ref(false)
const selectedTicket = ref<ServiceTicket | null>(null)

const canCreateTicketForUser = computed(() => {
  const user = page.props.auth.user
  return user ? canCreateTicket(user.role) : false
})

const filteredTickets = computed(() => {
  let list = props.tickets
  if (filterStage.value !== 'Tất cả') {
    list = list.filter(t => t.kanbanStage === filterStage.value)
  }
  if (search.value) {
    const q = search.value.toLowerCase()
    list = list.filter(t =>
      t.licensePlate.toLowerCase().includes(q) ||
      t.customerName.toLowerCase().includes(q)
    )
  }
  return list
})

const ticketHeaders = [
  { title: 'Biển số',    key: 'licensePlate' },
  { title: 'Khách hàng', key: 'customerName' },
  { title: 'Bộ phận',    key: 'department'   },
  { title: 'Cố vấn',     key: 'advisor'      },
  { title: 'Giai đoạn',  key: 'kanbanStage'  },
  { title: 'Ưu tiên',    key: 'priority'     },
  { title: 'Ngày vào',   key: 'checkInAt'    },
  { title: 'Hạn xong',   key: 'dueAt'        },
]

const summaryCards = computed(() => [
  { label: 'Đang mở',        value: props.summary.openCount,       icon: 'mdi-car',             color: 'blue'   },
  { label: 'Chờ điều phối',  value: props.summary.waitingDispatch, icon: 'mdi-account-clock',   color: 'orange' },
  { label: 'Đang thực hiện', value: props.summary.inProgress,      icon: 'mdi-wrench',          color: 'green'  },
  { label: 'Chờ phụ tùng',   value: props.summary.waitingParts,    icon: 'mdi-package',         color: 'purple' },
  { label: 'Chờ giao xe',    value: props.summary.handoffReady,    icon: 'mdi-car-arrow-right', color: 'teal'   },
  { label: 'Khẩn',           value: props.summary.urgentCases,     icon: 'mdi-alert',           color: 'red'    },
])

function openDetail(ticket: ServiceTicket) {
  selectedTicket.value = ticket
  detailDialog.value   = true
}

function onTicketRowClick(_: Event, row: { item: ServiceTicket }) {
  openDetail(row.item)
}

async function deleteTicket(ticket: ServiceTicket) {
  const ok = await confirm.confirm(`Xóa phiếu ${ticket.licensePlate}? Thao tác không thể hoàn tác.`)
  if (!ok) return
  router.delete(`/tickets/${ticket.id}`)
  detailDialog.value = false
}

// ── Departments tab ───────────────────────────────────────────────────────────

const deptHeaders = [
  { title: 'Biển số',    key: 'licensePlate' },
  { title: 'Khách hàng', key: 'customerName' },
  { title: 'Bộ phận',    key: 'department'   },
  { title: 'Giai đoạn',  key: 'kanbanStage'  },
  { title: 'Ưu tiên',    key: 'priority'     },
  { title: 'Cố vấn',     key: 'advisor'      },
]

function countByDept(dept: DepartmentName): number {
  return props.tickets.filter(t => t.department === dept && t.kanbanStage !== 'Chờ giao xe').length
}

// ── Customers tab ─────────────────────────────────────────────────────────────

const customerSearch = ref('')

const filteredCustomers = computed(() => {
  if (!customerSearch.value) return props.tickets
  const q = customerSearch.value.toLowerCase()
  return props.tickets.filter(t =>
    t.licensePlate.toLowerCase().includes(q) ||
    t.customerName.toLowerCase().includes(q) ||
    t.phoneNumber.includes(q)
  )
})

const customerHeaders = [
  { title: 'Biển số',    key: 'licensePlate' },
  { title: 'Khách hàng', key: 'customerName' },
  { title: 'SĐT',        key: 'phoneNumber'  },
  { title: 'Xe',         key: 'model'        },
  { title: 'Bộ phận',    key: 'department'   },
  { title: 'Ưu tiên',    key: 'priority'     },
  { title: 'Vào lúc',    key: 'checkInAt'    },
  { title: 'Cảnh báo',   key: 'vehicleAlert', sortable: false },
]

function formatDate(d: string): string {
  return new Date(d).toLocaleString('vi-VN', { dateStyle: 'short', timeStyle: 'short' })
}
</script>