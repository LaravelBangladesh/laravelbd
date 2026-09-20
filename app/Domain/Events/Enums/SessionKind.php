<?php

namespace App\Domain\Events\Enums;

enum SessionKind: string
{
    case Talk = 'talk';
    case Workshop = 'workshop';
    case Panel = 'panel';
    case Keynote = 'keynote';
    case Break = 'break';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Talk => __('events.sessions.kinds.talk'),
            self::Workshop => __('events.sessions.kinds.workshop'),
            self::Panel => __('events.sessions.kinds.panel'),
            self::Keynote => __('events.sessions.kinds.keynote'),
            self::Break => __('events.sessions.kinds.break'),
            self::Other => __('events.sessions.kinds.other'),
        };
    }
}
