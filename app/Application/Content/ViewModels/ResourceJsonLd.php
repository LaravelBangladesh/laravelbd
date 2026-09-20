<?php

namespace App\Application\Content\ViewModels;

use App\Application\Shared\ViewModels\JsonLd;
use App\Application\Shared\ViewModels\MetaDescription;
use App\Domain\Content\Enums\ResourceKind;
use App\Domain\Content\Models\Resource;
use App\Domain\Shared\VideoEmbed;

final class ResourceJsonLd
{
    /**
     * Build the schema.org entity describing a resource, which varies by the
     * kind of thing the resource points at.
     *
     * @return array<string, mixed>
     */
    public static function make(Resource $resource): array
    {
        $url = route('resources.show', $resource->slug);
        $name = $resource->localized('title');
        $description = MetaDescription::make(
            $resource->localized('excerpt'),
            $resource->localized('description'),
        );

        return match ($resource->kind) {
            ResourceKind::Video => self::video($resource, $name, $description, $url),
            ResourceKind::Article => self::article($resource, $name, $description, $url),
            ResourceKind::Link => self::webPage($name, $description, $url),
        };
    }

    /**
     * @return array<string, mixed>
     */
    private static function video(Resource $resource, string $name, string $description, string $url): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'VideoObject',
            'name' => $name,
            'description' => $description,
            'url' => $url,
        ];

        $videoId = $resource->embed_url === null ? null : VideoEmbed::id($resource->embed_url);

        if ($videoId !== null) {
            $schema['embedUrl'] = 'https://www.youtube.com/embed/'.$videoId;
            $schema['contentUrl'] = 'https://www.youtube.com/watch?v='.$videoId;
            $schema['thumbnailUrl'] = 'https://img.youtube.com/vi/'.$videoId.'/maxresdefault.jpg';
        }

        if ($resource->published_at !== null) {
            $schema['uploadDate'] = $resource->published_at->toIso8601String();
        }

        return $schema;
    }

    /**
     * @return array<string, mixed>
     */
    private static function article(Resource $resource, string $name, string $description, string $url): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $name,
            'description' => $description,
            'url' => $url,
            'publisher' => JsonLd::organizer(),
        ];

        if ($resource->published_at !== null) {
            $schema['datePublished'] = $resource->published_at->toIso8601String();
        }

        if ($resource->speaker !== null) {
            $schema['author'] = [
                '@type' => 'Person',
                'name' => $resource->speaker->name,
            ];
        }

        return $schema;
    }

    /**
     * @return array<string, mixed>
     */
    private static function webPage(string $name, string $description, string $url): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebPage',
            'name' => $name,
            'description' => $description,
            'url' => $url,
        ];
    }
}
