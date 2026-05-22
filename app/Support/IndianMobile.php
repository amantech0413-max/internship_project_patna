<?php

namespace App\Support;

class IndianMobile
{
    /**
     * Normalize Indian mobile numbers for import/entry.
     * Accepts 10 digits, or 11–12 digits with leading 0 / 91 / +91 — uses the last 10 digits.
     */
    public static function normalize(?string $value): ?string
    {
        $digits = preg_replace('/\D/', '', (string) $value);

        if ($digits === '') {
            return null;
        }

        if (strlen($digits) > 10) {
            $digits = substr($digits, -10);
        } elseif (strlen($digits) === 11 && str_starts_with($digits, '0')) {
            $digits = substr($digits, 1);
        }

        if (! preg_match('/^\d{10}$/', $digits)) {
            return null;
        }

        return $digits;
    }

    public static function isValid(?string $value): bool
    {
        return self::normalize($value) !== null;
    }
}
