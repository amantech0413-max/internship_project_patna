<?php

namespace App\Helpers;

use App\Models\Student;

class StudentCodeGenerator
{
    public static function generateStudentCode(): string
    {
        return self::nextCode('student_code', "BLI%sS", now()->format('Y'));
    }

    public static function generateRegistrationNo(): string
    {
        return self::nextCode('registration_no', 'REG%s', now()->format('Y'));
    }

    /** @deprecated Use generateStudentCode() */
    public static function generate(): string
    {
        return self::generateStudentCode();
    }

    protected static function nextCode(string $column, string $pattern, string $year): string
    {
        $prefix = sprintf($pattern, $year);

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
