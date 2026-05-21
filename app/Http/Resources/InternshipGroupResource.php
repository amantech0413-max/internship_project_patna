<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InternshipGroupResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'semester' => $this->semester,
            'subject' => $this->subject,
            'college_name' => $this->college_name,
            'internship_mode' => $this->internship_mode?->value,
            'whatsapp_group_link' => $this->whatsapp_group_link,
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'status' => $this->status?->value,
            'students_count' => $this->when(isset($this->students_count), $this->students_count),
            'students' => StudentResource::collection($this->whenLoaded('students')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
