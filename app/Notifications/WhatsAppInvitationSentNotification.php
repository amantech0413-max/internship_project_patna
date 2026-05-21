<?php

namespace App\Notifications;

use App\Models\InternshipGroup;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * In-app notification only — students open WhatsApp manually via invite link.
 */
class WhatsAppInvitationSentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public InternshipGroup $group,
        public ?string $whatsappGroupLink
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'WhatsApp Group Invitation',
            'message' => 'You have been invited to join your internship WhatsApp group.',
            'group_id' => $this->group->id,
            'group_name' => $this->group->name,
            'internship_mode' => $this->group->internship_mode?->value ?? $this->group->getAttributes()['internship_mode'] ?? null,
            'whatsapp_group_link' => $this->whatsappGroupLink,
            'flutter_action' => 'open_whatsapp',
        ];
    }
}
