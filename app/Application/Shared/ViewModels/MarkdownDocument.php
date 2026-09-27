<?php

namespace App\Application\Shared\ViewModels;

use Illuminate\Http\Response;

class MarkdownDocument
{
    /**
     * @param  array<int, string>  $sections
     */
    public static function respond(string $title, array $sections): Response
    {
        $lines = ["# {$title}", ''];

        foreach ($sections as $section) {
            $lines[] = $section;
            $lines[] = '';
        }

        return response(rtrim(implode("\n", $lines))."\n", 200, [
            'Content-Type' => 'text/markdown',
            // Keep search engines pointed at the html page, not its .md twin.
            'Link' => '<'.request()->url().'>; rel="canonical"',
        ]);
    }

    /**
     * Turn numbered `{prefix}.N.title` / `{prefix}.N.body` translations into
     * headed sections, stopping at the first missing number.
     *
     * @return list<string>
     */
    public static function translatedSections(string $prefix, int $level = 2): array
    {
        $heading = str_repeat('#', $level);
        $sections = [];

        for ($n = 1; trans()->has("{$prefix}.{$n}.title"); $n++) {
            $sections[] = "{$heading} ".__("{$prefix}.{$n}.title")."\n\n".__("{$prefix}.{$n}.body");
        }

        return $sections;
    }

    /**
     * Turn numbered `{prefix}.N` translations into a bullet list.
     */
    public static function translatedList(string $prefix): string
    {
        $items = [];

        for ($n = 1; trans()->has("{$prefix}.{$n}"); $n++) {
            $items[] = '- '.__("{$prefix}.{$n}");
        }

        return implode("\n", $items);
    }
}
