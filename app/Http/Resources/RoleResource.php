<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'is_system' => $this->is_system,
            'is_assignable' => $this->is_assignable,
            'users_count' => $this->whenCounted('users'),
            'permission_keys' => $this->whenLoaded('permissions', fn () => $this->permissions->pluck('key')->values()),
            'permissions' => $this->whenLoaded('permissions', fn () => $this->permissions->map(fn ($p) => [
                'key' => $p->key,
                'label' => $p->label,
            ])->values()),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
