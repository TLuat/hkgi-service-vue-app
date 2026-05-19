// ── Domain union types ──────────────────────────────────────────────────────

export type AppUserRole =
  | 'owner'
  | 'admin'
  | 'manager'
  | 'service_advisor'
  | 'dispatcher'
  | 'technician'
  | 'customer_care'

export type AppSectionKey =
  | 'dashboard'
  | 'intake'
  | 'intake_display'
  | 'kanban'
  | 'departments'
  | 'customers'
  | 'accounts'
  | 'control'

export type KanbanStage =
  | 'Mới tiếp nhận'
  | 'Chờ điều phối'
  | 'Đang thực hiện'
  | 'Tạm dừng'
  | 'Chờ phụ tùng'
  | 'Chờ kiểm tra cuối'
  | 'Chờ giao xe'

export type DepartmentName = 'Bảo dưỡng' | 'Sửa chữa' | 'Đồng sơn' | 'Rửa xe'

export type TicketPriority = 'Thấp' | 'Trung bình' | 'Cao' | 'Khẩn'

export type PendingIntakeStatus =
  | 'Khách không hẹn'
  | 'Khách hẹn'
  | 'Đang được tiếp nhận'

// ── Core entities ───────────────────────────────────────────────────────────

export interface AppUser {
  id: string
  name: string
  username: string
  role: AppUserRole
  sections: string[] | null
  is_active?: boolean
}

export interface VehicleAlertData {
  customerName: string
  phoneNumber: string
  invoiceNo: string
  contractNo: string
  points: number
}

export interface ServiceTicket {
  id: string
  department: DepartmentName
  kanbanStage: KanbanStage
  priority: TicketPriority
  advisor: string
  dispatcher: string
  technician: string
  technicianUserId: string
  bayId: string
  customerName: string
  licensePlate: string
  model: string
  phoneNumber: string
  source: string
  status: string
  checkInAt: string
  dueAt: string
  inspectionDueDate: string
  combineMaintenance: boolean
  combinePaint: boolean
  hasWash: boolean
  actualStartedAt: string | null
  pausedAt: string | null
  completedAt: string | null
  deliveredAt: string | null
  pauseReason: string
  partsReason: string
  delayReason: string
  insurance: boolean
  waitingParts: boolean
  concern: string
  note: string
  vehicleAlert: VehicleAlertData | null
  createdAt: string
}

export interface PendingIntake {
  id: string
  customerName: string
  phoneNumber: string
  licensePlate: string
  model: string
  inspectionDueDate: string
  combineMaintenance: boolean
  combinePaint: boolean
  hasWash: boolean
  receptionist: string
  isAppointment: boolean
  status: PendingIntakeStatus
  assignedAdvisor: string
  assignedBy: string
  arrivedAt: string
  intakeStartedAt: string
  appointmentAt: string
  note: string
  createdAt: string
}

export interface DashboardSummary {
  openCount: number
  waitingDispatch: number
  inProgress: number
  waitingParts: number
  handoffReady: number
  urgentCases: number
}

export interface VehicleModel {
  id: string
  name: string
}

export interface AppSettings {
  storageMode: string
  googleSheetId: string | null
  syncedAt: string | null
}

export interface ActivityLogEntry {
  id: string
  action: string
  description: string
  performedBy: string
  entityType: string
  entityId: string | null
  createdAt: string
}

// ── Inertia shared props (from HandleInertiaRequests) ───────────────────────

export interface SharedProps {
  auth: { user: AppUser | null }
  flash: { success?: string; error?: string }
  [key: string]: unknown
}

// ── Page-specific props ─────────────────────────────────────────────────────

export interface DashboardProps extends SharedProps {
  tickets: ServiceTicket[]
  summary: DashboardSummary
  pendingIntakes: PendingIntake[]
  vehicleAlerts: Record<string, VehicleAlertData>
  activityLogs: ActivityLogEntry[]
  vehicleModels: string[]
  accountUsers: AppUser[]
  settings: AppSettings | null
}