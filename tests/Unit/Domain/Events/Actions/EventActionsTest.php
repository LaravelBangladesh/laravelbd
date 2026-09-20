<?php

use App\Domain\Events\Actions\CreateEvent;
use App\Domain\Events\Actions\DeleteEvent;
use App\Domain\Events\Actions\UpdateEvent;
use App\Domain\Events\Data\EventData;
use App\Domain\Events\Enums\EventStatus;
use App\Domain\Events\Enums\EventType;
use App\Domain\Events\Models\Event;
use App\Domain\Identity\Models\User;
use App\Domain\Shared\Contracts\ImageStorage;
use App\Domain\Shared\Data\UploadedImage;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function eventPayload(array $overrides = []): array
{
    return [
        'title_en' => 'October Meetup',
        'type' => EventType::Meetup->value,
        'status' => EventStatus::Published->value,
        'starts_at' => '2030-10-01T10:00',
        'ends_at' => '2030-10-01T12:00',
        ...$overrides,
    ];
}

test('creates an event with a unique slug, author and cover', function () {
    $author = User::factory()->create();

    $event = app(CreateEvent::class)(
        EventData::fromValidated(eventPayload()),
        $author,
        new UploadedImage('binary', 'hall.jpg'),
    );

    expect($event->exists)->toBeTrue()
        ->and($event->slug)->toBe('october-meetup')
        ->and($event->created_by)->toBe($author->id)
        ->and($event->cover_path)->not->toBeNull()
        ->and($event->published_at)->not->toBeNull();
});

test('creates a draft event without a published date or cover', function () {
    $event = app(CreateEvent::class)(
        EventData::fromValidated(eventPayload(['status' => EventStatus::Draft->value])),
        null,
    );

    expect($event->published_at)->toBeNull()
        ->and($event->created_by)->toBeNull()
        ->and($event->cover_path)->toBeNull();
});

test('updates an event and replaces its cover', function () {
    $event = Event::factory()->create(['cover_path' => 'events/covers/old.jpg']);

    app(UpdateEvent::class)(
        $event,
        EventData::fromValidated(eventPayload(['title_en' => 'November Meetup'])),
        new UploadedImage('binary', 'new.jpg'),
    );

    expect($event->fresh()?->title_en)->toBe('November Meetup')
        ->and($event->fresh()?->slug)->toBe('november-meetup')
        ->and($event->fresh()?->cover_path)->not->toBe('events/covers/old.jpg');
});

test('updates an event without a new cover', function () {
    $event = Event::factory()->create(['cover_path' => 'events/covers/old.jpg']);

    app(UpdateEvent::class)($event, EventData::fromValidated(eventPayload()));

    expect($event->fresh()?->cover_path)->toBe('events/covers/old.jpg');
});

test('deletes an event and its cover', function () {
    $event = Event::factory()->create(['cover_path' => 'events/covers/hall.jpg']);
    $images = Mockery::mock(ImageStorage::class);
    $images->shouldReceive('delete')->once()->with('events/covers/hall.jpg');

    (new DeleteEvent($images))($event);

    expect(Event::query()->whereKey($event->id)->exists())->toBeFalse();
});
