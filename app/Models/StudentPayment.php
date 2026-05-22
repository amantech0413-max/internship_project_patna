<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentPayment extends Model
{
    protected $fillable = [
        'student_id',
        'amount',
        'transaction_id',
        'screenshot_path',
        'payment_mode_offline',
        'status',
        'status_changed_by',
        'status_changed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'payment_mode_offline' => 'boolean',
            'status' => PaymentStatus::class,
            'status_changed_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function statusChanger(): BelongsTo
    {
        return $this->belongsTo(User::class, 'status_changed_by');
    }
}
