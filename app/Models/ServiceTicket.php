<?php

namespace App\Models;

use App\Traits\CamelCaseSerialization;
use App\Traits\HasUuidKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceTicket extends Model
{
    use HasFactory, HasUuidKey, CamelCaseSerialization;

    protected $fillable = [
        'department',
        'service_group_id',
        'service_job_type',
        'advisor',
        'dispatcher',
        'technician',
        'technician_user_id',
        'bay_id',
        'customer_name',
        'license_plate',
        'model',
        'phone_number',
        'source',
        'status',
        'kanban_stage',
        'priority',
        'check_in_at',
        'due_at',
        'inspection_due_date',
        'combine_maintenance',
        'combine_paint',
        'has_wash',
        'actual_started_at',
        'paused_at',
        'completed_at',
        'delivered_at',
        'pause_reason',
        'parts_reason',
        'delay_reason',
        'insurance',
        'waiting_parts',
        'concern',
        'note',
        'vehicle_alert',
    ];

    protected function casts(): array
    {
        return [
            'combine_maintenance' => 'boolean',
            'combine_paint'       => 'boolean',
            'has_wash'            => 'boolean',
            'insurance'           => 'boolean',
            'waiting_parts'       => 'boolean',
            'vehicle_alert'       => 'array',
            'check_in_at'         => 'datetime',
            'due_at'              => 'datetime',
            'inspection_due_date' => 'date',
            'actual_started_at'   => 'datetime',
            'paused_at'           => 'datetime',
            'completed_at'        => 'datetime',
            'delivered_at'        => 'datetime',
        ];
    }

    public function scopeOpen($query)
    {
        return $query->whereNotIn('kanban_stage', ['Chờ giao xe']);
    }

    public function scopeUrgent($query)
    {
        return $query->where('priority', 'Khẩn')
                     ->whereNotIn('kanban_stage', ['Chờ giao xe']);
    }
}