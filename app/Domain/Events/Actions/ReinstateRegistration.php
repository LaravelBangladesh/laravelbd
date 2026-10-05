<?php

namespace App\Domain\Events\Actions;

use App\Domain\Events\Enums\RegistrationStatus;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\EventRegistration;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Staff bring back a cancelled registration. Like a fresh RSVP, it takes a
 * free seat or joins the waitlist, and the answers given before are kept.
 */
final class ReinstateRegistration
{
    public function __invoke(EventRegistration $registration): RegistrationStatus
    {
        $event = $registration->event;

        if (! $event->isUpcoming()) {
            throw ValidationException::withMessages([
                'registration' => __('admin.event_ended'),
            ]);
        }

        if ($registration->user === null) {
            throw ValidationException::withMessages([
                'registration' => __('admin.attendee_deactivated'),
            ]);
        }

        if ($registration->isActive()) {
            return $registration->status;
        }

        return DB::transaction(function () use ($event, $registration) {
            Event::query()->whereKey($event->id)->lockForUpdate()->first();

            $status = $event->seatStatus();

            $registration->forceFill([
                'status' => $status,
                'registered_at' => now(),
            ])->save();

            return $status;
        });
    }
}
