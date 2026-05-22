<?php

namespace App\Models;

use App\Enums\InternshipMode;
use App\Enums\StudentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'college_id',
        'created_by',
        'user_id',
        'student_code',
        'registration_no',
        'student_name',
        'father_name',
        'university_roll_no',
        'college_roll_no',
        'subject',
        'semester',
        'mobile_number',
        'email',
        'internship_mode',
        'address',
        'photo_path',
        'id_proof_path',
        'status',
        'profile_completed',
        'college_coordinator_id',
        'approved_at',
        'approved_by',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'internship_mode' => InternshipMode::class,
            'status' => StudentStatus::class,
            'profile_completed' => 'boolean',
            'approved_at' => 'datetime',
        ];
    }

    public function college(): BelongsTo
    {
        return $this->belongsTo(College::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(InternshipGroup::class, 'group_students')
            ->withPivot(['assignment_type', 'assigned_at']);
    }

    public function whatsappMessages(): HasMany
    {
        return $this->hasMany(WhatsappMessage::class);
    }

    public function dailyReports(): HasMany
    {
        return $this->hasMany(DailyReport::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    public function offerLetters(): HasMany
    {
        return $this->hasMany(OfferLetter::class);
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function activeGroup(): ?InternshipGroup
    {
        return $this->groups()->where('internship_groups.status', 'active')->first();
    }
}
