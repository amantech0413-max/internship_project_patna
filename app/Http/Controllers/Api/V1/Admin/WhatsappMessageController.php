<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Resources\WhatsappMessageResource;
use App\Models\WhatsappMessage;
use App\Services\WhatsApp\WhatsappMessageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WhatsappMessageController extends Controller
{
    public function __construct(protected WhatsappMessageService $whatsapp) {}

    public function index(Request $request): JsonResponse
    {
        $logs = $this->whatsapp->paginateLogs(
            $request->only(['internship_group_id', 'status', 'mobile']),
            (int) $request->get('per_page', 20)
        );

        return $this->success(WhatsappMessageResource::collection($logs));
    }

    public function retryFailed(Request $request): JsonResponse
    {
        $count = $this->whatsapp->retryFailed($request->integer('internship_group_id') ?: null);

        return $this->success(['retried' => $count], "{$count} failed message(s) queued for retry.");
    }

    public function resend(WhatsappMessage $whatsappMessage, Request $request): JsonResponse
    {
        $log = $this->whatsapp->resend($whatsappMessage, $request->user()->id);

        return $this->success(new WhatsappMessageResource($log), 'WhatsApp invitation re-queued.');
    }
}
