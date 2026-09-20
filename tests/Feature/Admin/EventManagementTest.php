<?php

use App\Domain\Events\Enums\EventStatus;
use App\Domain\Events\Enums\RegistrationStatus;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\EventMedium;
use App\Domain\Events\Models\EventRegistration;
use App\Domain\Events\Models\EventSession;
use App\Domain\Events\Models\Speaker;
use App\Domain\Identity\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

test('members cannot manage events', function () {
    $member = User::factory()->create();

    $this->actingAs($member)
        ->get(route('admin.events.index'))
        ->assertForbidden();
});

test('staff can create an event', function () {
    $moderator = User::factory()->moderator()->create();

    $response = $this->actingAs($moderator)
        ->post(route('admin.events.store'), [
            'title_en' => 'October Meetup',
            'type' => 'meetup',
            'status' => 'published',
            'starts_at' => '2026-10-15T18:00',
            'ends_at' => '2026-10-15T21:00',
        ]);

    $event = Event::query()->where('title_en', 'October Meetup')->first();

    expect($event)->not->toBeNull()
        ->and($event?->status)->toBe(EventStatus::Published)
        ->and($event?->slug)->toBe('october-meetup');

    $response->assertRedirect(route('admin.events.show', $event));
});

test('staff can open event management', function () {
    $event = Event::factory()->create();
    $session = EventSession::factory()->create(['event_id' => $event->id]);
    $moderator = User::factory()->moderator()->create();

    $this->actingAs($moderator)
        ->get(route('admin.events.show', $event))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/events/manage')
            ->where('event.id', $event->id)
            ->has('event.sessions', 1)
            ->where('event.sessions.0.id', $session->id)
            ->has('event.speakers')
            ->has('event.attendees'));

    $this->actingAs($moderator)
        ->get(route('admin.events.show', ['event' => $event, 'tab' => 'attendees']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/events/manage')
            ->where('event.id', $event->id));
});

test('staff can edit event details', function () {
    $event = Event::factory()->create();
    $moderator = User::factory()->moderator()->create();

    $this->actingAs($moderator)
        ->get(route('admin.events.edit', $event))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/events/edit')
            ->where('event.id', $event->id)
            ->has('event.sessions'));

    $this->actingAs($moderator)
        ->patch(route('admin.events.update', $event), [
            'title_en' => 'Updated Meetup',
            'type' => 'meetup',
            'status' => 'draft',
            'venue_address' => 'Banani, Dhaka',
            'starts_at' => '2026-11-15T18:00',
            'ends_at' => '2026-11-15T21:00',
        ])
        ->assertRedirect();

    $event->refresh();

    expect($event->title_en)->toBe('Updated Meetup')
        ->and($event->venue_address)->toBe('Banani, Dhaka');
});

test('staff can update a session', function () {
    $event = Event::factory()->create();
    $session = EventSession::factory()->create([
        'event_id' => $event->id,
        'title_en' => 'Opening talk',
        'sort_order' => 3,
    ]);
    $moderator = User::factory()->moderator()->create();

    $this->actingAs($moderator)
        ->patch(route('admin.events.sessions.update', [$event, $session]), [
            'title_en' => 'Keynote',
            'kind' => 'talk',
            'starts_at' => '2026-10-15T18:30',
            'ends_at' => '2026-10-15T19:30',
            'room' => 'Hall A',
        ])
        ->assertRedirect();

    $session->refresh();

    expect($session->title_en)->toBe('Keynote')
        ->and($session->room)->toBe('Hall A')
        ->and($session->sort_order)->toBe(3);
});

test('staff can reorder sessions', function () {
    $event = Event::factory()->create();
    $first = EventSession::factory()->create([
        'event_id' => $event->id,
        'title_en' => 'Opening',
        'sort_order' => 0,
        'starts_at' => now()->addDay()->setTime(18, 0),
        'ends_at' => now()->addDay()->setTime(18, 30),
    ]);
    $second = EventSession::factory()->create([
        'event_id' => $event->id,
        'title_en' => 'Closing',
        'sort_order' => 1,
        'starts_at' => now()->addDay()->setTime(19, 0),
        'ends_at' => now()->addDay()->setTime(19, 30),
    ]);
    $moderator = User::factory()->moderator()->create();

    $this->actingAs($moderator)
        ->patch(route('admin.events.sessions.reorder', $event), [
            'session_ids' => [$second->id, $first->id],
        ])
        ->assertRedirect();

    expect($event->sessions()->pluck('title_en')->all())->toBe(['Closing', 'Opening']);
});

