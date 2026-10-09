<?php

namespace App\Domain\Events\Enums;

use App\Domain\Events\Mail\EventRegistrationMail;
use App\Domain\Events\Mail\EventReminderMail;
use App\Domain\Events\Mail\RegistrationConfirmationMail;
use App\Domain\Events\Models\EventRegistration;

/**
 * The emails an attendee can receive, each tracked in its own
 * "{value}_status" and "{value}_sent_at" columns on the registration.
 */
enum AttendeeMail: string
{
    case Confirmation = 'confirmation';
    case Reminder = 'reminder';

    public function mailable(EventRegistration $registration): EventRegistrationMail
    {
        return match ($this) {
            self::Confirmation => new RegistrationConfirmationMail($registration),
            self::Reminder => new EventReminderMail($registration),
        };
    }

    public function statusColumn(): string
    {
        return "{$this->value}_status";
    }

    public function sentAtColumn(): string
    {
        return "{$this->value}_sent_at";
    }
}
