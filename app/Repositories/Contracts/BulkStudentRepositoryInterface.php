<?php

namespace App\Repositories\Contracts;

use App\Models\BulkStudent;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
interface BulkStudentRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 10, ?int $scopeUserId = null): LengthAwarePaginator;

    public function findById(int $id): ?BulkStudent;

    public function create(array $data): BulkStudent;

    public function update(BulkStudent $entry, array $data): BulkStudent;

    public function delete(BulkStudent $entry): bool;

    public function reportEntriesPaginated(array $filters = [], ?int $scopeUserId = null, int $perPage = 10): LengthAwarePaginator;

    /** @return array<int, array{created_by: int, staff_name: string, total: int}> */
    public function reportSummaryByStaff(array $filters = [], ?int $scopeUserId = null): array;
}
