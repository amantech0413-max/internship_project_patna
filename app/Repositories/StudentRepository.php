<?php

namespace App\Repositories;

use App\Models\Student;
use App\Repositories\Contracts\StudentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class StudentRepository implements StudentRepositoryInterface
{
    public function findById(int $id): ?Student
    {
        return Student::with(['creator', 'college', 'groups'])->find($id);
    }

    public function findByRegistrationNo(string $registrationNo): ?Student
    {
        return $this->findByStudentCode($registrationNo);
    }

    public function findByStudentCode(string $studentCode): ?Student
    {
        return Student::with('user')->where('student_code', $studentCode)->first();
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Student::with(['creator', 'college', 'groups']);

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('student_name', 'like', "%{$search}%")
                    ->orWhere('student_code', 'like', "%{$search}%")
                    ->orWhere('mobile_number', 'like', "%{$search}%")
                    ->orWhere('college_name', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['mobile'])) {
            $query->where('mobile_number', $filters['mobile']);
        }

        if (! empty($filters['college_name'])) {
            $query->where('college_name', $filters['college_name']);
        }

        if (! empty($filters['semester'])) {
            $query->where('semester', $filters['semester']);
        }

        if (! empty($filters['internship_mode'])) {
            $query->where('internship_mode', $filters['internship_mode']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function findByMobile(string $mobile): Collection
    {
        return Student::with('user')->where('mobile_number', $mobile)->get();
    }

    public function create(array $data): Student
    {
        return Student::create($data);
    }

    public function update(Student $student, array $data): Student
    {
        $student->update($data);

        return $student->fresh(['user', 'groups']);
    }
}
