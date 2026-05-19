<?php

namespace App\Models;

use App\Traits\CamelCaseSerialization;
use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    use CamelCaseSerialization;
    const CREATED_AT = null;

    protected $fillable = [
        'storage_mode',
        'google_sheet_id',
        'synced_at',
    ];

    protected function casts(): array
    {
        return [
            'synced_at' => 'datetime',
        ];
    }
}