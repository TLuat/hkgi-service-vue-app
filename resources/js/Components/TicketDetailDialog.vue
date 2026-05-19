<template>
  <v-dialog v-model="model" max-width="800" scrollable persistent>
    <v-card v-if="ticket">
      <v-toolbar color="primary" density="compact">
        <v-toolbar-title>{{ ticket.licensePlate }} — {{ ticket.customerName }}</v-toolbar-title>
        <v-spacer />
        <v-btn :icon="editMode ? 'mdi-eye' : 'mdi-pencil'" @click="toggleEdit" />
        <v-btn icon="mdi-close" @click="close" />
      </v-toolbar>

      <v-card-text>

        <!-- Vehicle alert -->
        <VehicleAlertBanner v-if="ticket.vehicleAlert" :alert="ticket.vehicleAlert" class="mb-3" />

        <!-- Stage navigation -->
        <div v-if="canMove" class="d-flex align-center ga-2 mb-4">
          <v-btn
            variant="outlined"
            size="small"
            prepend-icon="mdi-chevron-left"
            :disabled="!prevStage"
            @click="moveStage(prevStage!)"
          >
            {{ prevStage ?? '—' }}
          </v-btn>
          <v-chip label size="small" color="primary" class="mx-auto">
            {{ ticket.kanbanStage }}
          </v-chip>
          <v-btn
            variant="outlined"
            size="small"
            append-icon="mdi-chevron-right"
            :disabled="!nextStage"
            @click="moveStage(nextStage!)"
          >
            {{ nextStage ?? '—' }}
          </v-btn>
        </div>
        <div v-else class="d-flex justify-center mb-4">
          <v-chip label size="small" color="primary">{{ ticket.kanbanStage }}</v-chip>
        </div>

        <!-- ── VIEW MODE ──────────────────────────────────────────────────────── -->
        <template v-if="!editMode">
          <v-row dense>
            <v-col cols="12" sm="6">
              <div class="text-caption text-medium-emphasis">Phòng ban</div>
              <div class="text-body-2">{{ ticket.department }}</div>
            </v-col>
            <v-col cols="12" sm="6">
              <div class="text-caption text-medium-emphasis">Nguồn tiếp nhận</div>
              <div class="text-body-2">{{ ticket.source || '—' }}</div>
            </v-col>

            <v-col cols="12" sm="6">
              <div class="text-caption text-medium-emphasis">Khách hàng</div>
              <div class="text-body-2">{{ ticket.customerName }}</div>
            </v-col>
            <v-col cols="12" sm="6">
              <div class="text-caption text-medium-emphasis">Số điện thoại</div>
              <div class="text-body-2">{{ ticket.phoneNumber }}</div>
            </v-col>

            <v-col cols="12" sm="6">
              <div class="text-caption text-medium-emphasis">Biển số</div>
              <div class="text-body-2">{{ ticket.licensePlate }}</div>
            </v-col>
            <v-col cols="12" sm="6">
              <div class="text-caption text-medium-emphasis">Dòng xe</div>
              <div class="text-body-2">{{ ticket.model }}</div>
            </v-col>

            <v-col cols="12" sm="6">
              <div class="text-caption text-medium-emphasis">Cố vấn dịch vụ</div>
              <div class="text-body-2">{{ ticket.advisor || '—' }}</div>
            </v-col>
            <v-col cols="12" sm="6">
              <div class="text-caption text-medium-emphasis">Điều phối</div>
              <div class="text-body-2">{{ ticket.dispatcher || '—' }}</div>
            </v-col>

            <v-col cols="12" sm="6">
              <div class="text-caption text-medium-emphasis">Kỹ thuật viên</div>
              <div class="text-body-2">{{ ticket.technician || '—' }}</div>
            </v-col>
            <v-col cols="12" sm="6">
              <div class="text-caption text-medium-emphasis">Bay / Vị trí</div>
              <div class="text-body-2">{{ ticket.bayId || '—' }}</div>
            </v-col>

            <v-col cols="12" sm="6">
              <div class="text-caption text-medium-emphasis">Ưu tiên</div>
              <v-chip size="x-small" :color="PRIORITY_COLORS[ticket.priority]" label>
                {{ ticket.priority }}
              </v-chip>
            </v-col>
            <v-col cols="12" sm="6">
              <div class="text-caption text-medium-emphasis">Hạn đăng kiểm</div>
              <div class="text-body-2">{{ ticket.inspectionDueDate || '—' }}</div>
            </v-col>

            <v-col cols="12" sm="6">
              <div class="text-caption text-medium-emphasis">Giờ vào</div>
              <div class="text-body-2">{{ formatDate(ticket.checkInAt) }}</div>
            </v-col>
            <v-col cols="12" sm="6">
              <div class="text-caption text-medium-emphasis">Hẹn xong</div>
              <div class="text-body-2" :class="isOverdue ? 'text-error font-weight-bold' : ''">
                {{ ticket.dueAt ? formatDate(ticket.dueAt) : '—' }}
              </div>
            </v-col>

            <!-- Service flags -->
            <v-col cols="12">
              <div class="d-flex flex-wrap ga-2 mt-1">
                <v-chip v-if="ticket.combineMaintenance" size="x-small" label color="teal" variant="tonal">Bảo dưỡng</v-chip>
                <v-chip v-if="ticket.combinePaint"       size="x-small" label color="indigo" variant="tonal">Đồng sơn</v-chip>
                <v-chip v-if="ticket.hasWash"            size="x-small" label color="cyan" variant="tonal">Rửa xe</v-chip>
                <v-chip v-if="ticket.insurance"          size="x-small" label color="orange" variant="tonal">Bảo hiểm</v-chip>
                <v-chip v-if="ticket.waitingParts"       size="x-small" label color="red" variant="tonal">Chờ PT</v-chip>
              </div>
            </v-col>

            <v-col cols="12">
              <div class="text-caption text-medium-emphasis">Yêu cầu khách hàng</div>
              <div class="text-body-2 text-pre-wrap">{{ ticket.concern || '—' }}</div>
            </v-col>
            <v-col v-if="ticket.note" cols="12">
              <div class="text-caption text-medium-emphasis">Ghi chú nội bộ</div>
              <div class="text-body-2 text-pre-wrap">{{ ticket.note }}</div>
            </v-col>
            <v-col v-if="ticket.pauseReason" cols="12">
              <div class="text-caption text-medium-emphasis">Lý do tạm dừng</div>
              <div class="text-body-2">{{ ticket.pauseReason }}</div>
            </v-col>
            <v-col v-if="ticket.partsReason" cols="12">
              <div class="text-caption text-medium-emphasis">Lý do chờ phụ tùng</div>
              <div class="text-body-2">{{ ticket.partsReason }}</div>
            </v-col>

            <v-col cols="12">
              <v-divider class="my-2" />
              <div class="text-caption text-medium-emphasis">Tạo lúc: {{ formatDate(ticket.createdAt) }}</div>
            </v-col>
          </v-row>
        </template>

        <!-- ── EDIT MODE ──────────────────────────────────────────────────────── -->
        <template v-else>
          <v-row dense>
            <v-col cols="12" sm="6">
              <v-autocomplete
                v-model="form.advisor"
                :items="advisorNames"
                label="Cố vấn dịch vụ"
                density="compact"
                clearable
              />
            </v-col>
            <v-col cols="12" sm="6">
              <v-text-field
                v-model="form.dispatcher"
                label="Điều phối"
                density="compact"
              />
            </v-col>

            <v-col cols="12" sm="6">
              <v-autocomplete
                v-model="form.technician"
                :items="technicianNames"
                label="Kỹ thuật viên"
                density="compact"
                clearable
                @update:model-value="onTechnicianChange"
              />
            </v-col>
            <v-col cols="12" sm="6">
              <v-text-field
                v-model="form.bay_id"
                label="Bay / Vị trí"
                density="compact"
              />
            </v-col>

            <!-- Priority -->
            <v-col cols="12">
              <div class="text-caption text-medium-emphasis mb-1">Ưu tiên</div>
              <v-btn-toggle
                v-model="form.priority"
                mandatory
                density="compact"
                variant="outlined"
                divided
              >
                <v-btn value="Thấp"       size="small">Thấp</v-btn>
                <v-btn value="Trung bình" size="small" color="blue">Trung bình</v-btn>
                <v-btn value="Cao"        size="small" color="orange">Cao</v-btn>
                <v-btn value="Khẩn"       size="small" color="red">Khẩn</v-btn>
              </v-btn-toggle>
            </v-col>

            <v-col cols="12" sm="6">
              <v-text-field
                v-model="form.due_at"
                label="Hẹn xong"
                type="datetime-local"
                density="compact"
              />
            </v-col>
            <v-col cols="12" sm="6">
              <v-text-field
                v-model="form.inspection_due_date"
                label="Hạn đăng kiểm"
                type="date"
                density="compact"
              />
            </v-col>

            <v-col cols="12">
              <v-textarea
                v-model="form.concern"
                label="Yêu cầu khách hàng"
                rows="2"
                density="compact"
              />
            </v-col>
            <v-col cols="12">
              <v-textarea
                v-model="form.note"
                label="Ghi chú nội bộ"
                rows="2"
                density="compact"
              />
            </v-col>
            <v-col cols="12" sm="6">
              <v-textarea
                v-model="form.pause_reason"
                label="Lý do tạm dừng"
                rows="2"
                density="compact"
              />
            </v-col>
            <v-col cols="12" sm="6">
              <v-textarea
                v-model="form.parts_reason"
                label="Lý do chờ phụ tùng"
                rows="2"
                density="compact"
              />
            </v-col>

            <v-col cols="12">
              <div class="d-flex flex-wrap ga-3">
                <v-checkbox v-model="form.insurance"     label="Bảo hiểm" density="compact" hide-details />
                <v-checkbox v-model="form.waiting_parts" label="Chờ PT"   density="compact" hide-details />
              </div>
            </v-col>
          </v-row>
        </template>

      </v-card-text>

      <v-card-actions>
        <v-btn
          v-if="canDelete"
          color="error"
          variant="text"
          @click="deleteTicket"
        >
          Xóa phiếu
        </v-btn>
        <v-spacer />
        <v-btn variant="text" @click="close">Đóng</v-btn>
        <v-btn
          v-if="editMode"
          color="primary"
          :loading="form.processing"
          @click="submit"
        >
          Lưu
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { router, useForm, usePage } from '@inertiajs/vue3'
import VehicleAlertBanner from './VehicleAlertBanner.vue'
import { KANBAN_STAGES, PRIORITY_COLORS, canMoveKanban } from '../utils/roles'
import { useConfirm } from '../composables/useConfirm'
import type { ServiceTicket, KanbanStage, TicketPriority, AppUser, SharedProps } from '../types'

