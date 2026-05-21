<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OtpNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $otp,
        public string $purpose
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('BLI Internship - OTP Verification')
            ->line('Your OTP for ' . str_replace('_', ' ', $this->purpose) . ' is:')
            ->line($this->otp)
            ->line('This OTP expires in 10 minutes.');
    }
}
