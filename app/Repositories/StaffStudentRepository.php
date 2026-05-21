<?php

namespace App\Repositories;

use App\Models\Student;
use App\Repositories\Contracts\StaffStudentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class StaffStudentRepository implements StaffStudentRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Student::query()
            ->with(['college:id,college_name', 'creator:id,name'])
            ->whereNotNull('college_id');

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('student_name', 'like', "%{$search}%")
                    ->orWhere('mobile_number', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['college_id'])) {
            $query->where('college_id', $filters['college_id']);
        }

        if (! empty($filters['mobile'])) {
            $query->where('mobile_number', $filters['mobile']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data): Student
    {
        return Student::create($data);
    }
}
