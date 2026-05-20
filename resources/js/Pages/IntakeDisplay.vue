<template>
  <Head title="Màn hình chờ" />

  <div class="ds-root">

    <!-- ══ HEADER (full-width) ═════════════════════════════════════════════ -->
    <header class="ds-header">
      <div class="ds-brand">
        <v-icon size="34" color="#90CAF9">mdi-car-wrench</v-icon>
        <div>
          <div class="ds-brand-name">HKGI SERVICE</div>
          <div class="ds-brand-sub">Trung tâm dịch vụ xe</div>
        </div>
      </div>

      <div class="ds-title-block">
        <div class="ds-title">TRẠNG THÁI XE DỊCH VỤ</div>
        <div class="ds-subtitle">{{ currentDate }}</div>
      </div>

      <div class="ds-clock-block">
        <div class="ds-clock">{{ currentClock }}</div>
        <div class="ds-header-stats">
          <span><b>{{ tickets.length }}</b> xe đang phục vụ</span>
          <span class="sep">·</span>
          <span><b>{{ intakes.length }}</b> xe chờ</span>
        </div>
      </div>
    </header>

    <!-- ══ BODY: table + queue panel ═══════════════════════════════════════ -->
    <div class="ds-body">

      <!-- ── Left: ticket table ─────────────────────────────────────────── -->
      <main class="ds-main">
        <div v-if="tickets.length === 0" class="ds-empty">
          <v-icon size="72" color="#C8D8EC">mdi-car-off</v-icon>
          <p>Chưa có xe đang được phục vụ</p>
        </div>

        <table v-else class="ds-table">
          <thead>
            <tr>
              <th class="th th-num">TT</th>
              <th class="th th-plate">BIỂN SỐ</th>
              <th class="th th-model">DÒNG XE</th>
              <th class="th th-status">TRẠNG THÁI</th>
              <th class="th th-due">GIỜ GIAO</th>
              <th class="th th-advisor">CỐ VẤN</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(ticket, i) in tickets"
              :key="ticket.id"
              :class="[
                'tr',
                i % 2 === 1 ? 'tr-alt' : '',
                ticket.kanbanStage === 'Chờ giao xe' ? 'tr-ready' : '',
                ticket.priority === 'Khẩn' ? 'tr-urgent' : '',
              ]"
            >
              <td class="td td-num">{{ i + 1 }}</td>

              <td class="td td-plate">
                <span class="plate">{{ ticket.licensePlate }}</span>
                <span v-if="ticket.priority === 'Khẩn'" class="badge-urgent">KHẨN</span>
              </td>

              <td class="td td-model">{{ (ticket.model || '—').toUpperCase() }}</td>

              <td class="td td-status">
                <span class="dot" :class="dotClass(ticket.kanbanStage)" />
                <span class="status-text" :class="statusClass(ticket.kanbanStage)">
                  {{ stageLabel(ticket) }}
                </span>
              </td>

              <td class="td td-due">
                <span v-if="ticket.dueAt" :class="isOverdue(ticket.dueAt) ? 'overdue' : 'due-time'">
                  {{ formatTime(ticket.dueAt) }}
                </span>
                <span v-else class="no-due">—</span>
              </td>

              <td class="td td-advisor">{{ shortName(ticket.advisor) }}</td>
            </tr>
          </tbody>
        </table>
      </main>

      <!-- ── Right: pending queue panel ────────────────────────────────── -->
      <aside class="ds-queue">
        <div class="queue-header">
          <v-icon size="16" color="#1E5FA0" class="mr-1">mdi-clock-outline</v-icon>
          <span>HÀNG CHỜ TIẾP NHẬN</span>
          <span class="queue-count">{{ intakes.length }}</span>
        </div>

        <div v-if="intakes.length === 0" class="queue-empty">
          <v-icon size="36" color="#C8D8EC">mdi-car-off</v-icon>
          <p>Không có xe đang chờ</p>
        </div>

        <div v-else class="queue-list">
          <div
            v-for="(intake, idx) in intakes"
            :key="intake.id"
            :class="['queue-card', intake.status === 'Đang được tiếp nhận' ? 'queue-card-active' : '']"
          >
            <div class="q-num">{{ idx + 1 }}</div>
            <div class="q-body">
              <div class="q-name">{{ intake.customerName }}</div>
              <div class="q-meta">
                <span v-if="intake.licensePlate" class="q-plate">{{ intake.licensePlate }}</span>
                <span v-if="intake.model" class="q-model">{{ intake.model }}</span>
              </div>
              <div class="q-time">
                <v-icon size="11" class="mr-1">mdi-login</v-icon>
                {{ intake.arrivedAt ? formatTime(intake.arrivedAt) : 'Chờ vào' }}
                <span v-if="intake.isAppointment" class="appt-badge">Hẹn</span>
              </div>
            </div>
            <div v-if="intake.status === 'Đang được tiếp nhận'" class="q-active-indicator">
              <span class="active-pulse" />
              <span class="active-label">Đang tiếp nhận</span>
            </div>
          </div>
        </div>
      </aside>
    </div>

  </div>
