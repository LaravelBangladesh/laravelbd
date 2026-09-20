<?php

namespace App\Domain\Events\Actions;

use App\Domain\Events\Data\SessionSpeakerData;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\EventSession;
use App\Domain\Events\Models\Speaker;
use App\Domain\Events\SessionRoster;
use App\Domain\Shared\Contracts\ImageStorage;
use App\Domain\Shared\Data\UploadedImage;

final class AttachSessionSpeaker
{
    public function __construct(private readonly ImageStorage $images) {}

    public function __invoke(Event $event, EventSession $session, SessionSpeakerData $data, ?UploadedImage $photo = null): ?Speaker
    {
        $speaker = SessionRoster::speakerFrom(
            $data,
            $photo === null ? null : $this->images->put($photo->contents, $photo->name, 'speakers'),
        );

        if ($speaker === null) {
            return null;
        }

        SessionRoster::attach($event, $session, $speaker, $data->role);

        return $speaker;
    }
}
