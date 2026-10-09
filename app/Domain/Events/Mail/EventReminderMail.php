<?php

namespace App\Domain\Events\Mail;

class EventReminderMail extends EventRegistrationMail
{
    protected function key(): string
    {
        return 'events.mail.reminder';
    }
}
