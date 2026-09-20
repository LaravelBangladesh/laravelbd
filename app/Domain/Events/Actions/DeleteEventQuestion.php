<?php

namespace App\Domain\Events\Actions;

use App\Domain\Events\Models\EventQuestion;

final class DeleteEventQuestion
{
    public function __invoke(EventQuestion $question): void
    {
        $question->delete();
    }
}
