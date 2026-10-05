<?php

namespace App\Domain\Events\Actions;

use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\EventSession;
use App\Domain\Events\SessionRoster;
use App\Domain\Identity\Models\User;

final class DetachSessionSpeaker
{
    public function __invoke(Event $event, EventSession $session, User $speaker): void
    {
        SessionRoster::detach($event, $session, $speaker);
    }
}
