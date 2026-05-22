<?php

namespace App\Services;

use App\Models\BulkStudent;
use App\Models\InternshipGroup;
use App\Services\WhatsApp\WhatsappMessageService;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class WhatsAppService
{
    public function __construct(protected WhatsappMessageService $messages) {}

    public function resolveStudents(
        ?int $collegeId,
        string $sendType,
        array $studentIds = [],
        ?int $startStudentId = null,
        ?int $endStudentId = null
    ): Collection {
        $query = BulkStudent::query()
            ->whereNotNull('mobile_number')
            ->where('mobile_number', '!=', '');

        if ($collegeId) {
            $query->where('college_id', $collegeId);
        }

        if ($sendType === 'range') {
            if (! $startStudentId || ! $endStudentId) {
                throw ValidationException::withMessages([
                    'start_student_id' => ['Start and end student ID are required for range mode.'],
                ]);
            }
            $query->whereBetween('id', [min($startStudentId, $endStudentId), max($startStudentId, $endStudentId)]);
        } else {
            if (empty($studentIds)) {
                throw ValidationException::withMessages([
                    'student_ids' => ['Select at least one student.'],
                ]);
            }
            $query->whereIn('id', $studentIds);
        }

        $students = $query->orderBy('id')->get(['id', 'student_name', 'mobile_number', 'college_id']);

        if ($students->isEmpty()) {
            throw ValidationException::withMessages([
                'students' => ['No students found for the selected criteria.'],
            ]);
        }

        return $students;
    }

    public function preview(
        ?int $collegeId,
        string $sendType,
        array $studentIds = [],
        ?int $startStudentId = null,
        ?int $endStudentId = null
    ): array {
        $students = $this->resolveStudents($collegeId, $sendType, $studentIds, $startStudentId, $endStudentId);

        return [
            'count' => $students->count(),
            'students' => $students->map(fn (BulkStudent $s) => [
                'id' => $s->id,
                'student_name' => $s->student_name,
                'student_code' => null,
                'mobile_number' => $s->mobile_number,
                'college_id' => $s->college_id,
            ])->values()->all(),
        ];
    }

    public function send(
        int $groupId,
        ?int $collegeId,
        string $sendType,
        array $studentIds,
        ?int $startStudentId,
        ?int $endStudentId,
        int $adminId,
        bool $resend = false
    ): Collection {
        $group = InternshipGroup::findOrFail($groupId);
        $students = $this->resolveStudents($collegeId, $sendType, $studentIds, $startStudentId, $endStudentId);

        return $this->messages->queueInvitations(
            $group,
            $students->pluck('id')->all(),
            $adminId,
            $resend
        );
    }
}
