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
use App\Domain\Events\Models\Speaker;
use App\Domain\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('detail merges session speakers onto the public roster', function () {
    $event = Event::factory()->published()->create();
    $session = EventSession::factory()->create(['event_id' => $event->id]);
    $speaker = Speaker::factory()->create(['name' => 'Ada Lovelace']);

    $session->speakers()->attach($speaker, ['role' => 'speaker']);
    $event->load(['speakers', 'sessions.speakers', 'media', 'registrations']);

    $detail = EventPresenter::detail($event, null);

    expect($detail['speakers'])->toHaveCount(1)
        ->and($detail['speakers'][0]['name'])->toBe('Ada Lovelace')
        ->and($detail['sessions'][0]['speakers'][0]['name'])->toBe('Ada Lovelace')
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

test('admin payload hides cancelled attendees', function () {
    $event = Event::factory()->published()->create();
    $user = User::factory()->create(['name' => 'Active Member']);

    EventRegistration::factory()->create([
        'event_id' => $event->id,
        'user_id' => $user->id,
        'status' => RegistrationStatus::Registered,
    ]);
    EventRegistration::factory()->cancelled()->create(['event_id' => $event->id]);

    $event->load(['speakers', 'sessions.speakers', 'media', 'registrations.user']);

    $admin = EventPresenter::admin($event);

    expect($admin['attendees'])->toHaveCount(1)
        ->and($admin['attendees'][0]['name'])->toBe('Active Member');
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
    $speaker = Speaker::factory()->create(['photo_path' => 'speakers/ada.jpg']);
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
