<?php

/**
 * Demo login accounts (password for all: Admin@123)
 * Used by DemoDatabaseSeeder after migrate:fresh --seed
 */
return [
    'password' => 'Admin@123',

    'super_admin' => [
        'email' => 'super@bhagyalaxmi.local',
        'name' => 'Super Admin',
        'phone' => '9000000001',
    ],

    'admin' => [
        'email' => 'admin@bhagyalaxmi.local',
        'name' => 'Admin User',
        'phone' => '9000000002',
    ],

    'staff' => [
        ['email' => 'staff1@bhagyalaxmi.local', 'name' => 'Staff Entry Only', 'phone' => '9000000011', 'role_slug' => 'entry-operator'],
        ['email' => 'staff2@bhagyalaxmi.local', 'name' => 'Staff Student Viewer', 'phone' => '9000000012', 'role_slug' => 'student-viewer'],
        ['email' => 'staff3@bhagyalaxmi.local', 'name' => 'Staff Student Manager', 'phone' => '9000000013', 'role_slug' => 'student-manager'],
        ['email' => 'staff4@bhagyalaxmi.local', 'name' => 'Staff College Manager', 'phone' => '9000000014', 'role_slug' => 'college-coordinator-role'],
        ['email' => 'staff5@bhagyalaxmi.local', 'name' => 'Staff Full Panel', 'phone' => '9000000015', 'role_slug' => 'full-staff-panel'],
    ],
];
