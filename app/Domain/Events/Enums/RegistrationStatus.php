<?php

namespace App\Domain\Events\Enums;

enum RegistrationStatus: string
{
    case Registered = 'registered';
    case Cancelled = 'cancelled';
    case Waitlisted = 'waitlisted';

    public function label(): string
    {
        return match ($this) {
            self::Registered => __('events.rsvp.registered'),
            self::Cancelled => __('events.rsvp.cancelled'),
            self::Waitlisted => __('events.rsvp.waitlisted'),
        };
    }
}
