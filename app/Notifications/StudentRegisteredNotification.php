<?php

namespace App\Notifications;

use App\Models\Student;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StudentRegisteredNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Student $student,
        public string $plainPassword
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Internship Registration - M/s Bhagya Laxmi')
            ->line('Your internship registration is received.')
            ->line('Student Code: ' . $this->student->student_code)
            ->line('Student Code: ' . $this->student->student_code)
            ->line('Login using registration number or student code with your password.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Registration Received',
            'message' => 'Your registration is pending admin approval.',
            'student_code' => $this->student->student_code,
        ];
    }
}
