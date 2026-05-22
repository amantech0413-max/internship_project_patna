<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Support\StaffPermissions;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissionIds = [];
        foreach (StaffPermissions::labels() as $key => $label) {
            $permissionIds[$key] = Permission::updateOrCreate(
                ['key' => $key],
                ['label' => $label, 'group' => 'staff']
            )->id;
        }

        $sync = fn (Role $role, array $keys) => $role->permissions()->sync(
            collect($keys)->map(fn ($k) => $permissionIds[$k])->filter()->values()
        );

        foreach ([
            ['slug' => 'super_admin', 'name' => 'Super Admin', 'description' => 'Full system access'],
            ['slug' => 'admin', 'name' => 'Admin', 'description' => 'Full panel access'],
        ] as $row) {
            $role = Role::updateOrCreate(
                ['slug' => $row['slug']],
                [
                    'name' => $row['name'],
                    'description' => $row['description'],
                    'is_system' => true,
                    'is_assignable' => false,
                ]
            );
            $sync($role, StaffPermissions::keys());
        }

        Role::updateOrCreate(
            ['slug' => 'student'],
            [
                'name' => 'Student',
                'description' => 'No admin panel access',
                'is_system' => true,
                'is_assignable' => false,
            ]
        );

        $assignable = [
            [
                'slug' => 'entry-operator',
                'name' => 'Entry Operator',
                'description' => 'Staff entry + import only',
                'keys' => [
                    StaffPermissions::STAFF_ENTRY,
                    StaffPermissions::BULK_STUDENT_VIEW,
                    StaffPermissions::BULK_STUDENT_CREATE,
                ],
            ],
            [
                'slug' => 'student-viewer',
                'name' => 'Student Viewer',
                'description' => 'View students and entry',
                'keys' => [
                    StaffPermissions::STAFF_ENTRY,
                    StaffPermissions::BULK_STUDENT_VIEW,
                    StaffPermissions::STUDENT_VIEW,
                ],
            ],
            [
                'slug' => 'student-manager',
                'name' => 'Student Manager',
                'description' => 'View, create, edit, approve students',
                'keys' => [
                    StaffPermissions::STAFF_ENTRY,
                    StaffPermissions::BULK_STUDENT_VIEW,
                    StaffPermissions::BULK_STUDENT_CREATE,
                    StaffPermissions::BULK_STUDENT_EDIT,
                    StaffPermissions::BULK_STUDENT_DELETE,
                    StaffPermissions::STUDENT_VIEW,
                    StaffPermissions::STUDENT_CREATE,
                    StaffPermissions::STUDENT_EDIT,
                    StaffPermissions::STUDENT_DELETE,
                    StaffPermissions::STUDENT_APPROVE,
                ],
            ],
            [
                'slug' => 'college-coordinator-role',
                'name' => 'College Manager',
                'description' => 'Manage colleges + entry',
                'keys' => [
                    StaffPermissions::COLLEGE_MANAGE,
                    StaffPermissions::STAFF_ENTRY,
                    StaffPermissions::BULK_STUDENT_VIEW,
                    StaffPermissions::BULK_STUDENT_CREATE,
                    StaffPermissions::BULK_STUDENT_EDIT,
                    StaffPermissions::STUDENT_VIEW,
                ],
            ],
            [
                'slug' => 'full-staff-panel',
                'name' => 'Full Staff Panel',
                'description' => 'All staff permissions except staff user management',
                'keys' => array_values(array_filter(
                    StaffPermissions::keys(),
                    fn ($k) => $k !== StaffPermissions::STAFF_MANAGE
                )),
            ],
        ];

        foreach ($assignable as $row) {
            $keys = $row['keys'];
            unset($row['keys']);
            $role = Role::updateOrCreate(
                ['slug' => $row['slug']],
                array_merge($row, ['is_system' => false, 'is_assignable' => true])
            );
            $sync($role, $keys);
        }
    }
}
