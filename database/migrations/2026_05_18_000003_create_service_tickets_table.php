<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_tickets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('department', ['Bảo dưỡng', 'Sửa chữa', 'Đồng sơn', 'Rửa xe']);
            $table->uuid('service_group_id')->nullable();
            $table->enum('service_job_type', ['maintenance', 'repair', 'paint', 'wash']);
            $table->string('advisor')->nullable();
            $table->string('dispatcher')->nullable();
            $table->string('technician')->nullable();
            $table->uuid('technician_user_id')->nullable();
            $table->string('bay_id')->nullable();
            $table->string('customer_name');
            $table->string('license_plate');
            $table->string('model');
            $table->string('phone_number');
            $table->string('source')->nullable();
            $table->string('status')->nullable();
            $table->enum('kanban_stage', [
                'Mới tiếp nhận',
                'Chờ điều phối',
                'Đang thực hiện',
                'Tạm dừng',
                'Chờ phụ tùng',
                'Chờ kiểm tra cuối',
                'Chờ giao xe',
            ]);
            $table->enum('priority', ['Thấp', 'Trung bình', 'Cao', 'Khẩn']);
            $table->timestamp('check_in_at');
            $table->timestamp('due_at')->nullable();
            $table->date('inspection_due_date')->nullable();
            $table->boolean('combine_maintenance')->default(false);
            $table->boolean('combine_paint')->default(false);
            $table->boolean('has_wash')->default(false);
            $table->timestamp('actual_started_at')->nullable();
            $table->timestamp('paused_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->string('pause_reason')->nullable();
            $table->string('parts_reason')->nullable();
            $table->string('delay_reason')->nullable();
            $table->boolean('insurance')->default(false);
            $table->boolean('waiting_parts')->default(false);
            $table->text('concern')->nullable();
            $table->text('note')->nullable();
            $table->json('vehicle_alert')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_tickets');
    }
};