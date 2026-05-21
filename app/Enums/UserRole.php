<?php

namespace App\Enums;

enum UserRole: string
{
    case SuperAdmin = 'super_admin';
    case Admin = 'admin';
    case Student = 'student';
    case CollegeCoordinator = 'college_coordinator';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::Admin => 'Admin',
            self::Student => 'Student',
            self::CollegeCoordinator => 'College Coordinator',
        };
    }

    public function isStaff(): bool
    {
        return in_array($this, [self::SuperAdmin, self::Admin, self::CollegeCoordinator], true);
    }
}
