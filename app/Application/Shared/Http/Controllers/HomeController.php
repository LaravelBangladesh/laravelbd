<?php

namespace App\Application\Shared\Http\Controllers;

use App\Application\Events\ViewModels\EventPresenter;
use App\Application\Shared\ViewModels\JsonLd;
use App\Domain\Events\Models\Event;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('welcome', [
            'stats' => [
                'events' => Event::query()->published()->count(),
            ],
            'json_ld' => [
                JsonLd::organization(),
                JsonLd::website(),
            ],
            'upcomingEvents' => Event::query()
                ->published()
                ->upcoming()
                ->reorder('starts_at')
                ->limit(3)
                ->get()
                ->map(fn (Event $event) => EventPresenter::card($event))
                ->all(),
        ]);
    }
}
