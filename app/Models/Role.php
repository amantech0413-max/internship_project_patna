<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_system',
        'is_assignable',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
            'is_assignable' => 'boolean',
        ];
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'role_id');
    }

    public function grantsAllPermissions(): bool
    {
        return in_array($this->slug, ['super_admin', 'admin'], true);
    }

    public function canAccessPanel(): bool
    {
        return $this->slug !== 'student';
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeAssignable(Builder $query): Builder
    {
        return $query->where('is_assignable', true);
    }

    public function scopeManagedBySuperAdmin(Builder $query): Builder
    {
        return $query->where('is_assignable', true);
    }

    /** @return list<string> */
    public function permissionKeys(): array
    {
        return $this->permissions->pluck('key')->all();
    }
}
