<template>
  <v-container fluid class="pa-4">

    <Head title="Tài khoản" />

    <!-- Header -->
    <div class="d-flex align-center mb-4">
      <h2 class="text-h6 font-weight-bold">Quản lý tài khoản</h2>
      <v-spacer />
      <v-btn color="primary" prepend-icon="mdi-account-plus" @click="openCreate">
        Tạo tài khoản
      </v-btn>
    </div>

    <!-- Users table -->
    <v-card rounded="lg" :border="true">
      <v-data-table
        :items="users"
        :headers="headers"
        item-value="id"
        density="compact"
        hover
      >
        <template #item.role="{ item }">
          <RoleChip :role="item.role" />
        </template>

        <template #item.is_active="{ item }">
          <v-chip size="x-small" :color="item.is_active !== false ? 'success' : 'error'" label>
            {{ item.is_active !== false ? 'Hoạt động' : 'Vô hiệu' }}
          </v-chip>
        </template>

        <template #item.sections="{ item }">
          <v-chip
            v-if="!item.sections"
            size="x-small"
            variant="outlined"
          >
            Mặc định
          </v-chip>
          <v-chip
            v-else
            size="x-small"
            color="primary"
            variant="tonal"
          >
            Tùy chỉnh · {{ item.sections.length }}
          </v-chip>
        </template>

        <template #item.actions="{ item }">
          <v-btn icon="mdi-pencil"     size="small" variant="text"           @click="openEdit(item)" />
          <v-btn icon="mdi-lock-reset" size="small" variant="text"           @click="openResetPin(item)" />
          <v-btn icon="mdi-delete"     size="small" variant="text" color="error" @click="deleteUser(item)" />
        </template>
      </v-data-table>
    </v-card>

  </v-container>

  <!-- ── CreateUserDialog ───────────────────────────────────────────────────── -->
  <v-dialog v-model="createDialog" max-width="460" persistent>
    <v-card title="Tạo tài khoản" prepend-icon="mdi-account-plus">
      <v-card-text>
        <v-row dense>
          <v-col cols="12">
            <v-text-field
              v-model="createForm.name"
              label="Tên hiển thị *"
              density="compact"
              :error-messages="createForm.errors.name"
            />
          </v-col>
          <v-col cols="12">
            <v-text-field
              v-model="createForm.username"
              label="Tên đăng nhập *"
              density="compact"
              autocomplete="off"
              :error-messages="createForm.errors.username"
            />
          </v-col>
          <v-col cols="12">
            <v-select
              v-model="createForm.role"
              :items="ROLE_ITEMS"
              item-title="label"
              item-value="value"
              label="Vai trò *"
              density="compact"
              :error-messages="createForm.errors.role"
            />
          </v-col>
          <v-col cols="12">
            <v-text-field
              v-model="createForm.pin"
              label="PIN *"
              type="password"
              density="compact"
              autocomplete="new-password"
              :error-messages="createForm.errors.pin"
            />
          </v-col>
        </v-row>
      </v-card-text>
      <v-card-actions>
        <v-spacer />
        <v-btn variant="text" @click="closeCreate">Hủy</v-btn>
        <v-btn color="primary" :loading="createForm.processing" @click="submitCreate">
          Tạo
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>

  <!-- ── EditUserDialog ─────────────────────────────────────────────────────── -->
  <v-dialog v-model="editDialog" max-width="520" persistent>
    <v-card title="Sửa tài khoản" prepend-icon="mdi-account-edit">
      <v-card-text>
        <v-row dense>
          <v-col cols="12">
            <v-text-field
              v-model="editForm.name"
              label="Tên hiển thị *"
              density="compact"
              :error-messages="editForm.errors.name"
            />
          </v-col>
          <v-col cols="12">
            <v-select
              v-model="editForm.role"
              :items="ROLE_ITEMS"
              item-title="label"
              item-value="value"
              label="Vai trò *"
              density="compact"
              :error-messages="editForm.errors.role"
            />
          </v-col>
          <v-col cols="12">
            <v-switch
              v-model="editForm.is_active"
              label="Tài khoản hoạt động"
              density="compact"
              color="success"
              hide-details
            />
          </v-col>

          <!-- Custom sections toggle -->
          <v-col cols="12">
            <v-divider class="my-2" />
            <div class="d-flex align-center justify-space-between">
              <div>
                <div class="text-body-2 font-weight-medium">Phân quyền tùy chỉnh</div>
                <div class="text-caption text-medium-emphasis">
                  Tắt để dùng quyền mặc định theo vai trò
                </div>
              </div>
              <v-switch
                :model-value="customSectionsEnabled"
                density="compact"
                color="primary"
                hide-details
                :disabled="editForm.role === 'owner' || editForm.role === 'admin'"
                @update:model-value="toggleCustomSections"
              />
            </div>
          </v-col>

          <!-- Section checkboxes -->
          <v-col v-if="customSectionsEnabled" cols="12">
            <v-alert
              v-if="editForm.role === 'owner' || editForm.role === 'admin'"
              type="info"
              variant="tonal"
              density="compact"
              class="mb-2"
            >
              Owner / Admin luôn có toàn quyền truy cập
            </v-alert>
            <div class="d-flex flex-wrap ga-x-4">
              <v-checkbox
                v-for="section in ALL_SECTIONS"
                :key="section"
                :model-value="editForm.sections?.includes(section) ?? false"
                :label="SECTION_LABELS[section]"
                density="compact"
                hide-details
                class="flex-0-0"
                style="width: 48%;"
                @update:model-value="(v) => toggleSection(section, !!v)"
              />
            </div>
          </v-col>
        </v-row>
      </v-card-text>
      <v-card-actions>
        <v-spacer />
        <v-btn variant="text" @click="closeEdit">Hủy</v-btn>
        <v-btn color="primary" :loading="editForm.processing" @click="submitEdit">Lưu</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>

  <!-- ── ResetPinDialog ─────────────────────────────────────────────────────── -->
  <v-dialog v-model="resetPinDialog" max-width="360" persistent>
    <v-card title="Đặt lại PIN" prepend-icon="mdi-lock-reset">
      <v-card-text>
        <div class="text-body-2 mb-3">
          Đặt lại PIN cho: <strong>{{ resetTarget?.name }}</strong>
          <span class="text-medium-emphasis"> ({{ resetTarget?.username }})</span>
        </div>
        <v-text-field
          v-model="resetPinForm.pin"
          label="PIN mới *"
          type="password"
          density="compact"
          autocomplete="new-password"
          :error-messages="resetPinForm.errors.pin"
        />
      </v-card-text>
      <v-card-actions>
        <v-spacer />
        <v-btn variant="text" @click="resetPinDialog = false">Hủy</v-btn>
        <v-btn
          color="primary"
          :loading="resetPinForm.processing"
          @click="submitResetPin"
        >
          Lưu
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head, router, useForm } from '@inertiajs/vue3'
import AppLayout from '../Layouts/AppLayout.vue'
import RoleChip from '../Components/RoleChip.vue'
import { roleLabel, availableSectionsForRole } from '../utils/roles'
import { useConfirm } from '../composables/useConfirm'
import type { AppUser, AppUserRole, AppSectionKey } from '../types'

