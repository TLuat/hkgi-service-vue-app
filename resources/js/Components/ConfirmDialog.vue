<template>
  <v-dialog v-model="dialog.show" max-width="420" persistent>
    <v-card>
      <v-card-text class="pt-5 text-body-1">{{ dialog.message }}</v-card-text>
      <v-card-actions>
        <v-spacer />
        <v-btn @click="resolve(false)">Hủy</v-btn>
        <v-btn color="error" @click="resolve(true)">Xác nhận</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useStore } from 'vuex'
import type { RootState } from '../store'

const store  = useStore<RootState>()
const dialog = computed(() => store.state.ui.confirmDialog)

function resolve(value: boolean) {
  store.commit('ui/RESOLVE_CONFIRM', value)
}
</script>