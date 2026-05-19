<template>
  <v-alert
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
      <span class="font-weight-medium">{{ alert.customerName }}</span>
      <span class="mx-1">·</span>
      {{ alert.phoneNumber }}
      <span v-if="alert.points" class="ml-2">
        <v-chip size="x-small" color="amber-darken-2" label>
          {{ alert.points }} điểm
        </v-chip>
      </span>
    </div>
    <div v-if="alert.invoiceNo || alert.contractNo" class="text-caption text-medium-emphasis mt-1">
      <span v-if="alert.invoiceNo">HĐ: {{ alert.invoiceNo }}</span>
      <span v-if="alert.invoiceNo && alert.contractNo" class="mx-1">·</span>
      <span v-if="alert.contractNo">HĐ hợp đồng: {{ alert.contractNo }}</span>
    </div>
  </v-alert>
</template>

<script setup lang="ts">
import type { VehicleAlertData } from '../types'

defineProps<{ alert: VehicleAlertData }>()
</script>
