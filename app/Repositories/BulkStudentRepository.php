<?php

namespace App\Repositories;

use App\Models\BulkStudent;
use App\Repositories\Contracts\BulkStudentRepositoryInterface;
use App\Support\AppliesListSorting;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class BulkStudentRepository implements BulkStudentRepositoryInterface
{
    use AppliesListSorting;

    public function paginate(array $filters = [], int $perPage = 10, ?int $scopeUserId = null): LengthAwarePaginator
    {
        $query = BulkStudent::query()
            ->with(['college:id,college_name', 'creator:id,name', 'creator.roleModel:id,is_assignable']);

        if ($scopeUserId) {
            $query->where('created_by', $scopeUserId);
        }

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

        if (! empty($filters['created_by'])) {
            $query->where('created_by', $filters['created_by']);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        $this->applyListSorting($query, $filters, [
            'student_name',
            'mobile_number',
            'created_at',
        ], 'created_at');

        return $query->paginate($perPage);
    }

    public function findById(int $id): ?BulkStudent
    {
        return BulkStudent::with(['college:id,college_name', 'creator:id,name', 'creator.roleModel:id,is_assignable'])
            ->find($id);
    }

    public function create(array $data): BulkStudent
    {
        return BulkStudent::create($data);
    }

    public function update(BulkStudent $entry, array $data): BulkStudent
    {
        $entry->update($data);

        return $entry->fresh(['college:id,college_name', 'creator:id,name', 'creator.roleModel:id,is_assignable']);
    }

    public function delete(BulkStudent $entry): bool
    {
        return (bool) $entry->delete();
    }

    public function reportEntriesPaginated(array $filters = [], ?int $scopeUserId = null, int $perPage = 10): LengthAwarePaginator
    {
        $query = BulkStudent::query()
            ->with(['college:id,college_name', 'creator:id,name', 'creator.roleModel:id,is_assignable']);

        $this->applyReportFilters($query, $filters, $scopeUserId);

        return $query->orderByDesc('created_at')->paginate($perPage);
    }

    public function reportSummaryByStaff(array $filters = [], ?int $scopeUserId = null): array
    {
        $query = BulkStudent::query()
            ->select('created_by', DB::raw('COUNT(*) as total'))
            ->groupBy('created_by');

        $this->applyReportFilters($query, $filters, $scopeUserId);

        $rows = $query->get();
        $names = \App\Models\User::query()
            ->whereIn('id', $rows->pluck('created_by'))
            ->pluck('name', 'id');

        return $rows->map(fn ($row) => [
            'created_by' => (int) $row->created_by,
            'staff_name' => $names[$row->created_by] ?? '—',
            'total' => (int) $row->total,
        ])->sortByDesc('total')->values()->all();
    }

    protected function applyReportFilters($query, array $filters, ?int $scopeUserId): void
    {
        if ($scopeUserId) {
            $query->where('created_by', $scopeUserId);
        }

        if (! empty($filters['college_id'])) {
            $query->where('college_id', $filters['college_id']);
        }

        if (! empty($filters['created_by'])) {
            $query->where('created_by', $filters['created_by']);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }
    }
}
