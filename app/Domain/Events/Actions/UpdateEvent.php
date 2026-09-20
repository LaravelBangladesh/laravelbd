<?php

namespace App\Domain\Events\Actions;

use App\Domain\Events\Data\EventData;
use App\Domain\Events\Models\Event;
use App\Domain\Shared\Contracts\ImageStorage;
use App\Domain\Shared\Data\UploadedImage;
use App\Domain\Shared\UniqueSlug;

final class UpdateEvent
{
    public function __construct(private readonly ImageStorage $images) {}

    public function __invoke(Event $event, EventData $data, ?UploadedImage $cover = null): Event
    {
        $event->fill($data->attributes());
        $event->slug = UniqueSlug::make($data->titleEn, 'events', $event->id);

        if ($cover !== null) {
            $this->images->delete($event->cover_path);
            $event->cover_path = $this->images->put($cover->contents, $cover->name, 'events/covers');
        }

        $event->save();

        return $event;
    }
}
