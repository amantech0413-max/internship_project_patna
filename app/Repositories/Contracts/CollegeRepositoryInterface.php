<?php

namespace App\Repositories\Contracts;

use App\Models\College;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface CollegeRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function allActive(): Collection;

    public function findById(int $id): ?College;

    public function create(array $data): College;

    public function update(College $college, array $data): College;

    public function delete(College $college): bool;
}
