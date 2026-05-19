<template>
  <v-app>

    <!-- ── Navigation drawer ──────────────────────────────────────────── -->
    <v-navigation-drawer v-model="drawer" :rail="mobile" :width="240">
      <!-- Brand header -->
      <v-list-item
        prepend-icon="mdi-car-wrench"
        title="HKGI Service"
        subtitle="Quản lý dịch vụ"
        nav
        class="py-4"
      />

      <v-divider />

      <v-list density="compact" nav class="mt-1">
        <template v-for="item in navItems" :key="item.key">
          <v-list-item
            :prepend-icon="item.icon"
            :title="item.label"
            :active="isActive(item.href)"
            :value="item.href"
            color="primary"
            rounded="lg"
            @click="navigate(item.href)"
          />
        </template>
      </v-list>

      <template #append>
        <v-divider />
        <v-list density="compact" nav class="py-2">
          <v-list-item
            prepend-icon="mdi-key-variant"
            title="Đổi PIN"
            rounded="lg"
            @click="changePinDialog = true"
          />
        </v-list>
      </template>
    </v-navigation-drawer>

    <!-- ── App bar ────────────────────────────────────────────────────── -->
    <v-app-bar elevation="1" color="surface">
      <!-- Hamburger -->
      <v-app-bar-nav-icon @click="drawer = !drawer" />

      <!-- Title -->
      <v-app-bar-title class="font-weight-bold">HKGI Service</v-app-bar-title>

      <template #append>
        <div class="d-flex align-center ga-2 mr-2">
          <!-- User chip -->
          <template v-if="user">
            <v-chip
              size="small"
              :color="ROLE_COLORS[user.role]"
              variant="tonal"
              prepend-icon="mdi-account-circle"
            >
              {{ user.name }}
            </v-chip>
            <v-chip size="x-small" variant="outlined">
              {{ roleLabel(user.role) }}
            </v-chip>
          </template>

          <!-- Logout -->
          <v-btn
            icon="mdi-logout"
            variant="text"
            size="small"
            title="Đăng xuất"
            @click="logout"
          />
        </div>
      </template>
    </v-app-bar>

    <!-- ── Main content ───────────────────────────────────────────────── -->
    <v-main>
      <slot />
    </v-main>

    <!-- ── Global snackbar ───────────────────────────────────────────── -->
    <v-snackbar
      v-model="snackbar.show"
      :color="snackbar.color"
      location="top right"
      :timeout="3500"
      rounded="lg"
    >
      {{ snackbar.message }}
      <template #actions>
        <v-btn icon="mdi-close" variant="text" density="compact" @click="hideSnackbar" />
      </template>
    </v-snackbar>

    <!-- ── Global confirm dialog ──────────────────────────────────────── -->
    <ConfirmDialog />

    <!-- ── Change PIN dialog ──────────────────────────────────────────── -->
    <v-dialog v-model="changePinDialog" max-width="380" persistent>
      <v-card title="Đổi PIN" prepend-icon="mdi-key-variant">
        <v-card-text>
          <v-text-field
            v-model="pinForm.current_pin"
            label="PIN hiện tại"
            type="password"
            :error-messages="pinErrors.current_pin"
            class="mb-1"
          />
          <v-text-field
            v-model="pinForm.new_pin"
            label="PIN mới"
            type="password"
            :error-messages="pinErrors.new_pin"
            class="mb-1"
          />
          <v-text-field
            v-model="pinForm.new_pin_confirmation"
            label="Xác nhận PIN mới"
            type="password"
          />
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn variant="text" @click="changePinDialog = false">Hủy</v-btn>
          <v-btn color="primary" :loading="pinLoading" @click="submitChangePin">Lưu</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

  </v-app>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { useDisplay } from 'vuetify'
