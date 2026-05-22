<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Requests\Api\V1\Admin\StoreRoleRequest;
use App\Http\Resources\PermissionResource;
use App\Http\Resources\RoleResource;
use App\Models\Permission;
use App\Models\Role;
use App\Services\RoleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function __construct(protected RoleService $roles) {}

    public function permissions(): JsonResponse
    {
        return $this->success(PermissionResource::collection(Permission::orderBy('key')->get()));
    }

    public function assignable(): JsonResponse
    {
        return $this->success(RoleResource::collection($this->roles->assignable()));
    }

    public function index(Request $request): JsonResponse
    {
        $paginator = $this->roles->list(
            $request->only(['search', 'sort_by', 'sort_dir']),
            (int) $request->get('per_page', 10)
        );

        return $this->success(RoleResource::collection($paginator));
    }

    public function store(StoreRoleRequest $request): JsonResponse
    {
        $role = $this->roles->create($request->validated(), $request->user());

        return $this->success(new RoleResource($role), 'Role created successfully.', 201);
    }

    public function show(int $id): JsonResponse
    {
        $role = $this->roles->find($id);

        if (! $role) {
            return $this->error('Role not found.', 404);
        }

        return $this->success(new RoleResource($role));
    }

    public function update(int $id, StoreRoleRequest $request): JsonResponse
    {
        $role = Role::with('permissions')->find($id);

        if (! $role) {
            return $this->error('Role not found.', 404);
        }

        $updated = $this->roles->update($role, $request->validated());

        return $this->success(new RoleResource($updated), 'Role updated successfully.');
    }

    public function destroy(int $id): JsonResponse
    {
        $role = Role::find($id);

        if (! $role) {
            return $this->error('Role not found.', 404);
        }

        $this->roles->delete($role);

        return $this->success(null, 'Role deleted successfully.');
    }
}
