<?php

namespace App\Application\Shared\Http\Controllers;

use App\Application\Events\ViewModels\EventPresenter;
use App\Application\Shared\CommunityCounts;
use App\Application\Shared\ViewModels\JsonLd;
use App\Application\Shared\ViewModels\MarkdownDocument;
use App\Domain\Events\Models\Event;
use App\Domain\Shared\DhakaTime;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(Request $request): Response|HttpResponse
    {
        $upcomingEvents = Event::query()
            ->published()
            ->upcoming()
            ->reorder('starts_at')
            ->limit(3)
            ->get()
            ->map(fn (Event $event) => EventPresenter::card($event))
            ->all();

        if ($request->wantsMarkdown()) {
            $events = array_map(
                fn (array $event) => "- [{$event['title']}](".route('events.show', $event['slug'])."), {$event['starts_at']}: {$event['excerpt']}",
                $upcomingEvents,
            );

            return MarkdownDocument::respond(__('home.hero.title'), [
                __('home.hero.lead'),
                '## '.__('home.upcoming'),
                $events === [] ? __('home.no_events') : implode("\n", $events),
                '['.__('home.view_all_events').']('.route('events.index').')',
                '## '.__('home.community.title'),
                __('home.community.lead'),
                '['.__('home.community.cta').']('.route('about').')',
            ]);
        }

        $featuredEvent = Event::query()->next()
            ?? Event::query()->published()->past()->first();

        return Inertia::render('welcome', [
            'stats' => CommunityCounts::make(),
            'json_ld' => [
                JsonLd::organization(),
                JsonLd::website(),
            ],
            'upcomingEvents' => $upcomingEvents,
            'featuredEvent' => $featuredEvent === null ? null : [
                'slug' => $featuredEvent->slug,
                'title' => $featuredEvent->localized('title'),
                'starts_at' => DhakaTime::display($featuredEvent->starts_at, 'd M Y'),
                'starts_at_iso' => $featuredEvent->starts_at->toIso8601String(),
                'venue_name' => $featuredEvent->venue_name,
                'is_upcoming' => $featuredEvent->isUpcoming(),
            ],
        ]);
    }
}