const props = defineProps<{
  ticket:          ServiceTicket | null
  advisorUsers?:   AppUser[]
  technicianUsers?: AppUser[]
}>()

const model   = defineModel<boolean>({ default: false })
const page    = usePage<SharedProps>()
const { confirm } = useConfirm()

// ── Auth ──────────────────────────────────────────────────────────────────────

const authRole = computed(() => page.props.auth.user?.role)
const canMove  = computed(() => authRole.value ? canMoveKanban(authRole.value) : false)
const canDelete = computed(() => authRole.value === 'owner' || authRole.value === 'admin')

// ── Stage navigation ──────────────────────────────────────────────────────────

const stageIdx  = computed(() => props.ticket ? KANBAN_STAGES.indexOf(props.ticket.kanbanStage) : -1)
const prevStage = computed<KanbanStage | null>(() => stageIdx.value > 0 ? KANBAN_STAGES[stageIdx.value - 1] : null)
const nextStage = computed<KanbanStage | null>(() =>
  stageIdx.value >= 0 && stageIdx.value < KANBAN_STAGES.length - 1
    ? KANBAN_STAGES[stageIdx.value + 1]
    : null
)

function moveStage(stage: KanbanStage) {
  if (!props.ticket) return
  router.patch(`/tickets/${props.ticket.id}/stage`, { kanban_stage: stage }, { preserveScroll: true })
}

