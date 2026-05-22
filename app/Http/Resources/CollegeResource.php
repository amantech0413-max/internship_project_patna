<?php

namespace App\Http\Resources;

use App\Support\RegistrationPaths;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CollegeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'college_name' => $this->college_name,
            'slug' => $this->slug,
            'registration_url' => RegistrationPaths::collegeUrl($this->slug, 'short'),
            'registration_urls' => RegistrationPaths::collegeUrls($this->slug),
            'address' => $this->address,
            'contact_person' => $this->contact_person,
            'mobile_number' => $this->mobile_number,
            'status' => $this->status,
            'students_count' => $this->whenCounted('students'),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
