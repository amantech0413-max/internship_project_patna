<?php

namespace App\Models;

use App\Models\Role;
use App\Support\StaffPermissions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'role_id',
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
        ];
    }

    public function roleModel(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function roleSlug(): ?string
    {
        return $this->roleModel?->slug;
    }

    public function isSuperAdmin(): bool
    {
        return $this->roleSlug() === 'super_admin';
    }

    public function isAdmin(): bool
    {
        return in_array($this->roleSlug(), ['super_admin', 'admin'], true);
    }

    public function isStudent(): bool
    {
        return $this->roleSlug() === 'student';
    }

    public function canAccessPanel(): bool
    {
        return $this->roleSlug() !== null && $this->roleSlug() !== 'student';
    }

    public function hasPermission(string $permission): bool
    {
        $perms = $this->effectivePermissions();

        return (bool) ($perms[$permission] ?? false);
    }

    /** @return array<string, bool> */
    public function effectivePermissions(): array
    {
        $this->loadMissing('roleModel.permissions');

        $role = $this->roleModel;

        if (! $role) {
            return array_fill_keys(StaffPermissions::keys(), false);
        }

        if (in_array($role->slug, ['super_admin', 'admin'], true)) {
            return StaffPermissions::allGranted();
        }

        $granted = array_fill_keys(StaffPermissions::keys(), false);
        foreach ($role->permissions as $perm) {
            $granted[$perm->key] = true;
        }

        return $granted;
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
        return $this->canAccessPanel();
    }
}
