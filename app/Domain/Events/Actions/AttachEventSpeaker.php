<?php

namespace App\Domain\Events\Actions;

use App\Domain\Events\Models\Event;

final class AttachEventSpeaker
{
    public function __invoke(Event $event, string $speakerId, string $role): void
    {
        $event->speakers()->syncWithoutDetaching([
            $speakerId => ['role' => $role],
        ]);
    }
}
