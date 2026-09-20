<?php

namespace App\Domain\Events\Actions;

use App\Domain\Events\Data\SessionData;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\EventSession;
use App\Domain\Shared\Data\UploadedImage;

final class UpdateSession
{
    public function __construct(private readonly AttachSessionSpeaker $attachSpeaker) {}

    public function __invoke(Event $event, EventSession $session, SessionData $data, ?UploadedImage $speakerPhoto = null): EventSession
    {
        $session->update($data->attributes($session->sort_order));

        ($this->attachSpeaker)($event, $session, $data->speaker, $speakerPhoto);

        return $session;
    }
}
