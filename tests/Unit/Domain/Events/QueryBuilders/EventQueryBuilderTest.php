<?php

use App\Domain\Events\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('published returns only published events', function () {
    $published = Event::factory()->published()->create();
    $draft = Event::factory()->create();

    expect(Event::query()->published()->pluck('id')->all())
        ->toContain($published->id)
        ->not->toContain($draft->id);
});

test('upcoming returns events that have not ended', function () {
    $upcoming = Event::factory()->published()->create();
    $past = Event::factory()->published()->past()->create();

    expect(Event::query()->upcoming()->pluck('id')->all())
        ->toContain($upcoming->id)
        ->not->toContain($past->id);
});

test('past returns events that have ended', function () {
    $upcoming = Event::factory()->published()->create();
    $past = Event::factory()->published()->past()->create();

    expect(Event::query()->past()->pluck('id')->all())
        ->toContain($past->id)
        ->not->toContain($upcoming->id);
});

test('next returns the soonest published upcoming event', function () {
    $later = Event::factory()->published()->create([
        'starts_at' => now()->addDays(10),
        'ends_at' => now()->addDays(10)->addHour(),
    ]);
    $soonest = Event::factory()->published()->create([
        'starts_at' => now()->addDay(),
        'ends_at' => now()->addDay()->addHour(),
    ]);
    Event::factory()->published()->past()->create();

    expect(Event::query()->next()?->id)->toBe($soonest->id)
        ->and($later->id)->not->toBe($soonest->id);
});

test('next returns null when nothing is upcoming', function () {
    Event::factory()->published()->past()->create();

    expect(Event::query()->next())->toBeNull();
});
