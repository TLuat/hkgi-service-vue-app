<template>
  <v-app>
    <v-main
      class="d-flex align-center justify-center"
      style="min-height:100vh; background:#f0f2f5;"
    >
      <v-card max-width="400" width="100%" rounded="xl" elevation="4" class="pa-2 mx-4">
        <v-card-text class="text-center pt-8 pb-4">
          <div class="text-h3 font-weight-bold text-primary mb-1">HKGI</div>
          <div class="text-body-1 text-medium-emphasis">Quản lý dịch vụ nội bộ</div>
        </v-card-text>

        <v-card-text class="pt-2 pb-6">
          <v-alert
            v-if="form.errors.pin"
            type="error"
            density="compact"
            class="mb-4"
            rounded="lg"
          >
            {{ form.errors.pin }}
          </v-alert>

          <v-text-field
            v-model="form.username"
            label="Tên đăng nhập"
            prepend-inner-icon="mdi-account-outline"
            density="compact"
            autofocus
            autocomplete="username"
            :error-messages="form.errors.username"
            class="mb-3"
            @keyup.enter="submit"
          />

          <v-text-field
            v-model="form.pin"
            label="PIN"
            :type="showPin ? 'text' : 'password'"
            prepend-inner-icon="mdi-lock-outline"
            :append-inner-icon="showPin ? 'mdi-eye-off' : 'mdi-eye'"
            density="compact"
            autocomplete="current-password"
            class="mb-5"
            @keyup.enter="submit"
            @click:append-inner="showPin = !showPin"
          />

          <v-btn
            block
            color="primary"
            size="large"
            :loading="form.processing"
            @click="submit"
          >
            Đăng nhập
          </v-btn>
        </v-card-text>
      </v-card>
    </v-main>
  </v-app>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'

defineOptions({ layout: null })

const showPin = ref(false)

const form = useForm({ username: '', pin: '' })

const submit = () => form.post('/login')
</script>