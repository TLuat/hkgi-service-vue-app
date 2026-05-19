<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_alerts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('license_plate')->unique();
            $table->string('customer_name');
            $table->string('phone_number');
            $table->string('invoice_no')->nullable();
            $table->string('contract_no')->nullable();
            $table->integer('points')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_alerts');
    }
};