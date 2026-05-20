<template>
  <v-container fluid class="pa-4">
    <v-row>

      <!-- ── Left: queue list ──────────────────────────────────────────────── -->
      <v-col cols="12" md="5">

        <div class="d-flex align-center mb-3">
          <span class="text-subtitle-1 font-weight-bold">Hàng chờ tiếp nhận</span>
          <v-spacer />
          <v-btn
            color="primary"
            icon="mdi-plus"
            size="small"
            variant="tonal"
            title="Thêm xe chờ"
            @click="openAddDialog"
          />
        </div>

        <!-- Empty state -->
        <div
          v-if="pendingIntakes.length === 0"
          class="text-center text-medium-emphasis py-8"
        >
          Hàng chờ trống
        </div>

        <!-- Queue list -->
        <v-list v-else density="compact" rounded="lg">
          <v-list-item
            v-for="intake in pendingIntakes"
            :key="intake.id"
            :active="selectedIntakeId === intake.id"
            color="primary"
            rounded="lg"
            class="mb-1 border"
            @click="selectedIntakeId = intake.id"
          >
            <template #title>
              <span class="font-weight-bold">{{ intake.customerName }}</span>
            </template>

            <template #subtitle>
              {{ intake.phoneNumber }}
              <span v-if="intake.licensePlate"> · {{ intake.licensePlate }}</span>
            </template>

            <template #append>
              <div class="d-flex flex-column align-end ga-1">
                <v-chip size="x-small" :color="statusColor(intake.status)" label>
                  {{ intake.status }}
                </v-chip>
                <span class="text-caption text-medium-emphasis">
                  {{ relativeTime(intake.arrivedAt) }}
                </span>
              </div>
            </template>
          </v-list-item>
        </v-list>

      </v-col>

      <!-- ── Right: detail panel ───────────────────────────────────────────── -->
      <v-col cols="12" md="7">

        <!-- Placeholder -->
        <div
          v-if="!selectedIntake"
          class="d-flex flex-column align-center justify-center pa-12 text-center"
        >
          <v-icon size="52" color="grey-lighten-1" class="mb-3">mdi-car-search</v-icon>
          <span class="text-medium-emphasis">Chọn xe chờ để xem chi tiết</span>
        </div>

        <!-- Detail card -->
        <v-card v-else rounded="lg" :border="true">
          <v-card-title class="d-flex align-center gap-2 pt-4 px-4 flex-wrap">
            <span class="text-subtitle-1 font-weight-bold">{{ selectedIntake.customerName }}</span>
            <v-chip size="x-small" :color="statusColor(selectedIntake.status)" label>
              {{ selectedIntake.status }}
            </v-chip>
          </v-card-title>

          <v-card-text>
            <VehicleAlertBanner
              v-if="selectedIntake.licensePlate"
              :plate="selectedIntake.licensePlate"
            />

            <v-row dense>
              <v-col cols="6">
                <div class="text-caption text-medium-emphasis">Số điện thoại</div>
                <div class="text-body-2">{{ selectedIntake.phoneNumber }}</div>
              </v-col>
              <v-col cols="6">
                <div class="text-caption text-medium-emphasis">Biển số</div>
                <div class="text-body-2">{{ selectedIntake.licensePlate || '—' }}</div>
              </v-col>
              <v-col cols="6">
                <div class="text-caption text-medium-emphasis">Dòng xe</div>
                <div class="text-body-2">{{ selectedIntake.model || '—' }}</div>
              </v-col>
              <v-col cols="6">
                <div class="text-caption text-medium-emphasis">Giờ đến</div>
                <div class="text-body-2">{{ formatDateTime(selectedIntake.arrivedAt) }}</div>
              </v-col>
              <v-col v-if="selectedIntake.isAppointment && selectedIntake.appointmentAt" cols="6">
                <div class="text-caption text-medium-emphasis">Giờ hẹn</div>
                <div class="text-body-2">{{ formatDateTime(selectedIntake.appointmentAt) }}</div>
              </v-col>
              <v-col v-if="selectedIntake.assignedAdvisor" cols="6">
                <div class="text-caption text-medium-emphasis">Cố vấn được giao</div>
                <div class="text-body-2">{{ selectedIntake.assignedAdvisor }}</div>
              </v-col>
              <v-col v-if="selectedIntake.inspectionDueDate" cols="6">
                <div class="text-caption text-medium-emphasis">Hạn đăng kiểm</div>
                <div class="text-body-2">{{ selectedIntake.inspectionDueDate }}</div>
              </v-col>
              <v-col cols="12">
                <div class="d-flex ga-1 flex-wrap mt-1">
                  <v-chip v-if="selectedIntake.isAppointment"     size="x-small" color="blue"   label>Khách hẹn</v-chip>
                  <v-chip v-if="selectedIntake.hasWash"            size="x-small" color="cyan"   label>Rửa xe</v-chip>
                  <v-chip v-if="selectedIntake.combineMaintenance" size="x-small" color="teal"   label>Bảo dưỡng</v-chip>
                  <v-chip v-if="selectedIntake.combinePaint"       size="x-small" color="purple" label>Đồng sơn</v-chip>
                </div>
              </v-col>
              <v-col v-if="selectedIntake.note" cols="12">
                <div class="text-caption text-medium-emphasis mt-1">Ghi chú</div>
                <div class="text-body-2">{{ selectedIntake.note }}</div>
              </v-col>
            </v-row>
          </v-card-text>

          <v-card-actions class="pa-4 pt-0 flex-wrap ga-2">
            <v-btn
              :color="selectedIntake.status === 'Đang được tiếp nhận' ? 'success' : undefined"
              :variant="selectedIntake.status === 'Đang được tiếp nhận' ? 'tonal' : 'outlined'"
              prepend-icon="mdi-account-clock"
              size="small"
              @click="markInProgress"
            >
              Đang tiếp nhận
            </v-btn>

            <v-btn
              color="primary"
              prepend-icon="mdi-check-circle-outline"
              size="small"
              variant="tonal"
              @click="openAcceptDialog"
            >
              Tiếp nhận → Tạo phiếu
            </v-btn>

            <v-spacer />

            <v-btn
              color="error"
              icon="mdi-delete"
              size="small"
              variant="text"
              title="Xóa xe chờ"
              @click="deleteSelected"
            />
          </v-card-actions>
        </v-card>

      </v-col>
    </v-row>
  </v-container>

  <!-- ── AddIntakeDialog ────────────────────────────────────────────────────── -->
  <v-dialog v-model="addDialog" max-width="560" persistent>
    <v-card title="Thêm xe chờ" prepend-icon="mdi-car-plus">
      <v-card-text>
        <v-row dense>
          <v-col cols="12" sm="6">
            <v-text-field
              v-model="addForm.customer_name"
              label="Tên khách hàng *"
              density="compact"
              :error-messages="addForm.errors.customer_name"
            />
          </v-col>
          <v-col cols="12" sm="6">
            <v-text-field
              v-model="addForm.phone_number"
              label="Số điện thoại *"
              density="compact"
              :error-messages="addForm.errors.phone_number"
            />
          </v-col>
          <v-col cols="12" sm="6">
            <v-text-field
              v-model="addForm.license_plate"
              label="Biển số"
              density="compact"
            />
          </v-col>
          <v-col cols="12" sm="6">
            <v-autocomplete
              v-model="addForm.model"
              :items="vehicleModels"
              label="Dòng xe"
              density="compact"
            />
          </v-col>
          <v-col cols="12" sm="6">
            <v-checkbox
              v-model="addForm.is_appointment"
              label="Khách hẹn trước"
              density="compact"
              hide-details
            />
          </v-col>
          <v-col v-if="addForm.is_appointment" cols="12" sm="6">
            <v-text-field
              v-model="addForm.appointment_at"
              label="Giờ hẹn"
              type="datetime-local"
              density="compact"
            />
          </v-col>
          <v-col cols="12" sm="6">
            <v-autocomplete
              v-model="addForm.assigned_advisor"
              :items="advisorNames"
              label="Cố vấn được giao"
              density="compact"
              clearable
            />
          </v-col>
          <v-col cols="12" class="d-flex flex-wrap ga-3">
            <v-checkbox v-model="addForm.has_wash"            label="Rửa xe"    density="compact" hide-details />
            <v-checkbox v-model="addForm.combine_maintenance" label="Bảo dưỡng" density="compact" hide-details />
            <v-checkbox v-model="addForm.combine_paint"       label="Đồng sơn"  density="compact" hide-details />
          </v-col>
          <v-col cols="12">
            <v-textarea
              v-model="addForm.note"
              label="Ghi chú"
              rows="2"
              density="compact"
            />
          </v-col>
        </v-row>
      </v-card-text>

      <v-card-actions>
        <v-spacer />
        <v-btn variant="text" @click="closeAddDialog">Hủy</v-btn>
        <v-btn color="primary" :loading="addForm.processing" @click="submitAdd">Thêm</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>

  <!-- ── AcceptIntakeDialog ─────────────────────────────────────────────────── -->
  <v-dialog v-model="acceptDialog" max-width="580" persistent>
    <v-card title="Tiếp nhận & Tạo phiếu" prepend-icon="mdi-check-circle-outline">
      <v-card-text>
        <v-row dense>
          <v-col cols="12" sm="6">
            <v-text-field
              v-model="acceptForm.customer_name"
              label="Tên khách hàng"
              density="compact"
              :error-messages="acceptForm.errors.customer_name"
            />
          </v-col>
          <v-col cols="12" sm="6">
            <v-text-field
              v-model="acceptForm.phone_number"
              label="Số điện thoại"
              density="compact"
            />
          </v-col>
          <v-col cols="12" sm="6">
            <v-text-field
              v-model="acceptForm.license_plate"
              label="Biển số"
              density="compact"
            />
          </v-col>
          <v-col cols="12" sm="6">
            <v-autocomplete
              v-model="acceptForm.model"
              :items="vehicleModels"
              label="Dòng xe"
              density="compact"
            />
          </v-col>
          <v-col cols="12" sm="6">
            <v-select
              v-model="acceptForm.department"
              :items="DEPARTMENTS"
              label="Phòng ban *"
              density="compact"
              :error-messages="acceptForm.errors.department"
            />
          </v-col>
          <v-col cols="12" sm="6">
            <v-select
              v-model="acceptForm.priority"
              :items="PRIORITIES"
              label="Ưu tiên"
              density="compact"
            />
          </v-col>
          <v-col cols="12" sm="6">
            <v-autocomplete
              v-model="acceptForm.advisor"
              :items="advisorNames"
              label="Cố vấn *"
              density="compact"
              :error-messages="acceptForm.errors.advisor"
            />
          </v-col>
          <v-col cols="12" sm="6">
            <v-text-field
              v-model="acceptForm.dispatcher"
              label="Điều phối"
              density="compact"
            />
          </v-col>
          <v-col cols="12" sm="6">
            <v-text-field
              v-model="acceptForm.check_in_at"
              label="Giờ vào *"
              type="datetime-local"
              density="compact"
              :error-messages="acceptForm.errors.check_in_at"
            />
          </v-col>
          <v-col cols="12" sm="6">
            <v-text-field
              v-model="acceptForm.due_at"
              label="Hẹn xong"
              type="datetime-local"
              density="compact"
            />
          </v-col>
          <v-col cols="12">
            <v-textarea
              v-model="acceptForm.concern"
              label="Yêu cầu khách hàng *"
              rows="2"
              density="compact"
              :error-messages="acceptForm.errors.concern"
            />
          </v-col>
        </v-row>
      </v-card-text>

      <v-card-actions>
        <v-spacer />
        <v-btn variant="text" @click="acceptDialog = false">Hủy</v-btn>
        <v-btn color="success" :loading="acceptForm.processing" @click="submitAccept">
          Tạo phiếu
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import VehicleAlertBanner from './VehicleAlertBanner.vue'
import { DEPARTMENTS } from '../utils/roles'
import { useConfirm } from '../composables/useConfirm'

