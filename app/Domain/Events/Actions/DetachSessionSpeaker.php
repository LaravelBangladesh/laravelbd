<?php

namespace App\Domain\Events\Actions;

use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\EventSession;
use App\Domain\Events\Models\Speaker;
use App\Domain\Events\SessionRoster;

final class DetachSessionSpeaker
{
    public function __invoke(Event $event, EventSession $session, Speaker $speaker): void
    {
        SessionRoster::detach($event, $session, $speaker);
    }
}
