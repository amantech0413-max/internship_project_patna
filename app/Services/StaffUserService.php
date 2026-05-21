<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\User;
use App\Support\StaffPermissions;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

class StaffUserService
{
    public function list(int $perPage = 15): LengthAwarePaginator
    {
        return User::query()
            ->where('role', UserRole::CollegeCoordinator)
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data): User
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'role' => UserRole::CollegeCoordinator,
            'password' => Hash::make($data['password']),
            'is_active' => $data['is_active'] ?? true,
            'permissions' => StaffPermissions::normalize($data['permissions'] ?? []),
        ]);
    }

    public function update(User $user, array $data): User
    {
        if ($user->role !== UserRole::CollegeCoordinator) {
            abort(404);
        }

        $payload = [
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'is_active' => $data['is_active'] ?? true,
            'permissions' => StaffPermissions::normalize($data['permissions'] ?? []),
        ];

        if (! empty($data['password'])) {
            $payload['password'] = Hash::make($data['password']);
        }

        $user->update($payload);

        return $user->fresh();
    }

    public function delete(User $user): void
    {
        if ($user->role !== UserRole::CollegeCoordinator) {
            abort(404);
        }

        $user->delete();
    }

    public function find(int $id): ?User
    {
        return User::where('role', UserRole::CollegeCoordinator)->find($id);
    }
}
