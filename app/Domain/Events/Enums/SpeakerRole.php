<?php

namespace App\Domain\Events\Enums;

enum SpeakerRole: string
{
    case Speaker = 'speaker';
    case Host = 'host';
    case Moderator = 'moderator';

    public function label(): string
    {
        return match ($this) {
            self::Speaker => __('events.speakers.roles.speaker'),
            self::Host => __('events.speakers.roles.host'),
            self::Moderator => __('events.speakers.roles.moderator'),
        };
    }
}
