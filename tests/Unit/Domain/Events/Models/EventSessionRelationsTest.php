<?php

use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\EventSession;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('a session belongs back to its event', function () {
    $event = Event::factory()->create();
    $session = EventSession::factory()->create(['event_id' => $event->id]);

    expect($session->event?->id)->toBe($event->id);
});

test('session titles and descriptions fall back to english', function () {
    $session = EventSession::factory()->create([
        'title_en' => 'Queues in depth',
        'title_bn' => null,
        'description_en' => 'Workers and failures.',
        'description_bn' => null,
    ]);

    app()->setLocale('bn');

    expect($session->localized('title'))->toBe('Queues in depth')
        ->and($session->localized('description'))->toBe('Workers and failures.');
});
