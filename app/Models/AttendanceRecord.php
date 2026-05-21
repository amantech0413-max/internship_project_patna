<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceRecord extends Model
{
    protected $fillable = [
        'student_id',
        'internship_group_id',
        'attendance_date',
        'checked_in_at',
        'method',
        'qr_token',
        'is_present',
    ];

    protected function casts(): array
    {
        return [
            'attendance_date' => 'date',
            'checked_in_at' => 'datetime',
            'is_present' => 'boolean',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
