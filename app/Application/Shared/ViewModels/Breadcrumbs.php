<?php

namespace App\Application\Shared\ViewModels;

final class Breadcrumbs
{
    /**
     * Build a schema.org BreadcrumbList from ordered name => url pairs.
     *
     * @param  array<string, string>  $trail
     * @return array<string, mixed>
     */
    public static function make(array $trail): array
    {
        $items = [];
        $position = 1;

        foreach ($trail as $name => $url) {
            $items[] = [
                '@type' => 'ListItem',
                'position' => $position,
                'name' => $name,
                'item' => $url,
            ];

            $position++;
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $items,
        ];
    }
}
