<?php

use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\EventRegistration;
use App\Domain\Identity\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('the show page reports registration as open by default', function () {
    $event = Event::factory()->published()->create();

    $this->get(route('events.show', $event))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('event.registration_enabled', true)
            ->where('event.can_rsvp', true));
});

test('the show page reports registration as closed', function () {
    $event = Event::factory()->published()->registrationClosed()->create();

    $this->get(route('events.show', $event))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('event.registration_enabled', false)
            ->where('event.can_rsvp', false));
});

test('members cannot rsvp when registration is closed', function () {
    $event = Event::factory()->published()->registrationClosed()->create();
    $user = User::factory()->withCompleteProfile()->create();

    $this->actingAs($user)
        ->post(route('events.rsvp.store', $event))
        ->assertForbidden();

    expect(EventRegistration::query()->count())->toBe(0);
});

test('members can still cancel when registration is closed', function () {
    $event = Event::factory()->published()->create();
    $user = User::factory()->withCompleteProfile()->create();

    $this->actingAs($user)->post(route('events.rsvp.store', $event));

    $event->forceFill(['registration_enabled' => false])->save();

    $this->actingAs($user)
        ->delete(route('events.rsvp.destroy', $event))
        ->assertRedirect();

    expect(EventRegistration::query()->where('user_id', $user->id)->first()?->isActive())
        ->toBeFalse();
});

test('staff can turn registration off through the admin form', function () {
    $moderator = User::factory()->moderator()->create();

    $this->actingAs($moderator)
        ->post(route('admin.events.store'), [
            'title_en' => 'Closed Meetup',
            'type' => 'meetup',
            'status' => 'published',
            'starts_at' => '2026-10-15T18:00',
            'ends_at' => '2026-10-15T21:00',
            'registration_enabled' => '0',
        ]);

    expect(Event::query()->where('title_en', 'Closed Meetup')->first()?->registration_enabled)
        ->toBeFalse();
});

test('staff can turn registration on through the admin form', function () {
    $moderator = User::factory()->moderator()->create();

    $this->actingAs($moderator)
        ->post(route('admin.events.store'), [
            'title_en' => 'Open Meetup',
            'type' => 'meetup',
            'status' => 'published',
            'starts_at' => '2026-10-15T18:00',
            'ends_at' => '2026-10-15T21:00',
            'registration_enabled' => '1',
        ]);

    expect(Event::query()->where('title_en', 'Open Meetup')->first()?->registration_enabled)
        ->toBeTrue();
});

test('a non boolean registration flag is rejected', function () {
    $moderator = User::factory()->moderator()->create();

    $this->actingAs($moderator)
        ->post(route('admin.events.store'), [
            'title_en' => 'Broken Meetup',
            'type' => 'meetup',
            'status' => 'published',
            'starts_at' => '2026-10-15T18:00',
            'ends_at' => '2026-10-15T21:00',
            'registration_enabled' => 'maybe',
        ])
        ->assertSessionHasErrors('registration_enabled');
});

test('the admin form exposes the registration flag', function () {
    $event = Event::factory()->registrationClosed()->create();
    $moderator = User::factory()->moderator()->create();

    $this->actingAs($moderator)
        ->get(route('admin.events.edit', $event))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('event.registration_enabled', false));
});
