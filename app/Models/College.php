<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
class College extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'college_name',
        'slug',
        'address',
        'contact_person',
        'mobile_number',
        'status',
    ];

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function importLogs(): HasMany
    {
        return $this->hasMany(ExcelImportLog::class);
    }

    public static function shortDisplayName(string $collegeName): string
    {
        if (preg_match('/Internship Jun 2026\s+(.+)$/i', $collegeName, $matches)) {
            return trim($matches[1]);
        }

        return $collegeName;
    }

    public function registrationLabel(): string
    {
        return self::shortDisplayName($this->college_name);
    }
}
