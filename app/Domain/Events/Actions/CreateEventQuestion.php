<?php

namespace App\Domain\Events\Actions;

use App\Domain\Events\Data\EventQuestionData;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\EventQuestion;

final class CreateEventQuestion
{
    public function __invoke(Event $event, EventQuestionData $data): EventQuestion
    {
        return $event->questions()->create(
            $data->attributes(((int) $event->questions()->max('position')) + 1),
        );
    }
}