defineOptions({ layout: AppLayout })

const props = defineProps<{ users: AppUser[] }>()

const { confirm } = useConfirm()

// ── Constants ─────────────────────────────────────────────────────────────────

const ALL_ROLES: AppUserRole[] = [
  'owner', 'admin', 'manager', 'service_advisor',
  'dispatcher', 'technician', 'customer_care',
]

const ROLE_ITEMS = ALL_ROLES.map(r => ({ value: r, label: roleLabel(r) }))

const ALL_SECTIONS: AppSectionKey[] = [
  'dashboard', 'intake', 'intake_display', 'kanban',
  'departments', 'customers', 'accounts', 'control',
]

const SECTION_LABELS: Record<AppSectionKey, string> = {
  dashboard:      'Tổng quan',
  intake:         'Tiếp nhận',
  intake_display: 'Màn hình chờ',
  kanban:         'Kanban',
  departments:    'Bộ phận',
  customers:      'Khách hàng',
  accounts:       'Tài khoản',
  control:        'Điều khiển',
}

// ── Table ─────────────────────────────────────────────────────────────────────

const headers = [
  { title: 'Tên',          key: 'name' },
  { title: 'Tên đăng nhập', key: 'username' },
  { title: 'Vai trò',      key: 'role' },
  { title: 'Trạng thái',   key: 'is_active' },
  { title: 'Phân quyền',   key: 'sections', sortable: false },
  { title: '',             key: 'actions',   sortable: false },
]