</template>

<script setup lang="ts">
import { ref, toRefs, onMounted, onUnmounted } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import DisplayLayout from '../Layouts/DisplayLayout.vue'
import type { PendingIntake, ServiceTicket, KanbanStage } from '../types'

defineOptions({ layout: DisplayLayout })

const props = withDefaults(defineProps<{
  intakes: PendingIntake[]
  tickets: ServiceTicket[]
}>(), {
  intakes: () => [],
  tickets: () => [],
})

const { intakes, tickets } = toRefs(props)

// ── Stage config ──────────────────────────────────────────────────────────────

type StageClasses = { dot: string; text: string }
const STAGE: Record<KanbanStage, StageClasses> = {
  'Mới tiếp nhận':     { dot: 'dot-new',     text: 'st-new'     },
  'Chờ điều phối':     { dot: 'dot-wait',    text: 'st-wait'    },
  'Đang thực hiện':    { dot: 'dot-active',  text: 'st-active'  },
  'Tạm dừng':          { dot: 'dot-paused',  text: 'st-paused'  },
  'Chờ phụ tùng':      { dot: 'dot-parts',   text: 'st-parts'   },
  'Chờ kiểm tra cuối': { dot: 'dot-inspect', text: 'st-inspect' },
  'Chờ giao xe':       { dot: 'dot-ready',   text: 'st-ready'   },
}

function dotClass(s: KanbanStage)    { return STAGE[s]?.dot  ?? '' }
function statusClass(s: KanbanStage) { return STAGE[s]?.text ?? '' }

function stageLabel(ticket: ServiceTicket): string {
  const { kanbanStage: s, department: d } = ticket
  if (s === 'Đang thực hiện') {
    if (d === 'Bảo dưỡng') return 'BẢO DƯỠNG'
    if (d === 'Sửa chữa')  return 'ĐANG SỬA'
    if (d === 'Đồng sơn')  return 'ĐỒNG SƠN'
    if (d === 'Rửa xe')    return 'RỬA XE'
  }
  const labels: Record<KanbanStage, string> = {
    'Mới tiếp nhận':     'MỚI TIẾP NHẬN',
    'Chờ điều phối':     'CHỜ ĐIỀU PHỐI',
    'Đang thực hiện':    'ĐANG THỰC HIỆN',
    'Tạm dừng':          'TẠM DỪNG',
    'Chờ phụ tùng':      'CHỜ PHỤ TÙNG',
    'Chờ kiểm tra cuối': 'KIỂM TRA CUỐI',
    'Chờ giao xe':       'CHỜ GIAO XE ✓',
  }
  return labels[s] ?? s.toUpperCase()
}

