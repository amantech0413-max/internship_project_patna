<?php

namespace App\Services;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Support\AppliesListSorting;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RoleService
{
    use AppliesListSorting;

    public function list(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = Role::query()->with('permissions')->withCount('users');

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        $this->applyListSorting($query, $filters, ['name', 'slug', 'created_at'], 'name');

        return $query->paginate($perPage);
    }

    public function assignable(): \Illuminate\Database\Eloquent\Collection
    {
        return Role::query()
            ->assignable()
            ->with('permissions')
            ->orderBy('name')
            ->get();
    }

    public function find(int $id): ?Role
    {
        return Role::with('permissions')->find($id);
    }

    public function create(array $data, ?User $creator = null): Role
    {
        $slug = $this->uniqueSlug($data['slug'] ?? $data['name']);

        $role = Role::create([
            'name' => $data['name'],
            'slug' => $slug,
            'description' => $data['description'] ?? null,
            'is_system' => false,
            'is_assignable' => true,
            'created_by' => $creator?->id,
        ]);

        $this->syncPermissions($role, $data['permission_keys'] ?? []);

        return $role->load('permissions');
    }

    public function update(Role $role, array $data): Role
    {
        if ($role->is_system) {
            throw ValidationException::withMessages([
                'role' => ['System roles cannot be modified.'],
            ]);
        }

        $payload = [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ];

        if (! empty($data['slug']) && $data['slug'] !== $role->slug) {
            $payload['slug'] = $this->uniqueSlug($data['slug'], $role->id);
        }

        $role->update($payload);
        $this->syncPermissions($role, $data['permission_keys'] ?? []);

        return $role->fresh(['permissions']);
    }

    public function delete(Role $role): void
    {
        if ($role->is_system) {
            throw ValidationException::withMessages([
                'role' => ['System roles cannot be deleted.'],
            ]);
        }

        if ($role->users()->exists()) {
            throw ValidationException::withMessages([
                'role' => ['This role is assigned to staff users. Reassign them first.'],
            ]);
        }

        $role->permissions()->detach();
        $role->delete();
    }

    public function syncPermissions(Role $role, array $keys): void
    {
        $ids = Permission::whereIn('key', $keys)->pluck('id');
        $role->permissions()->sync($ids);
    }

    protected function uniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value);
        if ($base === '') {
            $base = 'role';
        }

        $slug = $base;
        $n = 1;
        while ($this->slugExists($slug, $ignoreId)) {
            $slug = $base.'-'.$n;
            $n++;
        }

        if (in_array($slug, ['super_admin', 'admin'], true)) {
            throw ValidationException::withMessages([
                'slug' => ['This slug is reserved for system roles.'],
            ]);
        }

        return $slug;
    }

    protected function slugExists(string $slug, ?int $ignoreId): bool
    {
        $q = Role::where('slug', $slug);
        if ($ignoreId) {
            $q->where('id', '!=', $ignoreId);
        }

        return $q->exists();
    }
}
