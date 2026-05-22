<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use App\Support\AppliesListSorting;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class StaffUserService
{
    use AppliesListSorting;

    public function list(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = User::query()
            ->with('roleModel')
            ->whereHas('roleModel', fn ($q) => $q->where('is_assignable', true));

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $this->applyListSorting($query, $filters, ['name', 'email', 'phone', 'created_at']);

        return $query->paginate($perPage);
    }

    public function create(array $data): User
    {
        $role = $this->resolveAssignableRole((int) $data['role_id']);

        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'role_id' => $role->id,
            'password' => Hash::make($data['password']),
            'is_active' => $data['is_active'] ?? true,
        ])->load('roleModel');
    }

    public function update(User $user, array $data): User
    {
        if (! $user->roleModel?->is_assignable) {
            abort(404);
        }

        $role = $this->resolveAssignableRole((int) ($data['role_id'] ?? $user->role_id));

        $payload = [
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'is_active' => $data['is_active'] ?? true,
            'role_id' => $role->id,
        ];

        if (! empty($data['password'])) {
            $payload['password'] = Hash::make($data['password']);
        }

        $user->update($payload);

        return $user->fresh(['roleModel']);
    }

    public function delete(User $user): void
    {
        if (! $user->roleModel?->is_assignable) {
            abort(404);
        }

        $user->delete();
    }

    public function find(int $id): ?User
    {
        return User::query()
            ->with('roleModel')
            ->whereHas('roleModel', fn ($q) => $q->where('is_assignable', true))
            ->find($id);
    }

    protected function resolveAssignableRole(?int $roleId): Role
    {
        if (! $roleId) {
            throw ValidationException::withMessages([
                'role_id' => ['Please select a role for this staff user.'],
            ]);
        }

        $role = Role::assignable()->find($roleId);

        if (! $role) {
            throw ValidationException::withMessages([
                'role_id' => ['Invalid or non-assignable role.'],
            ]);
        }

        return $role;
    }
}
