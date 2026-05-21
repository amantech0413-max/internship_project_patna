<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Resources\WhatsappMessageResource;
use App\Services\WhatsAppService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WhatsAppController extends Controller
{
    public function __construct(protected WhatsAppService $whatsapp) {}

    public function previewStudents(Request $request): JsonResponse
    {
        $request->validate([
            'college_id' => ['nullable', 'exists:colleges,id'],
            'send_type' => ['required', Rule::in(['manual', 'range'])],
            'student_ids' => ['array'],
            'student_ids.*' => ['integer'],
            'start_student_id' => ['nullable', 'integer'],
            'end_student_id' => ['nullable', 'integer'],
        ]);

        $data = $this->whatsapp->preview(
            $request->integer('college_id') ?: null,
            $request->send_type,
            $request->input('student_ids', []),
            $request->integer('start_student_id') ?: null,
            $request->integer('end_student_id') ?: null
        );

        return $this->success($data, 'Students loaded for preview.');
    }

    public function sendMessages(Request $request): JsonResponse
    {
        $request->validate([
            'internship_group_id' => ['required', 'exists:internship_groups,id'],
            'college_id' => ['nullable', 'exists:colleges,id'],
            'send_type' => ['required', Rule::in(['manual', 'range'])],
            'student_ids' => ['array'],
            'student_ids.*' => ['integer'],
            'start_student_id' => ['nullable', 'integer'],
            'end_student_id' => ['nullable', 'integer'],
            'resend' => ['boolean'],
        ]);

        $logs = $this->whatsapp->send(
            $request->integer('internship_group_id'),
            $request->integer('college_id') ?: null,
            $request->send_type,
            $request->input('student_ids', []),
            $request->integer('start_student_id') ?: null,
            $request->integer('end_student_id') ?: null,
            $request->user()->id,
            $request->boolean('resend')
        );

        return $this->success(
            WhatsappMessageResource::collection($logs),
            $logs->count().' message(s) queued for sending.'
        );
    }
}
