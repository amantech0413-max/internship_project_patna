<?php

namespace App\Services;

use App\Models\College;
use App\Models\Student;
use App\Support\AppliesListSorting;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

class BinService
{
    use AppliesListSorting;

    public function list(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $type = $filters['type'] ?? 'all';

        if ($type === 'college') {
            return $this->paginateColleges($filters, $perPage);
        }

        if ($type === 'student') {
            return $this->paginateStudents($filters, $perPage);
        }

        return $this->paginateCombined($filters, $perPage);
    }

    public function restore(string $type, int $id): bool
    {
        $model = $this->findTrashed($type, $id);

        if (! $model) {
            return false;
        }

        $model->restore();

        return true;
    }

    public function forceDelete(string $type, int $id): bool
    {
        $model = $this->findTrashed($type, $id);

        if (! $model) {
            return false;
        }

        return (bool) $model->forceDelete();
    }

    protected function findTrashed(string $type, int $id): ?Model
    {
        return match ($type) {
            'college' => College::onlyTrashed()->find($id),
            'student' => Student::onlyTrashed()->find($id),
            default => null,
        };
    }

    protected function paginateColleges(array $filters, int $perPage): LengthAwarePaginator
    {
        $query = College::onlyTrashed();

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('college_name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        $this->applyListSorting($query, $filters, ['college_name', 'slug', 'deleted_at'], 'deleted_at');

        $paginator = $query->paginate($perPage);

        $paginator->getCollection()->transform(fn ($row) => $this->mapBinRow($row, 'college'));

        return $paginator;
    }

    protected function paginateStudents(array $filters, int $perPage): LengthAwarePaginator
    {
        $query = Student::onlyTrashed()->with(['college', 'creator']);

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('student_name', 'like', "%{$search}%")
                    ->orWhere('student_code', 'like', "%{$search}%")
                    ->orWhere('mobile_number', 'like', "%{$search}%");
            });
        }

        $this->applyListSorting($query, $filters, ['student_name', 'student_code', 'deleted_at'], 'deleted_at');

        $paginator = $query->paginate($perPage);

        $paginator->getCollection()->transform(fn ($row) => $this->mapBinRow($row, 'student'));

        return $paginator;
    }

    protected function paginateCombined(array $filters, int $perPage): LengthAwarePaginator
    {
        $colleges = College::onlyTrashed()->get()->map(fn ($r) => $this->mapBinRow($r, 'college'));
        $students = Student::onlyTrashed()->with(['college'])->get()->map(fn ($r) => $this->mapBinRow($r, 'student'));

        $rows = $colleges->concat($students)->sortByDesc('deleted_at')->values();

        if (! empty($filters['search'])) {
            $search = strtolower($filters['search']);
            $rows = $rows->filter(function ($row) use ($search) {
                return str_contains(strtolower($row['title'] ?? ''), $search)
                    || str_contains(strtolower($row['subtitle'] ?? ''), $search);
            })->values();
        }

        $page = max(1, (int) ($filters['page'] ?? 1));
        $total = $rows->count();
        $items = $rows->slice(($page - 1) * $perPage, $perPage)->values();

        return new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    protected function mapBinRow(Model $model, string $type): array
    {
        if ($type === 'college') {
            /** @var College $model */
            return [
                'id' => $model->id,
                'type' => 'college',
                'title' => $model->college_name,
                'subtitle' => $model->slug,
                'deleted_at' => $model->deleted_at?->toIso8601String(),
            ];
        }

        /** @var Student $model */
        return [
            'id' => $model->id,
            'type' => 'student',
            'title' => $model->student_name,
            'subtitle' => ($model->student_code ?? '').' · '.($model->college_name ?? $model->college?->college_name ?? '—'),
            'deleted_at' => $model->deleted_at?->toIso8601String(),
        ];
    }
}
