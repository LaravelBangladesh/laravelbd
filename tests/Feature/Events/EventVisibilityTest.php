<?php

use App\Domain\Events\Enums\EventType;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\EventSession;
use App\Domain\Events\Models\Speaker;
use App\Domain\Identity\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('the home page returns a successful response', function () {
    $this->get(route('home'))->assertOk();
});

test('guests can view published events', function () {
    $event = Event::factory()->published()->create([
        'title_en' => 'Published Meetup',
    ]);

    $this->get(route('events.index'))->assertOk();
    $this->get(route('events.show', $event))->assertOk()->assertSee('Published Meetup');
});

test('guests cannot view draft events', function () {
    $event = Event::factory()->create();

    $this->get(route('events.show', $event))->assertForbidden();
});

test('staff can view draft events', function () {
    $event = Event::factory()->create();
    $moderator = User::factory()->moderator()->create();

    $this->actingAs($moderator)
        ->get(route('events.show', $event))
        ->assertOk();
});

test('home lists upcoming published events', function () {
    Event::factory()->published()->create(['title_en' => 'Next Meetup']);
    Event::factory()->create(['title_en' => 'Hidden Draft']);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Next Meetup')
        ->assertDontSee('Hidden Draft');
});

test('published event lists speakers from sessions', function () {
    $event = Event::factory()->published()->create();
    $session = EventSession::factory()->create(['event_id' => $event->id]);
    $speaker = Speaker::factory()->create(['name' => 'Ada Lovelace']);

    $session->speakers()->attach($speaker, ['role' => 'speaker']);

    $this->get(route('events.show', $event))
        ->assertOk()
        ->assertSee('Ada Lovelace');
});

test('published events list sessions in staff order', function () {
    $event = Event::factory()->published()->create();
    EventSession::factory()->create([
        'event_id' => $event->id,
        'title_en' => 'Later talk',
        'sort_order' => 1,
        'starts_at' => now()->addDay()->setTime(18, 0),
        'ends_at' => now()->addDay()->setTime(18, 30),
    ]);
    EventSession::factory()->create([
        'event_id' => $event->id,
        'title_en' => 'Opening remarks',
        'sort_order' => 0,
        'starts_at' => now()->addDay()->setTime(20, 0),
        'ends_at' => now()->addDay()->setTime(20, 30),
    ]);

    $this->get(route('events.show', $event))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('events/show')
            ->where('event.sessions.0.title', 'Opening remarks')
            ->where('event.sessions.1.title', 'Later talk'));
});

test('the events index orders upcoming and past events newest first', function () {
    Event::factory()->published()->create([
        'title_en' => 'Sooner upcoming',
        'starts_at' => now()->addDays(5),
        'ends_at' => now()->addDays(5)->addHours(3),
    ]);
    Event::factory()->published()->create([
        'title_en' => 'Later upcoming',
        'starts_at' => now()->addDays(30),
        'ends_at' => now()->addDays(30)->addHours(3),
    ]);
    Event::factory()->published()->create([
        'title_en' => 'Recent past',
        'starts_at' => now()->subDays(5),
        'ends_at' => now()->subDays(5)->addHours(3),
    ]);
    Event::factory()->published()->create([
        'title_en' => 'Older past',
        'starts_at' => now()->subDays(40),
        'ends_at' => now()->subDays(40)->addHours(3),
    ]);

    $this->get(route('events.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('events/index')
            ->where('upcoming.0.title', 'Later upcoming')
            ->where('upcoming.1.title', 'Sooner upcoming')
            ->where('past.0.title', 'Recent past')
            ->where('past.1.title', 'Older past'));
});

test('the home page lists the three soonest upcoming events first', function () {
    Event::factory()->published()->create([
        'title_en' => 'Furthest away',
        'starts_at' => now()->addDays(60),
        'ends_at' => now()->addDays(60)->addHours(3),
    ]);
    Event::factory()->published()->create([
        'title_en' => 'Very soon',
        'starts_at' => now()->addDays(2),
        'ends_at' => now()->addDays(2)->addHours(3),
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('welcome')
            ->where('upcomingEvents.0.title', 'Very soon')
            ->where('upcomingEvents.1.title', 'Furthest away'));
});

test('the events index can be filtered by type', function () {
    Event::factory()->published()->create([
        'title_en' => 'A workshop',
        'type' => EventType::Workshop,
    ]);
    Event::factory()->published()->create([
        'title_en' => 'A meetup',
        'type' => EventType::Meetup,
    ]);

    $this->get(route('events.index', ['type' => 'workshop']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('events/index')
            ->where('type', 'workshop')
            ->has('upcoming', 1)
            ->where('upcoming.0.title', 'A workshop'));
});

test('an unknown event type filter is ignored', function () {
    Event::factory()->published()->create(['title_en' => 'A meetup']);

    $this->get(route('events.index', ['type' => 'not-a-type']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('events/index')
            ->where('type', null)
            ->has('upcoming', 1));
});

test('the shared inertia props expose the app version', function () {
    config(['app.version' => '1.2.3']);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('version', '1.2.3'));
});

test('an event accepting proposals advertises its open call', function () {
    $event = Event::factory()->acceptingProposals()->create();

    $this->get(route('events.show', $event))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('event.cfp.enabled', true)
            ->where('event.cfp.accepting', true)
            ->where('event.cfp.pending', false)
            ->whereNot('event.cfp.closes_at', null));
});

test('an event whose call has not opened yet is marked pending', function () {
    $event = Event::factory()->published()->create([
        'cfp_enabled' => true,
        'cfp_opens_at' => now()->addWeek(),
    ]);

    $this->get(route('events.show', $event))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('event.cfp.accepting', false)
            ->where('event.cfp.pending', true)
            ->whereNot('event.cfp.opens_at', null));
});

test('an event with a closed call is neither accepting nor pending', function () {
    $event = Event::factory()->cfpClosed()->create();

    $this->get(route('events.show', $event))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('event.cfp.enabled', true)
            ->where('event.cfp.accepting', false)
            ->where('event.cfp.pending', false));
});

test('an event without a call exposes a disabled cfp block', function () {
    $event = Event::factory()->published()->create();

    $this->get(route('events.show', $event))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('event.cfp.enabled', false)
            ->where('event.cfp.opens_at', null)
            ->where('event.cfp.closes_at', null));
});
