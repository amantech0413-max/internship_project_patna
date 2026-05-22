<?php

use App\Support\StaffPermissions;

return [
    'route_permissions' => [
        'colleges' => StaffPermissions::COLLEGE_MANAGE,
        'entry' => StaffPermissions::STAFF_ENTRY,
        'import-logs' => StaffPermissions::STAFF_ENTRY,
        'students' => StaffPermissions::STUDENT_VIEW,
        'student-create' => StaffPermissions::STUDENT_CREATE,
        'student-edit' => StaffPermissions::STUDENT_EDIT,
        'staff-users' => StaffPermissions::STAFF_MANAGE,
        'bin' => StaffPermissions::BIN_MANAGE,
    ],

    'super_admin_only_routes' => [
        'roles',
    ],

    'admin_only_routes' => [
        'groups',
        'group-create',
        'group-edit',
        'whatsapp',
        'reports',
        'certificates',
        'notifications',
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
            'title' => 'Students',
            'items' => [
                ['to' => '/students', 'route' => 'students', 'label' => 'Students', 'icon' => 'bi-people', 'permission' => StaffPermissions::STUDENT_VIEW],
            ],
        ],
        [
            'title' => 'Internship (Full)',
            'items' => [
                ['to' => '/groups', 'route' => 'groups', 'label' => 'Internship Groups', 'icon' => 'bi-collection', 'admin_only' => true],
                ['to' => '/whatsapp', 'route' => 'whatsapp', 'label' => 'WhatsApp', 'icon' => 'bi-whatsapp', 'admin_only' => true],
                ['to' => '/reports', 'route' => 'reports', 'label' => 'Reports', 'icon' => 'bi-bar-chart', 'admin_only' => true],
                ['to' => '/certificates', 'route' => 'certificates', 'label' => 'Certificates', 'icon' => 'bi-award', 'admin_only' => true],
                ['to' => '/notifications', 'route' => 'notifications', 'label' => 'Notifications', 'icon' => 'bi-bell', 'admin_only' => true],
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
