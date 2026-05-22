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

        $query = $model->newQuery()->where('slug', $slug);

        if (in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses_recursive($model))) {
            $query->withTrashed();
        }

        if ($ignoreId) {
            $query->where($model->getKeyName(), '!=', $ignoreId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'slug' => ['This slug is already in use. Please choose a different slug.'],
            ]);
        }

        return $slug;
    }

    protected function slugFromName(string $name): string
    {
        return Str::slug($name) ?: 'item';
    }
}
