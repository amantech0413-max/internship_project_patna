<?php

namespace App\Http\Controllers\Api\V1\Staff;

use App\Exports\StudentSampleExport;
use App\Http\Controllers\Api\V1\Controller;
use App\Services\StaffStudentImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class StaffStudentImportController extends Controller
{
    public function __construct(protected StaffStudentImportService $import) {}

    public function uploadExcel(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv,txt', 'max:5120'],
            'college_id' => ['required', 'exists:colleges,id'],
        ]);

        $preview = $this->import->previewExcel($request->file('file'));

        return $this->success(array_merge($preview, [
            'college_id' => (int) $request->college_id,
        ]), 'Excel preview generated.');
    }

    public function previewExcel(Request $request): JsonResponse
    {
        return $this->uploadExcel($request);
    }

    public function confirmImport(Request $request): JsonResponse
    {
        $request->validate([
            'college_id' => ['required', 'exists:colleges,id'],
            'import_token' => ['required', 'string'],
            'rows' => ['nullable', 'array'],
            'rows.*.student_name' => ['required_with:rows', 'string'],
            'rows.*.mobile_number' => ['required_with:rows', 'string'],
            'file_name' => ['nullable', 'string', 'max:255'],
        ]);

        $result = $this->import->confirmImport(
            (int) $request->college_id,
            $request->import_token,
            $request->user()->id,
            $request->rows
        );

        return $this->success($result, 'Import completed.');
    }

    public function downloadSample(): BinaryFileResponse
    {
        return Excel::download(new StudentSampleExport, 'student_import_sample.xlsx');
    }
}
