<?php

use App\Domain\Events\Enums\SpeakerRole;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\EventSession;
use App\Domain\Events\Models\Speaker;
use App\Domain\Events\Models\SpeakerAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('a speaker lists the events they are billed on with their pivot role', function () {
    $speaker = Speaker::factory()->create();
    $event = Event::factory()->create();

    $speaker->events()->attach($event, ['role' => SpeakerRole::Speaker->value, 'sort_order' => 3]);

    $billed = $speaker->events()->first();

    expect($speaker->events)->toHaveCount(1)
        ->and($billed?->id)->toBe($event->id)
        ->and($billed?->pivot)->toBeInstanceOf(SpeakerAssignment::class)
        ->and($billed?->pivot->role)->toBe(SpeakerRole::Speaker->value)
        ->and($billed?->pivot->sort_order)->toBe(3)
        ->and($billed?->pivot->created_at)->not->toBeNull();
});

test('a speaker lists the sessions they are assigned to', function () {
    $speaker = Speaker::factory()->create();
    $session = EventSession::factory()->create();
    $otherSession = EventSession::factory()->create();

    $speaker->eventSessions()->attach($session, ['role' => SpeakerRole::Speaker->value, 'sort_order' => 0]);

    expect($speaker->eventSessions->pluck('id')->all())->toBe([$session->id])
        ->and($speaker->eventSessions->pluck('id')->all())->not->toContain($otherSession->id);
});

test('speaker bios fall back to english when bengali is missing', function () {
    $speaker = Speaker::factory()->create([
        'bio_en' => 'Builds Laravel packages.',
        'bio_bn' => null,
    ]);

    app()->setLocale('bn');

    expect($speaker->localized('bio'))->toBe('Builds Laravel packages.');

    $speaker->bio_bn = 'লারাভেল প্যাকেজ বানান।';

    expect($speaker->localized('bio'))->toBe('লারাভেল প্যাকেজ বানান।');
});
