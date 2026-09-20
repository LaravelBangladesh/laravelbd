<?php

namespace App\Domain\Events\Actions;

use App\Domain\Events\Data\EventData;
use App\Domain\Events\Models\Event;
use App\Domain\Identity\Models\User;
use App\Domain\Shared\Contracts\ImageStorage;
use App\Domain\Shared\Data\UploadedImage;
use App\Domain\Shared\UniqueSlug;

final class CreateEvent
{
    public function __construct(private readonly ImageStorage $images) {}

    public function __invoke(EventData $data, ?User $author, ?UploadedImage $cover = null): Event
    {
        $event = new Event;
        $event->fill($data->attributes());
        $event->slug = UniqueSlug::make($data->titleEn, 'events');
        $event->created_by = $author?->id;

        if ($cover !== null) {
            $event->cover_path = $this->images->put($cover->contents, $cover->name, 'events/covers');
        }

        $event->save();

        return $event;
    }
}
