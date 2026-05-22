<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Requests\Api\V1\Admin\StoreStaffStudentRequest;
use App\Http\Resources\BulkStudentResource;
use App\Services\BulkStudentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StaffStudentController extends Controller
{
    public function __construct(protected BulkStudentService $bulkStudents) {}

    public function index(Request $request): JsonResponse
    {
        $paginator = $this->bulkStudents->list(
            $request->only(['search', 'college_id', 'mobile', 'date_from', 'date_to', 'sort_by', 'sort_dir']),
            (int) $request->get('per_page', 10),
            $request->user(),
            $request->boolean('mine', ! $request->user()->isAdmin())
        );

        return $this->success(BulkStudentResource::collection($paginator));
    }

    public function store(StoreStaffStudentRequest $request): JsonResponse
    {
        $entry = $this->bulkStudents->storeEntry(
            (int) $request->college_id,
            $request->student_name,
            $request->mobile_number,
            $request->user()->id
        );

        $entry->load(['college:id,college_name', 'creator:id,name', 'creator.roleModel:id,is_assignable']);

        return $this->success(new BulkStudentResource($entry), 'Student entry saved successfully.', 201);
    }
}
