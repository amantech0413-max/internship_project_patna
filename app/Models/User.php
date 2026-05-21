<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Support\StaffPermissions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'role',
        'permissions',
        'password',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'role' => UserRole::class,
            'permissions' => 'array',
        ];
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->role === UserRole::SuperAdmin || $this->role === UserRole::Admin) {
            return true;
        }

        if ($this->role !== UserRole::CollegeCoordinator) {
            return false;
        }

        $perms = $this->effectivePermissions();

        return (bool) ($perms[$permission] ?? false);
    }

    /** @return array<string, bool> */
    public function effectivePermissions(): array
    {
        if ($this->role === UserRole::SuperAdmin || $this->role === UserRole::Admin) {
            return StaffPermissions::allGranted();
        }

        if ($this->role === UserRole::CollegeCoordinator) {
            return StaffPermissions::normalize($this->permissions);
        }

        return [];
    }

    public function student(): HasOne
    {
        return $this->hasOne(Student::class);
    }

    public function coordinatedStudents(): HasMany
    {
        return $this->hasMany(Student::class, 'college_coordinator_id');
    }

    public function isStaff(): bool
    {
        return $this->role?->isStaff() ?? false;
    }

    public function isStudent(): bool
    {
        return $this->role === UserRole::Student;
    }
}
