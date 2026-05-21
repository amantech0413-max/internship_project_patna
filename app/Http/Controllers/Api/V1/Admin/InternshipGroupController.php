<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Requests\Api\V1\Admin\SendWhatsappInvitationRequest;
use App\Http\Requests\Api\V1\Admin\StoreInternshipGroupRequest;
use App\Http\Resources\InternshipGroupResource;
use App\Http\Resources\StudentResource;
use App\Http\Resources\WhatsappMessageResource;
use App\Models\InternshipGroup;
use App\Repositories\Contracts\InternshipGroupRepositoryInterface;
use App\Services\InternshipGroupService;
use App\Services\WhatsApp\WhatsappMessageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InternshipGroupController extends Controller
{
    public function __construct(
        protected InternshipGroupRepositoryInterface $groups,
        protected InternshipGroupService $groupService,
        protected WhatsappMessageService $whatsapp
    ) {}

    public function index(Request $request): JsonResponse
    {
        $paginator = $this->groups->paginate($request->only([
            'search', 'status', 'college_name', 'internship_mode', 'semester',
        ]));

        return $this->success(InternshipGroupResource::collection($paginator));
    }

    public function store(StoreInternshipGroupRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['created_by'] = $request->user()->id;

        $group = $this->groups->create($data);

        return $this->success(new InternshipGroupResource($group), 'Internship group created.', 201);
    }

    public function show(InternshipGroup $group): JsonResponse
    {
        $group->load(['students', 'creator']);

        return $this->success(new InternshipGroupResource($group));
    }

    public function update(InternshipGroup $group, StoreInternshipGroupRequest $request): JsonResponse
    {
        $updated = $this->groups->update($group, $request->validated());

        return $this->success(new InternshipGroupResource($updated), 'Internship group updated.');
    }

    public function destroy(InternshipGroup $group): JsonResponse
    {
        $group->students()->detach();
        $group->delete();

        return $this->success(null, 'Internship group deleted.');
    }

    public function assignStudents(InternshipGroup $group, Request $request): JsonResponse
    {
        $request->validate([
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => ['integer', 'exists:students,id'],
        ]);

        $this->groupService->assignStudents($group, $request->student_ids, 'manual');

        return $this->success(
            new InternshipGroupResource($group->fresh(['students'])),
            'Students assigned. Use Send WhatsApp Invitation to notify them.'
        );
    }

    public function unassignStudents(InternshipGroup $group, Request $request): JsonResponse
    {
        $request->validate([
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => ['integer', 'exists:students,id'],
        ]);

        $this->groupService->unassignStudents($group, $request->student_ids);

        return $this->success(
            new InternshipGroupResource($group->fresh(['students'])),
            'Students removed from group.'
        );
    }

    public function availableStudents(InternshipGroup $group, Request $request): JsonResponse
    {
        $students = $this->groupService->filterStudentsForAssignment($group, $request->only([
            'search', 'internship_mode',
        ]));

        return $this->success(StudentResource::collection($students));
    }

    public function sendWhatsappInvitations(InternshipGroup $group, SendWhatsappInvitationRequest $request): JsonResponse
    {
        $logs = $this->whatsapp->queueInvitations(
            $group,
            $request->student_ids,
            $request->user()->id,
            $request->boolean('resend', false)
        );

        return $this->success(
            WhatsappMessageResource::collection($logs),
            'WhatsApp invitation messages queued. Students will join manually via the invite link.',
            202
        );
    }
}
