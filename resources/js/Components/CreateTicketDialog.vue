<template>
  <v-dialog v-model="model" max-width="720" scrollable persistent>
    <v-card title="Tạo phiếu tiếp nhận" prepend-icon="mdi-car-arrow-right">
      <v-card-text>
        <v-row dense>

          <!-- Department + Source -->
          <v-col cols="12" sm="6">
            <v-select
              v-model="form.department"
              :items="DEPARTMENTS"
              label="Phòng ban *"
              density="compact"
              :error-messages="form.errors.department"
              @update:model-value="onDeptChange"
            />
          </v-col>
          <v-col cols="12" sm="6">
            <v-select
              v-model="form.source"
              :items="SOURCES"
              label="Nguồn tiếp nhận"
              density="compact"
              clearable
            />
          </v-col>

          <!-- Customer name + Phone -->
          <v-col cols="12" sm="6">
            <v-text-field
              v-model="form.customer_name"
              label="Tên khách hàng *"
              density="compact"
              :error-messages="form.errors.customer_name"
            />
          </v-col>
          <v-col cols="12" sm="6">
            <v-text-field
              v-model="form.phone_number"
              label="Số điện thoại *"
              density="compact"
              :error-messages="form.errors.phone_number"
            />
          </v-col>

          <!-- License plate + Model -->
          <v-col cols="12" sm="6">
            <v-text-field
              v-model="form.license_plate"
              label="Biển số *"
              density="compact"
              :error-messages="form.errors.license_plate"
              @input="form.license_plate = normalizePlate(($event.target as HTMLInputElement).value)"
            />
          </v-col>
          <v-col cols="12" sm="6">
            <v-autocomplete
              v-model="form.model"
              :items="vehicleModels"
              label="Dòng xe *"
              density="compact"
              :error-messages="form.errors.model"
            />
          </v-col>

          <!-- Vehicle alert banner -->
          <v-col v-if="vehicleAlert" cols="12">
            <VehicleAlertBanner :alert="vehicleAlert" />
          </v-col>

          <!-- Advisor + Dispatcher -->
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

          <!-- Technician + Bay -->
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

          <!-- Priority (btn-toggle) -->
          <v-col cols="12">
            <div class="text-caption text-medium-emphasis mb-1">Ưu tiên *</div>
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

          <!-- Check-in + Due -->
          <v-col cols="12" sm="6">
            <v-text-field
              v-model="form.check_in_at"
              label="Giờ vào *"
              type="datetime-local"
              density="compact"
              :error-messages="form.errors.check_in_at"
            />
          </v-col>
          <v-col cols="12" sm="6">
            <v-text-field
              v-model="form.due_at"
              label="Hẹn xong"
              type="datetime-local"
              density="compact"
            />
          </v-col>

          <!-- Inspection due date -->
          <v-col cols="12" sm="6">
            <v-text-field
              v-model="form.inspection_due_date"
              label="Hạn đăng kiểm"
              type="date"
              density="compact"
            />
          </v-col>

          <!-- Service checkboxes -->
          <v-col cols="12">
            <div class="d-flex flex-wrap ga-3">
              <v-checkbox v-model="form.combine_maintenance" label="Bảo dưỡng" density="compact" hide-details />
              <v-checkbox v-model="form.combine_paint"       label="Đồng sơn"  density="compact" hide-details />
              <v-checkbox v-model="form.has_wash"            label="Rửa xe"    density="compact" hide-details />
              <v-checkbox v-model="form.insurance"           label="Bảo hiểm"  density="compact" hide-details />
              <v-checkbox v-model="form.waiting_parts"       label="Chờ PT"    density="compact" hide-details />
            </div>
          </v-col>

          <!-- Concern -->
          <v-col cols="12">
            <v-textarea
              v-model="form.concern"
              label="Yêu cầu khách hàng *"
              rows="2"
              density="compact"
              :error-messages="form.errors.concern"
            />
          </v-col>

          <!-- Note -->
          <v-col cols="12">
            <v-textarea
              v-model="form.note"
              label="Ghi chú nội bộ"
              rows="2"
              density="compact"
            />
          </v-col>

        </v-row>
      </v-card-text>

      <v-card-actions>
        <v-spacer />
        <v-btn variant="text" @click="close">Hủy</v-btn>
        <v-btn color="primary" :loading="form.processing" @click="submit">
          Tạo phiếu
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup lang="ts">
import { computed, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { useStore } from 'vuex'
import VehicleAlertBanner from './VehicleAlertBanner.vue'
import { normalizePlate } from '../utils/licensePlate'
import { DEPARTMENTS } from '../utils/roles'
import type { RootState } from '../store'
import type { AppUser, DepartmentName, TicketPriority, KanbanStage } from '../types'

const SOURCES: string[] = ['Đặt lịch', 'Vãng lai', 'Nội bộ', 'Bảo hiểm']

const DEPT_TO_JOB: Record<DepartmentName, string> = {
  'Bảo dưỡng': 'maintenance',
  'Sửa chữa':  'repair',
  'Đồng sơn':  'paint',
  'Rửa xe':    'wash',
}

const props = defineProps<{
  vehicleModels:   string[]
  advisorUsers:    AppUser[]
  technicianUsers: AppUser[]
}>()

const model = defineModel<boolean>({ default: false })
const store  = useStore<RootState>()

// ── Derived lists ─────────────────────────────────────────────────────────────

const advisorNames = computed(() =>
  props.advisorUsers.filter(u => u.is_active !== false).map(u => u.name)
)

const technicianNames = computed(() =>
  props.technicianUsers.filter(u => u.is_active !== false).map(u => u.name)
)

const vehicleAlert = computed(() =>
  form.license_plate
    ? store.getters['vehicleAlerts/getAlert'](normalizePlate(form.license_plate))
    : null
)

// ── Form ──────────────────────────────────────────────────────────────────────

function nowDateTime(): string {
  const d = new Date()
  d.setSeconds(0, 0)
  return d.toISOString().slice(0, 16)
}

const form = useForm({
  license_plate:       '',
  model:               '',
  customer_name:       '',
  phone_number:        '',
  department:          null as DepartmentName | null,
  service_job_type:    '',
  source:              '',
  priority:            'Trung bình' as TicketPriority,
  check_in_at:         nowDateTime(),
  due_at:              '',
  inspection_due_date: '',
  kanban_stage:        'Mới tiếp nhận' as KanbanStage,
  advisor:             '',
  dispatcher:          '',
  technician:          '',
  technician_user_id:  '',
  bay_id:              '',
  combine_maintenance: false,
  combine_paint:       false,
  has_wash:            false,
  insurance:           false,
  waiting_parts:       false,
  concern:             '',
  note:                '',
})

// Reset form when dialog closes
watch(model, (open) => {
  if (!open) {
    form.reset()
    form.clearErrors()
  }
})

// ── Handlers ──────────────────────────────────────────────────────────────────

function onDeptChange(dept: DepartmentName | null) {
  if (dept) form.service_job_type = DEPT_TO_JOB[dept] ?? ''
}

function onTechnicianChange(name: string | null) {
  if (!name) { form.technician_user_id = ''; return }
  const user = props.technicianUsers.find(u => u.name === name)
  form.technician_user_id = user?.id ?? ''
}

function close() {
  model.value = false
}

function submit() {
  form.post('/tickets', {
    onSuccess: () => {
      model.value = false
      form.reset()
    },
  })
}
</script>