test('members cannot reorder sessions', function () {
    $event = Event::factory()->create();
    $session = EventSession::factory()->create(['event_id' => $event->id]);
    $member = User::factory()->create();

    $this->actingAs($member)
        ->patch(route('admin.events.sessions.reorder', $event), [
            'session_ids' => [$session->id],
        ])
        ->assertForbidden();
});

test('staff can remove an attendee', function () {
    $event = Event::factory()->published()->create();
    $registration = EventRegistration::factory()->create(['event_id' => $event->id]);
    $moderator = User::factory()->moderator()->create();

    $this->actingAs($moderator)
        ->delete(route('admin.events.registrations.destroy', [$event, $registration]))
        ->assertRedirect();

    expect($registration->fresh()?->status)->toBe(RegistrationStatus::Cancelled);
});

test('staff can add a youtube video but not a random url', function () {
    $event = Event::factory()->create();
    $moderator = User::factory()->moderator()->create();

    $this->actingAs($moderator)
        ->post(route('admin.events.media.store', $event), [
            'kind' => 'video',
            'embed_url' => 'https://example.com/watch',
        ])
        ->assertSessionHasErrors('embed_url');

    $this->actingAs($moderator)
        ->post(route('admin.events.media.store', $event), [
            'kind' => 'video',
            'embed_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('event_media', [
        'event_id' => $event->id,
        'kind' => 'video',
        'embed_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
    ]);
});

