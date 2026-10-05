<?php

namespace App\Domain\Events\Actions;

use App\Domain\Events\Data\SessionSpeakerData;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\EventSession;
use App\Domain\Events\SessionRoster;
use App\Domain\Identity\Actions\CreateGuestUser;
use App\Domain\Identity\Models\User;
use App\Domain\Shared\Data\UploadedImage;

final class AttachSessionSpeaker
{
    public function __construct(private readonly CreateGuestUser $createGuest) {}

    public function __invoke(Event $event, EventSession $session, SessionSpeakerData $data, ?UploadedImage $photo = null): ?User
    {
        $guest = $data->guest();

        $speaker = match (true) {
            $data->source === 'existing' => User::query()->find($data->speakerId),
            $guest !== null => ($this->createGuest)($guest, $photo),
            default => null,
        };

        if ($speaker === null) {
            return null;
        }

        SessionRoster::attach($event, $session, $speaker, $data->role);

        return $speaker;
    }
}
