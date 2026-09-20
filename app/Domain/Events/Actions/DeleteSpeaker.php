<?php

namespace App\Domain\Events\Actions;

use App\Domain\Events\Models\Speaker;
use App\Domain\Shared\Contracts\ImageStorage;

final class DeleteSpeaker
{
    public function __construct(private readonly ImageStorage $images) {}

    public function __invoke(Speaker $speaker): void
    {
        $this->images->delete($speaker->photo_path);
        $speaker->delete();
    }
}
