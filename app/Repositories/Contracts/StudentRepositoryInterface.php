<?php

namespace App\Repositories\Contracts;

use App\Models\Student;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface StudentRepositoryInterface
{
    public function findById(int $id): ?Student;

    public function findByRegistrationNo(string $registrationNo): ?Student;

    public function findByStudentCode(string $studentCode): ?Student;

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findByMobile(string $mobile): Collection;

    public function create(array $data): Student;

    public function update(Student $student, array $data): Student;

    public function delete(Student $student): bool;
}
