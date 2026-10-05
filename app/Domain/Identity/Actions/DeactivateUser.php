<?php

namespace App\Domain\Identity\Actions;

use App\Domain\Events\Enums\RegistrationStatus;
use App\Domain\Events\QueryBuilders\EventQueryBuilder;
use App\Domain\Identity\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Soft deletes the user. They can no longer sign in, their open sessions end
 * on the next request, and they drop out of public pages, while their
 * proposals and past registrations stay for the record.
 *
 * Their registrations for events that have not ended are cancelled, so the
 * seats go back to the event. Reactivating does not bring them back; staff
 * register the person again from the attendees page.
 */
final class DeactivateUser
{
    public function __invoke(User $user): void
    {
        DB::transaction(function () use ($user) {
            $user->eventRegistrations()
                ->where('status', '!=', RegistrationStatus::Cancelled)
                ->whereHas('event', fn (EventQueryBuilder $events) => $events->upcoming())
                ->update(['status' => RegistrationStatus::Cancelled]);

            $user->delete();
        });
    }
}
