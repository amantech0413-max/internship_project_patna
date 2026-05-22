<?php

namespace App\Support;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;

class StaffPermissions
{
    public const STAFF_ENTRY = 'staff_entry';

    public const BULK_STUDENT_VIEW = 'bulk_student_view';

    public const BULK_STUDENT_CREATE = 'bulk_student_create';

    public const BULK_STUDENT_EDIT = 'bulk_student_edit';

    public const BULK_STUDENT_DELETE = 'bulk_student_delete';

    public const STUDENT_VIEW = 'student_view';

    public const STUDENT_CREATE = 'student_create';

    public const STUDENT_EDIT = 'student_edit';

    public const STUDENT_DELETE = 'student_delete';

    public const STUDENT_APPROVE = 'student_approve';

    public const COLLEGE_MANAGE = 'college_manage';

    public const STAFF_MANAGE = 'staff_manage';

    public const BIN_MANAGE = 'bin_manage';

    public const BIN_DELETE_PERMANENT = 'bin_delete_permanent';

    public const PAYMENT_VIEW = 'payment_view';

    public const PAYMENT_STATUS = 'payment_status';

    /** @return list<string> */
    public static function keys(): array
    {
        return [
            self::STAFF_ENTRY,
            self::BULK_STUDENT_VIEW,
            self::BULK_STUDENT_CREATE,
            self::BULK_STUDENT_EDIT,
            self::BULK_STUDENT_DELETE,
            self::STUDENT_VIEW,
            self::STUDENT_CREATE,
            self::STUDENT_EDIT,
            self::STUDENT_DELETE,
            self::STUDENT_APPROVE,
            self::COLLEGE_MANAGE,
            self::STAFF_MANAGE,
            self::BIN_MANAGE,
            self::BIN_DELETE_PERMANENT,
            self::PAYMENT_VIEW,
            self::PAYMENT_STATUS,
        ];
    }

    /** @return array<string, bool> */
    public static function defaultsForStaff(): array
    {
        return [
            self::STAFF_ENTRY => true,
            self::BULK_STUDENT_VIEW => true,
            self::BULK_STUDENT_CREATE => true,
            self::BULK_STUDENT_EDIT => false,
            self::BULK_STUDENT_DELETE => false,
            self::STUDENT_VIEW => true,
            self::STUDENT_CREATE => false,
            self::STUDENT_EDIT => false,
            self::STUDENT_DELETE => false,
            self::STUDENT_APPROVE => false,
            self::COLLEGE_MANAGE => false,
            self::STAFF_MANAGE => false,
        ];
    }

    /** @return array<string, bool> */
    public static function allGranted(): array
    {
        return array_fill_keys(self::keys(), true);
    }

    /**
     * @param  array<string, bool>|null  $permissions
     * @return array<string, bool>
     */
    public static function normalize(?array $permissions): array
    {
        $base = self::defaultsForStaff();
        foreach (self::keys() as $key) {
            if (array_key_exists($key, $permissions ?? [])) {
                $base[$key] = (bool) $permissions[$key];
            }
        }

        return $base;
    }

    /** @return array<string, string> */
    public static function labels(): array
    {
        return [
            self::STAFF_ENTRY => 'Staff Entry & Excel Import',
            self::BULK_STUDENT_VIEW => 'View Bulk Students',
            self::BULK_STUDENT_CREATE => 'Create Bulk Students',
            self::BULK_STUDENT_EDIT => 'Edit Bulk Students',
            self::BULK_STUDENT_DELETE => 'Delete Bulk Students',
            self::STUDENT_VIEW => 'View Students',
            self::STUDENT_CREATE => 'Create Students',
            self::STUDENT_EDIT => 'Edit Students',
            self::STUDENT_DELETE => 'Delete Students',
            self::STUDENT_APPROVE => 'Approve / Reject Students',
            self::COLLEGE_MANAGE => 'Manage Colleges',
            self::STAFF_MANAGE => 'Manage Staff Users',
            self::BIN_MANAGE => 'Recycle Bin — Restore Items',
            self::BIN_DELETE_PERMANENT => 'Recycle Bin — Delete Permanently',
            self::PAYMENT_VIEW => 'View Payments',
            self::PAYMENT_STATUS => 'Update Payment Status',
        ];
    }

    /** @return array<string, mixed> */
    public static function accessPayload(?User $user = null): array
    {
        $dbPermissions = Permission::query()->orderBy('key')->get();
        $labels = self::labels();
        $permissionList = $dbPermissions->isNotEmpty()
            ? $dbPermissions->map(fn ($p) => [
                'id' => $p->id,
                'key' => $p->key,
                'label' => $p->label,
            ])->values()->all()
            : collect(self::keys())->map(fn ($k) => ['key' => $k, 'label' => $labels[$k] ?? $k])->values()->all();

        $assignableRoles = Role::query()->assignable()->with('permissions')->orderBy('name')->get();

        return [
            'permissions' => [
                'keys' => self::keys(),
                'labels' => $labels,
                'defaults' => self::defaultsForStaff(),
                'list' => $permissionList,
            ],
            'roles' => $assignableRoles->map(fn ($r) => [
                'id' => $r->id,
                'name' => $r->name,
                'slug' => $r->slug,
                'permission_keys' => $r->permissions->pluck('key')->values(),
            ])->values()->all(),
            'menu' => config('admin_access.menu', []),
            'route_permissions' => config('admin_access.route_permissions', []),
            'admin_only_routes' => config('admin_access.admin_only_routes', []),
            'super_admin_only_routes' => config('admin_access.super_admin_only_routes', []),
            'can_manage_roles' => $user?->isSuperAdmin() ?? false,
        ];
    }
}
