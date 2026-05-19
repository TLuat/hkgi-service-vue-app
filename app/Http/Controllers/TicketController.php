<?php

namespace App\Http\Controllers;

use App\Helpers\ActivityLogger;
use App\Models\ServiceTicket;
use App\Models\VehicleAlert;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    private const CAN_CREATE  = ['owner', 'admin', 'manager', 'service_advisor'];
    private const CAN_MOVE    = ['owner', 'admin', 'manager', 'dispatcher', 'technician'];
    private const OWNER_ADMIN = ['owner', 'admin'];

    // ── store ──────────────────────────────────────────────────────────────

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        abort_unless(in_array($user->role, self::CAN_CREATE), 403);

        $data = $request->validate([
            'department'          => 'required|in:Bảo dưỡng,Sửa chữa,Đồng sơn,Rửa xe',
            'service_job_type'    => 'required|in:maintenance,repair,paint,wash',
            'customer_name'       => 'required|string|max:255',
            'license_plate'       => 'required|string|max:20',
            'model'               => 'required|string|max:255',
            'phone_number'        => 'required|string|max:20',
            'kanban_stage'        => 'required|in:Mới tiếp nhận,Chờ điều phối,Đang thực hiện,Tạm dừng,Chờ phụ tùng,Chờ kiểm tra cuối,Chờ giao xe',
            'priority'            => 'required|in:Thấp,Trung bình,Cao,Khẩn',
            'check_in_at'         => 'required|date',
            // optional
            'service_group_id'    => 'sometimes|nullable|uuid',
            'advisor'             => 'sometimes|nullable|string|max:255',
            'dispatcher'          => 'sometimes|nullable|string|max:255',
            'technician'          => 'sometimes|nullable|string|max:255',
            'technician_user_id'  => 'sometimes|nullable|uuid',
            'bay_id'              => 'sometimes|nullable|string|max:50',
            'source'              => 'sometimes|nullable|string|max:255',
            'status'              => 'sometimes|nullable|string|max:255',
            'due_at'              => 'sometimes|nullable|date',
            'inspection_due_date' => 'sometimes|nullable|date',
            'combine_maintenance' => 'sometimes|boolean',
            'combine_paint'       => 'sometimes|boolean',
            'has_wash'            => 'sometimes|boolean',
            'insurance'           => 'sometimes|boolean',
            'waiting_parts'       => 'sometimes|boolean',
            'pause_reason'        => 'sometimes|nullable|string|max:500',
            'parts_reason'        => 'sometimes|nullable|string|max:500',
            'delay_reason'        => 'sometimes|nullable|string|max:500',
            'concern'             => 'sometimes|nullable|string',
            'note'                => 'sometimes|nullable|string',
        ]);

        $licensePlate = strtoupper(str_replace(' ', '', $data['license_plate']));
        $data['license_plate'] = $licensePlate;

        $vehicleAlert = VehicleAlert::where('license_plate', $licensePlate)->first();
        if ($vehicleAlert) {
            $data['vehicle_alert'] = $vehicleAlert->only([
                'customer_name', 'phone_number', 'invoice_no',
                'contract_no', 'points',
            ]);
        }

        $ticket = ServiceTicket::create($data);

        ActivityLogger::log(
            'Tạo phiếu',
            "Tạo phiếu cho {$licensePlate}",
            $user->name,
            'ticket',
            $ticket->id,
        );

        return redirect('/')->with('success', "Đã tạo phiếu cho {$licensePlate}");
    }

    // ── updateStage ────────────────────────────────────────────────────────

    public function updateStage(Request $request, ServiceTicket $ticket): RedirectResponse
    {
        $user = $request->user();

        abort_unless(in_array($user->role, self::CAN_MOVE), 403);

        $data = $request->validate([
            'kanban_stage' => 'required|in:Mới tiếp nhận,Chờ điều phối,Đang thực hiện,Tạm dừng,Chờ phụ tùng,Chờ kiểm tra cuối,Chờ giao xe',
        ]);

        $newStage    = $data['kanban_stage'];
        $prevStage   = $ticket->kanban_stage;
        $updates     = ['kanban_stage' => $newStage];

        if ($newStage === 'Đang thực hiện' && ! $ticket->actual_started_at) {
            $updates['actual_started_at'] = now();
        }

        if ($newStage === 'Chờ giao xe' && ! $ticket->completed_at) {
            $updates['completed_at'] = now();
        }

        // Moving a ticket OUT of 'Chờ giao xe' means the vehicle was accepted/delivered
        if ($prevStage === 'Chờ giao xe' && $newStage !== 'Chờ giao xe' && ! $ticket->delivered_at) {
            $updates['delivered_at'] = now();
        }

        $ticket->update($updates);

        ActivityLogger::log(
            'Cập nhật giai đoạn',
            "Chuyển {$ticket->license_plate}: '{$prevStage}' → '{$newStage}'",
            $user->name,
            'ticket',
            $ticket->id,
        );

        return back()->with('success', "Đã chuyển sang '{$newStage}'");
    }

    // ── updatePlan ─────────────────────────────────────────────────────────

    public function updatePlan(Request $request, ServiceTicket $ticket): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'advisor'            => 'sometimes|nullable|string|max:255',
            'dispatcher'         => 'sometimes|nullable|string|max:255',
            'technician'         => 'sometimes|nullable|string|max:255',
            'technician_user_id' => 'sometimes|nullable|uuid',
            'bay_id'             => 'sometimes|nullable|string|max:50',
            'priority'           => 'sometimes|in:Thấp,Trung bình,Cao,Khẩn',
            'due_at'             => 'sometimes|nullable|date',
            'delay_reason'       => 'sometimes|nullable|string|max:500',
            'pause_reason'       => 'sometimes|nullable|string|max:500',
            'parts_reason'       => 'sometimes|nullable|string|max:500',
            'insurance'          => 'sometimes|boolean',
            'waiting_parts'      => 'sometimes|boolean',
            'concern'            => 'sometimes|nullable|string',
            'note'               => 'sometimes|nullable|string',
        ]);

        $ticket->update($data);

        ActivityLogger::log(
            'Cập nhật kế hoạch',
            "Cập nhật thông tin phiếu {$ticket->license_plate}",
            $user->name,
            'ticket',
            $ticket->id,
        );

        return back()->with('success', 'Cập nhật thành công');
    }

    // ── destroy ────────────────────────────────────────────────────────────

    public function destroy(Request $request, ServiceTicket $ticket): RedirectResponse
    {
        $user = $request->user();

        abort_unless(in_array($user->role, self::OWNER_ADMIN), 403);

        $licensePlate = $ticket->license_plate;
        $ticketId     = $ticket->id;

        $ticket->delete();

        ActivityLogger::log(
            'Xóa phiếu',
            "Đã xóa phiếu {$licensePlate}",
            $user->name,
            'ticket',
            $ticketId,
        );

        return redirect('/')->with('success', "Đã xóa phiếu {$licensePlate}");
    }
}