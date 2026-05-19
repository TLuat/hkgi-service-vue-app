<template>
  <v-container fluid class="pa-4">
      <div class="d-flex align-center ga-2 mb-4 flex-wrap">
        <h2 class="text-h6 font-weight-bold">Hàng chờ tiếp nhận</h2>
        <v-spacer />
        <v-btn color="primary" prepend-icon="mdi-plus" @click="addDialog = true">Thêm xe chờ</v-btn>
      </div>

      <v-row>
        <!-- Queue list -->
        <v-col cols="12" md="5">
          <div v-if="pendingIntakes.length === 0" class="text-center text-medium-emphasis py-8">
            Hàng chờ trống
          </div>
          <PendingIntakeItem
            v-for="intake in pendingIntakes"
            :key="intake.id"
            :intake="intake"
            @accept="startAccept(intake)"
            @edit="startEdit(intake)"
            @delete="deleteIntake(intake)"
          />
        </v-col>

        <!-- Accept form panel -->
        <v-col cols="12" md="7">
          <v-card v-if="accepting" rounded="lg" :border="true">
            <v-card-title class="text-subtitle-1">
              Tiếp nhận: {{ accepting.customerName }} ({{ accepting.licensePlate }})
            </v-card-title>
            <v-card-text>
              <VehicleAlertBanner v-if="accepting.licensePlate" :plate="accepting.licensePlate" />
              <v-row dense>
                <v-col cols="12" sm="6">
                  <v-select
                    v-model="acceptForm.department"
                    :items="DEPARTMENTS"
                    label="Phòng ban *"
                    density="compact"
                    :error-messages="acceptErrors.department ? [acceptErrors.department] : []"
                  />
                </v-col>
                <v-col cols="12" sm="6">
                  <v-select v-model="acceptForm.priority" :items="PRIORITIES" label="Ưu tiên *" density="compact" />
                </v-col>
                <v-col cols="12" sm="6">
                  <v-text-field
                    v-model="acceptForm.advisor"
                    label="Cố vấn *"
                    density="compact"
                    :error-messages="acceptErrors.advisor ? [acceptErrors.advisor] : []"
                  />
                </v-col>
                <v-col cols="12" sm="6">
                  <v-text-field v-model="acceptForm.check_in_at" label="Giờ vào *" type="datetime-local" density="compact" />
                </v-col>
                <v-col cols="12" sm="6">
                  <v-text-field v-model="acceptForm.due_at" label="Hẹn xong *" type="datetime-local" density="compact" />
                </v-col>
                <v-col cols="12">
                  <v-textarea
                    v-model="acceptForm.concern"
                    label="Yêu cầu KH *"
                    rows="2"
                    density="compact"
                    :error-messages="acceptErrors.concern ? [acceptErrors.concern] : []"
                  />
                </v-col>
              </v-row>
            </v-card-text>
            <v-card-actions>
              <v-btn @click="accepting = null">Hủy</v-btn>
              <v-spacer />
              <v-btn color="success" :loading="acceptLoading" @click="submitAccept">Tiếp nhận</v-btn>
            </v-card-actions>
          </v-card>

          <div v-else class="text-center text-medium-emphasis py-8">
            Chọn xe để tiếp nhận
          </div>
        </v-col>
      </v-row>
    </v-container>

    <!-- Add/Edit dialog -->
    <v-dialog v-model="addDialog" max-width="540">
      <v-card :title="editing ? 'Sửa thông tin xe chờ' : 'Thêm xe chờ'">
        <v-card-text>
          <v-row dense>
            <v-col cols="12" sm="6">
              <v-text-field
                v-model="intakeForm.customer_name"
                label="Tên KH *"
                density="compact"
                :error-messages="intakeErrors.customer_name ? [intakeErrors.customer_name] : []"
              />
            </v-col>
            <v-col cols="12" sm="6">
              <v-text-field
                v-model="intakeForm.phone_number"
                label="SĐT *"
                density="compact"
                :error-messages="intakeErrors.phone_number ? [intakeErrors.phone_number] : []"
              />
            </v-col>
            <v-col cols="12" sm="6">
              <v-text-field v-model="intakeForm.license_plate" label="Biển số" density="compact" />
            </v-col>
            <v-col cols="12" sm="6">
              <v-autocomplete v-model="intakeForm.model" :items="vehicleModels" label="Dòng xe" density="compact" />
            </v-col>
            <v-col cols="12" sm="6">
              <v-text-field v-model="intakeForm.arrived_at" label="Giờ đến" type="datetime-local" density="compact" />
            </v-col>
            <v-col cols="12" sm="6">
              <v-checkbox v-model="intakeForm.is_appointment" label="Khách hẹn trước" density="compact" hide-details />
            </v-col>
            <v-col cols="12">
              <v-textarea v-model="intakeForm.note" label="Ghi chú" rows="2" density="compact" />
            </v-col>
            <v-col cols="12" class="d-flex flex-wrap ga-3">
              <v-checkbox v-model="intakeForm.has_wash"            label="Rửa xe"    density="compact" hide-details />
              <v-checkbox v-model="intakeForm.combine_maintenance" label="Bảo dưỡng" density="compact" hide-details />
              <v-checkbox v-model="intakeForm.combine_paint"       label="Đồng sơn"  density="compact" hide-details />
            </v-col>
          </v-row>
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn @click="closeIntakeDialog">Hủy</v-btn>
          <v-btn color="primary" :loading="intakeLoading" @click="submitIntake">
            {{ editing ? 'Lưu' : 'Thêm' }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import { useStore } from 'vuex'
import AppLayout from '../Layouts/AppLayout.vue'

defineOptions({ layout: AppLayout })
import PendingIntakeItem from '../Components/PendingIntakeItem.vue'
import VehicleAlertBanner from '../Components/VehicleAlertBanner.vue'
import { DEPARTMENTS } from '../utils/roles'
import { useConfirm } from '../composables/useConfirm'
import type { PendingIntake, VehicleAlertData, DepartmentName } from '../types'
import type { RootState } from '../store'

const PRIORITIES = ['Thấp', 'Trung bình', 'Cao', 'Khẩn']

const props = defineProps<{
  pendingIntakes: PendingIntake[]
  vehicleAlerts: Record<string, VehicleAlertData>
  vehicleModels: string[]
}>()

const store   = useStore<RootState>()
const confirm = useConfirm()
onMounted(() => store.dispatch('vehicleAlerts/initFromPageProps', props.vehicleAlerts))

// ── Add / Edit ──────────────────────────────────────────────────────────────

const addDialog     = ref(false)
const editing       = ref<PendingIntake | null>(null)
const intakeLoading = ref(false)
// Inertia Errors is Record<string, string>
const intakeErrors  = ref<Record<string, string>>({})

const now = () => { const d = new Date(); d.setSeconds(0, 0); return d.toISOString().slice(0, 16) }

// Form keys use snake_case to match the Laravel API payload
const intakeForm = ref({
  customer_name: '', phone_number: '', license_plate: '', model: '',
  arrived_at: now(), is_appointment: false, note: '',
  has_wash: false, combine_maintenance: false, combine_paint: false,
})

function startEdit(intake: PendingIntake) {
  editing.value = intake
  Object.assign(intakeForm.value, {
    customer_name:       intake.customerName,
    phone_number:        intake.phoneNumber,
    license_plate:       intake.licensePlate ?? '',
    model:               intake.model ?? '',
    arrived_at:          intake.arrivedAt?.slice(0, 16) ?? now(),
    is_appointment:      intake.isAppointment,
    note:                intake.note ?? '',
    has_wash:            intake.hasWash,
    combine_maintenance: intake.combineMaintenance,
    combine_paint:       intake.combinePaint,
  })
  addDialog.value = true
}

function closeIntakeDialog() {
  addDialog.value    = false
  editing.value      = null
  intakeErrors.value = {}
}

function submitIntake() {
  intakeLoading.value = true
  intakeErrors.value  = {}
  const url    = editing.value ? `/intake-queue/${editing.value.id}` : '/intake-queue'
  const method = editing.value ? 'patch' : 'post' as const
  router[method](url, intakeForm.value, {
    onSuccess: closeIntakeDialog,
    onError:   (e) => { intakeErrors.value = e },
    onFinish:  () => { intakeLoading.value = false },
  })
}

async function deleteIntake(intake: PendingIntake) {
  const ok = await confirm.confirm(`Xóa ${intake.customerName} khỏi hàng chờ?`)
  if (!ok) return
  router.delete(`/intake-queue/${intake.id}`)
}

// ── Accept ──────────────────────────────────────────────────────────────────

const accepting     = ref<PendingIntake | null>(null)
const acceptLoading = ref(false)
const acceptErrors  = ref<Record<string, string>>({})

const acceptForm = ref({
  department:  null as DepartmentName | null,
  priority:    'Trung bình',
  advisor:     '',
  check_in_at: now(),
  due_at:      '',
  concern:     '',
})

function startAccept(intake: PendingIntake) {
  accepting.value    = intake
  acceptErrors.value = {}
  acceptForm.value   = {
    department: null,
    priority: 'Trung bình', advisor: '',
    check_in_at: now(), due_at: '', concern: '',
  }
}

function submitAccept() {
  if (!accepting.value) return
  acceptLoading.value = true
  router.post(`/intake-queue/${accepting.value.id}/accept`, acceptForm.value, {
    onSuccess: () => { accepting.value = null },
    onError:   (e) => { acceptErrors.value = e },
    onFinish:  () => { acceptLoading.value = false },
  })
}
</script>