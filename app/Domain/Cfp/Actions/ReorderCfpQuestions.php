<?php

namespace App\Domain\Cfp\Actions;

use App\Domain\Events\Models\Event;

final class ReorderCfpQuestions
{
    /**
     * @param  list<string>  $questionIds  every question id of the event, in the new order
     */
    public function __invoke(Event $event, array $questionIds): void
    {
        $questions = array_column($event->cfp_questions ?? [], null, 'id');

        $event->cfp_questions = array_values(array_filter(
            array_map(fn (string $id) => $questions[$id] ?? null, $questionIds),
        ));
        $event->save();
    }
}
