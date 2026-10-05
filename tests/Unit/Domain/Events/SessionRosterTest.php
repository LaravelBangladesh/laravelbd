<?php

use App\Domain\Events\Enums\SpeakerRole;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\EventSession;
use App\Domain\Events\SessionRoster;
use App\Domain\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('attaches a speaker to the session and the event roster', function () {
    $event = Event::factory()->create();
    $session = EventSession::factory()->create(['event_id' => $event->id]);
    $speaker = User::factory()->create();

    SessionRoster::attach($event, $session, $speaker, SpeakerRole::Host->value);

    expect($session->speakers()->whereKey($speaker)->exists())->toBeTrue()
        ->and($event->speakers()->whereKey($speaker)->exists())->toBeTrue()
        ->and($session->speakers()->first()?->pivot->role)->toBe('host');
});

test('detaches from the event only when no other session uses the speaker', function () {
    $event = Event::factory()->create();
    $first = EventSession::factory()->create(['event_id' => $event->id]);
    $second = EventSession::factory()->create(['event_id' => $event->id]);
    $speaker = User::factory()->create();

    SessionRoster::attach($event, $first, $speaker, SpeakerRole::Speaker->value);
    SessionRoster::attach($event, $second, $speaker, SpeakerRole::Speaker->value);
    SessionRoster::detach($event, $first, $speaker);

    expect($first->speakers()->whereKey($speaker)->exists())->toBeFalse()
        ->and($second->speakers()->whereKey($speaker)->exists())->toBeTrue()
        ->and($event->speakers()->whereKey($speaker)->exists())->toBeTrue();

    SessionRoster::detach($event, $second, $speaker);

    expect($event->speakers()->whereKey($speaker)->exists())->toBeFalse();
});
