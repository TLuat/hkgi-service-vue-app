<?php

namespace Database\Seeders;

use App\Models\PendingIntake;
use App\Models\ServiceTicket;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Users ──────────────────────────────────────────────────────────
        User::updateOrCreate(
            ['username' => 'owner'],
            [
                'name'     => 'Chủ hệ thống',
                'pin_hash' => Hash::make('1234'),
                'role'     => 'owner',
            ]
        );

        User::updateOrCreate(
            ['username' => 'cvdv'],
            [
                'name'     => 'CVDV Minh',
                'pin_hash' => Hash::make('1234'),
                'role'     => 'service_advisor',
            ]
        );

        // ── Service Tickets ────────────────────────────────────────────────
        ServiceTicket::create([
            'department'          => 'Bảo dưỡng',
            'service_job_type'    => 'maintenance',
            'customer_name'       => 'Nguyễn Văn A',
            'license_plate'       => '51A-12345',
            'model'               => 'Toyota Vios',
            'phone_number'        => '0901234567',
            'kanban_stage'        => 'Mới tiếp nhận',
            'priority'            => 'Trung bình',
            'check_in_at'         => now(),
            'has_wash'            => true,
        ]);

        ServiceTicket::create([
            'department'          => 'Sửa chữa',
            'service_job_type'    => 'repair',
            'customer_name'       => 'Trần Thị B',
            'license_plate'       => '30H-56789',
            'model'               => 'Honda CR-V',
            'phone_number'        => '0912345678',
            'kanban_stage'        => 'Đang thực hiện',
            'priority'            => 'Cao',
            'check_in_at'         => now()->subHours(2),
            'actual_started_at'   => now()->subHour(),
            'insurance'           => true,
        ]);

        ServiceTicket::create([
            'department'          => 'Sửa chữa',
            'service_job_type'    => 'repair',
            'customer_name'       => 'Lê Văn C',
            'license_plate'       => '43A-99999',
            'model'               => 'Mazda CX-5',
            'phone_number'        => '0923456789',
            'kanban_stage'        => 'Chờ phụ tùng',
            'priority'            => 'Thấp',
            'check_in_at'         => now()->subHours(5),
            'actual_started_at'   => now()->subHours(4),
            'paused_at'           => now()->subHours(3),
            'parts_reason'        => 'Chờ lọc gió điều hòa',
            'waiting_parts'       => true,
        ]);

        // ── Pending Intakes ────────────────────────────────────────────────
        PendingIntake::create([
            'customer_name'       => 'Phạm Thị D',
            'phone_number'        => '0934567890',
            'license_plate'       => '51G-88888',
            'model'               => 'Kia Morning',
            'is_appointment'      => true,
            'status'              => 'Khách hẹn',
            'appointment_at'      => now()->addHours(2),
            'combine_maintenance' => true,
            'has_wash'            => true,
        ]);

        PendingIntake::create([
            'customer_name'  => 'Hoàng Văn E',
            'phone_number'   => '0945678901',
            'license_plate'  => '29A-77777',
            'model'          => 'Ford Ranger',
            'is_appointment' => false,
            'status'         => 'Khách không hẹn',
            'arrived_at'     => now(),
        ]);
    }
}