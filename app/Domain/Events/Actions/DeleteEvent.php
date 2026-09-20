<?php

namespace App\Domain\Events\Actions;

use App\Domain\Events\Models\Event;
use App\Domain\Shared\Contracts\ImageStorage;

final class DeleteEvent
{
    public function __construct(private readonly ImageStorage $images) {}

    public function __invoke(Event $event): void
    {
        $this->images->delete($event->cover_path);
        $event->delete();
    }
}
