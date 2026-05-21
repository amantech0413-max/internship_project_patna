<?php

namespace App\Repositories\Contracts;

use App\Models\Student;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface StaffStudentRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function create(array $data): Student;
}
