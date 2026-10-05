<?php

namespace App\Domain\Events\Actions;

use App\Domain\Events\Models\Event;
use App\Domain\Identity\Models\User;

final class DetachEventSpeaker
{
    public function __invoke(Event $event, User $speaker): void
    {
        $event->speakers()->detach($speaker);
    }
}
