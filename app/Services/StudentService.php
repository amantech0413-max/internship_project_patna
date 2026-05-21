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
        protected StudentRepositoryInterface $students
    ) {}

    /** Public self-registration — no login, no college, no added_by. */
    public function register(array $data, ?UploadedFile $photo = null, ?UploadedFile $idProof = null): Student
    {
        return $this->createStudentRecord($data, $photo, $idProof, StudentStatus::Pending, null);
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
                'student_name' => $data['name'] ?? $data['student_name'],
                'father_name' => $data['father_name'] ?? null,
                'university_roll_no' => $data['university_roll_no'] ?? null,
                'college_roll_no' => $data['college_roll_no'] ?? null,
                'college_name' => $data['college_name'] ?? null,
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

    protected function isProfileComplete(array $data, ?UploadedFile $photo, ?UploadedFile $idProof): bool
    {
        $required = ['father_name', 'subject', 'semester', 'address'];

        foreach ($required as $field) {
            if (empty($data[$field])) {
                return false;
            }
        }

        return ($photo || ! empty($data['photo_path']))
            && ($idProof || ! empty($data['id_proof_path']));
    }
}
