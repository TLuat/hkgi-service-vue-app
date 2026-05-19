<template>
  <v-card
    rounded="lg"
    :color="color"
    variant="tonal"
    style="cursor: pointer;"
    @click="emit('click')"
  >
    <v-card-text class="pa-4">
      <div class="d-flex align-start justify-space-between">
        <div>
          <div class="text-h4 font-weight-bold">{{ value }}</div>
          <div class="text-body-2 text-medium-emphasis mt-1">{{ label }}</div>
        </div>
        <v-icon :icon="icon" size="36" :color="color" />
      </div>
    </v-card-text>
  </v-card>
</template>

<script setup lang="ts">
defineProps<{
  label: string
  value: number
  icon:  string
  color?: string
}>()

const emit = defineEmits<{ click: [] }>()
</script>