function nowDateTime(): string {
  const d = new Date()
  d.setSeconds(0, 0)
  return d.toISOString().slice(0, 16)
}
import type { PendingIntake, AppUser, DepartmentName, TicketPriority } from '../types'

const PRIORITIES: TicketPriority[] = ['Thấp', 'Trung bình', 'Cao', 'Khẩn']

const props = defineProps<{
  pendingIntakes: PendingIntake[]
  vehicleModels: string[]
  advisorUsers: AppUser[]
}>()

const { confirm } = useConfirm()

const advisorNames = computed(() =>
  props.advisorUsers
    .filter(u => u.is_active !== false && ['service_advisor', 'manager', 'owner', 'admin'].includes(u.role))
    .map(u => u.name)
)

// ── Selection ─────────────────────────────────────────────────────────────────

const selectedIntakeId = ref<string | null>(null)

const selectedIntake = computed<PendingIntake | null>(
  () => props.pendingIntakes.find(i => i.id === selectedIntakeId.value) ?? null
)

// ── Helpers ───────────────────────────────────────────────────────────────────

function statusColor(status: string): string {
  if (status === 'Khách hẹn')           return 'blue'
  if (status === 'Đang được tiếp nhận') return 'green'
  return 'grey'
}

function relativeTime(dateStr: string | null): string {
  if (!dateStr) return ''
  const mins = Math.floor((Date.now() - new Date(dateStr).getTime()) / 60_000)
  if (mins < 1)  return 'Vừa đến'
  if (mins < 60) return `${mins} phút trước`
  const hours = Math.floor(mins / 60)
  if (hours < 24) return `${hours} giờ trước`
  return new Date(dateStr).toLocaleDateString('vi-VN')
}

