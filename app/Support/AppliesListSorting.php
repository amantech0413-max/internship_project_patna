<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;

trait AppliesListSorting
{
    /**
     * @param  list<string>  $allowed
     */
    protected function applyListSorting(Builder $query, array $filters, array $allowed, string $defaultColumn = 'created_at'): void
    {
        $sortBy = $filters['sort_by'] ?? $defaultColumn;
        $sortDir = strtolower((string) ($filters['sort_dir'] ?? 'desc')) === 'asc' ? 'asc' : 'desc';

        if (! in_array($sortBy, $allowed, true)) {
            $sortBy = $defaultColumn;
        }

        $query->orderBy($sortBy, $sortDir);
    }
}