// ── User lists ────────────────────────────────────────────────────────────────

const advisorNames = computed(() =>
  (props.advisorUsers ?? []).filter(u => u.is_active !== false).map(u => u.name)
)

const technicianNames = computed(() =>
  (props.technicianUsers ?? []).filter(u => u.is_active !== false).map(u => u.name)
)

// ── Edit mode ─────────────────────────────────────────────────────────────────

const editMode = ref(false)

function toggleEdit() {
  if (editMode.value) {
    editMode.value = false
    form.reset()
  } else {
    editMode.value = true
  }
}

// ── Form ──────────────────────────────────────────────────────────────────────

const form = useForm({
  advisor:             '',
  dispatcher:          '',
  technician:          '',
  technician_user_id:  '',
  bay_id:              '',
  priority:            'Trung bình' as TicketPriority,
  due_at:              '',
  inspection_due_date: '',
  concern:             '',
  note:                '',
  pause_reason:        '',
  parts_reason:        '',
  insurance:           false,
  waiting_parts:       false,
})

function fillForm(t: ServiceTicket) {
  form.advisor             = t.advisor ?? ''
  form.dispatcher          = t.dispatcher ?? ''
  form.technician          = t.technician ?? ''
  form.technician_user_id  = t.technicianUserId ?? ''
  form.bay_id              = t.bayId ?? ''
  form.priority            = t.priority
  form.due_at              = t.dueAt?.slice(0, 16) ?? ''
  form.inspection_due_date = t.inspectionDueDate ?? ''
  form.concern             = t.concern ?? ''
  form.note                = t.note ?? ''
  form.pause_reason        = t.pauseReason ?? ''
  form.parts_reason        = t.partsReason ?? ''
  form.insurance           = t.insurance
  form.waiting_parts       = t.waitingParts
}