// ── CreateUserDialog ──────────────────────────────────────────────────────────

const createDialog = ref(false)

const createForm = useForm({
  username: '',
  name:     '',
  role:     'customer_care' as AppUserRole,
  pin:      '',
})

function openCreate() {
  createForm.reset()
  createForm.clearErrors()
  createDialog.value = true
}

function closeCreate() {
  createDialog.value = false
  createForm.reset()
  createForm.clearErrors()
}

function submitCreate() {
  createForm.post('/users', {
    onSuccess: () => { createDialog.value = false; createForm.reset() },
  })
}

// ── EditUserDialog ────────────────────────────────────────────────────────────

const editDialog = ref(false)
const editTarget  = ref<AppUser | null>(null)

const editForm = useForm({
  name:      '' as string,
  role:      'customer_care' as AppUserRole,
  sections:  null as string[] | null,
  is_active: true as boolean,
})

const customSectionsEnabled = computed(() => Array.isArray(editForm.sections))

function toggleCustomSections(val: boolean | null) {
  if (val) {
    editForm.sections = availableSectionsForRole(editForm.role, null).slice()
  } else {
    editForm.sections = null
  }
}

function toggleSection(section: AppSectionKey, checked: boolean) {
  if (!Array.isArray(editForm.sections)) return
  if (checked) {
    if (!editForm.sections.includes(section)) {
      editForm.sections = [...editForm.sections, section]
    }
  } else {
    editForm.sections = editForm.sections.filter(s => s !== section)
  }
}

function openEdit(user: AppUser) {
  editTarget.value   = user
  editForm.name      = user.name
  editForm.role      = user.role
  editForm.sections  = user.sections ? [...user.sections] : null
  editForm.is_active = user.is_active !== false
  editForm.clearErrors()
  editDialog.value = true
}

function closeEdit() {
  editDialog.value = false
  editForm.reset()
  editForm.clearErrors()
}

function submitEdit() {
  if (!editTarget.value) return
  editForm.patch(`/users/${editTarget.value.id}`, {
    onSuccess: () => { editDialog.value = false; editForm.reset() },
  })
}

// ── ResetPinDialog ────────────────────────────────────────────────────────────

const resetPinDialog = ref(false)
const resetTarget    = ref<AppUser | null>(null)

const resetPinForm = useForm({ pin: '' })

function openResetPin(user: AppUser) {
  resetTarget.value    = user
  resetPinForm.reset()
  resetPinForm.clearErrors()
  resetPinDialog.value = true
}

function submitResetPin() {
  if (!resetTarget.value) return
  resetPinForm.post(`/users/${resetTarget.value.id}/reset-pin`, {
    onSuccess: () => { resetPinDialog.value = false; resetPinForm.reset() },
  })
}

// ── Delete ────────────────────────────────────────────────────────────────────

async function deleteUser(user: AppUser) {
  const ok = await confirm(`Xóa tài khoản "${user.name}" (${user.username})?`)
  if (!ok) return
  router.delete(`/users/${user.id}`)
}
</script>
