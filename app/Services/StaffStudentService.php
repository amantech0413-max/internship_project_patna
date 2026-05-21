<?php

namespace App\Services;

use App\Enums\StudentStatus;
use App\Helpers\StudentCodeGenerator;
use App\Models\Student;
use App\Repositories\Contracts\StaffStudentRepositoryInterface;

class StaffStudentService
{
    public function __construct(protected StaffStudentRepositoryInterface $students) {}

    public function list(array $filters, int $perPage = 15)
    {
        return $this->students->paginate($filters, $perPage);
    }

    public function storeEntry(int $collegeId, string $studentName, string $mobileNumber, int $createdBy): Student
    {
        return $this->students->create([
            'college_id' => $collegeId,
            'created_by' => $createdBy,
            'student_code' => StudentCodeGenerator::generateStudentCode(),
            'student_name' => $studentName,
            'mobile_number' => $mobileNumber,
            'status' => StudentStatus::Approved,
            'internship_mode' => 'online',
        ]);
    }
}
