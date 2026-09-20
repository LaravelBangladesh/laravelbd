<?php

namespace App\Application\Shared\Http\Controllers;

use App\Application\Shared\ViewModels\Breadcrumbs;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\Speaker;
use Inertia\Inertia;
use Inertia\Response;

class AboutController extends Controller
{
    public function __invoke(): Response
    {
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
