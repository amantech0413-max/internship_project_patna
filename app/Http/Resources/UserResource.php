<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'role_id' => $this->role_id,
            'role' => $this->roleSlug(),
            'role_label' => $this->roleModel?->name,
            'is_super_admin' => $this->isSuperAdmin(),
            'is_active' => $this->is_active,
            'can_access_panel' => $this->canAccessPanel(),
            'permissions' => $this->effectivePermissions(),
            'created_at' => $this->created_at?->toIso8601String(),
            'role_detail' => $this->whenLoaded('roleModel', fn () => new RoleResource($this->roleModel)),
            'student' => $this->whenLoaded('student', fn () => new StudentResource($this->student)),
        ];
    }
}
