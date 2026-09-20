<?php

namespace App\Domain\Events\Actions;

use App\Domain\Events\Data\SpeakerData;
use App\Domain\Events\Models\Speaker;
use App\Domain\Shared\Contracts\ImageStorage;
use App\Domain\Shared\Data\UploadedImage;
use App\Domain\Shared\UniqueSlug;

final class CreateSpeaker
{
    public function __construct(private readonly ImageStorage $images) {}

    public function __invoke(SpeakerData $data, ?UploadedImage $photo = null): Speaker
    {
        $speaker = new Speaker;
        $speaker->fill($data->attributes());
        $speaker->slug = UniqueSlug::make($data->name, 'speakers');

        if ($photo !== null) {
            $speaker->photo_path = $this->images->put($photo->contents, $photo->name, 'speakers');
        }

        $speaker->save();

        return $speaker;
    }
}
