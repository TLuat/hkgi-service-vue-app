<template>
  <v-card rounded="lg" :border="true" class="mb-2">
    <v-card-text class="pa-3">
      <div class="d-flex align-center justify-space-between">
        <div>
          <div class="font-weight-bold">{{ intake.customerName }}</div>
          <div class="text-body-2 text-medium-emphasis">{{ intake.phoneNumber }}</div>
          <div v-if="intake.licensePlate" class="text-caption">{{ intake.licensePlate }}</div>
        </div>
        <div class="d-flex flex-column align-end ga-1">
          <v-chip size="x-small" :color="statusColor" label>{{ intake.status }}</v-chip>
          <span class="text-caption text-medium-emphasis">{{ arrivedText }}</span>
        </div>
      </div>

      <div class="d-flex ga-1 mt-2 flex-wrap">
        <v-chip v-if="intake.hasWash"            size="x-small" color="cyan"   label>Rửa xe</v-chip>
        <v-chip v-if="intake.combineMaintenance" size="x-small" color="teal"   label>Bảo dưỡng</v-chip>
        <v-chip v-if="intake.combinePaint"       size="x-small" color="purple" label>Đồng sơn</v-chip>
        <v-chip v-if="intake.inspectionDueDate"  size="x-small" color="orange" label>Đăng kiểm</v-chip>
      </div>

      <div v-if="intake.note" class="text-caption mt-1 text-medium-emphasis">
        <v-icon size="12">mdi-note-text</v-icon> {{ intake.note }}
      </div>
    </v-card-text>

    <v-card-actions class="pa-2 pt-0 ga-1">
      <v-btn size="small" color="success" variant="tonal" prepend-icon="mdi-check" @click="emit('accept')">
        Tiếp nhận
      </v-btn>
      <v-btn size="small" variant="tonal" prepend-icon="mdi-pencil" @click="emit('edit')">
        Sửa
      </v-btn>
      <v-spacer />
      <v-btn size="small" color="error" icon="mdi-delete" variant="text" @click="emit('delete')" />
    </v-card-actions>
  </v-card>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import type { PendingIntake } from '../types'

const props = defineProps<{ intake: PendingIntake }>()
const emit  = defineEmits<{ accept: []; edit: []; delete: [] }>()

const statusColor = computed(() => {
  if (props.intake.status === 'Khách hẹn')           return 'blue'
  if (props.intake.status === 'Đang được tiếp nhận') return 'green'
  return 'grey'
})

const arrivedText = computed(() => {
  if (!props.intake.arrivedAt) return ''
  return new Date(props.intake.arrivedAt).toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' })
})
</script>