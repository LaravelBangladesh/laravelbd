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

        return response(rtrim(implode("\n", $lines))."\n", 200, ['Content-Type' => 'text/markdown']);
    }
}
