<?php

namespace App\Http\Controllers;

use App\Helpers\ActivityLogger;
use App\Models\PendingIntake;
use App\Models\ServiceTicket;
use App\Models\VehicleAlert;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PendingIntakeController extends Controller
{
    private const CAN_CREATE = ['owner', 'admin', 'manager', 'service_advisor'];

    private const DEPT_TO_JOB = [
        'Bảo dưỡng' => 'maintenance',
        'Sửa chữa'  => 'repair',
        'Đồng sơn'  => 'paint',
        'Rửa xe'    => 'wash',
    ];

    // ── store ──────────────────────────────────────────────────────────────

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'customer_name'       => 'required|string|max:255',
            'phone_number'        => 'required|string|max:20',
            'license_plate'       => 'sometimes|nullable|string|max:20',
            'model'               => 'sometimes|nullable|string|max:255',
            'inspection_due_date' => 'sometimes|nullable|date',
            'combine_maintenance' => 'sometimes|boolean',
            'combine_paint'       => 'sometimes|boolean',
            'has_wash'            => 'sometimes|boolean',
            'receptionist'        => 'sometimes|nullable|string|max:255',
            'is_appointment'      => 'sometimes|boolean',
            'status'              => 'sometimes|in:Khách không hẹn,Khách hẹn,Đang được tiếp nhận',
            'assigned_advisor'    => 'sometimes|nullable|string|max:255',
            'assigned_by'         => 'sometimes|nullable|string|max:255',
            'arrived_at'          => 'sometimes|nullable|date',
            'intake_started_at'   => 'sometimes|nullable|date',
            'appointment_at'      => 'sometimes|nullable|date',
            'note'                => 'sometimes|nullable|string',
        ]);

        $data['status'] ??= ($data['is_appointment'] ?? false) ? 'Khách hẹn' : 'Khách không hẹn';

        $intake = PendingIntake::create($data);

        ActivityLogger::log(
            'Thêm xe chờ',
            "Thêm {$intake->customer_name} ({$intake->license_plate}) vào hàng chờ",
            $user->name,
            'intake',
            $intake->id,
        );

        return back()->with('success', 'Đã thêm vào hàng chờ tiếp nhận');
    }

    // ── update ─────────────────────────────────────────────────────────────

    public function update(Request $request, PendingIntake $intake): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'customer_name'       => 'sometimes|string|max:255',
            'phone_number'        => 'sometimes|string|max:20',
            'license_plate'       => 'sometimes|nullable|string|max:20',
            'model'               => 'sometimes|nullable|string|max:255',
            'inspection_due_date' => 'sometimes|nullable|date',
            'combine_maintenance' => 'sometimes|boolean',
            'combine_paint'       => 'sometimes|boolean',
            'has_wash'            => 'sometimes|boolean',
            'receptionist'        => 'sometimes|nullable|string|max:255',
            'is_appointment'      => 'sometimes|boolean',
            'status'              => 'sometimes|in:Khách không hẹn,Khách hẹn,Đang được tiếp nhận',
            'assigned_advisor'    => 'sometimes|nullable|string|max:255',
            'assigned_by'         => 'sometimes|nullable|string|max:255',
            'arrived_at'          => 'sometimes|nullable|date',
            'intake_started_at'   => 'sometimes|nullable|date',
            'appointment_at'      => 'sometimes|nullable|date',
            'note'                => 'sometimes|nullable|string',
        ]);

        $intake->update($data);

        ActivityLogger::log(
            'Cập nhật xe chờ',
            "Cập nhật thông tin {$intake->customer_name} ({$intake->license_plate})",
            $user->name,
            'intake',
            $intake->id,
        );

        return back()->with('success', 'Đã cập nhật thông tin');
    }

    // ── accept ─────────────────────────────────────────────────────────────

    public function accept(Request $request, PendingIntake $intake): RedirectResponse
    {
        $user = $request->user();

        abort_unless(in_array($user->role, self::CAN_CREATE), 403);

        $data = $request->validate([
            'department'          => 'required|in:Bảo dưỡng,Sửa chữa,Đồng sơn,Rửa xe',
            'advisor'             => 'required|string|max:255',
            'priority'            => 'required|in:Thấp,Trung bình,Cao,Khẩn',
            'check_in_at'         => 'required|date',
            'due_at'              => 'required|date',
            'concern'             => 'required|string',
            // optional extras
            'service_job_type'    => 'sometimes|in:maintenance,repair,paint,wash',
            'service_group_id'    => 'sometimes|nullable|uuid',
            'dispatcher'          => 'sometimes|nullable|string|max:255',
            'technician'          => 'sometimes|nullable|string|max:255',
            'technician_user_id'  => 'sometimes|nullable|uuid',
            'bay_id'              => 'sometimes|nullable|string|max:50',
            'source'              => 'sometimes|nullable|string|max:255',
            'kanban_stage'        => 'sometimes|in:Mới tiếp nhận,Chờ điều phối,Đang thực hiện,Tạm dừng,Chờ phụ tùng,Chờ kiểm tra cuối,Chờ giao xe',
            'insurance'           => 'sometimes|boolean',
            'note'                => 'sometimes|nullable|string',
        ]);

        $licensePlate = strtoupper(str_replace(' ', '', $intake->license_plate ?? ''));

        $vehicleAlert = $licensePlate
            ? VehicleAlert::where('license_plate', $licensePlate)->first()
            : null;

        $ticketData = array_merge(
            // base from intake
            [
                'customer_name'       => $intake->customer_name,
                'phone_number'        => $intake->phone_number,
                'license_plate'       => $licensePlate ?: $intake->license_plate,
                'model'               => $intake->model,
                'has_wash'            => $intake->has_wash,
                'combine_maintenance' => $intake->combine_maintenance,
                'combine_paint'       => $intake->combine_paint,
                'note'                => $intake->note,
                'kanban_stage'        => 'Mới tiếp nhận',
                'service_job_type'    => self::DEPT_TO_JOB[$data['department']] ?? 'repair',
            ],
            // overrides from request
            $data,
        );

        if ($vehicleAlert) {
            $ticketData['vehicle_alert'] = $vehicleAlert->only([
                'customer_name', 'phone_number', 'invoice_no', 'contract_no', 'points',
            ]);
        }

        $ticket = ServiceTicket::create($ticketData);

        $intake->delete();

        ActivityLogger::log(
            'Tiếp nhận xe từ hàng chờ',
            "Tiếp nhận {$licensePlate} — tạo phiếu #{$ticket->id}",
            $user->name,
            'ticket',
            $ticket->id,
        );

        return redirect('/')->with('success', "Đã tiếp nhận {$licensePlate}");
    }

    // ── destroy ────────────────────────────────────────────────────────────

    public function destroy(Request $request, PendingIntake $intake): RedirectResponse
    {
        $user = $request->user();

        $label = "{$intake->customer_name} ({$intake->license_plate})";
        $id    = $intake->id;

        $intake->delete();

        ActivityLogger::log(
            'Xóa xe chờ',
            "Đã xóa {$label} khỏi hàng chờ",
            $user->name,
            'intake',
            $id,
        );

        return back()->with('success', "Đã xóa {$label} khỏi hàng chờ");
    }
}