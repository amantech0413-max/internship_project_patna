<?php

namespace App\Jobs;

use App\Services\WhatsApp\WhatsappMessageService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class SendWhatsappInvitationJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public function __construct(public int $whatsappMessageId) {}

    public function handle(WhatsappMessageService $service): void
    {
        $service->processMessage($this->whatsappMessageId);
    }

    public function failed(Throwable $exception): void
    {
        report($exception);
    }
}
