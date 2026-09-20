<?php

use App\Domain\Events\Enums\RegistrationStatus;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\EventRegistration;
use App\Domain\Identity\Models\User;

test('guests cannot rsvp', function () {
    $event = Event::factory()->published()->create();

    $this->post(route('events.rsvp.store', $event))->assertRedirect(route('login'));
});

test('members can register for a published event', function () {
    $event = Event::factory()->published()->create();
    $user = User::factory()->withCompleteProfile()->create();

    $this->actingAs($user)
        ->post(route('events.rsvp.store', $event))
        ->assertRedirect();

    $this->assertDatabaseHas('event_registrations', [
        'event_id' => $event->id,
        'user_id' => $user->id,
        'status' => RegistrationStatus::Registered->value,
    ]);
});

test('rsvp is idempotent', function () {
    $event = Event::factory()->published()->create();
    $user = User::factory()->withCompleteProfile()->create();

    $this->actingAs($user)->post(route('events.rsvp.store', $event));
    $this->actingAs($user)->post(route('events.rsvp.store', $event));

    expect(EventRegistration::query()->where('event_id', $event->id)->count())->toBe(1);
});

test('full events go to the waitlist', function () {
    $event = Event::factory()->published()->create(['capacity' => 1]);
    EventRegistration::factory()->create([
        'event_id' => $event->id,
        'status' => RegistrationStatus::Registered,
    ]);

    $user = User::factory()->withCompleteProfile()->create();

    $this->actingAs($user)
        ->post(route('events.rsvp.store', $event))
        ->assertRedirect();

    expect(EventRegistration::query()->where('user_id', $user->id)->first()?->status)
        ->toBe(RegistrationStatus::Waitlisted);
});

test('members can cancel their registration', function () {
    $event = Event::factory()->published()->create();
    $user = User::factory()->withCompleteProfile()->create();

    $this->actingAs($user)->post(route('events.rsvp.store', $event));
    $this->actingAs($user)->delete(route('events.rsvp.destroy', $event));

    expect(EventRegistration::query()->where('user_id', $user->id)->first()?->status)
        ->toBe(RegistrationStatus::Cancelled);
});

test('members cannot rsvp to a past event', function () {
    $event = Event::factory()->published()->past()->create();
    $user = User::factory()->withCompleteProfile()->create();

    $this->actingAs($user)
        ->post(route('events.rsvp.store', $event))
        ->assertForbidden();
});
