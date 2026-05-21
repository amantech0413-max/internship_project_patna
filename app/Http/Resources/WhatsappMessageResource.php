<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WhatsappMessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student_id' => $this->student_id,
            'student_name' => $this->whenLoaded('student', fn () => $this->student->student_name),
            'student_code' => $this->whenLoaded('student', fn () => $this->student->student_code),
            'internship_group_id' => $this->internship_group_id,
            'group_name' => $this->whenLoaded('group', fn () => $this->group->name),
            'mobile' => $this->mobile,
            'message_body' => $this->message_body,
            'status' => $this->status?->value,
            'whatsapp_message_id' => $this->whatsapp_message_id,
            'error_message' => $this->error_message,
            'retry_count' => $this->retry_count,
            'max_retries' => $this->max_retries,
            'can_retry' => $this->canRetry(),
            'sent_at' => $this->sent_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