import { usePage, router } from '@inertiajs/vue3'
import { useStore } from 'vuex'
import { ROLE_COLORS, roleLabel, availableSectionsForRole } from '../utils/roles'
import type { SharedProps, AppSectionKey } from '../types'
import type { RootState } from '../store'
import ConfirmDialog from '../Components/ConfirmDialog.vue'

// ── Inertia / store ─────────────────────────────────────────────────────────

const page  = usePage<SharedProps>()
const store = useStore<RootState>()

const user  = computed(() => page.props.auth.user)
const flash = computed(() => page.props.flash)

// ── Drawer + display ────────────────────────────────────────────────────────

const { mobile } = useDisplay()
const drawer = ref(!mobile.value)

// ── Nav items (driven by section access) ────────────────────────────────────

const SECTION_ITEMS: Record<AppSectionKey, { icon: string; label: string; href: string }> = {
  dashboard:      { icon: 'mdi-view-dashboard',  label: 'Tổng quan',      href: '/' },
  intake:         { icon: 'mdi-car-arrow-right',  label: 'Tiếp nhận',     href: '/' },
  intake_display: { icon: 'mdi-monitor',          label: 'Màn hình chờ', href: '/intake-display' },
  kanban:         { icon: 'mdi-view-column',      label: 'Kanban',         href: '/kanban' },
  departments:    { icon: 'mdi-garage',           label: 'Bộ phận',        href: '/departments' },
  customers:      { icon: 'mdi-account-group',    label: 'Khách hàng',    href: '/customers' },
  accounts:       { icon: 'mdi-account-cog',      label: 'Tài khoản',     href: '/accounts' },
  control:        { icon: 'mdi-tune',             label: 'Điều khiển',    href: '/control' },
}

const navItems = computed(() => {
  if (!user.value) return []
  const sections = availableSectionsForRole(user.value.role, user.value.sections)
  // Deduplicate hrefs so 'dashboard' and 'intake' (both '/') don't show twice
  const seen = new Set<string>()
  return sections
    .map(key => ({ key, ...SECTION_ITEMS[key] }))
    .filter(item => {
      if (seen.has(item.href)) return false
      seen.add(item.href)
      return true
    })
})

function isActive(href: string): boolean {
  if (href === '/') return page.url === '/'
  return page.url.startsWith(href)
}

function navigate(href: string) {
  if (page.url === href) return
  router.visit(href)
}

function logout() {
  router.post('/logout')
}

// ── Flash → snackbar bridge ─────────────────────────────────────────────────

watch(flash, (f) => {
  if (f?.success) store.dispatch('ui/showSuccess', f.success)
  if (f?.error)   store.dispatch('ui/showError',   f.error)
}, { immediate: true, deep: true })

// ── Global snackbar (connected to Vuex ui module) ───────────────────────────

const snackbar   = computed(() => store.state.ui.snackbar)
const hideSnackbar = () => store.commit('ui/HIDE_SNACKBAR')

// ── Auto-refresh every 30 s (tickets + pendingIntakes only) ─────────────────

let refreshTimer: ReturnType<typeof setInterval>

onMounted(() => {
  refreshTimer = setInterval(() => {
    router.reload({ only: ['tickets', 'pendingIntakes'] })
  }, 30_000)
})

onUnmounted(() => clearInterval(refreshTimer))

// ── Change PIN ───────────────────────────────────────────────────────────────

const changePinDialog = ref(false)
const pinLoading      = ref(false)
const pinForm         = ref({ current_pin: '', new_pin: '', new_pin_confirmation: '' })
const pinErrors       = ref<Record<string, string>>({})

function submitChangePin() {
  pinLoading.value = true
  pinErrors.value  = {}
  router.post('/auth/change-pin', pinForm.value, {
    onSuccess: () => {
      changePinDialog.value = false
      pinForm.value = { current_pin: '', new_pin: '', new_pin_confirmation: '' }
    },
    onError:  (errors) => { pinErrors.value = errors },
    onFinish: () => { pinLoading.value = false },
  })
}
</script>