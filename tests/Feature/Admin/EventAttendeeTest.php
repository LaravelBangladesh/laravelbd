<?php

use App\Domain\Events\Enums\RegistrationStatus;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\EventQuestion;
use App\Domain\Events\Models\EventRegistration;
use App\Domain\Events\Models\EventRegistrationAnswer;
use App\Domain\Identity\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

/**
 * @param  array<string, string>  $query
 * @return list<string>
 */
function listedAttendeeNames(mixed $test, User $viewer, Event $event, array $query): array
{
    $names = [];

    $test->actingAs($viewer)
        ->get(route('admin.events.attendees.index', [$event, ...$query]))
        ->assertOk()
        ->assertInertia(function (Assert $page) use (&$names) {
            $names = array_column($page->toArray()['props']['attendees']['data'], 'name');
        });

    return $names;
}

function attendee(Event $event, array $user, RegistrationStatus $status = RegistrationStatus::Registered): EventRegistration
{
    return EventRegistration::factory()->create([
        'event_id' => $event->id,
        'user_id' => User::factory()->create($user)->id,
        'status' => $status,
    ]);
}

test('staff see an event\'s attendees 25 a page', function () {
    $moderator = User::factory()->moderator()->create();
    $event = Event::factory()->create(['title_en' => 'October Meetup']);
    EventRegistration::factory()->count(27)->create(['event_id' => $event->id]);
    EventRegistration::factory()->create();
    $first = attendee($event, ['name' => 'Ada Lovelace', 'mobile_number' => '+8801712345678']);
    $first->update(['registered_at' => now()->subDay()]);

    $this->actingAs($moderator)
        ->get(route('admin.events.attendees.index', $event))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/events/attendees')
            ->where('event', ['id' => $event->id, 'title_en' => 'October Meetup'])
            ->has('attendees.data', 25)
            ->where('attendees.total', 28)
            ->where('attendees.data.0.id', $first->id)
            ->where('attendees.data.0.name', 'Ada Lovelace')
            ->where('attendees.data.0.mobile_number', '+8801712345678')
            ->where('attendees.data.0.status', 'registered')
            ->where('attendees.data.0.registered_at', fn (string $value) => $value !== '')
            ->where('filters', ['q' => '', 'status' => '', 'reminder' => ''])
            ->has('statuses', 3));

    $this->actingAs($moderator)
        ->get(route('admin.events.attendees.index', [$event, 'page' => 2]))
        ->assertInertia(fn (Assert $page) => $page->has('attendees.data', 3));
});

test('staff search attendees and filter them by status', function () {
    $moderator = User::factory()->moderator()->create();
    $event = Event::factory()->create();
    attendee($event, ['name' => 'Ada Lovelace', 'email' => 'ada@analytical.test', 'mobile_number' => '+8801712345678']);
    attendee($event, ['name' => 'Grace Hopper', 'email' => 'grace@navy.test'], RegistrationStatus::Waitlisted);
    attendee($event, ['name' => 'Alan Turing', 'email' => 'alan@turing.test'], RegistrationStatus::Cancelled);
    attendee(Event::factory()->create(), ['name' => 'Ada Elsewhere']);

    expect(listedAttendeeNames($this, $moderator, $event, ['q' => 'ada']))->toBe(['Ada Lovelace'])
        ->and(listedAttendeeNames($this, $moderator, $event, ['q' => 'ANALYTICAL']))->toBe(['Ada Lovelace'])
        ->and(listedAttendeeNames($this, $moderator, $event, ['q' => '01712345678']))->toBe(['Ada Lovelace'])
        ->and(listedAttendeeNames($this, $moderator, $event, ['status' => 'waitlisted']))->toBe(['Grace Hopper'])
        ->and(listedAttendeeNames($this, $moderator, $event, ['status' => 'cancelled']))->toBe(['Alan Turing'])
        ->and(listedAttendeeNames($this, $moderator, $event, []))->toHaveCount(3);
});

test('an unknown attendee status is rejected', function () {
    $moderator = User::factory()->moderator()->create();
    $event = Event::factory()->create();

    $this->actingAs($moderator)
        ->get(route('admin.events.attendees.index', [$event, 'status' => 'maybe']))
        ->assertSessionHasErrors('status');
});

test('staff export attendees with one column per question', function () {
    $moderator = User::factory()->moderator()->create();
    $event = Event::factory()->create(['slug' => 'october-meetup']);
    $company = EventQuestion::factory()->create(['event_id' => $event->id, 'label_en' => 'Company', 'position' => 1]);
    $topics = EventQuestion::factory()->multipleChoice()->create(['event_id' => $event->id, 'label_en' => '@Topics', 'position' => 2]);
    $ada = attendee($event, ['name' => 'Ada Lovelace', 'email' => 'ada@example.com', 'mobile_number' => '+8801712345678']);
    $ada->update(['registered_at' => '2026-10-01 12:00:00']);
    EventRegistrationAnswer::factory()->create(['event_registration_id' => $ada->id, 'question' => $company->snapshot(), 'value' => '=1+1']);
    EventRegistrationAnswer::factory()->create(['event_registration_id' => $ada->id, 'question' => $topics->snapshot(), 'value' => ['Testing', 'Queues']]);
    attendee($event, ['name' => 'Grace Hopper', 'email' => 'grace@example.com'], RegistrationStatus::Waitlisted)
        ->update(['registered_at' => '2026-10-02 12:00:00']);
    attendee($event, ['name' => 'Alan Turing', 'email' => 'alan@turing.test'], RegistrationStatus::Cancelled);

    $response = $this->actingAs($moderator)
        ->get(route('admin.events.attendees.export', [$event, 'q' => 'example.com']));

    $response->assertOk()
        ->assertHeader('Content-Type', 'text/csv; charset=UTF-8')
        ->assertDownload('october-meetup-attendees.csv');

    expect(array_map(str_getcsv(...), explode("\n", trim(csvBody($response)))))->toBe([
        [__('auth.name'), __('auth.email'), 'Mobile', 'Status', 'Registered', 'Company', "'@Topics"],
        ['Ada Lovelace', 'ada@example.com', '+8801712345678', __('events.rsvp.registered'), '2026-10-01 18:00', "'=1+1", 'Testing, Queues'],
        ['Grace Hopper', 'grace@example.com', '', __('events.rsvp.waitlisted'), '2026-10-02 18:00', '', ''],
    ]);
});

