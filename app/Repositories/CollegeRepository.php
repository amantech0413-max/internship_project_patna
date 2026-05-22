<?php

namespace App\Repositories;

use App\Models\College;
use App\Repositories\Contracts\CollegeRepositoryInterface;
use App\Support\AppliesListSorting;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class CollegeRepository implements CollegeRepositoryInterface
{
    use AppliesListSorting;

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = College::query()->withCount('students');

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('college_name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%")
                    ->orWhere('contact_person', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $this->applyListSorting($query, $filters, [
            'college_name',
            'slug',
            'address',
            'contact_person',
            'mobile_number',
            'status',
            'created_at',
        ]);

        return $query->paginate($perPage);
    }

    public function allActive(): Collection
    {
        return College::query()
            ->where('status', 'active')
            ->orderBy('college_name')
            ->get(['id', 'college_name', 'slug']);
    }

    public function allActiveForRegistration(): Collection
    {
        return College::query()
            ->where('status', 'active')
            ->whereNotNull('slug')
            ->orderBy('college_name')
            ->get();
    }

    public function findBySlug(string $slug): ?College
    {
        return College::query()
            ->where('slug', $slug)
            ->where('status', 'active')
            ->first();
    }

    public function findById(int $id): ?College
    {
        return College::withCount('students')->find($id);
    }

    public function create(array $data): College
    {
        return College::create($data);
    }

    public function update(College $college, array $data): College
    {
        $college->update($data);

        return $college->fresh();
    }

    public function delete(College $college): bool
    {
        return (bool) $college->delete();
    }
}
