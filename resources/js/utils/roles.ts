import type { AppUserRole, AppSectionKey, KanbanStage } from '../types'

// ── Labels ──────────────────────────────────────────────────────────────────

const ROLE_LABEL_MAP: Record<AppUserRole, string> = {
  owner:           'Chủ',
  admin:           'Quản trị',
  manager:         'Quản lý',
  service_advisor: 'Cố vấn DV',
  dispatcher:      'Điều phối',
  technician:      'Kỹ thuật viên',
  customer_care:   'Chăm sóc KH',
}

export function roleLabel(role: AppUserRole): string {
  return ROLE_LABEL_MAP[role]
}

// ── Permission helpers ──────────────────────────────────────────────────────

export function canCreateTicket(role: AppUserRole): boolean {
  return ['owner', 'admin', 'manager', 'service_advisor'].includes(role)
}

export function canMoveKanban(role: AppUserRole): boolean {
  return ['owner', 'admin', 'manager', 'dispatcher', 'technician'].includes(role)
}

export function canViewCustomers(role: AppUserRole): boolean {
  return ['owner', 'admin', 'manager', 'customer_care'].includes(role)
}

// ── Section access ──────────────────────────────────────────────────────────

const ALL_SECTIONS: AppSectionKey[] = [
  'dashboard', 'intake', 'intake_display', 'kanban',
  'departments', 'customers', 'accounts', 'control',
]

const VALID_SECTION_SET = new Set<string>(ALL_SECTIONS)

const ROLE_DEFAULT_SECTIONS: Record<AppUserRole, AppSectionKey[]> = {
  owner:           ALL_SECTIONS,
  admin:           ALL_SECTIONS,
  manager:         ['dashboard', 'intake', 'kanban', 'departments', 'customers'],
  service_advisor: ['dashboard', 'intake', 'kanban'],
  dispatcher:      ['dashboard', 'kanban'],
  technician:      ['dashboard', 'kanban'],
  customer_care:   ['dashboard', 'customers'],
}

/**
 * Returns the sections a user can access.
 * owner/admin always get all sections.
 * For other roles: use their custom sections list if set, otherwise fall back
 * to the role's defaults.
 */
export function availableSectionsForRole(
  role: AppUserRole,
  customSections: string[] | null | undefined,
): AppSectionKey[] {
  if (role === 'owner' || role === 'admin') return ALL_SECTIONS

  if (customSections && customSections.length > 0) {
    return customSections.filter((s): s is AppSectionKey => VALID_SECTION_SET.has(s))
  }

  return ROLE_DEFAULT_SECTIONS[role]
}

// ── Kanban stages (ordered) ─────────────────────────────────────────────────

export const KANBAN_STAGES: KanbanStage[] = [
  'Mới tiếp nhận',
  'Chờ điều phối',
  'Đang thực hiện',
  'Tạm dừng',
  'Chờ phụ tùng',
  'Chờ kiểm tra cuối',
  'Chờ giao xe',
]

// ── Misc display constants ──────────────────────────────────────────────────

export const ROLE_COLORS: Record<AppUserRole, string> = {
  owner:           'purple',
  admin:           'deep-orange',
  manager:         'blue',
  service_advisor: 'teal',
  dispatcher:      'green',
  technician:      'grey',
  customer_care:   'pink',
}

export const PRIORITY_COLORS: Record<string, string> = {
  'Thấp':       'grey',
  'Trung bình': 'blue',
  'Cao':        'orange',
  'Khẩn':       'red',
}

export const DEPARTMENTS = ['Bảo dưỡng', 'Sửa chữa', 'Đồng sơn', 'Rửa xe'] as const

// ── CSS class helpers ───────────────────────────────────────────────────────

const PRIORITY_CSS: Record<string, string> = {
  'Thấp':       'chip-priority-low',
  'Trung bình': 'chip-priority-medium',
  'Cao':        'chip-priority-high',
  'Khẩn':       'chip-priority-urgent',
}

const STAGE_CSS: Record<string, string> = {
  'Mới tiếp nhận':      'chip-stage-new',
  'Chờ điều phối':      'chip-stage-dispatch',
  'Đang thực hiện':     'chip-stage-inprogress',
  'Tạm dừng':           'chip-stage-paused',
  'Chờ phụ tùng':       'chip-stage-parts',
  'Chờ kiểm tra cuối':  'chip-stage-inspect',
  'Chờ giao xe':        'chip-stage-handoff',
}

const KANBAN_CARD_CSS: Record<string, string> = {
  'Thấp':       'kanban-card--low',
  'Trung bình': 'kanban-card--medium',
  'Cao':        'kanban-card--high',
  'Khẩn':       'kanban-card--urgent',
}

export function priorityChipClass(priority: string): string {
  return PRIORITY_CSS[priority] ?? ''
}

export function stageChipClass(stage: string): string {
  return STAGE_CSS[stage] ?? ''
}

export function kanbanCardClass(priority: string): string {
  return KANBAN_CARD_CSS[priority] ?? ''
}

export function isTicketOverdue(ticket: { dueAt: string | null; kanbanStage: KanbanStage }): boolean {
  if (!ticket.dueAt || ticket.kanbanStage === 'Chờ giao xe') return false
  return new Date(ticket.dueAt) < new Date()
}