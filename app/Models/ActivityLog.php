<?php

namespace App\Models;

use App\Traits\CamelCaseSerialization;
use App\Traits\HasUuidKey;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasUuidKey, CamelCaseSerialization;

    const UPDATED_AT = null;

    protected $fillable = [
        'action',
        'description',
        'performed_by',
        'entity_type',
        'entity_id',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }
}