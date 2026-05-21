<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Requests\Api\V1\Auth\RegisterStudentRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use App\Services\StudentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $auth,
        protected StudentService $students
    ) {}

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->auth->login($request->login, $request->password);

        return $this->success([
            'token' => $result['token'],
            'token_type' => $result['token_type'],
            'user' => new UserResource($result['user']),
        ], 'Login successful');
    }

    public function register(RegisterStudentRequest $request): JsonResponse
    {
        $student = $this->students->register($request->validated());

        return $this->success(
            new \App\Http\Resources\StudentResource($student),
            'Registration submitted successfully. No student login — admin will review your application.',
            201
        );
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['identifier' => ['required', 'string']]);
        $this->auth->sendPasswordResetOtp($request->identifier);

        return $this->success(null, 'OTP sent if account exists.');
    }

    public function verifyOtp(Request $request): JsonResponse
    {
        $request->validate([
            'identifier' => ['required', 'string'],
            'otp' => ['required', 'digits:6'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $this->auth->verifyOtpAndResetPassword(
            $request->identifier,
            $request->otp,
            $request->password
        );

        return $this->success(null, 'Password reset successful.');
    }

    public function logout(Request $request): JsonResponse
    {
        $this->auth->logout($request->user());

        return $this->success(null, 'Logged out successfully.');
    }

    public function me(Request $request): JsonResponse
    {
        return $this->success(new UserResource($this->auth->loadUserRelations($request->user())));
    }
}
