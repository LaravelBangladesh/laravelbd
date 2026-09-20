<?php

namespace App\Domain\Events\Actions;

use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\Speaker;

final class DetachEventSpeaker
{
    public function __invoke(Event $event, Speaker $speaker): void
    {
        $event->speakers()->detach($speaker);
    }
}
