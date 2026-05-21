<?php

namespace App\Services\WhatsApp;

use App\Enums\WhatsappMessageStatus;
use App\Jobs\SendWhatsappInvitationJob;
use App\Models\InternshipGroup;
use App\Models\Student;
use App\Models\WhatsappMessage;
use App\Notifications\WhatsAppInvitationSentNotification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class WhatsappMessageService
{
    public function __construct(
        protected WhatsAppCloudApiService $api
    ) {}

    public function buildInvitationMessage(InternshipGroup $group): string
    {
        $link = $group->whatsapp_group_link ?? '(link not set by admin)';

        return implode("\n\n", [
            'Welcome to M/s Bhagya Laxmi Internship Program, Mohali Chandigarh.',
            'You have been assigned to:',
            $group->name,
            'Join WhatsApp Group:',
            $link,
        ]);
    }

    public function queueInvitations(
        InternshipGroup $group,
        array $studentIds,
        int $adminId,
        bool $resend = false
    ): Collection {
        if (! $group->whatsapp_group_link) {
            throw ValidationException::withMessages([
                'whatsapp_group_link' => ['Admin must paste the WhatsApp group invite link before sending messages.'],
            ]);
        }

        $students = Student::query()
            ->whereIn('id', $studentIds)
            ->get();

        if ($students->isEmpty()) {
            throw ValidationException::withMessages([
                'student_ids' => ['Select at least one student.'],
            ]);
        }

        $messageBody = $this->buildInvitationMessage($group);
        $logs = collect();

        foreach ($students as $student) {
            if (! $resend) {
                $existing = WhatsappMessage::query()
                    ->where('student_id', $student->id)
                    ->where('internship_group_id', $group->id)
                    ->where('status', WhatsappMessageStatus::Sent)
                    ->exists();

                if ($existing) {
                    continue;
                }
            }

            $log = WhatsappMessage::create([
                'student_id' => $student->id,
                'college_id' => $student->college_id,
                'internship_group_id' => $group->id,
                'sent_by' => $adminId,
                'mobile' => $student->mobile_number,
                'message_body' => $messageBody,
                'status' => WhatsappMessageStatus::Pending,
                'max_retries' => config('whatsapp.max_retries', 3),
            ]);

            SendWhatsappInvitationJob::dispatch($log->id);
            $logs->push($log);
        }

        return $logs;
    }

    public function processMessage(int $messageId): WhatsappMessage
    {
        $log = WhatsappMessage::with(['student.user', 'group'])->findOrFail($messageId);

        if ($log->status === WhatsappMessageStatus::Sent) {
            return $log;
        }

        $result = $this->api->sendTextMessage($log->mobile, $log->message_body);

        if ($result['success']) {
            $log->update([
                'status' => WhatsappMessageStatus::Sent,
                'whatsapp_message_id' => $result['message_id'],
                'error_message' => null,
                'sent_at' => now(),
            ]);

            if ($log->student?->user) {
                $log->student->user->notify(
                    new WhatsAppInvitationSentNotification($log->group, $log->group->whatsapp_group_link)
                );
            }

            return $log->fresh();
        }

        $retryCount = $log->retry_count + 1;
        $maxRetries = $log->max_retries;

        $log->update([
            'status' => $retryCount >= $maxRetries ? WhatsappMessageStatus::Failed : WhatsappMessageStatus::Pending,
            'error_message' => $result['error'],
            'retry_count' => $retryCount,
            'next_retry_at' => $retryCount < $maxRetries
                ? now()->addSeconds(config('whatsapp.retry_delay_seconds', 120))
                : null,
        ]);

        if ($retryCount < $maxRetries) {
            SendWhatsappInvitationJob::dispatch($log->id)
                ->delay(now()->addSeconds(config('whatsapp.retry_delay_seconds', 120)));
        }

        return $log->fresh();
    }

    public function resend(WhatsappMessage $message, int $adminId): WhatsappMessage
    {
        $message->update([
            'sent_by' => $adminId,
            'status' => WhatsappMessageStatus::Pending,
            'error_message' => null,
            'retry_count' => 0,
            'next_retry_at' => null,
        ]);

        SendWhatsappInvitationJob::dispatch($message->id);

        return $message->fresh();
    }

    public function retryFailed(?int $groupId = null): int
    {
        $query = WhatsappMessage::query()
            ->where('status', WhatsappMessageStatus::Failed)
            ->whereColumn('retry_count', '<', 'max_retries');

        if ($groupId) {
            $query->where('internship_group_id', $groupId);
        }

        $count = 0;

        foreach ($query->get() as $message) {
            $message->update([
                'status' => WhatsappMessageStatus::Pending,
                'retry_count' => 0,
            ]);
            SendWhatsappInvitationJob::dispatch($message->id);
            $count++;
        }

        return $count;
    }

    public function paginateLogs(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = WhatsappMessage::with(['student', 'group', 'sender'])->latest();

        if (! empty($filters['internship_group_id'])) {
            $query->where('internship_group_id', $filters['internship_group_id']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['mobile'])) {
            $query->where('mobile', $filters['mobile']);
        }

        return $query->paginate($perPage);
    }
}
