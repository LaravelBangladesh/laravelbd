<?php

namespace App\Domain\Events\Jobs;

use App\Domain\Events\Enums\AttendeeMail;
use App\Domain\Events\Enums\MailDeliveryStatus;
use App\Domain\Events\Enums\RegistrationStatus;
use App\Domain\Events\Models\EventRegistration;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendAttendeeMail implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /** @var list<int> */
    public array $backoff = [10, 60, 300];

    public bool $deleteWhenMissingModels = true;

    public function __construct(
        public readonly EventRegistration $registration,
        public readonly AttendeeMail $mail,
    ) {
        $this->afterCommit();
    }

    public function handle(): void
    {
        $registration = $this->registration->refresh()->load(['event', 'user']);

        // The attendee cancelled or was deactivated after the mail was queued.
        if ($registration->status !== RegistrationStatus::Registered || $registration->user === null) {
            $this->mark(MailDeliveryStatus::NotSent);

            return;
        }

        Mail::to($registration->user)->send($this->mail->mailable($registration));

        $this->mark(MailDeliveryStatus::Sent, now());
    }

    public function failed(?Throwable $exception): void
    {
        $this->mark(MailDeliveryStatus::Failed);
    }

    private function mark(MailDeliveryStatus $status, mixed $sentAt = null): void
    {
        $this->registration->forceFill([
            $this->mail->statusColumn() => $status,
            $this->mail->sentAtColumn() => $sentAt,
        ])->save();
    }
}
