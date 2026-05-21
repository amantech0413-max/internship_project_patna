<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtpVerification extends Model
{
    protected $fillable = [
        'identifier',
        'otp',
        'purpose',
        'expires_at',
        'is_used',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'is_used' => 'boolean',
        ];
    }
}
