<?php

namespace App\Services;

use App\Enums\StudentStatus;
use App\Enums\UserRole;
use App\Models\OtpVerification;
use App\Models\Student;
use App\Models\User;
use App\Notifications\OtpNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function login(string $login, string $password): array
    {
        $user = $this->resolveUserByLogin($login);

        if (! $user || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'login' => ['Invalid email or password.'],
            ]);
        }

        if ($user->isStudent()) {
            throw ValidationException::withMessages([
                'login' => ['Student login is disabled. Students are registered as records only — contact admin if you need help.'],
            ]);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'login' => ['Your account is inactive. Please contact admin.'],
            ]);
        }

        $user->tokens()->where('name', 'api-token')->delete();
        $token = $user->createToken('api-token')->plainTextToken;

        return [
            'user' => $this->loadUserRelations($user),
            'token' => $token,
            'token_type' => 'Bearer',
        ];
    }

    public function loadUserRelations(User $user): User
    {
        if ($user->isStudent()) {
            return $user->load('student.groups');
        }

        return $user->load('student');
    }

    public function resolveUserByLogin(string $login): ?User
    {
        $login = trim($login);

        if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
            return User::where('email', $login)->first();
        }

        $student = Student::query()
            ->where('student_code', $login)
            ->first();

        if ($student) {
            return $student->user;
        }

        // Staff login by email only — mobile is NOT used for authentication
        return User::query()
            ->where('email', $login)
            ->whereIn('role', [
                UserRole::SuperAdmin->value,
                UserRole::Admin->value,
                UserRole::CollegeCoordinator->value,
            ])
            ->first();
    }

    public function sendPasswordResetOtp(string $identifier): void
    {
        $user = $this->resolveUserByLogin($identifier);

        if (! $user) {
            throw ValidationException::withMessages([
                'identifier' => ['No account found for the provided identifier.'],
            ]);
        }

        $otp = (string) random_int(100000, 999999);

        OtpVerification::create([
            'identifier' => $identifier,
            'otp' => $otp,
            'purpose' => 'password_reset',
            'expires_at' => now()->addMinutes(10),
        ]);

        if ($user->email) {
            Notification::route('mail', $user->email)
                ->notify(new OtpNotification($otp, 'password_reset'));
        }
    }

    public function verifyOtpAndResetPassword(string $identifier, string $otp, string $password): void
    {
        $record = OtpVerification::query()
            ->where('identifier', $identifier)
            ->where('otp', $otp)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (! $record) {
            throw ValidationException::withMessages([
                'otp' => ['Invalid or expired OTP.'],
            ]);
        }

        $user = $this->resolveUserByLogin($identifier);

        if (! $user) {
            throw ValidationException::withMessages([
                'identifier' => ['Account not found.'],
            ]);
        }

        $user->update(['password' => $password]);
        $record->update(['is_used' => true]);
    }

    public function registerStaff(array $data, UserRole $role): User
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'role' => $role,
            'password' => $data['password'],
            'is_active' => true,
        ]);
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }
}
