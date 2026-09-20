<?php

namespace App\Domain\Events;

use App\Domain\Events\Data\SessionSpeakerData;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\EventSession;
use App\Domain\Events\Models\Speaker;
use App\Domain\Shared\UniqueSlug;

class SessionRoster
{
    public static function attach(Event $event, EventSession $session, Speaker $speaker, string $role): void
    {
        $payload = ['role' => $role];

        $session->speakers()->syncWithoutDetaching([
            $speaker->id => $payload,
        ]);

        $event->speakers()->syncWithoutDetaching([
            $speaker->id => $payload,
        ]);
    }

    public static function detach(Event $event, EventSession $session, Speaker $speaker): void
    {
        $session->speakers()->detach($speaker);

        self::releaseFromEvent($event, $speaker);
    }

    public static function releaseFromEvent(Event $event, Speaker $speaker): void
    {
        $stillOnEvent = $event->sessions()
            ->whereHas('speakers', fn ($query) => $query->whereKey($speaker->id))
            ->exists();

        if (! $stillOnEvent) {
            $event->speakers()->detach($speaker);
        }
    }

    public static function speakerFrom(SessionSpeakerData $data, ?string $photoPath = null): ?Speaker
    {
        if ($data->source === 'existing') {
            return Speaker::query()->find($data->speakerId);
        }

        if ($data->source !== 'new' || $data->name === null || $data->name === '') {
            return null;
        }

        $speaker = new Speaker;
        $speaker->fill([
            'name' => $data->name,
            'title' => $data->title,
            'company' => $data->company,
        ]);
        $speaker->slug = UniqueSlug::make($speaker->name, 'speakers');

        if ($photoPath !== null) {
            $speaker->photo_path = $photoPath;
        }

        $speaker->save();

        return $speaker;
    }
}
