<?php

namespace App\Domain\Events\Enums;

enum EventType: string
{
    case Meetup = 'meetup';
    case Workshop = 'workshop';
    case Conference = 'conference';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Meetup => __('events.types.meetup'),
            self::Workshop => __('events.types.workshop'),
            self::Conference => __('events.types.conference'),
            self::Other => __('events.types.other'),
        };
    }
}
