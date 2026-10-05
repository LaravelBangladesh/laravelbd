<?php

namespace App\Domain\Shared;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UniqueSlug
{
    /**
     * @param  string|list<string>  $tables  every table whose slugs share one URL space
     */
    public static function make(string $title, string|array $tables, string|int|null $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'item';
        $slug = $base;
        $suffix = 2;

        while (self::taken($slug, (array) $tables, $ignoreId)) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    /**
     * @param  list<string>  $tables
     */
    private static function taken(string $slug, array $tables, string|int|null $ignoreId): bool
    {
        foreach ($tables as $table) {
            if (DB::table($table)
                ->where('slug', $slug)
                ->when($ignoreId !== null, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()) {
                return true;
            }
        }

        return false;
    }
}
