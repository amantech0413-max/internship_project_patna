<?php

namespace App\Support;

class RegistrationPaths
{
    /** First URL segment — must not be treated as a college slug. */
    public static function reservedWebSegments(): array
    {
        return [
            'admin',
            'api',
            'login',
            'register',
            'storage',
            'build',
            'sanctum',
            'vendor',
            'assets',
            'dashboard',
            'colleges',
            'students',
            'entry',
            'up',
            'health',
        ];
    }

    public static function isReservedSegment(string $segment): bool
    {
        return in_array(strtolower($segment), self::reservedWebSegments(), true);
    }

    public static function collegeUrl(?string $slug, string $style = 'short'): ?string
    {
        $slug = trim((string) $slug);
        if ($slug === '') {
            return null;
        }

        return match ($style) {
            'admin' => url('/admin/register/'.$slug),
            'register' => url('/register/'.$slug),
            default => url('/'.$slug),
        };
    }

    /** @return array{short: string, register: string, admin: string}|null */
    public static function collegeUrls(?string $slug): ?array
    {
        $slug = trim((string) $slug);
        if ($slug === '') {
            return null;
        }

        return [
            'short' => self::collegeUrl($slug, 'short'),
            'register' => self::collegeUrl($slug, 'register'),
            'admin' => self::collegeUrl($slug, 'admin'),
        ];
    }
}
