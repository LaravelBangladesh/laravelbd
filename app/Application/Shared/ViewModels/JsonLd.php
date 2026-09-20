<?php

namespace App\Application\Shared\ViewModels;

final class JsonLd
{
    public const FACEBOOK_GROUP = 'https://www.facebook.com/groups/laravelbangladesh';

    /**
     * @return array<string, mixed>
     */
    public static function organization(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => config('app.name'),
            'url' => url('/'),
            'logo' => asset('images/laravel-logo.svg'),
            'sameAs' => [self::FACEBOOK_GROUP],
            'foundingDate' => '2012',
            'areaServed' => 'BD',
        ];
    }

    /**
     * The organizer reference embedded in other entities, which only needs
     * to identify the community rather than describe it again.
     *
     * @return array<string, mixed>
     */
    public static function organizer(): array
    {
        return [
            '@type' => 'Organization',
            'name' => config('app.name'),
            'url' => url('/'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function website(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => config('app.name'),
            'url' => url('/'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function collectionPage(string $name, string $url, string $description): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'CollectionPage',
            'name' => $name,
            'url' => $url,
            'description' => $description,
        ];
    }
}
