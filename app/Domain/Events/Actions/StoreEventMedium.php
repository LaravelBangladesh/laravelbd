<?php

namespace App\Domain\Events\Actions;

use App\Domain\Events\Data\EventMediumData;
use App\Domain\Events\Enums\MediaKind;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\EventMedium;
use App\Domain\Shared\Contracts\ImageStorage;
use App\Domain\Shared\Data\UploadedImage;

final class StoreEventMedium
{
    public function __construct(private readonly ImageStorage $images) {}

    public function __invoke(Event $event, EventMediumData $data, ?UploadedImage $photo = null): EventMedium
    {
        $path = $data->kind === MediaKind::Photo && $photo !== null
            ? $this->images->put($photo->contents, $photo->name, 'events/'.$event->id)
            : null;

        return $event->media()->create([
            'kind' => $data->kind,
            'path' => $path,
            'embed_url' => $data->embedUrl,
            'caption_en' => $data->captionEn,
            'caption_bn' => $data->captionBn,
            'sort_order' => $event->media()->count(),
        ]);
    }
}