watch(() => props.ticket, (t) => {
  if (t) fillForm(t)
}, { immediate: true })

// Re-fill on each open so form reflects latest server state
watch(model, (open) => {
  if (open && props.ticket) {
    editMode.value = false
    fillForm(props.ticket)
    form.clearErrors()
  }
})

function onTechnicianChange(name: string | null) {
  if (!name) { form.technician_user_id = ''; return }
  const user = (props.technicianUsers ?? []).find(u => u.name === name)
  form.technician_user_id = user?.id ?? ''
}

function submit() {
  if (!props.ticket) return
  form.patch(`/tickets/${props.ticket.id}/plan`, {
    preserveScroll: true,
    onSuccess: () => {
      editMode.value = false
    },
  })
}

// ── Delete ────────────────────────────────────────────────────────────────────

async function deleteTicket() {
  if (!props.ticket) return
  const ok = await confirm(`Xóa phiếu "${props.ticket.licensePlate} — ${props.ticket.customerName}"?`)
  if (!ok) return
  router.delete(`/tickets/${props.ticket.id}`, {
    onSuccess: () => { model.value = false },
  })
}

// ── Helpers ───────────────────────────────────────────────────────────────────

function close() {
  model.value = false
}

const isOverdue = computed(() => {
  if (!props.ticket?.dueAt) return false
  return new Date(props.ticket.dueAt) < new Date()
})

function formatDate(d: string): string {
  return new Date(d).toLocaleString('vi-VN', { dateStyle: 'short', timeStyle: 'short' })
}
</script>
