<?php

use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\EventRegistration;
use App\Domain\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('a registration links the event and the attendee', function () {
    $event = Event::factory()->create();
    $user = User::factory()->create();
    $registration = EventRegistration::factory()->create([
        'event_id' => $event->id,
        'user_id' => $user->id,
    ]);

    expect($registration->event?->id)->toBe($event->id)
        ->and($registration->user?->id)->toBe($user->id);
});
