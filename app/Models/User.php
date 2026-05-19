<?php

namespace App\Models;

use App\Traits\HasUuidKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    use HasFactory, HasUuidKey, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'pin_hash',
        'role',
        'sections',
        'is_active',
    ];

    protected $hidden = [
        'pin_hash',
    ];

    protected function casts(): array
    {
        return [
            'sections'  => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function checkPin(string $pin): bool
    {
        return Hash::check($pin, $this->pin_hash);
    }

    public function hasSection(string $section): bool
    {
        if (in_array($this->role, ['owner', 'admin'])) {
            return true;
        }

        return in_array($section, $this->sections ?? []);
    }
}