test('staff can upload a photo locally when cloudflare is not configured', function () {
    Storage::fake('public');

    $event = Event::factory()->create();
    $moderator = User::factory()->moderator()->create();

    $this->actingAs($moderator)
        ->post(route('admin.events.media.store', $event), [
            'kind' => 'photo',
            'photo' => UploadedFile::fake()->image('hall.jpg'),
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('event_media', [
        'event_id' => $event->id,
        'kind' => 'photo',
    ]);
});

test('staff can delete event media', function () {
    $event = Event::factory()->create();
    $medium = EventMedium::query()->create([
        'event_id' => $event->id,
        'kind' => 'video',
        'embed_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        'sort_order' => 0,
    ]);
    $moderator = User::factory()->moderator()->create();

    $this->actingAs($moderator)
        ->delete(route('admin.events.media.destroy', [$event, $medium]))
        ->assertRedirect();

    $this->assertDatabaseMissing('event_media', ['id' => $medium->id]);
});

test('staff can attach a speaker and session', function () {
    $event = Event::factory()->create();
    $speaker = Speaker::factory()->create();
    $moderator = User::factory()->moderator()->create();

    $this->actingAs($moderator)
        ->post(route('admin.events.speakers.store', $event), [
            'speaker_id' => $speaker->id,
            'role' => 'host',
        ])
        ->assertRedirect();

    $this->actingAs($moderator)
        ->post(route('admin.events.sessions.store', $event), [
            'title_en' => 'Opening talk',
            'kind' => 'talk',
            'starts_at' => '2026-10-15T18:30',
            'ends_at' => '2026-10-15T19:30',
        ])
        ->assertRedirect();

    expect($event->speakers()->whereKey($speaker)->exists())->toBeTrue()
        ->and($event->sessions()->count())->toBe(1);
});

test('staff can create a session with an existing speaker', function () {
    $event = Event::factory()->create();
    $speaker = Speaker::factory()->create();
    $moderator = User::factory()->moderator()->create();

    $this->actingAs($moderator)
        ->post(route('admin.events.sessions.store', $event), [
            'title_en' => 'Opening talk',
            'kind' => 'talk',
            'starts_at' => '2026-10-15T18:30',
            'ends_at' => '2026-10-15T19:30',
            'speaker_source' => 'existing',
            'speaker_id' => $speaker->id,
            'speaker_role' => 'host',
        ])
        ->assertRedirect();

    $session = $event->sessions()->first();

    expect($session)->not->toBeNull()
        ->and($session?->speakers()->whereKey($speaker)->exists())->toBeTrue()
        ->and($session?->speakers()->first()?->pivot->role)->toBe('host')
        ->and($event->speakers()->whereKey($speaker)->exists())->toBeTrue()
        ->and($event->speakers()->first()?->pivot->role)->toBe('host');
});

test('staff can create a session with a new speaker', function () {
    $event = Event::factory()->create();
    $moderator = User::factory()->moderator()->create();

    $this->actingAs($moderator)
        ->post(route('admin.events.sessions.store', $event), [
            'title_en' => 'Keynote',
            'kind' => 'talk',
            'starts_at' => '2026-10-15T18:30',
            'ends_at' => '2026-10-15T19:30',
            'speaker_source' => 'new',
            'speaker_name' => 'Ada Lovelace',
            'speaker_title' => 'Mathematician',
            'speaker_company' => 'Analytical Engine',
            'speaker_role' => 'speaker',
        ])
        ->assertRedirect();

    $speaker = Speaker::query()->where('name', 'Ada Lovelace')->first();
    $session = $event->sessions()->first();

    expect($speaker)->not->toBeNull()
        ->and($speaker?->slug)->toBe('ada-lovelace')
        ->and($speaker?->title)->toBe('Mathematician')
        ->and($speaker?->company)->toBe('Analytical Engine')
        ->and($session)->not->toBeNull()
        ->and($session?->speakers()->whereKey($speaker)->exists())->toBeTrue()
        ->and($event->speakers()->whereKey($speaker)->exists())->toBeTrue();
});

test('staff can add a new speaker to an existing session', function () {
    $event = Event::factory()->create();
    $session = EventSession::factory()->create(['event_id' => $event->id]);
    $moderator = User::factory()->moderator()->create();

    $this->actingAs($moderator)
        ->post(route('admin.events.sessions.speakers.store', [$event, $session]), [
            'speaker_source' => 'new',
            'speaker_name' => 'Grace Hopper',
            'speaker_role' => 'speaker',
        ])
        ->assertRedirect();

    $speaker = Speaker::query()->where('name', 'Grace Hopper')->first();

    expect($speaker)->not->toBeNull()
        ->and($session->speakers()->whereKey($speaker)->exists())->toBeTrue()
        ->and($event->speakers()->whereKey($speaker)->exists())->toBeTrue();
});

test('deleting a session releases its speaker from the event roster', function () {
    $event = Event::factory()->create();
    $session = EventSession::factory()->create(['event_id' => $event->id]);
    $speaker = Speaker::factory()->create();
    $moderator = User::factory()->moderator()->create();

    $this->actingAs($moderator)
        ->post(route('admin.events.sessions.speakers.store', [$event, $session]), [
            'speaker_source' => 'existing',
            'speaker_id' => $speaker->id,
            'speaker_role' => 'speaker',
        ])
        ->assertRedirect();

    $this->actingAs($moderator)
        ->delete(route('admin.events.sessions.destroy', [$event, $session]))
        ->assertRedirect();

    expect(EventSession::query()->whereKey($session->id)->exists())->toBeFalse()
        ->and($event->speakers()->whereKey($speaker)->exists())->toBeFalse();
});

test('detaching a session speaker leaves them on the event when another session uses them', function () {
    $event = Event::factory()->create();
    $first = EventSession::factory()->create(['event_id' => $event->id]);
    $second = EventSession::factory()->create(['event_id' => $event->id]);
    $speaker = Speaker::factory()->create();
    $moderator = User::factory()->moderator()->create();

    foreach ([$first, $second] as $session) {
        $this->actingAs($moderator)
            ->post(route('admin.events.sessions.speakers.store', [$event, $session]), [
                'speaker_source' => 'existing',
                'speaker_id' => $speaker->id,
                'speaker_role' => 'speaker',
            ])
            ->assertRedirect();
    }

    $this->actingAs($moderator)
        ->delete(route('admin.events.sessions.speakers.destroy', [$event, $first, $speaker]))
        ->assertRedirect();

    expect($first->speakers()->whereKey($speaker)->exists())->toBeFalse()
        ->and($second->speakers()->whereKey($speaker)->exists())->toBeTrue()
        ->and($event->speakers()->whereKey($speaker)->exists())->toBeTrue();
});

test('staff can delete an event', function () {
    $event = Event::factory()->create();
    $moderator = User::factory()->moderator()->create();

    $this->actingAs($moderator)
        ->delete(route('admin.events.destroy', $event))
        ->assertRedirect(route('admin.events.index'));

    $this->assertDatabaseMissing('events', ['id' => $event->id]);
});

test('staff can detach an event-level speaker', function () {
    $event = Event::factory()->create();
    $speaker = Speaker::factory()->create();
    $moderator = User::factory()->moderator()->create();

    $event->speakers()->attach($speaker, ['role' => 'host']);

    $this->actingAs($moderator)
        ->delete(route('admin.events.speakers.destroy', [$event, $speaker]))
        ->assertRedirect();

    expect($event->speakers()->whereKey($speaker)->exists())->toBeFalse();
});

test('a photo medium without a file is rejected', function () {
    $event = Event::factory()->create();
    $moderator = User::factory()->moderator()->create();

    $this->actingAs($moderator)
        ->post(route('admin.events.media.store', $event), ['kind' => 'photo'])
        ->assertSessionHasErrors('photo');

    $this->assertDatabaseCount('event_media', 0);
});

test('a video medium without an embed url is rejected', function () {
    $event = Event::factory()->create();
    $moderator = User::factory()->moderator()->create();

    $this->actingAs($moderator)
        ->post(route('admin.events.media.store', $event), ['kind' => 'video'])
        ->assertSessionHasErrors('embed_url');

    $this->assertDatabaseCount('event_media', 0);
});

test('reordering must list every session on the event exactly once', function () {
    $event = Event::factory()->create();
    $first = EventSession::factory()->create(['event_id' => $event->id, 'sort_order' => 0]);
    EventSession::factory()->create(['event_id' => $event->id, 'sort_order' => 1]);
    $moderator = User::factory()->moderator()->create();

    $this->actingAs($moderator)
        ->patch(route('admin.events.sessions.reorder', $event), [
            'session_ids' => [$first->id],
        ])
        ->assertSessionHasErrors('session_ids');

    expect($first->fresh()?->sort_order)->toBe(0);
});

test('reordering stops at the id rules before comparing the roster', function () {
    $event = Event::factory()->create();
    EventSession::factory()->create(['event_id' => $event->id]);
    $otherSession = EventSession::factory()->create();
    $moderator = User::factory()->moderator()->create();

    $this->actingAs($moderator)
        ->patch(route('admin.events.sessions.reorder', $event), [
            'session_ids' => [$otherSession->id],
        ])
        ->assertSessionHasErrors('session_ids.0')
        ->assertSessionDoesntHaveErrors('session_ids');
});

test('staff can open the call for proposals on an event', function () {
    $moderator = User::factory()->moderator()->create();

    $this->actingAs($moderator)
        ->post(route('admin.events.store'), [
            'title_en' => 'CFP meetup',
            'type' => 'meetup',
            'status' => 'published',
            'starts_at' => now()->addDays(20)->toDateTimeString(),
            'ends_at' => now()->addDays(20)->addHours(3)->toDateTimeString(),
            'cfp_enabled' => '1',
            'cfp_opens_at' => now()->subDay()->toDateTimeString(),
            'cfp_closes_at' => now()->addDays(10)->toDateTimeString(),
        ])
        ->assertRedirect();

    $event = Event::query()->where('title_en', 'CFP meetup')->firstOrFail();

    expect($event->cfp_enabled)->toBeTrue()
        ->and($event->cfp_opens_at)->not->toBeNull()
        ->and($event->isAcceptingProposals())->toBeTrue();
});

test('the cfp close date must come after the open date', function () {
    $moderator = User::factory()->moderator()->create();

    $this->actingAs($moderator)
        ->post(route('admin.events.store'), [
            'title_en' => 'Backwards window',
            'type' => 'meetup',
            'status' => 'draft',
            'starts_at' => now()->addDays(20)->toDateTimeString(),
            'ends_at' => now()->addDays(20)->addHours(3)->toDateTimeString(),
            'cfp_enabled' => '1',
            'cfp_opens_at' => now()->addDays(10)->toDateTimeString(),
            'cfp_closes_at' => now()->addDays(5)->toDateTimeString(),
        ])
        ->assertSessionHasErrors('cfp_closes_at');

    expect(Event::query()->where('title_en', 'Backwards window')->exists())->toBeFalse();
});

test('a cfp close date alone is accepted and the dates default to null', function () {
    $moderator = User::factory()->moderator()->create();

    $this->actingAs($moderator)
        ->post(route('admin.events.store'), [
            'title_en' => 'Only a deadline',
            'type' => 'meetup',
            'status' => 'draft',
            'starts_at' => now()->addDays(20)->toDateTimeString(),
            'ends_at' => now()->addDays(20)->addHours(3)->toDateTimeString(),
            'cfp_closes_at' => now()->addDays(5)->toDateTimeString(),
        ])
        ->assertRedirect();

    $event = Event::query()->where('title_en', 'Only a deadline')->firstOrFail();

    expect($event->cfp_enabled)->toBeFalse()
        ->and($event->cfp_opens_at)->toBeNull()
        ->and($event->cfp_closes_at)->not->toBeNull();
});

test('the admin event form exposes the cfp fields', function () {
    $event = Event::factory()->acceptingProposals()->create();
    $moderator = User::factory()->moderator()->create();

    $this->actingAs($moderator)
        ->get(route('admin.events.edit', $event))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/events/edit')
            ->where('event.cfp_enabled', true)
            ->whereNot('event.cfp_opens_at', null)
            ->whereNot('event.cfp_closes_at', null));
});
