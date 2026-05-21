<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendPushNotificationJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $userId,
        public string $title,
        public string $message,
        public array $data = []
    ) {}

    public function handle(): void
    {
        $user = User::find($this->userId);

        if (! $user) {
            return;
        }

        // Hook for FCM / Firebase — log payload for Flutter integration
        Log::info('Push notification queued', [
            'user_id' => $user->id,
            'title' => $this->title,
            'message' => $this->message,
            'data' => $this->data,
        ]);
    }
}
