<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Requests\Api\V1\Admin\StoreStaffUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\StaffUserService;
use App\Support\StaffPermissions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StaffUserController extends Controller
{
    public function __construct(protected StaffUserService $staffUsers) {}

    public function permissionKeys(): JsonResponse
    {
        return $this->success(StaffPermissions::accessPayload()['permissions']);
    }

    public function index(Request $request): JsonResponse
    {
        $paginator = $this->staffUsers->list(
            $request->only(['search', 'sort_by', 'sort_dir']),
            (int) $request->get('per_page', 10)
        );

        return $this->success(UserResource::collection($paginator));
    }

    public function store(StoreStaffUserRequest $request): JsonResponse
    {
        $user = $this->staffUsers->create($request->validated());

        return $this->success(new UserResource($user), 'Staff user created successfully.', 201);
    }

    public function show(int $id): JsonResponse
    {
        $user = $this->staffUsers->find($id);

        if (! $user) {
            return $this->error('Staff user not found.', 404);
        }

        return $this->success(new UserResource($user));
    }

    public function update(int $id, StoreStaffUserRequest $request): JsonResponse
    {
        $user = $this->staffUsers->find($id);

        if (! $user) {
            return $this->error('Staff user not found.', 404);
        }

        $updated = $this->staffUsers->update($user, $request->validated());

        return $this->success(new UserResource($updated), 'Staff user updated successfully.');
    }

    public function destroy(int $id): JsonResponse
    {
        $user = $this->staffUsers->find($id);

        if (! $user) {
            return $this->error('Staff user not found.', 404);
        }

        $this->staffUsers->delete($user);

        return $this->success(null, 'Staff user deleted successfully.');
    }
}
