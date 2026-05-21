<?php

namespace App\Repositories\Contracts;

use App\Models\InternshipGroup;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface InternshipGroupRepositoryInterface
{
    public function findById(int $id): ?InternshipGroup;

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function create(array $data): InternshipGroup;

    public function update(InternshipGroup $group, array $data): InternshipGroup;
}
