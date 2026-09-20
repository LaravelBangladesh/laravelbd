<?php

use App\Domain\Events\Actions\AttachSessionSpeaker;
use App\Domain\Events\Actions\CreateSession;
use App\Domain\Events\Actions\DeleteSession;
use App\Domain\Events\Actions\DetachSessionSpeaker;
use App\Domain\Events\Actions\ReorderSessions;
use App\Domain\Events\Actions\UpdateSession;
use App\Domain\Events\Data\SessionData;
use App\Domain\Events\Data\SessionSpeakerData;
use App\Domain\Events\Enums\SessionKind;
use App\Domain\Events\Enums\SpeakerRole;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\EventSession;
use App\Domain\Events\Models\Speaker;
use App\Domain\Shared\Data\UploadedImage;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function sessionPayload(array $overrides = []): array
{
    return [
        'title_en' => 'Keynote',
        'kind' => SessionKind::Talk->value,
        'starts_at' => '2030-10-01T10:00',
        'ends_at' => '2030-10-01T11:00',
        ...$overrides,
    ];
}

test('creates a session appended to the end of the agenda', function () {
    $event = Event::factory()->create();
    EventSession::factory()->create(['event_id' => $event->id, 'sort_order' => 4]);

    $session = app(CreateSession::class)($event, SessionData::fromValidated(sessionPayload()));

    expect($session->event_id)->toBe($event->id)
        ->and($session->sort_order)->toBe(5)
        ->and($session->title_en)->toBe('Keynote');
});

test('creates a session with an explicit sort order and a new speaker', function () {
    $event = Event::factory()->create();

    $session = app(CreateSession::class)(
        $event,
        SessionData::fromValidated(sessionPayload([
            'sort_order' => 2,
            'speaker_source' => 'new',
            'speaker_name' => 'Grace Hopper',
            'speaker_role' => SpeakerRole::Host->value,
        ])),
        new UploadedImage('binary', 'grace.jpg'),
    );

    expect($session->sort_order)->toBe(2)
        ->and($session->speakers()->first()?->name)->toBe('Grace Hopper')
        ->and($event->speakers()->first()?->name)->toBe('Grace Hopper');
});

test('updates a session and keeps its position', function () {
    $event = Event::factory()->create();
    $session = EventSession::factory()->create(['event_id' => $event->id, 'sort_order' => 3]);

    app(UpdateSession::class)($event, $session, SessionData::fromValidated(sessionPayload([
        'title_en' => 'Closing note',
    ])));

    expect($session->fresh()?->title_en)->toBe('Closing note')
        ->and($session->fresh()?->sort_order)->toBe(3);
});

test('deletes a session and releases speakers from the event', function () {
    $event = Event::factory()->create();
    $session = EventSession::factory()->create(['event_id' => $event->id]);
    $speaker = Speaker::factory()->create();

    app(AttachSessionSpeaker::class)($event, $session, SessionSpeakerData::fromValidated([
        'speaker_source' => 'existing',
        'speaker_id' => $speaker->id,
    ]));

    app(DeleteSession::class)($event, $session);

    expect(EventSession::query()->whereKey($session->id)->exists())->toBeFalse()
        ->and($event->speakers()->whereKey($speaker->id)->exists())->toBeFalse();
});

test('attaching returns null when the input names no speaker', function () {
    $event = Event::factory()->create();
    $session = EventSession::factory()->create(['event_id' => $event->id]);

    $speaker = app(AttachSessionSpeaker::class)($event, $session, SessionSpeakerData::fromValidated([
        'speaker_source' => 'none',
    ]));

    expect($speaker)->toBeNull();
});

test('detaches a speaker from a session', function () {
    $event = Event::factory()->create();
    $session = EventSession::factory()->create(['event_id' => $event->id]);
    $speaker = Speaker::factory()->create();

    app(AttachSessionSpeaker::class)($event, $session, SessionSpeakerData::fromValidated([
        'speaker_source' => 'existing',
        'speaker_id' => $speaker->id,
    ]));

    app(DetachSessionSpeaker::class)($event, $session, $speaker);

    expect($session->speakers()->whereKey($speaker->id)->exists())->toBeFalse()
        ->and($event->speakers()->whereKey($speaker->id)->exists())->toBeFalse();
});

test('reorders sessions by the given order', function () {
    $event = Event::factory()->create();
    $first = EventSession::factory()->create(['event_id' => $event->id, 'sort_order' => 0]);
    $second = EventSession::factory()->create(['event_id' => $event->id, 'sort_order' => 1]);

    app(ReorderSessions::class)($event, [$second->id, $first->id]);

    expect($first->fresh()?->sort_order)->toBe(1)
        ->and($second->fresh()?->sort_order)->toBe(0);
});
