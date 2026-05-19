<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pending_intakes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('customer_name');
            $table->string('phone_number');
            $table->string('license_plate');
            $table->string('model');
            $table->date('inspection_due_date')->nullable();
            $table->boolean('combine_maintenance')->default(false);
            $table->boolean('combine_paint')->default(false);
            $table->boolean('has_wash')->default(false);
            $table->string('receptionist')->nullable();
            $table->boolean('is_appointment')->default(false);
            $table->enum('status', ['Khách không hẹn', 'Khách hẹn', 'Đang được tiếp nhận']);
            $table->string('assigned_advisor')->nullable();
            $table->string('assigned_by')->nullable();
            $table->timestamp('arrived_at')->nullable();
            $table->timestamp('intake_started_at')->nullable();
            $table->timestamp('appointment_at')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pending_intakes');
    }
};