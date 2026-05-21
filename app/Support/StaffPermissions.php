<?php

namespace App\Support;

class StaffPermissions
{
    public const STAFF_ENTRY = 'staff_entry';

    public const STUDENT_VIEW = 'student_view';

    public const STUDENT_CREATE = 'student_create';

    public const STUDENT_EDIT = 'student_edit';

    public const STUDENT_DELETE = 'student_delete';

    public const STUDENT_APPROVE = 'student_approve';

    public const COLLEGE_MANAGE = 'college_manage';

    public const STAFF_MANAGE = 'staff_manage';

    /** @return list<string> */
    public static function keys(): array
    {
        return [
            self::STAFF_ENTRY,
            self::STUDENT_VIEW,
            self::STUDENT_CREATE,
            self::STUDENT_EDIT,
            self::STUDENT_DELETE,
            self::STUDENT_APPROVE,
            self::COLLEGE_MANAGE,
            self::STAFF_MANAGE,
        ];
    }

    /** @return array<string, bool> */
    public static function defaultsForStaff(): array
    {
        return [
            self::STAFF_ENTRY => true,
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
}
