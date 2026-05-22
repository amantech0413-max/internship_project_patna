<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Models\Student;
use App\Models\StudentPayment;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class StudentPaymentService
{
    public function __construct(protected SiteSettingService $settings) {}

    public function createForRegistration(
        Student $student,
        bool $offline,
        ?string $transactionId,
        ?UploadedFile $screenshot
    ): StudentPayment {
        $path = null;
        if ($screenshot && ! $offline) {
            $path = $screenshot->store('payments/screenshots', 'public');
        }

        return StudentPayment::create([
            'student_id' => $student->id,
            'amount' => $this->settings->registrationFeeAmount(),
            'transaction_id' => $offline ? null : $transactionId,
            'screenshot_path' => $path,
            'payment_mode_offline' => $offline,
            'status' => PaymentStatus::Pending,
        ]);
    }

    public function paginate(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        $query = StudentPayment::query()
            ->with([
                'student:id,student_name,registration_no,mobile_number,college_id',
                'student.college:id,college_name',
                'statusChanger:id,name',
            ]);

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('transaction_id', 'like', "%{$search}%")
                    ->orWhereHas('student', function ($sq) use ($search) {
                        $sq->where('student_name', 'like', "%{$search}%")
                            ->orWhere('registration_no', 'like', "%{$search}%")
                            ->orWhere('mobile_number', 'like', "%{$search}%");
                    });
            });
        }

        if (! empty($filters['college_id'])) {
            $query->whereHas('student', fn ($q) => $q->where('college_id', $filters['college_id']));
        }

        $sortBy = in_array($filters['sort_by'] ?? '', ['amount', 'created_at', 'status'], true)
            ? $filters['sort_by']
            : 'created_at';
        $sortDir = ($filters['sort_dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        return $query->orderBy($sortBy, $sortDir)->paginate($perPage);
    }

    public function updateStatus(StudentPayment $payment, string $status, User $user): StudentPayment
    {
        if (! $user->isAdmin() && ! $user->hasPermission('payment_status')) {
            throw new AccessDeniedHttpException('You cannot update payment status.');
        }

        $enum = PaymentStatus::from($status);

        $payment->update([
            'status' => $enum,
            'status_changed_by' => $user->id,
            'status_changed_at' => now(),
        ]);

        return $payment->fresh([
            'student.college',
            'statusChanger',
        ]);
    }

    public function screenshotUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        return Storage::disk('public')->url($path);
    }
}
