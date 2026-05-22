<?php

namespace App\Repositories;

use App\Models\Student;
use App\Repositories\Contracts\StudentRepositoryInterface;
use App\Support\AppliesListSorting;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class StudentRepository implements StudentRepositoryInterface
{
    use AppliesListSorting;
    public function findById(int $id): ?Student
    {
        return Student::with(['creator.roleModel', 'college'])->find($id);
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
        $query = Student::with(['creator.roleModel', 'college', 'groups']);

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('student_name', 'like', "%{$search}%")
                    ->orWhere('student_code', 'like', "%{$search}%")
                    ->orWhere('mobile_number', 'like', "%{$search}%")
                    ->orWhereHas('college', fn ($c) => $c->where('college_name', 'like', "%{$search}%"));
            });
        }

        if (! empty($filters['mobile'])) {
            $query->where('mobile_number', $filters['mobile']);
        }

        if (! empty($filters['college_id'])) {
            $query->where('college_id', $filters['college_id']);
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

        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDir = strtolower((string) ($filters['sort_dir'] ?? 'desc')) === 'asc' ? 'asc' : 'desc';

        if ($sortBy === 'college_name') {
            $query->leftJoin('colleges', 'colleges.id', '=', 'students.college_id')
                ->select('students.*')
                ->orderBy('colleges.college_name', $sortDir);
        } else {
            $this->applyListSorting($query, $filters, [
                'student_code',
                'student_name',
                'mobile_number',
                'internship_mode',
                'status',
                'registration_no',
                'created_by',
                'created_at',
            ]);
        }

        return $query->paginate($perPage);
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

    public function delete(Student $student): bool
    {
        return (bool) $student->delete();
    }
}
