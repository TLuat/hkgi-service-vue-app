<template>
  <v-container fluid class="pa-4">

    <Head title="Điều khiển" />
    <h2 class="text-h6 font-weight-bold mb-4">Điều khiển hệ thống</h2>

    <v-expansion-panels variant="accordion">

      <!-- ── Panel 1: Mô hình xe ────────────────────────────────────────────── -->
      <v-expansion-panel>
        <v-expansion-panel-title>
          <v-icon class="mr-3" color="primary">mdi-car-multiple</v-icon>
          Mô hình xe
          <v-chip size="x-small" class="ml-2" variant="tonal">{{ vehicleModels.length }}</v-chip>
        </v-expansion-panel-title>

        <v-expansion-panel-text>
          <!-- Add input -->
          <div class="d-flex ga-2 mb-4">
            <v-text-field
              v-model="modelForm.name"
              label="Tên dòng xe"
              density="compact"
              hide-details
              :error-messages="modelForm.errors.name"
              @keyup.enter="addModel"
            />
            <v-btn
              color="primary"
              icon="mdi-plus"
              :loading="modelForm.processing"
              @click="addModel"
            />
          </div>

          <!-- Model list -->
          <div v-if="vehicleModels.length === 0" class="text-center text-medium-emphasis py-4">
            Chưa có dòng xe nào
          </div>
          <v-list v-else density="compact" lines="one">
            <v-list-item
              v-for="m in vehicleModels"
              :key="m.id"
              :title="m.name"
            >
              <template #append>
                <v-btn
                  icon="mdi-delete"
                  size="small"
                  variant="text"
                  color="error"
                  @click="deleteModel(m)"
                />
              </template>
            </v-list-item>
          </v-list>
        </v-expansion-panel-text>
      </v-expansion-panel>

      <!-- ── Panel 2: Cảnh báo xe VETC ─────────────────────────────────────── -->
      <v-expansion-panel>
        <v-expansion-panel-title>
          <v-icon class="mr-3" color="warning">mdi-alert-circle</v-icon>
          Cảnh báo xe VETC
          <v-chip size="x-small" class="ml-2" variant="tonal" color="warning">
            {{ vehicleAlerts.length }}
          </v-chip>
        </v-expansion-panel-title>

        <v-expansion-panel-text>
          <!-- Import section -->
          <div class="d-flex align-end ga-3 mb-4 flex-wrap">
            <v-file-input
              v-model="alertFile"
              label="Chọn file .xlsx"
              accept=".xlsx,.xls"
              density="compact"
              prepend-icon="mdi-file-excel"
              hide-details
              clearable
              style="max-width: 360px;"
              :error-messages="alertErrors"
            />
            <v-btn
              color="primary"
              prepend-icon="mdi-upload"
              :loading="alertImporting"
              :disabled="!alertFile"
              @click="importAlerts"
            >
              Nhập danh sách
            </v-btn>
          </div>

          <div class="text-caption text-medium-emphasis mb-3">
            Cột Excel theo thứ tự: Biển số · Tên KH · SĐT · Hóa đơn · Hợp đồng · Điểm
          </div>

          <!-- Alerts data table -->
          <v-card :border="true" rounded="lg">
            <v-data-table
              :items="vehicleAlerts"
              :headers="alertHeaders"
              density="compact"
              :items-per-page="20"
            />
          </v-card>
        </v-expansion-panel-text>
      </v-expansion-panel>

      <!-- ── Panel 3: Nhật ký hoạt động ───────────────────────────────────── -->
      <v-expansion-panel>
        <v-expansion-panel-title>
          <v-icon class="mr-3" color="info">mdi-history</v-icon>
          Nhật ký hoạt động
        </v-expansion-panel-title>

        <v-expansion-panel-text>
          <v-card :border="true" rounded="lg">
            <v-data-table
              :items="activityLogs"
              :headers="logHeaders"
              density="compact"
              :items-per-page="25"
            >
              <template #item.createdAt="{ item }">
                {{ formatDate(item.createdAt) }}
              </template>

              <template #item.performedBy="{ item }">
                <span class="text-medium-emphasis">{{ item.performedBy }}</span>
              </template>
            </v-data-table>
          </v-card>
        </v-expansion-panel-text>
      </v-expansion-panel>

      <!-- ── Panel 4: Thông tin hệ thống ──────────────────────────────────── -->
      <v-expansion-panel>
        <v-expansion-panel-title>
          <v-icon class="mr-3" color="secondary">mdi-tune</v-icon>
          Thông tin hệ thống
        </v-expansion-panel-title>

        <v-expansion-panel-text>
          <v-row dense>
            <v-col cols="12" md="5">
              <v-card :border="true" rounded="lg" class="pa-4">
                <div class="text-subtitle-2 font-weight-bold mb-3">Cài đặt lưu trữ</div>

                <v-select
                  v-model="settingsForm.storage_mode"
                  :items="storageModes"
                  item-title="title"
                  item-value="value"
                  label="Chế độ lưu trữ"
                  density="compact"
                  class="mb-3"
                />

                <v-text-field
                  v-model="settingsForm.google_sheet_id"
                  label="Google Sheet ID"
                  density="compact"
                  :disabled="settingsForm.storage_mode !== 'google_sheet'"
                  class="mb-3"
                />

                <div v-if="settings?.syncedAt" class="text-caption text-medium-emphasis mb-3">
                  Đồng bộ lần cuối: {{ formatDate(settings.syncedAt) }}
                </div>

                <v-btn
                  color="primary"
                  :loading="settingsForm.processing"
                  block
                  @click="saveSettings"
                >
                  Lưu cài đặt
                </v-btn>
              </v-card>
            </v-col>

            <v-col cols="12" md="7">
              <v-card :border="true" rounded="lg" class="pa-4">
                <div class="text-subtitle-2 font-weight-bold mb-3">Thông tin ứng dụng</div>
                <v-list density="compact" lines="two">
                  <v-list-item
                    prepend-icon="mdi-application"
                    title="HKGI Service"
                    subtitle="Hệ thống quản lý dịch vụ xe"
                  />
                  <v-list-item
                    prepend-icon="mdi-database"
                    title="Chế độ lưu trữ"
                    :subtitle="settings?.storageMode === 'google_sheet' ? 'Google Sheet' : 'Nội bộ'"
                  />
                  <v-list-item
                    v-if="settings?.googleSheetId"
                    prepend-icon="mdi-google-spreadsheet"
                    title="Google Sheet ID"
                    :subtitle="settings.googleSheetId"
                  />
                </v-list>
              </v-card>
            </v-col>
          </v-row>
        </v-expansion-panel-text>
      </v-expansion-panel>

    </v-expansion-panels>
  </v-container>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { Head, router, useForm } from '@inertiajs/vue3'
