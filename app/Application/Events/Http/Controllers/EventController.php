<?php

namespace App\Application\Events\Http\Controllers;

use App\Application\Events\ViewModels\EventPresenter;
use App\Application\Shared\Http\Controllers\Controller;
use App\Application\Shared\ViewModels\Breadcrumbs;
use App\Application\Shared\ViewModels\JsonLd;
use App\Domain\Events\Enums\EventType;
use App\Domain\Events\Models\Event;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EventController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Event::class);

        $type = $request->enum('type', EventType::class);

        $published = Event::query()
            ->published()
            ->when($type, fn ($query) => $query->where('type', $type));

        return Inertia::render('events/index', [
            'json_ld' => [
                JsonLd::collectionPage(
                    __('events.title'),
                    route('events.index'),
                    __('events.lead'),
                ),
                Breadcrumbs::make([
                    __('nav.home') => url('/'),
                    __('nav.events') => route('events.index'),
                ]),
            ],
            'upcoming' => (clone $published)->upcoming()
                ->get()
                ->map(fn (Event $event) => EventPresenter::card($event))
                ->all(),
            'past' => (clone $published)->past()
                ->get()
                ->map(fn (Event $event) => EventPresenter::card($event))
                ->all(),
            'type' => $type?->value,
            'types' => EventPresenter::types(),
        ]);
    }

    public function show(Request $request, Event $event): Response
    {
        $this->authorize('view', $event);

        $event->load(['speakers', 'sessions.speakers', 'media', 'questions', 'registrations']);

        return Inertia::render('events/show', [
            'event' => EventPresenter::detail($event, $request->user()),
        ]);
    }
}
