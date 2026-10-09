<?php

namespace App\Domain\Events\Actions;

use App\Domain\Events\Enums\AttendeeMail;
use App\Domain\Events\Enums\MailDeliveryStatus;
use App\Domain\Events\Jobs\SendAttendeeMail;
use App\Domain\Events\Models\EventRegistration;

final class QueueAttendeeMail
{
    public function __invoke(EventRegistration $registration, AttendeeMail $mail): void
    {
        $registration->forceFill([
            $mail->statusColumn() => MailDeliveryStatus::Queued,
            $mail->sentAtColumn() => null,
        ])->save();

        SendAttendeeMail::dispatch($registration, $mail);
    }
}
