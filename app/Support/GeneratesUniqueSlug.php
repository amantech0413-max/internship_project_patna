<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

trait GeneratesUniqueSlug
{
    protected function assertUniqueSlug(Model $model, string $slug, ?int $ignoreId = null): string
    {
        $slug = Str::slug($slug);

        if ($slug === '') {
            throw ValidationException::withMessages([
                'slug' => ['Slug is invalid. Use letters and numbers only.'],
            ]);
        }

        if ($this->slugExists($model, $slug, $ignoreId)) {
            throw ValidationException::withMessages([
                'slug' => ['This slug is already in use. Please choose a different slug.'],
            ]);
        }

        return $slug;
    }

    /** Auto slug from name; appends -2, -3 if taken. */
    protected function makeUniqueSlug(Model $model, string $base, ?int $ignoreId = null): string
    {
        $base = Str::slug($base) ?: 'item';
        $slug = $base;
        $n = 1;

        while ($this->slugExists($model, $slug, $ignoreId)) {
            $slug = $base.'-'.$n++;
        }

        return $slug;
    }

    protected function slugExists(Model $model, string $slug, ?int $ignoreId = null): bool
    {
        $query = $model->newQuery()->where('slug', $slug);

        if (in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses_recursive($model))) {
            $query->withTrashed();
        }

        if ($ignoreId) {
            $query->where($model->getKeyName(), '!=', $ignoreId);
        }

        return $query->exists();
    }

    protected function slugFromName(string $name): string
    {
        return Str::slug($name) ?: 'item';
    }
}
