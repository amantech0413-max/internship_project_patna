<?php

namespace App\Services;

use App\Models\InternshipGroup;
use App\Models\Student;
use App\Repositories\Contracts\InternshipGroupRepositoryInterface;
use Illuminate\Support\Collection;

/**
 * Admin-controlled group management only.
 * No automatic WhatsApp group creation or member adding.
 */
class InternshipGroupService
{
    public function __construct(
        protected InternshipGroupRepositoryInterface $groups
    ) {}

    public function assignStudents(InternshipGroup $group, array $studentIds, string $type = 'manual'): InternshipGroup
    {
        $sync = [];
        foreach ($studentIds as $studentId) {
            $sync[$studentId] = [
                'assignment_type' => $type,
                'assigned_at' => now(),
            ];
        }

        $group->students()->syncWithoutDetaching($sync);

        return $group->load('students');
    }

    public function unassignStudents(InternshipGroup $group, array $studentIds): InternshipGroup
    {
        $group->students()->detach($studentIds);

        return $group->load('students');
    }

    public function filterStudentsForAssignment(InternshipGroup $group, array $filters = []): Collection
    {
        $query = Student::query()->where('status', 'approved');

        if ($group->semester) {
            $query->where('semester', $group->semester);
        }

        if ($group->subject) {
            $query->where('subject', $group->subject);
        }

        if ($group->college_name) {
            $query->where('college_name', $group->college_name);
        }

        if ($group->internship_mode) {
            $query->where('internship_mode', $group->internship_mode);
        }

        if (! empty($filters['internship_mode'])) {
            $query->where('internship_mode', $filters['internship_mode']);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('student_code', 'like', "%{$search}%")
                    ->orWhere('student_code', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('name')->get();
    }

    public function getWhatsAppDeepLink(?string $link, ?InternshipGroup $group = null): ?array
    {
        if (! $link) {
            return null;
        }

        return [
            'url' => $link,
            'deep_link' => $link,
            'flutter_action' => 'open_whatsapp',
            'group_name' => $group?->name,
            'internship_mode' => $group?->internship_mode?->value,
            'instructions' => 'Tap to open WhatsApp and join the group manually using the invite link.',
        ];
    }
}
