<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Api\V1\Controller;
use App\Http\Resources\StudentPaymentResource;
use App\Models\StudentPayment;
use App\Services\StudentPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StudentPaymentController extends Controller
{
    public function __construct(protected StudentPaymentService $payments) {}

    public function index(Request $request): JsonResponse
    {
        $paginator = $this->payments->paginate(
            $request->only(['search', 'status', 'college_id', 'sort_by', 'sort_dir']),
            (int) $request->get('per_page', 10)
        );

        return $this->success(StudentPaymentResource::collection($paginator));
    }

    public function show(StudentPayment $payment): JsonResponse
    {
        $payment->load(['student.college', 'statusChanger']);

        return $this->success(new StudentPaymentResource($payment));
    }

    public function updateStatus(StudentPayment $payment, Request $request): JsonResponse
    {
        $request->validate([
            'status' => ['required', Rule::in(PaymentStatus::values())],
        ]);

        $updated = $this->payments->updateStatus(
            $payment,
            $request->string('status')->toString(),
            $request->user()
        );

        return $this->success(
            new StudentPaymentResource($updated),
            'Payment status updated.'
        );
    }
}
