<?php

namespace Database\Seeders;

use App\Enums\InternshipMode;
use App\Enums\StudentStatus;
use App\Helpers\StudentCodeGenerator;
use App\Models\College;
use App\Models\Role;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = config('demo_accounts');
        $password = Hash::make($accounts['password']);

        $superRole = Role::where('slug', 'super_admin')->firstOrFail();
        $adminRole = Role::where('slug', 'admin')->firstOrFail();

        $super = User::create([
            'name' => $accounts['super_admin']['name'],
            'email' => $accounts['super_admin']['email'],
            'phone' => $accounts['super_admin']['phone'],
            'role_id' => $superRole->id,
            'password' => $password,
            'is_active' => true,
        ]);

        $admin = User::create([
            'name' => $accounts['admin']['name'],
            'email' => $accounts['admin']['email'],
            'phone' => $accounts['admin']['phone'],
            'role_id' => $adminRole->id,
            'password' => $password,
            'is_active' => true,
        ]);

        $staffUsers = [];
        foreach ($accounts['staff'] as $row) {
            $role = Role::where('slug', $row['role_slug'])->where('is_assignable', true)->firstOrFail();
            $staffUsers[] = User::create([
                'name' => $row['name'],
                'email' => $row['email'],
                'phone' => $row['phone'],
                'role_id' => $role->id,
                'password' => $password,
                'is_active' => true,
            ]);
        }

        $colleges = [];
        foreach (config('internship_registration.colleges', []) as $slug => $fullName) {
            $colleges[] = College::create([
                'college_name' => $fullName,
                'slug' => $slug,
                'address' => 'Bihar, India',
                'contact_person' => 'Coordinator',
                'mobile_number' => '9876543210',
                'status' => 'active',
            ]);
        }

        if ($colleges === []) {
            $colleges[] = College::create([
                'college_name' => 'Demo College',
                'slug' => 'demo-college',
                'status' => 'active',
            ]);
        }

        $statuses = [
            StudentStatus::Pending,
            StudentStatus::Approved,
            StudentStatus::Approved,
            StudentStatus::Rejected,
        ];

        $firstNames = ['Amit', 'Priya', 'Rahul', 'Sneha', 'Vikash', 'Anjali', 'Rohan', 'Kavita', 'Suresh', 'Pooja', 'Manish', 'Neha', 'Arjun', 'Divya', 'Sanjay'];

        for ($i = 0; $i < 15; $i++) {
            $college = $colleges[$i % count($colleges)];
            $creator = $staffUsers[$i % count($staffUsers)] ?? $admin;
            $status = $statuses[$i % count($statuses)];

            Student::create([
                'college_id' => $college->id,
                'created_by' => $creator->id,
                'student_code' => StudentCodeGenerator::generateStudentCode(),
                'registration_no' => 'REG2026'.str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
                'student_name' => $firstNames[$i].' Kumar',
                'father_name' => 'Father '.($i + 1),
                'college_roll_no' => 'CR'.str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT),
                'university_roll_no' => 'UR'.str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT),
                'subject' => 'Computer Science',
                'mobile_number' => '98'.str_pad((string) (10000000 + $i), 8, '0', STR_PAD_LEFT),
                'email' => $i % 3 === 0 ? "student{$i}@demo.local" : null,
                'internship_mode' => $i % 2 === 0 ? InternshipMode::Online : InternshipMode::Offline,
                'status' => $status,
                'profile_completed' => true,
            ]);
        }

        $this->command?->info('Demo data seeded. Password for all users: '.$accounts['password']);
        $this->command?->info('Super Admin: '.$super->email);
        $this->command?->info('Admin: '.$admin->email);
    }
}
