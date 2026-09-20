<?php

use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\EventMedium;
use App\Domain\Events\Models\EventSession;
use App\Domain\Events\Models\Speaker;
use App\Domain\Events\QueryBuilders\EventQueryBuilder;
use App\Domain\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('an event exposes its creator', function () {
    $author = User::factory()->create();
    $event = Event::factory()->create(['created_by' => $author->id]);

    expect($event->creator?->id)->toBe($author->id);
});

test('event media are ordered by sort order', function () {
    $event = Event::factory()->create();
    EventMedium::factory()->create(['event_id' => $event->id, 'caption_en' => 'second', 'sort_order' => 2]);
    EventMedium::factory()->create(['event_id' => $event->id, 'caption_en' => 'first', 'sort_order' => 1]);

    expect($event->media->pluck('caption_en')->all())->toBe(['first', 'second']);
});

test('the event query uses the dedicated builder', function () {
    expect(Event::query())->toBeInstanceOf(EventQueryBuilder::class);
});

test('event speakers keep their pivot order', function () {
    $event = Event::factory()->create();
    $second = Speaker::factory()->create(['name' => 'Second']);
    $first = Speaker::factory()->create(['name' => 'First']);

    $event->speakers()->attach($second, ['role' => 'speaker', 'sort_order' => 2]);
    $event->speakers()->attach($first, ['role' => 'host', 'sort_order' => 1]);

    expect($event->speakers->pluck('name')->all())->toBe(['First', 'Second']);
});

test('event sessions are ordered by sort order then start time', function () {
    $event = Event::factory()->create();
    EventSession::factory()->create(['event_id' => $event->id, 'title_en' => 'Closing', 'sort_order' => 2]);
    EventSession::factory()->create(['event_id' => $event->id, 'title_en' => 'Opening', 'sort_order' => 1]);

    expect($event->sessions->pluck('title_en')->all())->toBe(['Opening', 'Closing']);
});
