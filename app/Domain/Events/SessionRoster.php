<?php

namespace App\Domain\Events;

use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\EventSession;
use App\Domain\Identity\Models\User;

class SessionRoster
{
    public static function attach(Event $event, EventSession $session, User $speaker, string $role): void
    {
        $payload = ['role' => $role];

        $session->speakers()->syncWithoutDetaching([
            $speaker->id => $payload,
        ]);

        $event->speakers()->syncWithoutDetaching([
            $speaker->id => $payload,
        ]);
    }

    public static function detach(Event $event, EventSession $session, User $speaker): void
    {
        $session->speakers()->detach($speaker);

        self::releaseFromEvent($event, $speaker);
    }

    public static function releaseFromEvent(Event $event, User $speaker): void
    {
        $stillOnEvent = $event->sessions()
            ->whereHas('speakers', fn ($query) => $query->whereKey($speaker->id))
            ->exists();

        if (! $stillOnEvent) {
            $event->speakers()->detach($speaker);
        }
    }
}
