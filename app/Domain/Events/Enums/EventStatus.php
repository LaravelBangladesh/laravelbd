<?php

namespace App\Domain\Events\Enums;

enum EventStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => __('events.status.draft'),
            self::Published => __('events.status.published'),
            self::Cancelled => __('events.status.cancelled'),
        };
    }
}
