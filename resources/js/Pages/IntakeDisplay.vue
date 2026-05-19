<template>
  <Head title="Màn hình chờ" />
  <div class="d-flex flex-column" style="min-height: 100vh; padding: 0;">

    <!-- ── Top bar ────────────────────────────────────────────────────────── -->
    <div class="d-flex align-center justify-space-between px-6 py-4" style="border-bottom: 1px solid rgba(255,255,255,.08);">
      <div>
        <div class="text-h5 font-weight-bold text-white">HKGI Service</div>
        <div class="text-subtitle-2 text-medium-emphasis">{{ currentDate }}</div>
      </div>
      <div
        class="text-h3 font-weight-bold text-white"
        style="font-family: 'Courier New', monospace; letter-spacing: 4px; font-variant-numeric: tabular-nums;"
      >
        {{ currentClock }}
      </div>
    </div>

    <!-- ── Content ────────────────────────────────────────────────────────── -->
    <div class="pa-4" style="flex: 1; display: flex; flex-direction: column; gap: 28px;">

      <!-- Section 1: Xe đang chờ ─────────────────────────────────────────── -->
      <section>
        <div class="d-flex align-center mb-3 gap-2">
          <v-icon color="amber-lighten-1" size="24">mdi-clock-outline</v-icon>
          <span class="text-h6 font-weight-bold text-white">Xe đang chờ</span>
          <v-chip size="small" variant="tonal" class="ml-1">{{ waitingIntakes.length }}</v-chip>
        </div>

        <!-- Empty state -->
        <div
          v-if="waitingIntakes.length === 0"
          class="d-flex align-center justify-center text-medium-emphasis py-8"
          style="border: 1px dashed rgba(255,255,255,.15); border-radius: 12px;"
        >
          <v-icon size="28" class="mr-2">mdi-car-off</v-icon>
          <span class="text-body-1">Không có xe đang chờ</span>
        </div>

        <!-- Grid of cards -->
        <v-row v-else>
          <v-col
            v-for="intake in waitingIntakes"
            :key="intake.id"
            cols="12"
            sm="6"
            md="4"
            lg="3"
          >
            <v-card
              rounded="lg"
              elevation="4"
              color="grey-darken-4"
              class="pa-4"
              style="min-height: 140px;"
            >
              <!-- Customer name -->
              <div class="text-h5 font-weight-bold mb-1 text-white">
                {{ intake.customerName }}
              </div>

              <!-- License plate -->
              <div
                class="text-h6 font-weight-bold mb-2"
                style="font-family: 'Courier New', monospace; letter-spacing: 2px; color: #B0BEC5;"
              >
                {{ intake.licensePlate || '—' }}
              </div>

              <!-- Model + appointment badge -->
              <div class="d-flex align-center ga-2 mb-2 flex-wrap">
                <span v-if="intake.model" class="text-body-2 text-medium-emphasis">
                  {{ intake.model }}
                </span>
                <v-icon
                  v-if="intake.isAppointment"
                  color="blue-lighten-2"
                  size="18"
                  title="Khách hẹn trước"
                >
                  mdi-calendar
                </v-icon>
              </div>

              <!-- Arrived time -->
              <div v-if="intake.arrivedAt" class="text-caption text-medium-emphasis">
                <v-icon size="14" class="mr-1">mdi-login</v-icon>
                Đến lúc {{ formatTime(intake.arrivedAt) }}
              </div>
            </v-card>
          </v-col>
        </v-row>
      </section>

      <!-- Section 2: Đang tiếp nhận ──────────────────────────────────────── -->
      <section>
        <div class="d-flex align-center mb-3 gap-2">
          <v-icon color="green-lighten-2" size="24">mdi-account-arrow-right</v-icon>
          <span class="text-h6 font-weight-bold text-white">Đang tiếp nhận</span>
          <v-chip size="small" color="success" variant="tonal" class="ml-1">
            {{ activeIntakes.length }}
          </v-chip>
        </div>

        <!-- Empty state -->
        <div
          v-if="activeIntakes.length === 0"
          class="d-flex align-center text-medium-emphasis py-4 px-2"
        >
          <v-icon size="20" class="mr-2">mdi-minus-circle-outline</v-icon>
          <span class="text-body-1">Chưa có xe đang tiếp nhận</span>
        </div>

        <!-- Active cards with green glow -->
        <v-row v-else>
          <v-col
            v-for="intake in activeIntakes"
            :key="intake.id"
            cols="12"
            sm="6"
            md="4"
            lg="3"
          >
            <v-card
              rounded="lg"
              elevation="8"
              color="grey-darken-3"
              class="pa-4"
              style="border: 2px solid #4CAF50; box-shadow: 0 0 28px rgba(76, 175, 80, .45) !important; min-height: 140px;"
            >
              <!-- Customer name -->
              <div class="text-h5 font-weight-bold mb-1" style="color: #A5D6A7;">
                {{ intake.customerName }}
              </div>

              <!-- License plate -->
              <div
                class="text-h6 font-weight-bold mb-2"
                style="font-family: 'Courier New', monospace; letter-spacing: 2px; color: #C8E6C9;"
              >
                {{ intake.licensePlate || '—' }}
              </div>

              <!-- Model + appointment badge -->
              <div class="d-flex align-center ga-2 mb-2 flex-wrap">
                <span v-if="intake.model" class="text-body-2 text-medium-emphasis">
                  {{ intake.model }}
                </span>
                <v-icon
                  v-if="intake.isAppointment"
                  color="blue-lighten-2"
                  size="18"
                  title="Khách hẹn trước"
                >
                  mdi-calendar
                </v-icon>
              </div>

              <!-- Arrived time -->
              <div v-if="intake.arrivedAt" class="text-caption text-medium-emphasis">
                <v-icon size="14" class="mr-1">mdi-login</v-icon>
                Đến lúc {{ formatTime(intake.arrivedAt) }}
              </div>
            </v-card>
          </v-col>
        </v-row>
      </section>

    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import DisplayLayout from '../Layouts/DisplayLayout.vue'
