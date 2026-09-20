<?php

namespace App\Application\Events\Http\Controllers;

use App\Application\Events\Http\Requests\RegisterForEventRequest;
use App\Application\Shared\Http\Controllers\Controller;
use App\Application\Shared\Http\ProfileGate;
use App\Domain\Events\Actions\CancelRegistration;
use App\Domain\Events\Actions\RegisterForEvent;
use App\Domain\Events\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EventRsvpController extends Controller
{
    public function store(RegisterForEventRequest $request, Event $event, RegisterForEvent $register): RedirectResponse
    {
        if (! $request->user()->hasCompleteProfile()) {
            return ProfileGate::redirect('events.show', $event->slug);
        }

        $this->authorize('rsvp', $event);

        $registration = $register($event, $request->user(), $request->answers());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('events.rsvp.saved', ['status' => $registration->status->label()]),
        ]);

        return back();
    }

    public function destroy(Request $request, Event $event, CancelRegistration $cancel): RedirectResponse
    {
        $this->authorize('cancelRsvp', $event);

        $cancel($event, $request->user());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('events.rsvp.cancelled_own'),
        ]);

        return back();
    }
}
