<?php

namespace App\Http\Middleware;

use App\Enums\StudentStatus;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStudentIsApproved
{
    public function handle(Request $request, Closure $next): Response
    {
        $student = $request->user()?->student;

        if (! $student || $student->status !== StudentStatus::Approved) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Your internship registration must be approved by admin.',
            ], 403);
        }

        return $next($request);
    }
}
