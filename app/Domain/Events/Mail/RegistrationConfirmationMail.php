<?php

namespace App\Domain\Events\Mail;

class RegistrationConfirmationMail extends EventRegistrationMail
{
    protected function key(): string
    {
        return 'events.mail.confirmation';
    }
}
