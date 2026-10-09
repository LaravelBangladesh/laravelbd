<?php

namespace App\Domain\Events\Actions;

use App\Domain\Events\Enums\AttendeeMail;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\EventRegistration;
use Illuminate\Validation\ValidationException;

final class SendEventReminders
{
    public function __construct(private readonly QueueAttendeeMail $queue) {}

    /**
     * Queue the reminder for every registered attendee who has not had it
     * yet, or whose last one failed. Returns how many were queued.
     */
    public function __invoke(Event $event): int
    {
        if (! $event->acceptsReminders()) {
            throw ValidationException::withMessages([
                'event' => __('admin.reminder_unavailable'),
            ]);
        }

        $count = 0;

        $event->reminderRecipients()->with(['event', 'user'])->lazyById(200)
            ->each(function (EventRegistration $registration) use (&$count) {
                ($this->queue)($registration, AttendeeMail::Reminder);
                $count++;
            });

        return $count;
    }
}
