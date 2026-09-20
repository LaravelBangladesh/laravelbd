<?php

namespace App\Domain\Events\Actions;

use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\EventSession;
use App\Domain\Events\Models\Speaker;
use App\Domain\Events\SessionRoster;
use Illuminate\Support\Facades\DB;

final class DeleteSession
{
    public function __invoke(Event $event, EventSession $session): void
    {
        DB::transaction(function () use ($event, $session): void {
            $speakers = $session->speakers()->get();
            $session->delete();

            $speakers->each(fn (Speaker $speaker) => SessionRoster::releaseFromEvent($event, $speaker));
        });
    }
}
