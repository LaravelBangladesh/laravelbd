<?php

namespace App\Domain\Content\Enums;

enum ResourceStatus: string
{
    case Draft = 'draft';
    case Published = 'published';

    public function label(): string
    {
        return match ($this) {
            self::Draft => __('events.status.draft'),
            self::Published => __('events.status.published'),
        };
    }
}
