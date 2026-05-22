<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Resources\BulkStudentResource;
use App\Services\BulkStudentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class BulkStudentReportController extends Controller
{
    public function __construct(protected BulkStudentService $bulkStudents) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['college_id', 'date_from', 'date_to', 'created_by']);
        $perPage = max(1, min(500, (int) $request->get('per_page', 10)));

        $report = $this->bulkStudents->report($filters, $request->user(), $perPage);

        /** @var LengthAwarePaginator $paginator */
        $paginator = $report['entries'];

        return $this->success([
            'summary' => $report['summary'],
            'total' => $report['total'],
            'entries' => BulkStudentResource::collection($paginator)->resolve(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }
}
