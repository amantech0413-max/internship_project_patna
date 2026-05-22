<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BulkStudentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'college_id' => $this->college_id,
            'student_name' => $this->student_name,
            'mobile_number' => $this->mobile_number,
            'created_by' => $this->created_by,
            'added_by' => $this->created_by,
            'college' => $this->whenLoaded('college', fn () => [
                'id' => $this->college->id,
                'college_name' => $this->college->college_name,
            ]),
            'college_name' => $this->college?->college_name,
            'creator' => $this->whenLoaded('creator', fn () => [
                'id' => $this->creator->id,
                'name' => $this->creator->name,
            ]),
            'added_by_user' => $this->whenLoaded('creator', function () {
                if (! $this->creator) {
                    return null;
                }

                return [
                    'id' => $this->creator->id,
                    'name' => $this->creator->name,
                    'is_assignable_staff' => (bool) $this->creator->roleModel?->is_assignable,
                ];
            }),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