test('members cannot see or export attendees', function () {
    $member = User::factory()->create();
    $event = Event::factory()->create();

    $this->actingAs($member)->get(route('admin.events.attendees.index', $event))->assertForbidden();
    $this->actingAs($member)->get(route('admin.events.attendees.export', $event))->assertForbidden();
});

test('removing an attendee returns to the attendees page', function () {
    $moderator = User::factory()->moderator()->create();
    $event = Event::factory()->published()->create();
    $registration = EventRegistration::factory()->create(['event_id' => $event->id]);

    $this->actingAs($moderator)
        ->delete(route('admin.events.registrations.destroy', [$event, $registration]))
        ->assertRedirect(route('admin.events.attendees.index', $event));

    expect($registration->fresh()?->status)->toBe(RegistrationStatus::Cancelled);
});

test('event management counts the active attendees', function () {
    $moderator = User::factory()->moderator()->create();
    $event = Event::factory()->create();
    EventRegistration::factory()->create(['event_id' => $event->id]);
    EventRegistration::factory()->waitlisted()->create(['event_id' => $event->id]);
    EventRegistration::factory()->cancelled()->create(['event_id' => $event->id]);

    $this->actingAs($moderator)
        ->get(route('admin.events.show', $event))
        ->assertInertia(fn (Assert $page) => $page
            ->where('attendeesCount', 2)
            ->missing('event.attendees'));
});

test('staff register a cancelled attendee again in a free seat or on the waitlist', function () {
    $moderator = User::factory()->moderator()->create();
    $event = Event::factory()->published()->create(['capacity' => 1]);
    $first = EventRegistration::factory()->cancelled()->create(['event_id' => $event->id]);
    $second = EventRegistration::factory()->cancelled()->create(['event_id' => $event->id]);

    $this->actingAs($moderator)
        ->patch(route('admin.events.registrations.restore', [$event, $first]))
        ->assertRedirect(route('admin.events.attendees.index', $event))
        ->assertInertiaFlash('toast.message', __('admin.attendee_reinstated', ['status' => __('events.rsvp.registered')]));

    $this->actingAs($moderator)
        ->patch(route('admin.events.registrations.restore', [$event, $second]))
        ->assertInertiaFlash('toast.message', __('admin.attendee_reinstated', ['status' => __('events.rsvp.waitlisted')]));

    expect($first->fresh()?->status)->toBe(RegistrationStatus::Registered)
        ->and($second->fresh()?->status)->toBe(RegistrationStatus::Waitlisted);

    $this->actingAs($moderator)
        ->patch(route('admin.events.registrations.restore', [$event, $second]))
        ->assertInertiaFlash('toast.message', __('admin.attendee_reinstated', ['status' => __('events.rsvp.waitlisted')]));
});

test('a registration cannot come back once the event ended or the user is deactivated', function () {
    $moderator = User::factory()->moderator()->create();
    $past = Event::factory()->published()->create(['starts_at' => now()->subDays(2), 'ends_at' => now()->subDay()]);
    $ended = EventRegistration::factory()->cancelled()->create(['event_id' => $past->id]);
    $event = Event::factory()->published()->create();
    $gone = EventRegistration::factory()->cancelled()->create(['event_id' => $event->id]);
    $gone->user?->delete();

    $this->actingAs($moderator)
        ->patch(route('admin.events.registrations.restore', [$past, $ended]))
        ->assertSessionHasErrors(['registration' => __('admin.event_ended')]);

    $this->actingAs($moderator)
        ->patch(route('admin.events.registrations.restore', [$event, $gone]))
        ->assertSessionHasErrors(['registration' => __('admin.attendee_deactivated')]);

    expect($ended->fresh()?->status)->toBe(RegistrationStatus::Cancelled)
        ->and($gone->fresh()?->status)->toBe(RegistrationStatus::Cancelled);
});

test('a registration from another event cannot be registered again here', function () {
    $moderator = User::factory()->moderator()->create();
    $event = Event::factory()->create();
    $other = EventRegistration::factory()->cancelled()->create();

    $this->actingAs($moderator)
        ->patch(route('admin.events.registrations.restore', [$event, $other]))
        ->assertNotFound();
});

test('a deactivated attendee keeps their name in admin', function () {
    $moderator = User::factory()->moderator()->create();
    $event = Event::factory()->create();
    attendee($event, ['name' => 'Ada Lovelace', 'email' => 'ada@example.com'])->user?->delete();

    $this->actingAs($moderator)
        ->get(route('admin.events.attendees.index', [$event, 'q' => 'lovelace']))
        ->assertInertia(fn (Assert $page) => $page
            ->where('attendees.data.0.name', 'Ada Lovelace')
            ->where('attendees.data.0.user_active', false));

    $export = $this->actingAs($moderator)->get(route('admin.events.attendees.export', $event));

    expect(csvBody($export))->toContain('Ada Lovelace');
});
