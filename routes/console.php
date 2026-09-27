<?php

use App\Domain\Events\Jobs\GenerateEventShortUrl;
use App\Domain\Events\Models\Event;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('events:short-urls', function () {
    $events = Event::query()->published()->whereNull('short_url')->get();

    $events->each(fn (Event $event) => GenerateEventShortUrl::dispatch($event));

    $this->info("Queued short urls for {$events->count()} published events.");
})->purpose('Create short urls for published events that do not have one yet');
