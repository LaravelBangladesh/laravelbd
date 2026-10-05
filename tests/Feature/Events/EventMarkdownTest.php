<?php

use App\Domain\Events\Enums\EventStatus;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\EventMedium;
use App\Domain\Events\Models\EventRegistration;
use App\Domain\Events\Models\EventSession;
use App\Domain\Identity\Models\User;

function eventMarkdown(Event $event): string
{
    return test()->get(route('events.show', $event).'.md')
        ->assertOk()
        ->assertHeader('Content-Type', 'text/markdown; charset=utf-8')
        ->getContent();
}

test('an event describes everything a visitor sees', function () {
    $event = Event::factory()->published()->create([
        'title_en' => 'Laracon Dhaka',
        'excerpt_en' => 'A day of Laravel talks.',
        'description_en' => 'Bring a laptop.',
        'venue_name' => 'BASIS Auditorium',
        'venue_address' => 'Kawran Bazar, Dhaka',
        'venue_map_url' => 'https://maps.example.com/basis',
        'online_url' => 'https://youtube.com/live/laracon',
        'starts_at' => '2030-01-10 12:00:00',
        'ends_at' => '2030-01-10 15:00:00',
        'capacity' => 2,
    ]);
    EventRegistration::factory()->create(['event_id' => $event->id]);

    $session = EventSession::factory()->create([
        'event_id' => $event->id,
        'title_en' => 'Queues in depth',
        'description_en' => 'How jobs are retried.',
        'starts_at' => '2030-01-10 12:30:00',
        'ends_at' => '2030-01-10 13:00:00',
        'room' => 'Hall A',
    ]);
    $speaker = User::factory()->listedInDirectory()->create(['name' => 'Ada Lovelace', 'slug' => 'ada-lovelace', 'title' => 'Engineer', 'company' => 'Engines']);
    $session->speakers()->attach($speaker, ['role' => 'speaker']);

    EventMedium::factory()->video()->create(['event_id' => $event->id, 'caption_en' => 'Keynote']);

    expect(eventMarkdown($event))
        ->toContain('# Laracon Dhaka')
        ->toContain('- **Status:** Upcoming')
        ->toContain('- **Type:** Meetup')
        ->toContain('- **When:** 10 Jan 2030, 18:00 – 21:00 (Asia/Dhaka, UTC+6)')
        ->toContain('- **Where:** BASIS Auditorium, Kawran Bazar, Dhaka ([map](https://maps.example.com/basis))')
        ->toContain('- **Online:** https://youtube.com/live/laracon')
        ->toContain('- **Registration:** Open. 1 of 2 seats taken. Free. Register on '.route('events.show', $event))
        ->toContain('- **Page:** '.route('events.show', $event))
        ->toContain('A day of Laravel talks.')
        ->toContain('Bring a laptop.')
        ->toContain("## Schedule\n\n- 18:30–19:00 · Talk: Queues in depth — Ada Lovelace (Hall A)\n  How jobs are retried.")
        ->toContain("## Speakers\n\n- [Ada Lovelace](".route('directory.show', 'ada-lovelace').') — Engineer, Engines')
        ->toContain("## Videos\n\n- [Keynote](https://www.youtube.com/watch?v=dQw4w9WgXcQ)");
});

test('a bare event leaves out the sections it has nothing for', function () {
    $event = Event::factory()->published()->create([
        'venue_name' => null,
        'online_url' => null,
    ]);
    $session = EventSession::factory()->create(['event_id' => $event->id, 'title_en' => 'Lunch', 'room' => null, 'description_en' => null]);
    $event->speakers()->attach(User::factory()->create(['name' => 'Grace Hopper', 'title' => null, 'company' => null]), ['role' => 'host']);
    EventMedium::factory()->video()->create(['event_id' => $event->id, 'caption_en' => null]);

    expect(eventMarkdown($event))
        ->not->toContain('**Where:**')
        ->not->toContain('**Online:**')
        ->not->toContain('**Call for papers:**')
        ->toContain('· Talk: Lunch'."\n")
        ->toContain("- Grace Hopper\n")
        ->toContain('- [Recording](');

    $session->delete();
    $event->speakers()->detach();
    $event->media()->delete();

    expect(eventMarkdown($event))
        ->not->toContain('## Schedule')
        ->not->toContain('## Speakers')
        ->not->toContain('## Videos');
});

test('the venue is listed without a map link when there is none', function () {
    $event = Event::factory()->published()->create([
        'venue_name' => 'BASIS Auditorium',
        'venue_address' => null,
        'venue_map_url' => null,
    ]);

    expect(eventMarkdown($event))->toContain("- **Where:** BASIS Auditorium\n");
});

test('the registration line follows the event', function (array $state, string $line) {
    $event = Event::factory()->published()->create($state);

    if (($state['capacity'] ?? null) === 1) {
        EventRegistration::factory()->create(['event_id' => $event->id]);
    }

    expect(eventMarkdown($event))->toContain("- **Registration:** {$line}");
})->with([
    'no capacity' => [['capacity' => null], 'Open. Free.'],
    'full' => [['capacity' => 1], 'Full, new registrations join the waitlist. 1 of 1 seats taken.'],
    'not required' => [['registration_enabled' => false], 'Not required'],
    'over' => [['starts_at' => now()->subDays(2), 'ends_at' => now()->subDay()], 'Closed'],
]);

test('a past event is marked as past', function () {
    $event = Event::factory()->published()->past()->create();

    expect(eventMarkdown($event))->toContain('- **Status:** Past');
});

test('a cancelled event is marked as cancelled', function () {
    $event = Event::factory()->create(['status' => EventStatus::Cancelled]);

    $this->actingAs(User::factory()->admin()->create());

    expect(eventMarkdown($event))->toContain('- **Status:** Cancelled');
});

test('the call for papers is described while it is open or about to open', function () {
    $open = Event::factory()->acceptingProposals()->create();
    $openEnded = Event::factory()->acceptingProposals()->create(['cfp_closes_at' => null]);
    $pending = Event::factory()->published()->create([
        'cfp_enabled' => true,
        'cfp_opens_at' => '2030-01-01 04:00:00',
        'cfp_closes_at' => '2030-02-01 04:00:00',
    ]);

    expect(eventMarkdown($open))
        ->toContain('- **Call for papers:** Open until ')
        ->toContain('Submit a talk on '.route('events.show', $open).'/cfp');
    expect(eventMarkdown($openEnded))->toContain('- **Call for papers:** Open. Submit a talk on ');
    expect(eventMarkdown($pending))->toContain('- **Call for papers:** Opens 01 Jan 2030, 10:00');
});
