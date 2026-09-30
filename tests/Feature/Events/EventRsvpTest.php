<?php

use App\Domain\Events\Enums\RegistrationStatus;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\EventRegistration;
use App\Domain\Identity\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests cannot rsvp', function () {
    $event = Event::factory()->published()->create();

    $this->post(route('events.rsvp.store', $event))->assertRedirect(route('login'));
});

test('members can register for a published event', function () {
    $event = Event::factory()->published()->create();
    $user = User::factory()->withCompleteProfile()->create();

    $this->actingAs($user)
        ->from(route('events.register.create', $event))
        ->post(route('events.rsvp.store', $event))
        ->assertRedirect(route('events.show', $event));

    $this->assertDatabaseHas('event_registrations', [
        'event_id' => $event->id,
        'user_id' => $user->id,
        'status' => RegistrationStatus::Registered->value,
    ]);
});

test('guests are sent to log in before the registration form', function () {
    $event = Event::factory()->published()->create();

    $this->get(route('events.register.create', $event))->assertRedirect(route('login'));
});

test('members see the registration form', function () {
    $event = Event::factory()->published()->create(['capacity' => 1]);
    EventRegistration::factory()->create([
        'event_id' => $event->id,
        'status' => RegistrationStatus::Registered,
    ]);

    $this->actingAs(User::factory()->withCompleteProfile()->create())
        ->get(route('events.register.create', $event))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('events/register')
            ->where('event.slug', $event->slug)
            ->where('is_full', true)
            ->where('questions', []));
});

test('the registration form is forbidden when registration is closed', function () {
    $event = Event::factory()->published()->create(['registration_enabled' => false]);

    $this->actingAs(User::factory()->withCompleteProfile()->create())
        ->get(route('events.register.create', $event))
        ->assertForbidden();
});

test('a registered member is sent back to the event instead of the form', function () {
    $event = Event::factory()->published()->create();
    $user = User::factory()->withCompleteProfile()->create();

    $this->actingAs($user)->post(route('events.rsvp.store', $event));

    $this->actingAs($user)
        ->get(route('events.register.create', $event))
        ->assertRedirect(route('events.show', $event));
});

test('a cancelled member can open the registration form again', function () {
    $event = Event::factory()->published()->create();
    $user = User::factory()->withCompleteProfile()->create();

    $this->actingAs($user)->post(route('events.rsvp.store', $event));
    $this->actingAs($user)->delete(route('events.rsvp.destroy', $event));

    $this->actingAs($user)
        ->get(route('events.register.create', $event))
        ->assertOk();
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
