<?php

namespace App\Domain\Events\Actions;

use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\EventQuestion;
use Illuminate\Support\Facades\DB;

final class ReorderEventQuestions
{
    /**
     * @param  list<string>  $questionIds
     */
    public function __invoke(Event $event, array $questionIds): void
    {
        DB::transaction(function () use ($event, $questionIds): void {
            foreach ($questionIds as $index => $questionId) {
                EventQuestion::query()
                    ->where('event_id', $event->id)
                    ->where('id', $questionId)
                    ->update(['position' => $index]);
            }
        });
    }
}