import type { PendingIntake } from '../types'

defineOptions({ layout: DisplayLayout })

const props = defineProps<{ intakes: PendingIntake[] }>()

// ── Derived lists ────────────────────────────────────────────────────────────

const waitingIntakes = computed<PendingIntake[]>(() =>
  props.intakes.filter(i => i.status === 'Khách hẹn' || i.status === 'Khách không hẹn')
)

const activeIntakes = computed<PendingIntake[]>(() =>
  props.intakes.filter(i => i.status === 'Đang được tiếp nhận')
)

// ── Live clock ───────────────────────────────────────────────────────────────

const WEEKDAYS = ['Chủ Nhật', 'Thứ Hai', 'Thứ Ba', 'Thứ Tư', 'Thứ Năm', 'Thứ Sáu', 'Thứ Bảy']

const currentDate  = ref('')
const currentClock = ref('')

function updateClock() {
  const now = new Date()
  const d   = String(now.getDate()).padStart(2, '0')
  const mo  = String(now.getMonth() + 1).padStart(2, '0')
  const y   = now.getFullYear()
  currentDate.value  = `${WEEKDAYS[now.getDay()]}, ngày ${d}/${mo}/${y}`
  currentClock.value = `${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}:${String(now.getSeconds()).padStart(2, '0')}`
}

// ── Intervals ────────────────────────────────────────────────────────────────

let clockTimer:   ReturnType<typeof setInterval>
let refreshTimer: ReturnType<typeof setInterval>

onMounted(() => {
  updateClock()
  clockTimer   = setInterval(updateClock, 1_000)
  refreshTimer = setInterval(() => router.reload({ only: ['intakes'] }), 15_000)
})

onUnmounted(() => {
  clearInterval(clockTimer)
  clearInterval(refreshTimer)
})

// ── Formatters ───────────────────────────────────────────────────────────────

function formatTime(dateStr: string): string {
  const d = new Date(dateStr)
  return `${String(d.getHours()).padStart(2, '0')}:${String(d.getMinutes()).padStart(2, '0')}`
}
</script>
