<?php

namespace App\Domain\Events\Actions;

use App\Domain\Events\Data\SessionData;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\EventSession;
use App\Domain\Shared\Data\UploadedImage;

final class CreateSession
{
    public function __construct(private readonly AttachSessionSpeaker $attachSpeaker) {}

    public function __invoke(Event $event, SessionData $data, ?UploadedImage $speakerPhoto = null): EventSession
    {
        $session = $event->sessions()->create(
            $data->attributes(((int) $event->sessions()->max('sort_order')) + 1),
        );

        ($this->attachSpeaker)($event, $session, $data->speaker, $speakerPhoto);

        return $session;
    }
}
