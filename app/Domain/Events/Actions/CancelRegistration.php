<?php

namespace App\Domain\Events\Actions;

use App\Domain\Events\Enums\RegistrationStatus;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\EventRegistration;
use App\Domain\Identity\Models\User;

final class CancelRegistration
{
    public function __invoke(Event $event, User $user): void
    {
        EventRegistration::query()
            ->where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->where('status', '!=', RegistrationStatus::Cancelled)
            ->update(['status' => RegistrationStatus::Cancelled]);
    }
}
