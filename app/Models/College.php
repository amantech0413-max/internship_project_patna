<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class College extends Model
{
    protected $fillable = [
        'college_name',
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
}
