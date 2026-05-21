<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\College;
use App\Models\Student;
use App\Models\User;
use App\Support\StaffPermissions;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@bhagyalaxmi.local'],
            [
            'name' => 'Admin User',
            'email' => 'admin@bhagyalaxmi.local',
            'phone' => '9876543211',
            'role' => UserRole::Admin,
            'password' => Hash::make('password'),
            'is_active' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'staff@bhagyalaxmi.local'],
            [
                'name' => 'College Staff Demo',
                'email' => 'staff@bhagyalaxmi.local',
                'phone' => '9876543212',
                'role' => UserRole::CollegeCoordinator,
                'password' => Hash::make('password'),
                'is_active' => true,
                'permissions' => array_merge(StaffPermissions::defaultsForStaff(), [
                    StaffPermissions::STUDENT_EDIT => true,
                    StaffPermissions::STUDENT_APPROVE => true,
                ]),
            ]
        );

        $collegeA = College::firstOrCreate(
            ['college_name' => 'Demo College Mohali'],
            [
            'college_name' => 'Demo College Mohali',
            'address' => 'Phase 7, Mohali, Chandigarh',
            'contact_person' => 'Dr. Sharma',
            'mobile_number' => '9876543210',
            'status' => 'active',
            ]
        );

        $collegeB = College::firstOrCreate(
            ['college_name' => 'PTU Regional College'],
            ['college_name' => 'PTU Regional College', 'address' => 'Punjab', 'status' => 'active']
        );

        foreach ([
            ['college_id' => $collegeA->id, 'student_name' => 'Rahul Kumar', 'mobile_number' => '9999999999'],
            ['college_id' => $collegeA->id, 'student_name' => 'Priya Kumar', 'mobile_number' => '9999999999'],
            ['college_id' => $collegeB->id, 'student_name' => 'Aman Singh', 'mobile_number' => '7087063168'],
        ] as $row) {
            Student::firstOrCreate(
                ['college_id' => $row['college_id'], 'student_name' => $row['student_name'], 'mobile_number' => $row['mobile_number']],
                array_merge($row, ['created_by' => $admin->id, 'status' => 'approved'])
            );
        }
    }
}
