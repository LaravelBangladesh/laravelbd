<?php

namespace App\Domain\Events\Actions;

use App\Domain\Events\Data\EventQuestionData;
use App\Domain\Events\Models\EventQuestion;

final class UpdateEventQuestion
{
    public function __invoke(EventQuestion $question, EventQuestionData $data): EventQuestion
    {
        $question->update($data->attributes($question->position));

        return $question;
    }
}
