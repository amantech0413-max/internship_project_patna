<?php

namespace App\Observers;

use App\Jobs\SendPushNotificationJob;
use App\Models\Student;

class StudentObserver
{
    public function updated(Student $student): void
    {
        if ($student->wasChanged('status') && $student->status->value === 'approved') {
            SendPushNotificationJob::dispatch(
                $student->user_id,
                'Registration Approved',
                'Your internship registration has been approved.',
                ['student_id' => $student->id]
            );
        }
    }
}
