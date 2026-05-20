<template>
  <v-alert
    v-if="resolved"
    color="amber"
    variant="tonal"
    density="compact"
    rounded="lg"
  >
    <template #prepend>
      <v-icon icon="mdi-shield-car" color="amber-darken-3" />
    </template>
    <div class="text-caption font-weight-bold text-amber-darken-4 mb-1">
      VETC — Cảnh báo xe
    </div>
    <div class="text-body-2">
      <span class="font-weight-medium">{{ resolved.customerName }}</span>
      <span class="mx-1">·</span>
      {{ resolved.phoneNumber }}
      <span v-if="resolved.points" class="ml-2">
        <v-chip size="x-small" color="amber-darken-2" label>
          {{ resolved.points }} điểm
        </v-chip>
      </span>
    </div>
    <div v-if="resolved.invoiceNo || resolved.contractNo" class="text-caption text-medium-emphasis mt-1">
      <span v-if="resolved.invoiceNo">HĐ: {{ resolved.invoiceNo }}</span>
      <span v-if="resolved.invoiceNo && resolved.contractNo" class="mx-1">·</span>
      <span v-if="resolved.contractNo">HĐ hợp đồng: {{ resolved.contractNo }}</span>
    </div>
  </v-alert>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useStore } from 'vuex'
import type { VehicleAlertData } from '../types'

const props = defineProps<{
  alert?: VehicleAlertData
  plate?: string
}>()

const store = useStore()

const resolved = computed<VehicleAlertData | null>(() => {
  if (props.alert) return props.alert
  if (props.plate) return store.getters['vehicleAlerts/getAlert'](props.plate)
  return null
})
</script>