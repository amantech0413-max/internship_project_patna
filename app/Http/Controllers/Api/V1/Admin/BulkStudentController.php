<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Requests\Api\V1\Admin\StoreBulkStudentRequest;
use App\Http\Requests\Api\V1\Admin\UpdateBulkStudentRequest;
use App\Http\Resources\BulkStudentResource;
use App\Services\BulkStudentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BulkStudentController extends Controller
{
    public function __construct(protected BulkStudentService $bulkStudents) {}

    public function index(Request $request): JsonResponse
    {
        $paginator = $this->bulkStudents->list(
            $request->only([
                'search', 'college_id', 'mobile', 'created_by',
                'date_from', 'date_to', 'sort_by', 'sort_dir',
            ]),
            (int) $request->get('per_page', 10),
            $request->user(),
            $request->boolean('mine')
        );

        return $this->success(BulkStudentResource::collection($paginator));
    }

    public function show(int $id, Request $request): JsonResponse
    {
        $entry = $this->bulkStudents->find($id, $request->user());

        if (! $entry) {
            return $this->error('Entry not found.', 404);
        }

        return $this->success(new BulkStudentResource($entry));
    }

    public function store(StoreBulkStudentRequest $request): JsonResponse
    {
        $entry = $this->bulkStudents->storeEntry(
            (int) $request->college_id,
            $request->student_name,
            $request->mobile_number,
            $request->user()->id
        );

        $entry->load(['college:id,college_name', 'creator:id,name', 'creator.roleModel:id,is_assignable']);

        return $this->success(new BulkStudentResource($entry), 'Bulk student created.', 201);
    }

    public function update(int $id, UpdateBulkStudentRequest $request): JsonResponse
    {
        $entry = $this->bulkStudents->find($id, $request->user());

        if (! $entry) {
            return $this->error('Entry not found.', 404);
        }

        $updated = $this->bulkStudents->update($entry, $request->validated(), $request->user());

        return $this->success(new BulkStudentResource($updated), 'Bulk student updated.');
    }

    public function destroy(int $id, Request $request): JsonResponse
    {
        $entry = $this->bulkStudents->find($id, $request->user());

        if (! $entry) {
            return $this->error('Entry not found.', 404);
        }

        $this->bulkStudents->delete($entry, $request->user());

        return $this->success(null, 'Bulk student deleted.');
    }
}
