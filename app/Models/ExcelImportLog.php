<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExcelImportLog extends Model
{
    protected $fillable = [
        'college_id',
        'imported_by',
        'file_name',
        'total_rows',
        'success_count',
        'failed_count',
        'skipped_count',
        'errors',
    ];

    protected function casts(): array
    {
        return [
            'errors' => 'array',
        ];
    }

    public function college(): BelongsTo
    {
        return $this->belongsTo(College::class);
    }

    public function importer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'imported_by');
    }
}
