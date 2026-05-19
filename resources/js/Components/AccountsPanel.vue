<template>
  <v-container fluid class="pa-4">
    <div class="d-flex align-center mb-4">
      <h2 class="text-h6 font-weight-bold">Quản lý tài khoản</h2>
      <v-spacer />
      <v-btn color="primary" prepend-icon="mdi-account-plus" @click="openCreate">Tạo tài khoản</v-btn>
    </div>

    <v-card rounded="lg" :border="true">
      <v-data-table
        :items="users"
        :headers="headers"
        item-value="id"
        density="compact"
      >
        <template #item.role="{ item }">
          <RoleChip :role="item.role" />
        </template>
        <template #item.is_active="{ item }">
          <v-chip size="x-small" :color="item.is_active ? 'success' : 'error'" label>
            {{ item.is_active ? 'Hoạt động' : 'Vô hiệu' }}
          </v-chip>
        </template>
        <template #item.actions="{ item }">
          <v-btn icon="mdi-pencil"     size="small" variant="text" @click="openEdit(item)" />
          <v-btn icon="mdi-lock-reset" size="small" variant="text" @click="openResetPin(item)" />
          <v-btn icon="mdi-delete"     size="small" variant="text" color="error" @click="deleteUser(item)" />
        </template>
      </v-data-table>
    </v-card>
  </v-container>

  <!-- Create / Edit dialog -->
  <v-dialog v-model="formDialog" max-width="480">
    <v-card :title="editing ? 'Sửa tài khoản' : 'Tạo tài khoản'">
      <v-card-text>
        <v-row dense>
          <v-col cols="12">
            <v-text-field
              v-model="form.name"
              label="Tên hiển thị *"
              density="compact"
              :error-messages="errors.name ? [errors.name] : []"
            />
          </v-col>
          <v-col v-if="!editing" cols="12">
            <v-text-field
              v-model="form.username"
              label="Tên đăng nhập *"
              density="compact"
              :error-messages="errors.username ? [errors.username] : []"
            />
          </v-col>
          <v-col v-if="!editing" cols="12">
            <v-text-field
              v-model="form.pin"
              label="PIN *"
              type="password"
              density="compact"
              :error-messages="errors.pin ? [errors.pin] : []"
            />
          </v-col>
          <v-col cols="12">
            <v-select
              v-model="form.role"
              :items="ROLES"
              label="Vai trò *"
              density="compact"
              :error-messages="errors.role ? [errors.role] : []"
            />
          </v-col>
          <v-col cols="12">
            <v-switch v-model="form.is_active" label="Hoạt động" density="compact" color="success" />
          </v-col>
        </v-row>
      </v-card-text>
      <v-card-actions>
        <v-spacer />
        <v-btn @click="formDialog = false">Hủy</v-btn>
        <v-btn color="primary" :loading="saving" @click="submitForm">Lưu</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>

  <!-- Reset PIN dialog -->
  <v-dialog v-model="resetPinDialog" max-width="360">
    <v-card title="Đặt lại PIN">
      <v-card-text>
        <div class="mb-3">Đặt lại PIN cho: <strong>{{ resetTarget?.username }}</strong></div>
        <v-text-field
          v-model="newPin"
          label="PIN mới *"
          type="password"
          density="compact"
          :error-messages="errors.pin ? [errors.pin] : []"
        />
      </v-card-text>
      <v-card-actions>
        <v-spacer />
        <v-btn @click="resetPinDialog = false">Hủy</v-btn>
        <v-btn color="primary" :loading="saving" @click="submitResetPin">Lưu</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import RoleChip from './RoleChip.vue'
import { useConfirm } from '../composables/useConfirm'
import type { AppUser, AppUserRole } from '../types'

const ROLES: AppUserRole[] = [
  'owner', 'admin', 'manager', 'service_advisor',
  'dispatcher', 'technician', 'customer_care',
]

const props   = defineProps<{ users: AppUser[] }>()
const confirm = useConfirm()

const headers = [
  { title: 'Tên',         key: 'name'      },
  { title: 'Đăng nhập',   key: 'username'  },
  { title: 'Vai trò',     key: 'role'      },
  { title: 'Trạng thái',  key: 'is_active' },
  { title: '',            key: 'actions', sortable: false },
]

const formDialog     = ref(false)
const resetPinDialog = ref(false)
const saving         = ref(false)
const editing        = ref<AppUser | null>(null)
const resetTarget    = ref<AppUser | null>(null)
const newPin         = ref('')
const errors         = ref<Record<string, string>>({})

const form = ref<{ name: string; username: string; pin: string; role: AppUserRole; is_active: boolean }>({
  name: '', username: '', pin: '', role: 'customer_care', is_active: true,
})

function openCreate() {
  editing.value    = null
  errors.value     = {}
  form.value       = { name: '', username: '', pin: '', role: 'customer_care', is_active: true }
  formDialog.value = true
}

function openEdit(user: AppUser) {
  editing.value    = user
  errors.value     = {}
  form.value       = { name: user.name, username: user.username, pin: '', role: user.role, is_active: user.is_active ?? true }
  formDialog.value = true
}

function submitForm() {
  saving.value = true
  errors.value = {}
  if (editing.value) {
    router.patch(`/users/${editing.value.id}`, { name: form.value.name, role: form.value.role, is_active: form.value.is_active }, {
      onSuccess: () => { formDialog.value = false },
      onError:   (e) => { errors.value = e },
      onFinish:  () => { saving.value = false },
      preserveScroll: true,
    })
  } else {
    router.post('/users', form.value, {
      onSuccess: () => { formDialog.value = false },
      onError:   (e) => { errors.value = e },
      onFinish:  () => { saving.value = false },
      preserveScroll: true,
    })
  }
}

function openResetPin(user: AppUser) {
  resetTarget.value    = user
  newPin.value         = ''
  errors.value         = {}
  resetPinDialog.value = true
}

function submitResetPin() {
  if (!resetTarget.value) return
  saving.value = true
  router.post(`/users/${resetTarget.value.id}/reset-pin`, { pin: newPin.value }, {
    onSuccess: () => { resetPinDialog.value = false },
    onError:   (e) => { errors.value = e },
    onFinish:  () => { saving.value = false },
    preserveScroll: true,
  })
}

async function deleteUser(user: AppUser) {
  const ok = await confirm.confirm(`Xóa tài khoản "${user.username}"?`)
  if (!ok) return
  router.delete(`/users/${user.id}`, { preserveScroll: true })
}
</script>