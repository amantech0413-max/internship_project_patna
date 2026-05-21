<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Resources\ExcelImportLogResource;
use App\Models\ExcelImportLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExcelImportLogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $paginator = ExcelImportLog::query()
            ->with(['college:id,college_name', 'importer:id,name'])
            ->when($request->filled('college_id'), fn ($q) => $q->where('college_id', $request->college_id))
            ->latest()
            ->paginate((int) $request->get('per_page', 15));

        return $this->success(ExcelImportLogResource::collection($paginator));
    }
}
