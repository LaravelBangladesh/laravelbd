<?php

use App\Domain\Content\Models\Resource;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\Speaker;
use App\Domain\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('a resource links its event, speaker and creator', function () {
    $event = Event::factory()->create();
    $speaker = Speaker::factory()->create();
    $author = User::factory()->create();

    $resource = Resource::factory()->create([
        'event_id' => $event->id,
        'speaker_id' => $speaker->id,
        'created_by' => $author->id,
    ]);

    expect($resource->event?->id)->toBe($event->id)
        ->and($resource->speaker?->id)->toBe($speaker->id)
        ->and($resource->creator?->id)->toBe($author->id);
});

test('an unattributed resource has no creator', function () {
    $resource = Resource::factory()->create(['created_by' => null]);

    expect($resource->creator)->toBeNull();
});
