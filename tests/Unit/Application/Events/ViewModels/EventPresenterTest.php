<?php

use App\Application\Events\ViewModels\EventPresenter;
use App\Domain\Events\Enums\EventStatus;
use App\Domain\Events\Enums\EventType;
use App\Domain\Events\Enums\MediaKind;
use App\Domain\Events\Enums\RegistrationStatus;
use App\Domain\Events\Enums\SessionKind;
use App\Domain\Events\Enums\SpeakerRole;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\EventMedium;
use App\Domain\Events\Models\EventRegistration;
use App\Domain\Events\Models\EventSession;
use App\Domain\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('detail merges session speakers onto the public roster', function () {
    $event = Event::factory()->published()->create();
    $session = EventSession::factory()->create(['event_id' => $event->id]);
    $speaker = User::factory()->create(['name' => 'Ada Lovelace']);

    $session->speakers()->attach($speaker, ['role' => 'speaker']);
    $event->load(['speakers', 'sessions.speakers', 'media', 'registrations']);

    $detail = EventPresenter::detail($event, null);

    expect($detail['speakers'])->toHaveCount(1)
        ->and($detail['speakers'][0]['name'])->toBe('Ada Lovelace')
        ->and($detail['sessions'][0]['speakers'][0]['name'])->toBe('Ada Lovelace')
        ->and($detail['speakers'][0]['directory_url'])->toBeNull()
        ->and($detail['speakers'][0])->not->toHaveKeys(['email', 'mobile_number'])
        ->and($detail['can_rsvp'])->toBeTrue();
});

test('card and form expose localized public and admin fields', function () {
    $event = Event::factory()->published()->create(['title_en' => 'October Meetup']);

    $card = EventPresenter::card($event);
    $form = EventPresenter::form($event);

    expect($card['title'])->toBe('October Meetup')
        ->and($card['is_upcoming'])->toBeTrue()
        ->and($form['title_en'])->toBe('October Meetup')
        ->and($form['id'])->toBe($event->id);
});

test('an attendee carries the staff-only contact details', function () {
    $user = User::factory()->create(['name' => 'Active Member', 'mobile_number' => '+8801712345678']);
    $registration = EventRegistration::factory()->create([
        'user_id' => $user->id,
        'status' => RegistrationStatus::Registered,
        'registered_at' => '2026-10-01 12:00:00',
    ]);

    $attendee = EventPresenter::attendee($registration);

    expect($attendee['name'])->toBe('Active Member')
        ->and($attendee['mobile_number'])->toBe('+8801712345678')
        ->and($attendee['registered_at'])->toBe('01 Oct 2026, 18:00')
        ->and($attendee['answers'])->toBe([]);
});

test('option lists include every case', function () {
    expect(EventPresenter::types())->toHaveCount(count(EventType::cases()))
        ->and(EventPresenter::statuses())->toHaveCount(count(EventStatus::cases()))
        ->and(EventPresenter::sessionKinds())->toHaveCount(count(SessionKind::cases()))
        ->and(EventPresenter::speakerRoles())->toHaveCount(count(SpeakerRole::cases()));
});

test('detail exposes a date and a compact time range in Dhaka time', function () {
    $event = Event::factory()->published()->create([
        'starts_at' => '2026-05-20 09:00:00',
        'ends_at' => '2026-05-20 13:00:00',
    ]);
    $event->load(['speakers', 'sessions.speakers', 'media', 'registrations']);

    $detail = EventPresenter::detail($event, null);

    expect($detail['date'])->toBe('20 May 2026')
        ->and($detail['time_range'])->toBe('15:00 – 19:00');
});

test('detail time range keeps the end date for multi-day events', function () {
    $event = Event::factory()->published()->create([
        'starts_at' => '2026-05-20 09:00:00',
        'ends_at' => '2026-05-21 11:00:00',
    ]);
    $event->load(['speakers', 'sessions.speakers', 'media', 'registrations']);

    $detail = EventPresenter::detail($event, null);

    expect($detail['time_range'])->toBe('15:00 – 21 May 2026, 17:00');
});

test('media, covers and speaker photos resolve through the image storage', function () {
    Storage::fake('public');

    $event = Event::factory()->published()->create(['cover_path' => 'events/covers/hall.jpg']);
    $speaker = User::factory()->create(['photo_path' => 'speakers/ada.jpg']);
    $event->speakers()->attach($speaker, ['role' => 'speaker']);

    EventMedium::query()->create([
        'event_id' => $event->id,
        'kind' => MediaKind::Photo,
        'path' => 'events/hall.jpg',
    ]);
    EventMedium::query()->create([
        'event_id' => $event->id,
        'kind' => MediaKind::Video,
        'embed_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
    ]);

    $event->load(['speakers', 'sessions.speakers', 'media', 'registrations']);

    $detail = EventPresenter::detail($event, null);
    $admin = EventPresenter::admin($event);

    expect($detail['cover_url'])->toBe(Storage::disk('public')->url('events/covers/hall.jpg'))
        ->and($detail['photos'][0]['url'])->toBe(Storage::disk('public')->url('events/hall.jpg'))
        ->and($detail['speakers'][0]['photo_url'])->toBe(Storage::disk('public')->url('speakers/ada.jpg'))
        ->and($admin['media'])->toHaveCount(2)
        ->and(collect($admin['media'])->firstWhere('kind', 'video')['url'])
        ->toBe('https://www.youtube.com/watch?v=dQw4w9WgXcQ');
});