function isOverdue(d: string): boolean { return new Date(d).getTime() < Date.now() }

function shortName(name: string | null): string {
  if (!name) return '—'
  const parts = name.trim().split(' ')
  return parts[parts.length - 1].toUpperCase()
}

function formatTime(dateStr: string): string {
  const d = new Date(dateStr)
  return `${String(d.getHours()).padStart(2, '0')}:${String(d.getMinutes()).padStart(2, '0')}`
}

// ── Clock ─────────────────────────────────────────────────────────────────────

const WEEKDAYS = ['Chủ Nhật', 'Thứ Hai', 'Thứ Ba', 'Thứ Tư', 'Thứ Năm', 'Thứ Sáu', 'Thứ Bảy']
const currentDate  = ref('')
const currentClock = ref('')

function updateClock() {
  const now = new Date()
  const d   = String(now.getDate()).padStart(2, '0')
  const mo  = String(now.getMonth() + 1).padStart(2, '0')
  currentDate.value  = `${WEEKDAYS[now.getDay()]}, ${d}/${mo}/${now.getFullYear()}`
  currentClock.value = `${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}:${String(now.getSeconds()).padStart(2, '0')}`
}

let clockTimer: ReturnType<typeof setInterval>
let refreshTimer: ReturnType<typeof setInterval>

onMounted(() => {
  updateClock()
  clockTimer   = setInterval(updateClock, 1_000)
  refreshTimer = setInterval(() => router.reload({ only: ['tickets', 'intakes'] }), 15_000)
})

onUnmounted(() => {
  clearInterval(clockTimer)
  clearInterval(refreshTimer)
})
</script>

<style scoped>
/* ── Root grid ────────────────────────────────────────────────────────────── */
.ds-root {
  display: grid;
  grid-template-rows: auto 1fr;
  height: 100dvh;
  background: #EDF2FA;
  font-family: 'Segoe UI', Arial, sans-serif;
  overflow: hidden;
}

/* ── Header ───────────────────────────────────────────────────────────────── */
.ds-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 28px;
  background: linear-gradient(135deg, #0B2A52 0%, #0E3F80 50%, #0B2A52 100%);
  border-bottom: 3px solid #1976D2;
  gap: 16px;
}

.ds-brand {
  display: flex;
  align-items: center;
  gap: 12px;
  min-width: 200px;
}
.ds-brand-name {
  font-size: 1.45rem;
  font-weight: 900;
  color: #90CAF9;
  letter-spacing: 3px;
}
.ds-brand-sub {
  font-size: 0.68rem;
  color: #4A7FA0;
  letter-spacing: 1.5px;
  text-transform: uppercase;
  margin-top: 2px;
}

.ds-title-block { flex: 1; text-align: center; }
.ds-title {
  font-size: 1.75rem;
  font-weight: 900;
  color: #FFFFFF;
  letter-spacing: 7px;
  text-shadow: 0 2px 12px rgba(100,181,246,.4);
}
.ds-subtitle {
  font-size: 0.75rem;
  color: #5C8FBC;
  letter-spacing: 2px;
  margin-top: 3px;
}