function formatDateTime(dateStr: string | null): string {
  if (!dateStr) return '—'
  return new Date(dateStr).toLocaleString('vi-VN', { dateStyle: 'short', timeStyle: 'short' })
}

// ── Action: "Đang tiếp nhận" toggle ──────────────────────────────────────────

function markInProgress() {
  if (!selectedIntake.value) return
  router.patch(
    `/intake-queue/${selectedIntake.value.id}`,
    { status: 'Đang được tiếp nhận' },
    { preserveScroll: true },
  )
}

// ── Action: delete ────────────────────────────────────────────────────────────

async function deleteSelected() {
  if (!selectedIntake.value) return
  const ok = await confirm(`Xóa "${selectedIntake.value.customerName}" khỏi hàng chờ?`)
  if (!ok) return
  const id = selectedIntake.value.id
  router.delete(`/intake-queue/${id}`, {
    preserveScroll: true,
    onSuccess: () => { if (selectedIntakeId.value === id) selectedIntakeId.value = null },
  })
}

// ── AddIntakeDialog ───────────────────────────────────────────────────────────

const addDialog = ref(false)

const addForm = useForm({
  customer_name:       '',
  phone_number:        '',
  license_plate:       '',
  model:               '',
  is_appointment:      false,
  appointment_at:      '',
  assigned_advisor:    '',
  note:                '',
  combine_maintenance: false,
  combine_paint:       false,
  has_wash:            false,
})

