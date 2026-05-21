<?php

namespace App\Services;

use App\Models\College;
use App\Repositories\Contracts\CollegeRepositoryInterface;

class CollegeService
{
    public function __construct(protected CollegeRepositoryInterface $colleges) {}

    public function list(array $filters, int $perPage = 15)
    {
        return $this->colleges->paginate($filters, $perPage);
    }

    public function dropdown()
    {
        return $this->colleges->allActive();
    }

    public function store(array $data): College
    {
        return $this->colleges->create($data);
    }

    public function update(int $id, array $data): ?College
    {
        $college = $this->colleges->findById($id);

        if (! $college) {
            return null;
        }

        return $this->colleges->update($college, $data);
    }

    public function delete(int $id): bool
    {
        $college = $this->colleges->findById($id);

        if (! $college) {
            return false;
        }

        return $this->colleges->delete($college);
    }

    public function show(int $id): ?College
    {
        return $this->colleges->findById($id);
    }
}
