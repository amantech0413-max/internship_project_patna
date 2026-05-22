<?php

namespace App\Helpers;

use App\Models\Student;

class StudentCodeGenerator
{
    public static function generateStudentCode(): string
    {
        $year = now()->format('Y');

        return self::nextCode('student_code', "BLI{$year}S", $year);
    }

    public static function generateRegistrationNo(): string
    {
        $year = now()->format('Y');

        return self::nextCode('registration_no', "REG{$year}", $year);
    }

    /** @deprecated Use generateStudentCode() */
    public static function generate(): string
    {
        return self::generateStudentCode();
    }

    protected static function nextCode(string $column, string $prefix, string $year): string
    {

        $last = Student::query()
            ->where($column, 'like', "{$prefix}%")
            ->orderByDesc('id')
            ->value($column);

        $sequence = 1;
        if ($last && preg_match('/(\d+)$/', $last, $matches)) {
            $sequence = (int) $matches[1] + 1;
        }

        return $prefix . str_pad((string) $sequence, 3, '0', STR_PAD_LEFT);
    }
}