.ds-clock-block { text-align: right; min-width: 200px; }
.ds-clock {
  font-size: 2.4rem;
  font-weight: 900;
  color: #FFD54F;
  font-family: 'Courier New', monospace;
  font-variant-numeric: tabular-nums;
  letter-spacing: 3px;
  line-height: 1;
}
.ds-header-stats {
  font-size: 0.75rem;
  color: #4A7FA0;
  margin-top: 4px;
  display: flex;
  gap: 6px;
  justify-content: flex-end;
  align-items: center;
}
.ds-header-stats b { color: #90CAF9; }
.sep { color: #1E3A5F; }

/* ── Body: 2-col layout ───────────────────────────────────────────────────── */
.ds-body {
  display: grid;
  grid-template-columns: 1fr 300px;
  overflow: hidden;
  height: 100%;
}

/* ── Main: ticket table ───────────────────────────────────────────────────── */
.ds-main {
  overflow-y: auto;
  background: #EDF2FA;
}
.ds-main::-webkit-scrollbar { width: 4px; }
.ds-main::-webkit-scrollbar-thumb { background: #B0C8E8; border-radius: 2px; }

.ds-empty {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 100%;
  color: #9BB4CC;
  gap: 12px;
  font-size: 1rem;
  letter-spacing: 1px;
}

/* ── Table ────────────────────────────────────────────────────────────────── */
.ds-table {
  width: 100%;
  border-collapse: collapse;
  table-layout: fixed;
}

thead tr {
  position: sticky;
  top: 0;
  z-index: 5;
  background: #0D3670;
}

.th {
  padding: 13px 16px;
  font-size: 0.78rem;
  font-weight: 700;
  color: #90CAF9;
  letter-spacing: 2px;
  text-align: left;
  border-bottom: 2px solid #1565C0;
  white-space: nowrap;
}

/* Column widths */
.th-num     { width: 56px;  text-align: center; }
.th-plate   { width: 175px; }
.th-model   { width: 155px; }
.th-status  { }               /* flex remaining */
.th-due     { width: 110px; text-align: center; }
.th-advisor { width: 120px; }

/* Rows */
.tr { background: #FFFFFF; border-bottom: 1px solid #DDE8F5; }
.tr-alt { background: #F4F8FF; }
.tr-ready { background: #EDF7EE !important; }
.tr-urgent { box-shadow: inset 4px 0 0 #E53935; }
.tr:hover { background: #E8F0FB !important; }

.td {
  padding: 15px 16px;
  vertical-align: middle;
  overflow: hidden;
}

/* ── Cells ────────────────────────────────────────────────────────────────── */
.td-num {
  text-align: center;
  font-size: 1rem;
  font-weight: 700;
  color: #94AAC0;
}

.td-plate {
  white-space: nowrap;
  display: flex;
  align-items: center;
  gap: 8px;
}
.plate {
  font-size: 1.45rem;
  font-weight: 900;
  font-family: 'Courier New', monospace;
  color: #0D2A50;
  letter-spacing: 1px;
}
.badge-urgent {
  font-size: 0.58rem;
  font-weight: 800;
  background: #E53935;
  color: white;
  padding: 2px 6px;
  border-radius: 4px;
  letter-spacing: 1px;
  animation: blink 1s ease-in-out infinite;
  flex-shrink: 0;
}

.td-model {
  font-size: 1.15rem;
  font-weight: 700;
  color: #2C4A6A;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.td-status {
  display: flex;
  align-items: center;
  gap: 9px;
  white-space: nowrap;
}
.dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  flex-shrink: 0;
}
.status-text {
  font-size: 1.1rem;
  font-weight: 800;
  letter-spacing: 1px;
}

.td-due {
  text-align: center;
  font-family: 'Courier New', monospace;
  font-size: 1.2rem;
  font-weight: 700;
  white-space: nowrap;
}
.due-time { color: #1A5276; }
.overdue  { color: #C0392B; animation: blink 1s ease-in-out infinite; }
.no-due   { color: #BCC8D4; }

.td-advisor {
  font-size: 1.15rem;
  font-weight: 700;
  color: #1A5276;
  letter-spacing: 1px;
  white-space: nowrap;
}

/* Status dot colors */
.dot-new     { background: #1565C0; }
.dot-wait    { background: #F9A825; }
.dot-active  { background: #0288D1; box-shadow: 0 0 6px #0288D1; animation: pulse-blue 1.5s ease-in-out infinite; }
.dot-paused  { background: #E64A19; }
.dot-parts   { background: #7B1FA2; }
.dot-inspect { background: #00695C; }
.dot-ready   { background: #2E7D32; box-shadow: 0 0 8px #43A047; animation: pulse-green 1.5s ease-in-out infinite; }

/* Status text colors */
.st-new     { color: #1565C0; }
.st-wait    { color: #F57F17; }
.st-active  { color: #0277BD; }
.st-paused  { color: #BF360C; }
.st-parts   { color: #6A1B9A; }
.st-inspect { color: #00695C; }
.st-ready   { color: #1B5E20; }

@keyframes pulse-blue {
  0%, 100% { box-shadow: 0 0 6px #0288D1; }
  50% { box-shadow: 0 0 14px #0288D1, 0 0 24px rgba(2,136,209,.3); }
}
@keyframes pulse-green {
  0%, 100% { box-shadow: 0 0 8px #43A047; }
  50% { box-shadow: 0 0 18px #43A047, 0 0 30px rgba(67,160,71,.35); }
}
@keyframes blink {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.45; }
}

/* ── Queue panel ──────────────────────────────────────────────────────────── */
.ds-queue {
  background: #FFFFFF;
  border-left: 1px solid #D0DFF0;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.queue-header {
  display: flex;
  align-items: center;
  padding: 12px 16px;
  background: #F0F6FF;
  border-bottom: 2px solid #D0DFF0;
  font-size: 0.72rem;
  font-weight: 700;
  color: #1E5FA0;
  letter-spacing: 2px;
  gap: 4px;
  flex-shrink: 0;
}
.queue-count {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 22px;
  height: 22px;
  background: #1565C0;
  color: white;
  font-size: 0.72rem;
  font-weight: 800;
  border-radius: 11px;
  padding: 0 6px;
  margin-left: auto;
}

.queue-empty {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: #B0C8E0;
  gap: 8px;
  font-size: 0.85rem;
}

.queue-list {
  flex: 1;
  overflow-y: auto;
  padding: 10px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.queue-list::-webkit-scrollbar { width: 3px; }
.queue-list::-webkit-scrollbar-thumb { background: #C5D8F0; border-radius: 2px; }

.queue-card {
  background: #F4F8FF;
  border: 1px solid #D8E8F8;
  border-radius: 10px;
  padding: 10px 12px;
  display: flex;
  align-items: flex-start;
  gap: 10px;
  transition: border-color 0.2s;
}
.queue-card-active {
  background: #F0FBF0;
  border-color: #4CAF50;
}

.q-num {
  font-size: 1rem;
  font-weight: 800;
  color: #94AAC0;
  min-width: 22px;
  text-align: center;
  padding-top: 1px;
}

.q-body { flex: 1; min-width: 0; }

.q-name {
  font-size: 0.95rem;
  font-weight: 700;
  color: #0D2A50;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.q-meta {
  display: flex;
  gap: 6px;
  margin-top: 2px;
  flex-wrap: nowrap;
}
.q-plate {
  font-size: 0.78rem;
  font-family: 'Courier New', monospace;
  color: #1565C0;
  font-weight: 600;
}
.q-model {
  font-size: 0.75rem;
  color: #7A9ABB;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.q-time {
  font-size: 0.72rem;
  color: #94AAC0;
  margin-top: 4px;
  display: flex;
  align-items: center;
}
.appt-badge {
  margin-left: 6px;
  font-size: 0.62rem;
  font-weight: 700;
  background: #E3F2FD;
  color: #1565C0;
  padding: 1px 5px;
  border-radius: 3px;
}

.q-active-indicator {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
  flex-shrink: 0;
}
.active-pulse {
  width: 10px;
  height: 10px;
  background: #4CAF50;
  border-radius: 50%;
  animation: pulse-green 1.5s ease-in-out infinite;
}
.active-label {
  font-size: 0.6rem;
  font-weight: 700;
  color: #2E7D32;
  letter-spacing: 0.5px;
  text-align: center;
  white-space: nowrap;
  writing-mode: vertical-lr;
  transform: rotate(180deg);
}
</style>