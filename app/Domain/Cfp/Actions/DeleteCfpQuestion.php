<?php

namespace App\Domain\Cfp\Actions;

use App\Domain\Events\Models\Event;

final class DeleteCfpQuestion
{
    public function __invoke(Event $event, string $questionId): void
    {
        $event->cfp_questions = array_values(array_filter(
            $event->cfp_questions ?? [],
            fn (array $question) => $question['id'] !== $questionId,
        ));
        $event->save();
    }
}
