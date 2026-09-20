<?php

namespace App\Domain\Content\Enums;

enum ResourceKind: string
{
    case Link = 'link';
    case Video = 'video';
    case Article = 'article';

    public function label(): string
    {
        return match ($this) {
            self::Link => __('resources.kinds.link'),
            self::Video => __('resources.kinds.video'),
            self::Article => __('resources.kinds.article'),
        };
    }

    public function needsVideo(): bool
    {
        return $this === self::Video;
    }
}
