<?php

namespace App\Application\Events\Http\Controllers\Admin;

use App\Application\Shared\Http\Controllers\Controller;
use App\Domain\Events\Actions\CancelRegistrationForAttendee;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\EventRegistration;
use Illuminate\Http\RedirectResponse;

class EventRegistrationController extends Controller
{
    public function destroy(Event $event, EventRegistration $registration, CancelRegistrationForAttendee $cancel): RedirectResponse
    {
        $this->authorize('update', $event);

        abort_unless($registration->event_id === $event->id, 404);

        $cancel($registration);

        return back();
    }
}
