<?php

namespace App\Domain\Events\Actions;

use App\Domain\Events\Models\EventMedium;
use App\Domain\Shared\Contracts\ImageStorage;

final class DeleteEventMedium
{
    public function __construct(private readonly ImageStorage $images) {}

    public function __invoke(EventMedium $medium): void
    {
        $this->images->delete($medium->path);
        $medium->delete();
    }
}
