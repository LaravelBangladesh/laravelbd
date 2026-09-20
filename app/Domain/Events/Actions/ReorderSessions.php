<?php

namespace App\Domain\Events\Actions;

use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\EventSession;
use Illuminate\Support\Facades\DB;

final class ReorderSessions
{
    /**
     * @param  list<string>  $sessionIds
     */
    public function __invoke(Event $event, array $sessionIds): void
    {
        DB::transaction(function () use ($event, $sessionIds): void {
            foreach ($sessionIds as $index => $sessionId) {
                EventSession::query()
                    ->where('event_id', $event->id)
                    ->where('id', $sessionId)
                    ->update(['sort_order' => $index]);
            }
        });
    }
}
