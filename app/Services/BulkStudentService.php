<?php

namespace App\Services;

use App\Models\BulkStudent;
use App\Models\User;
use App\Repositories\Contracts\BulkStudentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class BulkStudentService
{
    public function __construct(protected BulkStudentRepositoryInterface $bulkStudents) {}

    public function list(array $filters, int $perPage, User $user, bool $onlyMine = false): LengthAwarePaginator
    {
        $scopeId = ($onlyMine || ! $user->isAdmin()) ? $user->id : null;

        if (! $user->isAdmin() && ! empty($filters['created_by']) && (int) $filters['created_by'] !== $user->id) {
            $filters['created_by'] = $user->id;
        }

        return $this->bulkStudents->paginate($filters, $perPage, $scopeId);
    }

    public function find(int $id, User $user): ?BulkStudent
    {
        $entry = $this->bulkStudents->findById($id);

        if (! $entry) {
            return null;
        }

        $this->assertCanAccess($entry, $user);

        return $entry;
    }

    public function storeEntry(int $collegeId, string $studentName, string $mobileNumber, int $createdBy)
    {
        return $this->bulkStudents->create([
            'college_id' => $collegeId,
            'student_name' => $studentName,
            'mobile_number' => $mobileNumber,
            'created_by' => $createdBy,
        ]);
    }

    public function update(BulkStudent $entry, array $data, User $user): BulkStudent
    {
        $this->assertCanModify($entry, $user);

        return $this->bulkStudents->update($entry, [
            'college_id' => $data['college_id'] ?? $entry->college_id,
            'student_name' => $data['student_name'] ?? $entry->student_name,
            'mobile_number' => $data['mobile_number'] ?? $entry->mobile_number,
        ]);
    }

    public function delete(BulkStudent $entry, User $user): bool
    {
        $this->assertCanModify($entry, $user);

        return $this->bulkStudents->delete($entry);
    }

    public function report(array $filters, User $user, int $perPage = 10): array
    {
        $scopeId = $user->isAdmin() ? null : $user->id;

        if (! $user->isAdmin()) {
            unset($filters['created_by']);
        }

        $perPage = max(1, min(500, $perPage));
        $entries = $this->bulkStudents->reportEntriesPaginated($filters, $scopeId, $perPage);

        return [
            'summary' => $this->bulkStudents->reportSummaryByStaff($filters, $scopeId),
            'entries' => $entries,
            'total' => $entries->total(),
        ];
    }

    protected function assertCanAccess(BulkStudent $entry, User $user): void
    {
        if ($user->isAdmin()) {
            return;
        }

        if ($entry->created_by !== $user->id) {
            throw new AccessDeniedHttpException('You can only access your own entries.');
        }
    }

    protected function assertCanModify(BulkStudent $entry, User $user): void
    {
        if ($user->isAdmin()) {
            return;
        }

        if ($entry->created_by !== $user->id) {
            throw new AccessDeniedHttpException('You can only modify your own entries.');
        }
    }
}
