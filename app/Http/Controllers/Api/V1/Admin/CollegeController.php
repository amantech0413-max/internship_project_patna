<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Requests\Api\V1\Admin\StoreCollegeRequest;
use App\Http\Resources\CollegeResource;
use App\Services\CollegeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CollegeController extends Controller
{
    public function __construct(protected CollegeService $colleges) {}

    public function index(Request $request): JsonResponse
    {
        $paginator = $this->colleges->list(
            $request->only(['search', 'status', 'sort_by', 'sort_dir']),
            (int) $request->get('per_page', 10)
        );

        return $this->success(CollegeResource::collection($paginator));
    }

    public function dropdown(): JsonResponse
    {
        return $this->success(CollegeResource::collection($this->colleges->dropdown()));
    }

    public function store(StoreCollegeRequest $request): JsonResponse
    {
        $college = $this->colleges->store($request->validated());

        return $this->success(new CollegeResource($college), 'College created successfully.', 201);
    }

    public function show(int $id): JsonResponse
    {
        $college = $this->colleges->show($id);

        if (! $college) {
            return $this->error('College not found.', 404);
        }

        return $this->success(new CollegeResource($college));
    }

    public function update(int $id, StoreCollegeRequest $request): JsonResponse
    {
        $college = $this->colleges->update($id, $request->validated());

        if (! $college) {
            return $this->error('College not found.', 404);
        }

        return $this->success(new CollegeResource($college), 'College updated successfully.');
    }

    public function destroy(int $id): JsonResponse
    {
        if (! $this->colleges->delete($id)) {
            return $this->error('College not found.', 404);
        }

        return $this->success(null, 'College deleted successfully.');
    }
}
