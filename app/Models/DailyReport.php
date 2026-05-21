<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyReport extends Model
{
    protected $fillable = [
        'student_id',
        'report_date',
        'work_summary',
        'learnings',
        'status',
        'admin_remarks',
    ];

    protected function casts(): array
    {
        return [
            'report_date' => 'date',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
