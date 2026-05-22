<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class StudentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student_code' => $this->student_code,
            'registration_no' => $this->registration_no,
            'student_name' => $this->student_name,
            'name' => $this->student_name,
            'father_name' => $this->father_name,
            'university_roll_no' => $this->university_roll_no,
            'college_roll_no' => $this->college_roll_no,
            'college_id' => $this->college_id,
            'college_name' => $this->college?->college_name ?? $this->college_name,
            'subject' => $this->subject,
            'semester' => $this->semester,
            'mobile_number' => $this->mobile_number,
            'mobile' => $this->mobile_number,
            'email' => $this->email,
            'internship_mode' => $this->internship_mode?->value,
            'address' => $this->address,
            'photo_url' => $this->photo_path ? Storage::disk('public')->url($this->photo_path) : null,
            'id_proof_url' => $this->id_proof_path ? Storage::disk('public')->url($this->id_proof_path) : null,
            'status' => $this->status?->value,
            'profile_completed' => $this->profile_completed,
            'rejection_reason' => $this->rejection_reason,
            'approved_at' => $this->approved_at?->toIso8601String(),
            'created_by' => $this->created_by,
            'added_by' => $this->created_by,
            'added_by_user' => $this->whenLoaded('creator', function () {
                if (! $this->creator) {
                    return null;
                }

                return [
                    'id' => $this->creator->id,
                    'name' => $this->creator->name,
                    'email' => $this->creator->email,
                    'is_assignable_staff' => (bool) $this->creator->roleModel?->is_assignable,
                ];
            }),
            'college' => $this->whenLoaded('college', fn () => [
                'id' => $this->college->id,
                'college_name' => $this->college->college_name,
            ]),
            'groups' => InternshipGroupResource::collection($this->whenLoaded('groups')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
