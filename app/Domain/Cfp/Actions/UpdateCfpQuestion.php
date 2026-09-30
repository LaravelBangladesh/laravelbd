<?php

namespace App\Domain\Cfp\Actions;

use App\Domain\Events\Data\EventQuestionData;
use App\Domain\Events\Models\Event;

final class UpdateCfpQuestion
{
    public function __invoke(Event $event, string $questionId, EventQuestionData $data): void
    {
        $event->cfp_questions = array_map(
            fn (array $question) => $question['id'] === $questionId ? $data->stored($questionId) : $question,
            $event->cfp_questions ?? [],
        );
        $event->save();
    }
}
