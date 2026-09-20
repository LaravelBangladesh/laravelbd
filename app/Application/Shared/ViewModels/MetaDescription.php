<?php

namespace App\Application\Shared\ViewModels;

use Illuminate\Support\Str;

final class MetaDescription
{
    public const LIMIT = 160;

    /**
     * Flatten rich text into a single plain-text line short enough for a
     * search result snippet, falling back through the given candidates.
     */
    public static function make(?string ...$candidates): string
    {
        foreach ($candidates as $candidate) {
            $text = self::normalize($candidate);

            if ($text !== '') {
                return Str::limit($text, self::LIMIT);
            }
        }

        return '';
    }

    private static function normalize(?string $value): string
    {
        if ($value === null) {
            return '';
        }

        $text = strip_tags(html_entity_decode($value, ENT_QUOTES | ENT_HTML5));

        return trim((string) preg_replace('/\s+/u', ' ', $text));
    }
}
