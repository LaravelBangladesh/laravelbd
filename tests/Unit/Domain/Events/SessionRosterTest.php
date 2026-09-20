<?php

use App\Domain\Events\Data\SessionSpeakerData;
use App\Domain\Events\Enums\SpeakerRole;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\EventSession;
use App\Domain\Events\Models\Speaker;
use App\Domain\Events\SessionRoster;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

test('attaches a speaker to the session and the event roster', function () {
    $event = Event::factory()->create();
    $session = EventSession::factory()->create(['event_id' => $event->id]);
    $speaker = Speaker::factory()->create();

    SessionRoster::attach($event, $session, $speaker, SpeakerRole::Host->value);

    expect($session->speakers()->whereKey($speaker)->exists())->toBeTrue()
        ->and($event->speakers()->whereKey($speaker)->exists())->toBeTrue()
        ->and($session->speakers()->first()?->pivot->role)->toBe('host');
});

test('detaches from the event only when no other session uses the speaker', function () {
    $event = Event::factory()->create();
    $first = EventSession::factory()->create(['event_id' => $event->id]);
    $second = EventSession::factory()->create(['event_id' => $event->id]);
    $speaker = Speaker::factory()->create();

    SessionRoster::attach($event, $first, $speaker, SpeakerRole::Speaker->value);
    SessionRoster::attach($event, $second, $speaker, SpeakerRole::Speaker->value);
    SessionRoster::detach($event, $first, $speaker);

    expect($first->speakers()->whereKey($speaker)->exists())->toBeFalse()
        ->and($second->speakers()->whereKey($speaker)->exists())->toBeTrue()
        ->and($event->speakers()->whereKey($speaker)->exists())->toBeTrue();

    SessionRoster::detach($event, $second, $speaker);

    expect($event->speakers()->whereKey($speaker)->exists())->toBeFalse();
});

test('speaker from input finds an existing speaker or creates one', function () {
    $existing = Speaker::factory()->create();

    $found = SessionRoster::speakerFrom(SessionSpeakerData::fromValidated([
        'speaker_source' => 'existing',
        'speaker_id' => $existing->id,
    ]));

    $created = SessionRoster::speakerFrom(SessionSpeakerData::fromValidated([
        'speaker_source' => 'new',
        'speaker_name' => 'Grace Hopper',
        'speaker_title' => 'Rear Admiral',
    ]), 'speakers/grace.jpg');

    $withoutName = SessionRoster::speakerFrom(SessionSpeakerData::fromValidated(['speaker_source' => 'new']));
    $none = SessionRoster::speakerFrom(SessionSpeakerData::fromValidated(['speaker_source' => 'none']));

    expect($found?->is($existing))->toBeTrue()
        ->and($created)->not->toBeNull()
        ->and($created?->name)->toBe('Grace Hopper')
        ->and($created?->title)->toBe('Rear Admiral')
        ->and($created?->photo_path)->toBe('speakers/grace.jpg')
        ->and(Str::isUuid($created?->id))->toBeTrue()
        ->and($withoutName)->toBeNull()
        ->and($none)->toBeNull();
});
