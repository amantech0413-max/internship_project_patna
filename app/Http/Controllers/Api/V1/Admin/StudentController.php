<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Requests\Api\V1\Admin\StoreStudentRequest;
use App\Http\Requests\Api\V1\Admin\UpdateStudentRequest;
use App\Http\Resources\StudentResource;
use App\Models\Student;
use App\Repositories\Contracts\StudentRepositoryInterface;
use App\Services\DocumentService;
use App\Services\StudentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StudentController extends Controller
{
    public function __construct(
        protected StudentRepositoryInterface $students,
        protected StudentService $studentService,
        protected DocumentService $documents
    ) {}

    public function index(Request $request): JsonResponse
    {
        $paginator = $this->students->paginate($request->only([
            'search', 'mobile', 'college_name', 'semester', 'internship_mode', 'status',
            'sort_by', 'sort_dir',
        ]), (int) $request->get('per_page', 10));

        return $this->success(StudentResource::collection($paginator));
    }

    public function store(StoreStudentRequest $request): JsonResponse
    {
        $student = $this->studentService->createByAdmin(
            $request->validated(),
            $request->file('photo'),
            $request->file('id_proof'),
            $request->user()->id,
            $request->user()->id
        );

        return $this->success(new StudentResource($student), 'Student created successfully.', 201);
    }

    public function show(int $id): JsonResponse
    {
        $student = $this->students->findById($id);

        if (! $student) {
            return $this->error('Student not found.', 404);
        }

        return $this->success(new StudentResource($student));
    }

    public function update(int $id, UpdateStudentRequest $request): JsonResponse
    {
        $student = Student::with(['creator', 'college'])->findOrFail($id);

        $updated = $this->studentService->updateByAdmin(
            $student,
            $request->validated(),
            $request->file('photo'),
            $request->file('id_proof'),
            $request->user()->id
        );

        return $this->success(new StudentResource($updated), 'Student updated successfully.');
    }

    public function byMobile(string $mobile): JsonResponse
    {
        return $this->success(
            StudentResource::collection($this->students->findByMobile($mobile)),
            'Students with same parent contact number.'
        );
    }

    public function approve(int $id, Request $request): JsonResponse
    {
        $student = Student::findOrFail($id);
        $updated = $this->studentService->approve($student, $request->user()->id);

        return $this->success(new StudentResource($updated), 'Student approved.');
    }

    public function reject(int $id, Request $request): JsonResponse
    {
        $request->validate(['reason' => ['required', 'string']]);
        $student = Student::findOrFail($id);
        $updated = $this->studentService->reject($student, $request->user()->id, $request->reason);

        return $this->success(new StudentResource($updated), 'Student rejected.');
    }

    public function destroy(int $id): JsonResponse
    {
        $student = Student::findOrFail($id);

        if (! $this->studentService->delete($student)) {
            return $this->error('Could not delete student.', 500);
        }

        return $this->success(null, 'Student moved to recycle bin.');
    }

    public function forceDestroy(int $id): JsonResponse
    {
        $student = Student::withTrashed()->findOrFail($id);

        if (! $this->studentService->forceDelete($student)) {
            return $this->error('Could not permanently delete student.', 500);
        }

        return $this->success(null, 'Student permanently deleted.');
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $students = $this->students->paginate($request->all(), 10000);

        return response()->streamDownload(function () use ($students) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Student Code', 'Name', 'Mobile', 'College',
                'Semester', 'Mode', 'Status',
            ]);

            foreach ($students as $student) {
                fputcsv($handle, [
                    $student->student_code,
                    $student->student_name,
                    $student->mobile_number,
                    $student->college_name,
                    $student->semester,
                    $student->internship_mode?->value,
                    $student->status?->value,
                ]);
            }

            fclose($handle);
        }, 'students_export.csv', ['Content-Type' => 'text/csv']);
    }

    public function generateOfferLetter(int $id, Request $request): JsonResponse
    {
        $student = Student::findOrFail($id);
        $this->documents->queueAutoOfferLetter($student, $request->user()->id);

        return $this->success(null, 'Offer letter generation queued.');
    }

    public function generateCertificate(int $id, Request $request): JsonResponse
    {
        $student = Student::findOrFail($id);
        $this->documents->queueAutoCertificate($student, $request->user()->id);

        return $this->success(null, 'Certificate generation queued.');
    }

    public function uploadCertificate(int $id, Request $request): JsonResponse
    {
        $request->validate(['file' => ['required', 'file', 'max:5120']]);
        $student = Student::findOrFail($id);
        $cert = $this->documents->uploadCertificate($student, $request->file('file'), $request->user()->id);

        return $this->success($cert, 'Certificate uploaded.', 201);
    }
}
