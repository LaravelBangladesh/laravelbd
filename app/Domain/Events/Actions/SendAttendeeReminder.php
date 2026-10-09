<?php

namespace App\Domain\Events\Actions;

use App\Domain\Events\Enums\AttendeeMail;
use App\Domain\Events\Enums\RegistrationStatus;
use App\Domain\Events\Models\EventRegistration;
use Illuminate\Validation\ValidationException;

final class SendAttendeeReminder
{
    public function __construct(private readonly QueueAttendeeMail $queue) {}

    public function __invoke(EventRegistration $registration): void
    {
        if (! $registration->event->acceptsReminders() || $registration->status !== RegistrationStatus::Registered) {
            throw ValidationException::withMessages([
                'registration' => __('admin.reminder_unavailable'),
            ]);
        }

        ($this->queue)($registration, AttendeeMail::Reminder);
    }
}
