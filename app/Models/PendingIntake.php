<?php

namespace App\Models;

use App\Traits\CamelCaseSerialization;
use App\Traits\HasUuidKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PendingIntake extends Model
{
    use HasFactory, HasUuidKey, CamelCaseSerialization;

    protected $fillable = [
        'customer_name',
        'phone_number',
        'license_plate',
        'model',
        'inspection_due_date',
        'combine_maintenance',
        'combine_paint',
        'has_wash',
        'receptionist',
        'is_appointment',
        'status',
        'assigned_advisor',
        'assigned_by',
        'arrived_at',
        'intake_started_at',
        'appointment_at',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'combine_maintenance'  => 'boolean',
            'combine_paint'        => 'boolean',
            'has_wash'             => 'boolean',
            'is_appointment'       => 'boolean',
            'inspection_due_date'  => 'date',
            'arrived_at'           => 'datetime',
            'intake_started_at'    => 'datetime',
            'appointment_at'       => 'datetime',
        ];
    }
}