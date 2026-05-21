<?php

namespace App\Repositories;

use App\Models\InternshipGroup;
use App\Repositories\Contracts\InternshipGroupRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class InternshipGroupRepository implements InternshipGroupRepositoryInterface
{
    public function findById(int $id): ?InternshipGroup
    {
        return InternshipGroup::with(['students', 'creator'])->find($id);
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = InternshipGroup::withCount('students');

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where('name', 'like', "%{$search}%");
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['college_name'])) {
            $query->where('college_name', $filters['college_name']);
        }

        if (! empty($filters['internship_mode'])) {
            $query->where('internship_mode', $filters['internship_mode']);
        }

        if (! empty($filters['semester'])) {
            $query->where('semester', $filters['semester']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data): InternshipGroup
    {
        return InternshipGroup::create($data);
    }

    public function update(InternshipGroup $group, array $data): InternshipGroup
    {
        $group->update($data);

        return $group->fresh(['students', 'creator']);
    }
}
