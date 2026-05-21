<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Requests\Api\V1\Admin\StoreStaffStudentRequest;
use App\Http\Resources\StaffStudentResource;
use App\Services\StaffStudentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StaffStudentController extends Controller
{
    public function __construct(protected StaffStudentService $students) {}

    public function index(Request $request): JsonResponse
    {
        $paginator = $this->students->list(
            $request->only(['search', 'college_id', 'mobile']),
            (int) $request->get('per_page', 15)
        );

        return $this->success(StaffStudentResource::collection($paginator));
    }

    public function store(StoreStaffStudentRequest $request): JsonResponse
    {
        $student = $this->students->storeEntry(
            (int) $request->college_id,
            $request->student_name,
            $request->mobile_number,
            $request->user()->id
        );

        $student->load(['college:id,college_name', 'creator:id,name']);

        return $this->success(new StaffStudentResource($student), 'Student entry saved successfully.', 201);
    }
}
