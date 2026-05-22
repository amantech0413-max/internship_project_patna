<?php

namespace App\Models;

use App\Enums\WhatsappMessageStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsappMessage extends Model
{
    protected $fillable = [
        'student_id',
        'bulk_student_id',
        'college_id',
        'internship_group_id',
        'sent_by',
        'mobile',
        'message_body',
        'status',
        'whatsapp_message_id',
        'error_message',
        'retry_count',
        'max_retries',
        'sent_at',
        'next_retry_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => WhatsappMessageStatus::class,
            'sent_at' => 'datetime',
            'next_retry_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function bulkStudent(): BelongsTo
    {
        return $this->belongsTo(BulkStudent::class);
    }

    public function college(): BelongsTo
    {
        return $this->belongsTo(College::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(InternshipGroup::class, 'internship_group_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    public function canRetry(): bool
    {
        return $this->status === WhatsappMessageStatus::Failed
            && $this->retry_count < $this->max_retries;
    }
}
