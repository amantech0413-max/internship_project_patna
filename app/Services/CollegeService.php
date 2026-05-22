<?php

namespace App\Services;

use App\Models\College;
use App\Repositories\Contracts\CollegeRepositoryInterface;
use App\Support\GeneratesUniqueSlug;

class CollegeService
{
    use GeneratesUniqueSlug;

    public function __construct(protected CollegeRepositoryInterface $colleges) {}

    public function list(array $filters, int $perPage = 15)
    {
        return $this->colleges->paginate($filters, $perPage);
    }

    public function dropdown()
    {
        return $this->colleges->allActive();
    }

    public function registrationColleges()
    {
        return $this->colleges->allActiveForRegistration();
    }

    public function findBySlug(string $slug): ?College
    {
        return $this->colleges->findBySlug($slug);
    }

    public function store(array $data): College
    {
        $slugInput = isset($data['slug']) ? trim((string) $data['slug']) : '';
        $data['slug'] = $slugInput !== ''
            ? $this->assertUniqueSlug(new College, $slugInput)
            : $this->makeUniqueSlug(new College, $data['college_name']);

        return $this->colleges->create($data);
    }

    public function update(int $id, array $data): ?College
    {
        $college = $this->colleges->findById($id);

        if (! $college) {
            return null;
        }

        // Slug changes only when admin manually sets a new slug (not when college name changes).
        if (array_key_exists('slug', $data)) {
            $slugInput = trim((string) ($data['slug'] ?? ''));

            if ($slugInput === '' || $slugInput === $college->slug) {
                unset($data['slug']);
            } else {
                $data['slug'] = $this->assertUniqueSlug(new College, $slugInput, $college->id);
            }
        } else {
            unset($data['slug']);
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
