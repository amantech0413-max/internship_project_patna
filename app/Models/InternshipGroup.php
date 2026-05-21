<?php

namespace App\Models;

use App\Enums\GroupStatus;
use App\Enums\InternshipMode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InternshipGroup extends Model
{
    protected $fillable = [
        'name',
        'semester',
        'subject',
        'college_name',
        'internship_mode',
        'whatsapp_group_link',
        'start_date',
        'end_date',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'internship_mode' => InternshipMode::class,
            'status' => GroupStatus::class,
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'group_students')
            ->withPivot(['assignment_type', 'assigned_at']);
    }

    public function whatsappMessages(): HasMany
    {
        return $this->hasMany(WhatsappMessage::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
