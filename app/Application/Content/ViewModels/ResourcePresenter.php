<?php

namespace App\Application\Content\ViewModels;

use App\Application\Shared\ViewModels\Breadcrumbs;
use App\Application\Shared\ViewModels\MetaDescription;
use App\Domain\Content\Enums\ResourceKind;
use App\Domain\Content\Enums\ResourceStatus;
use App\Domain\Content\Models\Resource;
use App\Domain\Shared\VideoEmbed;

class ResourcePresenter
{
    /**
     * @return array<string, mixed>
     */
    public static function card(Resource $resource): array
    {
        return [
            'id' => $resource->id,
            'slug' => $resource->slug,
            'title' => $resource->localized('title'),
            'excerpt' => $resource->localized('excerpt'),
            'kind' => $resource->kind->value,
            'kind_label' => $resource->kind->label(),
            'event' => $resource->event === null ? null : [
                'slug' => $resource->event->slug,
                'title' => $resource->event->localized('title'),
            ],
            'speaker' => $resource->speaker === null ? null : [
                'name' => $resource->speaker->name,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function detail(Resource $resource): array
    {
        return [
            ...self::card($resource),
            'meta_description' => MetaDescription::make(
                $resource->localized('excerpt'),
                $resource->localized('description'),
            ),
            'json_ld' => [
                ResourceJsonLd::make($resource),
                Breadcrumbs::make([
                    __('nav.home') => url('/'),
                    __('nav.resources') => route('resources.index'),
                    $resource->localized('title') => route('resources.show', $resource->slug),
                ]),
            ],
            'description' => $resource->localized('description'),
            'url' => $resource->url,
            'embed' => $resource->embed_url ? VideoEmbed::src($resource->embed_url) : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function form(Resource $resource): array
    {
        return [
            'id' => $resource->id,
            'slug' => $resource->slug,
            'title_en' => $resource->title_en,
            'title_bn' => $resource->title_bn,
            'excerpt_en' => $resource->excerpt_en,
            'excerpt_bn' => $resource->excerpt_bn,
            'description_en' => $resource->description_en,
            'description_bn' => $resource->description_bn,
            'kind' => $resource->kind->value,
            'status' => $resource->status->value,
            'url' => $resource->url,
            'embed_url' => $resource->embed_url,
            'event_id' => $resource->event_id,
            'speaker_id' => $resource->speaker_id,
        ];
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function kinds(): array
    {
        return array_map(fn (ResourceKind $kind) => [
            'value' => $kind->value,
            'label' => $kind->label(),
        ], ResourceKind::cases());
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function statuses(): array
    {
        return array_map(fn (ResourceStatus $status) => [
            'value' => $status->value,
            'label' => $status->label(),
        ], ResourceStatus::cases());
    }
}
