<?php

use App\Application\Events\ViewModels\EventJsonLd;
use App\Domain\Events\Enums\EventStatus;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\EventRegistration;
use App\Domain\Events\Models\Speaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('an in person event describes its venue and schedule', function () {
    $event = Event::factory()->published()->create([
        'slug' => 'laracon-dhaka',
        'title_en' => 'Laracon Dhaka',
        'excerpt_en' => 'A day of Laravel talks.',
        'venue_name' => 'Dhaka University',
        'venue_address' => 'Shahbag, Dhaka',
        'online_url' => null,
        'starts_at' => '2026-03-12 12:00:00',
        'ends_at' => '2026-03-12 15:00:00',
    ]);

    $schema = EventJsonLd::make($event, 'https://laravelbd.test/covers/laracon.png');

    expect($schema['@context'])->toBe('https://schema.org')
        ->and($schema['@type'])->toBe('Event')
        ->and($schema['name'])->toBe('Laracon Dhaka')
        ->and($schema['description'])->toBe('A day of Laravel talks.')
        ->and($schema['url'])->toBe(route('events.show', 'laracon-dhaka'))
        ->and($schema['eventStatus'])->toBe('https://schema.org/EventScheduled')
        ->and($schema['eventAttendanceMode'])->toBe('https://schema.org/OfflineEventAttendanceMode')
        ->and($schema['image'])->toBe('https://laravelbd.test/covers/laracon.png');

    // Dhaka is UTC+6, so the stored UTC noon is published as 18:00 local.
    expect($schema['startDate'])->toBe('2026-03-12T18:00:00+06:00')
        ->and($schema['endDate'])->toBe('2026-03-12T21:00:00+06:00');

    expect($schema['location'])->toBe([
        '@type' => 'Place',
        'name' => 'Dhaka University',
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => 'Shahbag, Dhaka',
            'addressCountry' => 'BD',
        ],
    ]);
});

test('an online only event publishes a virtual location', function () {
    $event = Event::factory()->published()->create([
        'venue_name' => null,
        'venue_address' => null,
        'online_url' => 'https://meet.example.test/laravel',
    ]);

    $schema = EventJsonLd::make($event, null);

    expect($schema['eventAttendanceMode'])->toBe('https://schema.org/OnlineEventAttendanceMode')
        ->and($schema['location'])->toBe([
            '@type' => 'VirtualLocation',
            'url' => 'https://meet.example.test/laravel',
        ])
        ->and($schema)->not->toHaveKey('image');
});

test('an event with a venue and a stream is a mixed attendance event', function () {
    $event = Event::factory()->published()->create([
        'venue_name' => 'Dhaka University',
        'venue_address' => null,
        'online_url' => 'https://meet.example.test/laravel',
    ]);

    $schema = EventJsonLd::make($event, null);

    expect($schema['eventAttendanceMode'])->toBe('https://schema.org/MixedEventAttendanceMode')
        ->and($schema['location'])->toBe([
            ['@type' => 'Place', 'name' => 'Dhaka University'],
            ['@type' => 'VirtualLocation', 'url' => 'https://meet.example.test/laravel'],
        ]);
});

test('a cancelled event says so', function () {
    $event = Event::factory()->published()->create([
        'status' => EventStatus::Cancelled,
    ]);

    expect(EventJsonLd::make($event, null)['eventStatus'])
        ->toBe('https://schema.org/EventCancelled');
});

test('a free event with seats left is offered as in stock', function () {
    $event = Event::factory()->published()->create([
        'slug' => 'open-meetup',
        'capacity' => 10,
    ]);

    expect(EventJsonLd::make($event, null)['offers'])->toBe([
        '@type' => 'Offer',
        'price' => 0,
        'priceCurrency' => 'BDT',
        'availability' => 'https://schema.org/InStock',
        'url' => route('events.show', 'open-meetup'),
    ]);
});

test('a full event is offered as sold out', function () {
    $event = Event::factory()->published()->create(['capacity' => 1]);

    EventRegistration::factory()->create(['event_id' => $event->id]);

    expect(EventJsonLd::make($event->fresh(), null)['offers']['availability'])
        ->toBe('https://schema.org/SoldOut');
});

test('speakers are published as performers', function () {
    $event = Event::factory()->published()->create();
    $speaker = Speaker::factory()->create([
        'name' => 'Ada Lovelace',
        'title' => 'Principal Engineer',
        'photo_path' => null,
    ]);

    $event->speakers()->attach($speaker, ['role' => 'speaker']);

    $schema = EventJsonLd::make($event->fresh()->load('speakers'), null);

    expect($schema['performer'])->toBe([
        [
            '@type' => 'Person',
            'name' => 'Ada Lovelace',
            'jobTitle' => 'Principal Engineer',
        ],
    ]);
});

test('an event without speakers omits the performer list', function () {
    $event = Event::factory()->published()->create();

    expect(EventJsonLd::make($event->load('speakers'), null))
        ->not->toHaveKey('performer');
});
