<?php

namespace App\Application\Events\Http\Controllers\Admin;

use App\Application\Shared\Http\Controllers\Controller;
use App\Domain\Events\Actions\SendAttendeeReminder;
use App\Domain\Events\Actions\SendEventReminders;
use App\Domain\Events\Mail\EventReminderMail;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\EventRegistration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class EventReminderController extends Controller
{
    /**
     * The reminder exactly as attendees get it, addressed to the staff member
     * looking at it, in the language they pick.
     */
    public function preview(Request $request, Event $event): Response
    {
        $this->authorize('update', $event);

        $validated = $request->validate([
            'locale' => ['nullable', Rule::in(config('localization.available'))],
        ]);

        $user = clone $request->user();
        $user->locale = $validated['locale'] ?? $user->locale;

        $registration = (new EventRegistration)
            ->setRelation('event', $event)
            ->setRelation('user', $user);

        return response((new EventReminderMail($registration))->render());
    }

    public function store(Event $event, SendEventReminders $send): RedirectResponse
    {
        $this->authorize('update', $event);

        $count = $send($event);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('admin.reminders_queued', ['count' => (string) $count]),
        ]);

        return back(fallback: route('admin.events.attendees.index', $event));
    }

    public function resend(Event $event, EventRegistration $registration, SendAttendeeReminder $send): RedirectResponse
    {
        $this->authorize('update', $event);

        abort_unless($registration->event_id === $event->id, 404);

        $send($registration);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('admin.reminder_queued', ['name' => $registration->user?->name]),
        ]);

        return back(fallback: route('admin.events.attendees.index', $event));
    }
}
