<?php

use App\Domain\Events\Actions\AttachEventSpeaker;
use App\Domain\Events\Actions\DetachEventSpeaker;
use App\Domain\Events\Enums\SpeakerRole;
use App\Domain\Events\Models\Event;
use App\Domain\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('attaches and detaches a user on the event roster', function () {
    $event = Event::factory()->create();
    $speaker = User::factory()->create();

    app(AttachEventSpeaker::class)($event, $speaker->id, SpeakerRole::Host->value);

    expect($event->speakers()->first()?->pivot->role)->toBe('host');

    app(DetachEventSpeaker::class)($event, $speaker);

    expect($event->speakers()->whereKey($speaker->id)->exists())->toBeFalse();
});