import AppLayout from '../Layouts/AppLayout.vue'
import { useConfirm } from '../composables/useConfirm'
import type { AppSettings, VehicleModel, ActivityLogEntry } from '../types'

defineOptions({ layout: AppLayout })

// ── Local type for full VehicleAlert (not in shared types) ────────────────────

interface VehicleAlert {
  id: string
  licensePlate: string
  customerName: string
  phoneNumber: string
  invoiceNo: string
  contractNo: string
  points: number
}

const props = defineProps<{
  settings:      AppSettings | null
  vehicleModels: VehicleModel[]
  vehicleAlerts: VehicleAlert[]
  activityLogs:  ActivityLogEntry[]
}>()

const { confirm } = useConfirm()

// ── Panel 1: Vehicle models ───────────────────────────────────────────────────

const modelForm = useForm({ name: '' })

function addModel() {
  if (!modelForm.name.trim()) return
  modelForm.post('/vehicle-models', {
    preserveScroll: true,
    onSuccess: () => modelForm.reset(),
  })
}

async function deleteModel(m: VehicleModel) {
  const ok = await confirm(`Xóa dòng xe "${m.name}"?`)
  if (!ok) return
  router.delete(`/vehicle-models/${m.id}`, { preserveScroll: true })
}

// ── Panel 2: Vehicle alerts import ───────────────────────────────────────────

const alertFile      = ref<File | null>(null)
const alertImporting = ref(false)
const alertErrors    = ref<string[]>([])

const alertHeaders = [
  { title: 'Biển số',   key: 'licensePlate' },
  { title: 'Tên KH',    key: 'customerName' },
  { title: 'SĐT',       key: 'phoneNumber' },
  { title: 'Hóa đơn',   key: 'invoiceNo' },
  { title: 'Hợp đồng',  key: 'contractNo' },
  { title: 'Điểm',      key: 'points' },
]

function importAlerts() {
  if (!alertFile.value) return
  alertImporting.value = true
  alertErrors.value    = []
  const data = new FormData()
  data.append('file', alertFile.value)
  router.post('/vehicle-alerts/import', data, {
    preserveScroll: true,
    onSuccess: () => { alertFile.value = null },
    onError:   (e: Record<string, string>) => { alertErrors.value = Object.values(e) },
    onFinish:  () => { alertImporting.value = false },
  })
}

// ── Panel 3: Activity logs ────────────────────────────────────────────────────

const logHeaders = [
  { title: 'Thời gian',       key: 'createdAt',    width: 140 },
  { title: 'Hành động',       key: 'action',       width: 160 },
  { title: 'Mô tả',           key: 'description' },
  { title: 'Người thực hiện', key: 'performedBy',  width: 140 },
]

// ── Panel 4: App settings ─────────────────────────────────────────────────────

const storageModes = [
  { title: 'Nội bộ',        value: 'local' },
  { title: 'Google Sheet',  value: 'google_sheet' },
]

const settingsForm = useForm({
  storage_mode:    props.settings?.storageMode    ?? 'local',
  google_sheet_id: props.settings?.googleSheetId ?? '',
})

function saveSettings() {
  settingsForm.patch('/settings', { preserveScroll: true })
}

// ── Helpers ───────────────────────────────────────────────────────────────────

function formatDate(d: string): string {
  return new Date(d).toLocaleString('vi-VN', { dateStyle: 'short', timeStyle: 'short' })
}
</script>
