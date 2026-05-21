<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\Student\UpdateStudentProfileRequest;
use App\Http\Resources\InternshipGroupResource;
use App\Http\Resources\NotificationResource;
use App\Http\Resources\StudentResource;
use App\Models\Assignment;
use App\Models\DailyReport;
use App\Services\InternshipGroupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StudentDashboardController extends Controller
{
    public function __construct(protected InternshipGroupService $groups) {}

    public function profile(Request $request): JsonResponse
    {
        $student = $request->user()->student?->load('groups');

        if (! $student) {
            return $this->error('Student profile not found.', 404);
        }

        return $this->success(new StudentResource($student));
    }

    public function updateProfile(UpdateStudentProfileRequest $request): JsonResponse
    {
        $student = $request->user()->student;

        if (! $student) {
            return $this->error('Student profile not found.', 404);
        }

        $updated = app(\App\Services\StudentService::class)->updateProfile(
            $student,
            $request->validated(),
            $request->file('photo'),
            $request->file('id_proof')
        );

        return $this->success(new StudentResource($updated), 'Profile updated.');
    }

    public function internshipDetails(Request $request): JsonResponse
    {
        $student = $request->user()->student?->load('groups');

        return $this->success([
            'student' => new StudentResource($student),
            'internship_mode' => $student?->internship_mode?->value,
            'status' => $student?->status?->value,
        ]);
    }

    public function assignedGroup(Request $request): JsonResponse
    {
        $student = $request->user()->student;
        $group = $student?->activeGroup();

        if (! $group) {
            return $this->success([
                'group' => null,
                'internship_mode' => $student?->internship_mode?->value,
            ], 'No active group assigned yet.');
        }

        return $this->success([
            'group' => new InternshipGroupResource($group),
            'internship_mode' => $student?->internship_mode?->value,
            'join_whatsapp' => $this->groups->getWhatsAppDeepLink($group->whatsapp_group_link, $group),
        ]);
    }

    public function whatsappLink(Request $request): JsonResponse
    {
        $group = $request->user()->student?->activeGroup();

        return $this->success(
            $this->groups->getWhatsAppDeepLink($group?->whatsapp_group_link, $group),
            'Open WhatsApp to join your group manually using the invite link.'
        );
    }

    public function documents(Request $request): JsonResponse
    {
        $student = $request->user()->student;

        return $this->success([
            'offer_letters' => $student?->offerLetters()->latest()->get()->map(fn ($o) => [
                'id' => $o->id,
                'letter_no' => $o->letter_no,
                'url' => Storage::disk('public')->url($o->file_path),
            ]),
            'certificates' => $student?->certificates()->latest()->get()->map(fn ($c) => [
                'id' => $c->id,
                'certificate_no' => $c->certificate_no,
                'url' => Storage::disk('public')->url($c->file_path),
            ]),
        ]);
    }

    public function submitDailyReport(Request $request): JsonResponse
    {
        $request->validate([
            'report_date' => ['required', 'date'],
            'work_summary' => ['required', 'string'],
            'learnings' => ['nullable', 'string'],
        ]);

        $student = $request->user()->student;

        $report = DailyReport::updateOrCreate(
            [
                'student_id' => $student->id,
                'report_date' => $request->report_date,
            ],
            $request->only(['work_summary', 'learnings'])
        );

        return $this->success($report, 'Daily report submitted.');
    }

    public function submitAssignment(Request $request): JsonResponse
    {
        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'file' => ['nullable', 'file', 'max:5120'],
        ]);

        $student = $request->user()->student;
        $path = $request->hasFile('file')
            ? $request->file('file')->store('assignments', 'public')
            : null;

        $assignment = Assignment::create([
            'student_id' => $student->id,
            'internship_group_id' => $student->activeGroup()?->id,
            'title' => $request->title,
            'description' => $request->description,
            'file_path' => $path,
            'submitted_at' => now(),
        ]);

        return $this->success($assignment, 'Assignment submitted.', 201);
    }

    public function notifications(Request $request): JsonResponse
    {
        $notifications = $request->user()->notifications()->latest()->paginate(20);

        return $this->success(NotificationResource::collection($notifications));
    }

    public function dailyReports(Request $request): JsonResponse
    {
        $reports = $request->user()->student
            ->dailyReports()
            ->latest('report_date')
            ->paginate(20);

        return $this->success($reports);
    }

    public function assignments(Request $request): JsonResponse
    {
        $assignments = $request->user()->student
            ->assignments()
            ->latest()
            ->paginate(20);

        return $this->success($assignments);
    }

    public function markAttendance(Request $request): JsonResponse
    {
        $request->validate([
            'qr_token' => ['nullable', 'string'],
            'method' => ['nullable', 'in:qr,manual'],
        ]);

        $student = $request->user()->student;

        $record = \App\Models\AttendanceRecord::updateOrCreate(
            [
                'student_id' => $student->id,
                'attendance_date' => now()->toDateString(),
            ],
            [
                'internship_group_id' => $student->activeGroup()?->id,
                'checked_in_at' => now(),
                'method' => $request->get('method', 'qr'),
                'qr_token' => $request->qr_token,
                'is_present' => true,
            ]
        );

        return $this->success($record, 'Attendance marked.');
    }
}
