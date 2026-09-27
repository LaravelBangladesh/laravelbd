<?php

namespace App\Application\Shared\Http\Controllers;

use App\Application\Shared\ViewModels\Breadcrumbs;
use App\Application\Shared\ViewModels\MarkdownDocument;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\Speaker;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;

class AboutController extends Controller
{
    public function __invoke(Request $request): Response|HttpResponse
    {
        if ($request->wantsMarkdown()) {
            return MarkdownDocument::respond(__('about.hero.title'), [
                __('about.hero.lead'),
                '## '.__('about.manifesto.title'),
                ...MarkdownDocument::translatedSections('about.manifesto', 3),
                '## '.__('about.night.title'),
                ...MarkdownDocument::translatedSections('about.night', 3),
                '## '.__('about.who.title'),
                MarkdownDocument::translatedList('about.who'),
                '## '.__('about.join.title'),
                ...MarkdownDocument::translatedSections('about.join', 3),
                '## '.__('about.cities.title'),
                __('about.cities.body'),
                __('about.cities.list'),
            ]);
        }

        return Inertia::render('about', [
            'json_ld' => [
                Breadcrumbs::make([
                    __('nav.home') => url('/'),
                    __('nav.about') => route('about'),
                ]),
            ],
            'stats' => [
                'events' => Event::query()->published()->count(),
                'speakers' => Speaker::query()->count(),
            ],
        ]);
    }
}
