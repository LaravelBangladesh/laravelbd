<?php

namespace App\Application\Events\Http\Controllers\Admin;

use App\Application\Events\Http\Requests\Admin\StoreEventRequest;
use App\Application\Events\Http\Requests\Admin\UpdateEventRequest;
use App\Application\Events\ViewModels\EventPresenter;
use App\Application\Shared\Http\Controllers\Controller;
use App\Domain\Events\Actions\CreateEvent;
use App\Domain\Events\Actions\DeleteEvent;
use App\Domain\Events\Actions\UpdateEvent;
use App\Domain\Events\Data\EventData;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\Speaker;
use App\Infrastructure\Images\ImageUpload;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class EventController extends Controller
{
    public function index(): Response
    {
        $this->authorize('manage', Event::class);

        return Inertia::render('admin/events/index', [
            'events' => Event::query()
                ->orderByDesc('starts_at')
                ->get()
                ->map(fn (Event $event) => EventPresenter::card($event))
                ->all(),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Event::class);

        return Inertia::render('admin/events/create', [
            'types' => EventPresenter::types(),
            'statuses' => EventPresenter::statuses(),
        ]);
    }

    public function store(StoreEventRequest $request, CreateEvent $createEvent): RedirectResponse
    {
        $this->authorize('create', Event::class);

        $event = $createEvent(
            EventData::fromValidated($request->validated()),
            $request->user(),
            ImageUpload::from($request, 'cover'),
        );

        return to_route('admin.events.show', $event);
    }

    public function show(Event $event): Response
    {
        $this->authorize('update', $event);

        $event->load(['sessions.speakers', 'speakers', 'media', 'questions', 'registrations.user', 'registrations.answers.question']);

        return Inertia::render('admin/events/manage', [
            'event' => EventPresenter::admin($event),
            'sessionKinds' => EventPresenter::sessionKinds(),
            'questionKinds' => EventPresenter::questionKinds(),
            'speakerRoles' => EventPresenter::speakerRoles(),
            'availableSpeakers' => $this->availableSpeakers(),
        ]);
    }

    public function edit(Event $event): Response
    {
        $this->authorize('update', $event);

        $event->load(['sessions.speakers', 'speakers', 'media', 'questions', 'registrations.user', 'registrations.answers.question']);

        return Inertia::render('admin/events/edit', [
            'event' => EventPresenter::admin($event),
            'types' => EventPresenter::types(),
            'statuses' => EventPresenter::statuses(),
            'sessionKinds' => EventPresenter::sessionKinds(),
            'speakerRoles' => EventPresenter::speakerRoles(),
            'availableSpeakers' => $this->availableSpeakers(),
        ]);
    }

    public function update(UpdateEventRequest $request, Event $event, UpdateEvent $updateEvent): RedirectResponse
    {
        $this->authorize('update', $event);

        $updateEvent(
            $event,
            EventData::fromValidated($request->validated()),
            ImageUpload::from($request, 'cover'),
        );

        return back();
    }

    public function destroy(Event $event, DeleteEvent $deleteEvent): RedirectResponse
    {
        $this->authorize('delete', $event);

        $deleteEvent($event);

        return to_route('admin.events.index');
    }

    /**
     * @return array<int, Speaker>
     */
    private function availableSpeakers(): array
    {
        return Speaker::query()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->all();
    }
}
