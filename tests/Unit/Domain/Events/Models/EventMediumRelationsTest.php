<?php

use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\EventMedium;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('a medium belongs back to its event', function () {
    $event = Event::factory()->create();
    $medium = EventMedium::factory()->create(['event_id' => $event->id]);

    expect($medium->event?->id)->toBe($event->id);
});

test('media captions fall back to english', function () {
    $medium = EventMedium::factory()->create([
        'caption_en' => 'Opening keynote',
        'caption_bn' => null,
    ]);

    app()->setLocale('bn');

    expect($medium->localized('caption'))->toBe('Opening keynote');
});

test('the sort order is cast to an integer', function () {
    $medium = EventMedium::factory()->create(['sort_order' => '4']);

    expect($medium->fresh()?->sort_order)->toBe(4);
});
