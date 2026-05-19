<?php

namespace App\Models;

use App\Traits\CamelCaseSerialization;
use App\Traits\HasUuidKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleAlert extends Model
{
    use HasFactory, HasUuidKey, CamelCaseSerialization;

    protected $fillable = [
        'license_plate',
        'customer_name',
        'phone_number',
        'invoice_no',
        'contract_no',
        'points',
    ];

    protected function casts(): array
    {
        return [
            'points' => 'integer',
        ];
    }
}