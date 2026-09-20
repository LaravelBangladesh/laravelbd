<?php

namespace App\Domain\Events\Actions;

use App\Domain\Events\Enums\RegistrationStatus;
use App\Domain\Events\Models\EventRegistration;

final class CancelRegistrationForAttendee
{
    public function __invoke(EventRegistration $registration): void
    {
        $registration->forceFill([
            'status' => RegistrationStatus::Cancelled,
        ])->save();
    }
}
