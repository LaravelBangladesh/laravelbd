<?php

namespace App\Domain\Cfp\Actions;

use App\Domain\Events\Data\EventQuestionData;
use App\Domain\Events\Models\Event;
use Illuminate\Support\Str;

final class CreateCfpQuestion
{
    public function __invoke(Event $event, EventQuestionData $data): void
    {
        $event->cfp_questions = [
            ...$event->cfp_questions ?? [],
            $data->stored((string) Str::uuid()),
        ];
        $event->save();
    }
}
