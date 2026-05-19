<template>
  <v-chip :color="color" size="small" label>{{ label }}</v-chip>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { roleLabel, ROLE_COLORS } from '../utils/roles'
import type { AppUserRole } from '../types'

const props = defineProps<{ role: AppUserRole }>()

const label = computed(() => roleLabel(props.role))
const color = computed(() => ROLE_COLORS[props.role])
</script>