<?php

namespace App\Application\Shared\ViewModels;

final class Localized
{
    /**
     * Pick `{attribute}_{locale}` from a plain array (e.g. a JSON-stored
     * question), falling back to English like the LocalizesContent trait.
     *
     * @param  array<string, mixed>  $values
     */
    public static function pick(array $values, string $attribute): string
    {
        $value = $values[$attribute.'_'.app()->getLocale()] ?? null;

        if (is_string($value) && $value !== '') {
            return $value;
        }

        $fallback = $values[$attribute.'_en'] ?? null;

        return is_string($fallback) ? $fallback : '';
    }
}