function openAddDialog() {
  addForm.reset()
  addForm.clearErrors()
  addDialog.value = true
}

function closeAddDialog() {
  addDialog.value = false
  addForm.reset()
  addForm.clearErrors()
}

function submitAdd() {
  addForm.post('/intake-queue', {
    preserveScroll: true,
    onSuccess: () => {
      addDialog.value = false
      addForm.reset()
    },
  })
}

// ── AcceptIntakeDialog ────────────────────────────────────────────────────────

const acceptDialog = ref(false)

const acceptForm = useForm({
  customer_name:       '',
  phone_number:        '',
  license_plate:       '',
  model:               '',
  combine_maintenance: false,
  combine_paint:       false,
  has_wash:            false,
  department:          null as DepartmentName | null,
  advisor:             '',
  dispatcher:          '',
  priority:            'Trung bình' as TicketPriority,
  check_in_at:         '',
  due_at:              '',
  concern:             '',
})

function openAcceptDialog() {
  const intake = selectedIntake.value
  if (!intake) return
  acceptForm.customer_name       = intake.customerName
  acceptForm.phone_number        = intake.phoneNumber
  acceptForm.license_plate       = intake.licensePlate ?? ''
  acceptForm.model               = intake.model ?? ''
  acceptForm.combine_maintenance = intake.combineMaintenance
  acceptForm.combine_paint       = intake.combinePaint
  acceptForm.has_wash            = intake.hasWash
  acceptForm.department          = null
  acceptForm.advisor             = intake.assignedAdvisor ?? ''
  acceptForm.dispatcher          = ''
  acceptForm.priority            = 'Trung bình'
  acceptForm.check_in_at         = nowDateTime()
  acceptForm.due_at              = ''
  acceptForm.concern             = ''
  acceptForm.clearErrors()
  acceptDialog.value = true
}

function submitAccept() {
  if (!selectedIntake.value) return
  acceptForm.post(`/intake-queue/${selectedIntake.value.id}/accept`, {
    onSuccess: () => {
      acceptDialog.value = false
      acceptForm.reset()
      selectedIntakeId.value = null
    },
  })
}
</script>
