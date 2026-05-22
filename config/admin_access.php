<?php

use App\Support\StaffPermissions;

return [
    'route_permissions' => [
        'colleges' => StaffPermissions::COLLEGE_MANAGE,
        'entry' => StaffPermissions::STAFF_ENTRY,
        'import-logs' => StaffPermissions::STAFF_ENTRY,
        'bulk-students' => StaffPermissions::BULK_STUDENT_VIEW,
        'students' => StaffPermissions::STUDENT_VIEW,
        'student-edit' => StaffPermissions::STUDENT_VIEW,
        'staff-users' => StaffPermissions::STAFF_MANAGE,
        'bin' => StaffPermissions::BIN_MANAGE,
        'payments' => StaffPermissions::PAYMENT_VIEW,
    ],

    'super_admin_only_routes' => [
        'roles',
    ],

    'admin_only_routes' => [
        'whatsapp',
        'reports',
        'certificates',
        'settings',
    ],

    'menu' => [
        [
            'title' => 'Main',
            'items' => [
                ['to' => '/dashboard', 'route' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'bi-speedometer2'],
            ],
        ],
        [
            'title' => 'College & Entry',
            'items' => [
                ['to' => '/colleges', 'route' => 'colleges', 'label' => 'Colleges', 'icon' => 'bi-building', 'permission' => StaffPermissions::COLLEGE_MANAGE],
                ['to' => '/entry', 'route' => 'entry', 'label' => 'Add Student', 'icon' => 'bi-person-plus', 'permission' => StaffPermissions::STAFF_ENTRY],
                ['to' => '/import-logs', 'route' => 'import-logs', 'label' => 'Import Logs', 'icon' => 'bi-file-earmark-spreadsheet', 'permission' => StaffPermissions::STAFF_ENTRY],
            ],
        ],
        [
            'title' => 'Bulk Students',
            'items' => [
                ['to' => '/bulk-students', 'route' => 'bulk-students', 'label' => 'Bulk Students', 'icon' => 'bi-person-lines-fill', 'permission' => StaffPermissions::BULK_STUDENT_VIEW],
            ],
        ],
        [
            'title' => 'Students',
            'items' => [
                ['to' => '/students', 'route' => 'students', 'label' => 'Students', 'icon' => 'bi-people', 'permission' => StaffPermissions::STUDENT_VIEW],
                ['to' => '/payments', 'route' => 'payments', 'label' => 'Payments', 'icon' => 'bi-credit-card', 'permission' => StaffPermissions::PAYMENT_VIEW],
            ],
        ],
        [
            'title' => 'Internship (Full)',
            'items' => [
                ['to' => '/whatsapp', 'route' => 'whatsapp', 'label' => 'WhatsApp', 'icon' => 'bi-whatsapp', 'admin_only' => true],
                ['to' => '/reports', 'route' => 'reports', 'label' => 'Reports', 'icon' => 'bi-bar-chart', 'admin_only' => true],
                ['to' => '/certificates', 'route' => 'certificates', 'label' => 'Certificates', 'icon' => 'bi-award', 'admin_only' => true],
                ['to' => '/settings', 'route' => 'settings', 'label' => 'Settings', 'icon' => 'bi-gear', 'admin_only' => true],
            ],
        ],
        [
            'title' => 'Administration',
            'items' => [
                ['to' => '/roles', 'route' => 'roles', 'label' => 'Roles & Permissions', 'icon' => 'bi-shield-lock', 'super_admin_only' => true],
                ['to' => '/staff-users', 'route' => 'staff-users', 'label' => 'Staff Users', 'icon' => 'bi-person-badge', 'permission' => StaffPermissions::STAFF_MANAGE],
                [
                    'to' => '/bin',
                    'route' => 'bin',
                    'label' => 'Recycle Bin',
                    'icon' => 'bi-trash',
                    'permissions' => [StaffPermissions::BIN_MANAGE, StaffPermissions::BIN_DELETE_PERMANENT],
                ],
            ],
        ],
    ],
];
