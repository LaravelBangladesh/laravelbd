<?php

namespace App\Application\Events\Http\Controllers;

use App\Application\Events\ViewModels\EventPresenter;
use App\Application\Shared\Http\Controllers\Controller;
use App\Application\Shared\ViewModels\Breadcrumbs;
use App\Application\Shared\ViewModels\JsonLd;
use App\Application\Shared\ViewModels\MarkdownDocument;
use App\Domain\Events\Enums\EventType;
use App\Domain\Events\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;

class EventController extends Controller
{
    public function index(Request $request): Response|HttpResponse
    {
        $this->authorize('viewAny', Event::class);

        $type = $request->enum('type', EventType::class);

        $published = Event::query()
            ->published()
            ->when($type, fn ($query) => $query->where('type', $type));

        $upcoming = (clone $published)->upcoming()
            ->get()
            ->map(fn (Event $event) => EventPresenter::card($event))
            ->all();

        $past = (clone $published)->past()
            ->get()
            ->map(fn (Event $event) => EventPresenter::card($event))
            ->all();

        if ($request->wantsMarkdown()) {
            $link = fn (array $event) => "- [{$event['title']}](".route('events.show', $event['slug']).'): '.$event['excerpt'];

            return MarkdownDocument::respond(__('events.title'), [
                '## Upcoming',
                '',
                implode("\n", array_map($link, $upcoming)),
                '',
                '## Past',
                '',
                implode("\n", array_map($link, $past)),
            ]);
        }

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
            'upcoming' => $upcoming,
            'past' => $past,
            'type' => $type?->value,
            'types' => EventPresenter::types(),
        ]);
    }

    public function show(Request $request, Event $event): Response|HttpResponse
    {
        $this->authorize('view', $event);

        $event->load(['speakers', 'sessions.speakers', 'media', 'questions', 'registrations']);

        $detail = EventPresenter::detail($event, $request->user());

        if ($request->wantsMarkdown()) {
            return MarkdownDocument::respond($detail['title'], array_filter([
                "{$detail['date']}, {$detail['time_range']}",
                $detail['venue_name'],
                $detail['description'],
            ]));
        }

        return Inertia::render('events/show', [
            'event' => $detail,
        ]);
    }
}
