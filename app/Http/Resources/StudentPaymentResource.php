<?php

namespace App\Http\Resources;

use App\Services\StudentPaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentPaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $screenshotUrl = app(StudentPaymentService::class)->screenshotUrl($this->screenshot_path);

        return [
            'id' => $this->id,
            'student_id' => $this->student_id,
            'amount' => (float) $this->amount,
            'transaction_id' => $this->transaction_id,
            'screenshot_path' => $this->screenshot_path,
            'screenshot_url' => $screenshotUrl,
            'payment_mode_offline' => $this->payment_mode_offline,
            'status' => $this->status?->value,
            'status_label' => $this->status?->label(),
            'status_changed_by' => $this->status_changed_by,
            'status_changed_at' => $this->status_changed_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'student' => $this->whenLoaded('student', fn () => [
                'id' => $this->student->id,
                'student_name' => $this->student->student_name,
                'registration_no' => $this->student->registration_no,
                'mobile_number' => $this->student->mobile_number,
                'college_name' => $this->student->college?->college_name,
            ]),
            'status_changer' => $this->whenLoaded('statusChanger', fn () => $this->statusChanger ? [
                'id' => $this->statusChanger->id,
                'name' => $this->statusChanger->name,
            ] : null),
        ];
    }
}
