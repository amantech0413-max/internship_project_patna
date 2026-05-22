<?php

namespace App\Services;

use App\Enums\InternshipMode;
use App\Enums\StudentStatus;
use App\Helpers\StudentCodeGenerator;
use App\Models\Student;
use App\Repositories\Contracts\StudentRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class StudentService
{
    public function __construct(
        protected StudentRepositoryInterface $students,
        protected StudentPaymentService $payments
    ) {}

    /** Public self-registration — no login, no added_by; documents optional via admin later. */
    public function register(array $data, ?UploadedFile $paymentScreenshot = null): Student
    {
        $data['internship_mode'] = $data['internship_mode'] ?? InternshipMode::Online->value;
        $offline = ! empty($data['payment_mode_offline']);

        return DB::transaction(function () use ($data, $paymentScreenshot, $offline) {
            $student = $this->createStudentRecord($data, null, null, StudentStatus::Pending, null);

            $this->payments->createForRegistration(
                $student,
                $offline,
                $offline ? null : ($data['transaction_id'] ?? null),
                $offline ? null : $paymentScreenshot
            );

            return $student->fresh(['college', 'payment']);
        });
    }

    /** Admin/staff full student create — sets added_by (created_by). */
    public function createByAdmin(
        array $data,
        ?UploadedFile $photo = null,
        ?UploadedFile $idProof = null,
        ?int $adminId = null,
        ?int $createdBy = null
    ): Student {
        $status = isset($data['status'])
            ? StudentStatus::from($data['status'])
            : StudentStatus::Approved;

        $student = $this->createStudentRecord(
            $data,
            $photo,
            $idProof,
            $status,
            $createdBy ?? $adminId
        );

        if ($status === StudentStatus::Approved && $adminId) {
            $student->update([
                'approved_at' => now(),
                'approved_by' => $adminId,
            ]);
        }

        return $student->fresh(['creator', 'college', 'groups']);
    }

    protected function createStudentRecord(
        array $data,
        ?UploadedFile $photo,
        ?UploadedFile $idProof,
        StudentStatus $status,
        ?int $createdBy
    ): Student {
        return DB::transaction(function () use ($data, $photo, $idProof, $status, $createdBy) {
            $studentData = [
                'user_id' => null,
                'college_id' => $data['college_id'] ?? null,
                'created_by' => $createdBy,
                'student_code' => StudentCodeGenerator::generateStudentCode(),
                'registration_no' => $data['registration_no'] ?? null,
                'student_name' => $data['name'] ?? $data['student_name'],
                'father_name' => $data['father_name'] ?? null,
                'university_roll_no' => $data['university_roll_no'] ?? null,
                'college_roll_no' => $data['college_roll_no'] ?? null,
                'subject' => $data['subject'] ?? null,
                'semester' => $data['semester'] ?? null,
                'mobile_number' => $data['mobile'] ?? $data['mobile_number'],
                'email' => $data['email'] ?? null,
                'internship_mode' => $data['internship_mode'] ?? InternshipMode::Online->value,
                'address' => $data['address'] ?? null,
                'status' => $status,
                'profile_completed' => $this->isProfileComplete($data, $photo, $idProof),
            ];

            if ($photo) {
                $studentData['photo_path'] = $photo->store('students/photos', 'public');
            }

            if ($idProof) {
                $studentData['id_proof_path'] = $idProof->store('students/id_proofs', 'public');
            }

            return $this->students->create($studentData);
        });
    }

    public function updateByAdmin(Student $student, array $data, ?UploadedFile $photo = null, ?UploadedFile $idProof = null, ?int $adminId = null): Student
    {
        $data = $this->mapAdminPayloadToStudentColumns($data);

        if ($photo) {
            $data['photo_path'] = $photo->store('students/photos', 'public');
        }

        if ($idProof) {
            $data['id_proof_path'] = $idProof->store('students/id_proofs', 'public');
        }

        if (isset($data['status'])) {
            $newStatus = StudentStatus::from($data['status']);
            if ($newStatus === StudentStatus::Approved && $adminId) {
                $data['approved_at'] = now();
                $data['approved_by'] = $adminId;
                $data['rejection_reason'] = null;
            }
            $data['status'] = $newStatus->value;
        }

        unset($data['password'], $data['password_confirmation']);

        $student = $this->students->update($student, array_filter($data, fn ($v) => $v !== null));
        $student->update([
            'profile_completed' => $this->isProfileComplete($student->fresh()->toArray(), $photo, $idProof),
        ]);

        return $student->fresh(['creator', 'college', 'groups']);
    }

    public function updateProfile(Student $student, array $data, ?UploadedFile $photo = null, ?UploadedFile $idProof = null): Student
    {
        if ($photo) {
            $data['photo_path'] = $photo->store('students/photos', 'public');
        }

        if ($idProof) {
            $data['id_proof_path'] = $idProof->store('students/id_proofs', 'public');
        }

        $student = $this->students->update($student, $data);
        $student->update([
            'profile_completed' => $this->isProfileComplete($student->toArray(), $photo, $idProof),
        ]);

        return $student->fresh(['creator', 'college', 'groups']);
    }

    public function approve(Student $student, int $adminId): Student
    {
        return $this->students->update($student, [
            'status' => StudentStatus::Approved,
            'approved_at' => now(),
            'approved_by' => $adminId,
            'rejection_reason' => null,
        ]);
    }

    public function reject(Student $student, int $adminId, string $reason): Student
    {
        return $this->students->update($student, [
            'status' => StudentStatus::Rejected,
            'approved_by' => $adminId,
            'rejection_reason' => $reason,
        ]);
    }

    public function delete(Student $student): bool
    {
        return $this->students->delete($student);
    }

    public function forceDelete(Student $student): bool
    {
        if (! $student->trashed()) {
            $student->delete();
        }

        return (bool) $student->forceDelete();
    }

    /** @return array<string, mixed> */
    protected function mapAdminPayloadToStudentColumns(array $data): array
    {
        if (array_key_exists('name', $data)) {
            $data['student_name'] = $data['name'];
            unset($data['name']);
        }

        if (array_key_exists('mobile', $data)) {
            $data['mobile_number'] = $data['mobile'];
            unset($data['mobile']);
        }

        return $data;
    }

    protected function isProfileComplete(array $data, ?UploadedFile $photo, ?UploadedFile $idProof): bool
    {
        $required = [
            'registration_no',
            'father_name',
            'university_roll_no',
            'college_roll_no',
            'college_id',
            'subject',
        ];

        foreach ($required as $field) {
            if ($field === 'college_id') {
                if (empty($data['college_id'])) {
                    return false;
                }

                continue;
            }

            if (empty($data[$field])) {
                return false;
            }
        }

        return true;
    }
}
