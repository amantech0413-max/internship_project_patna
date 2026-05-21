<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfferLetter extends Model
{
    protected $fillable = [
        'student_id',
        'file_path',
        'letter_no',
        'issued_date',
        'issued_by',
        'auto_generated',
    ];

    protected function casts(): array
    {
        return [
            'issued_date' => 'date',
            'auto_generated' => 'boolean',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
