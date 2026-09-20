<?php

use App\Domain\Events\Enums\RegistrationStatus;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\EventRegistration;
use App\Domain\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('upcoming published events report capacity and rsvp eligibility', function () {
    $event = Event::factory()->published()->create(['capacity' => 1]);

    expect($event->isPublished())->toBeTrue()
        ->and($event->isUpcoming())->toBeTrue()
        ->and($event->isFull())->toBeFalse()
        ->and($event->registeredCount())->toBe(0);

    EventRegistration::factory()->create([
        'event_id' => $event->id,
        'status' => RegistrationStatus::Registered,
    ]);

    expect($event->fresh()->isFull())->toBeTrue()
        ->and($event->registeredCount())->toBe(1);
});

test('registration for returns the active signup for a user', function () {
    $event = Event::factory()->published()->create();
    $user = User::factory()->create();
    $other = User::factory()->create();

    EventRegistration::factory()->create([
        'event_id' => $event->id,
        'user_id' => $user->id,
        'status' => RegistrationStatus::Registered,
    ]);

    $event->load('registrations');

    expect($event->registrationFor($user)?->user_id)->toBe($user->id)
        ->and($event->registrationFor($other))->toBeNull()
        ->and($event->registrationFor(null))->toBeNull();
});

test('localized content falls back to english', function () {
    $event = Event::factory()->create([
        'title_en' => 'October Meetup',
        'title_bn' => null,
    ]);

    app()->setLocale('bn');

    expect($event->localized('title'))->toBe('October Meetup');

    $event->title_bn = 'অক্টোবর মিটআপ';

    expect($event->localized('title'))->toBe('অক্টোবর মিটআপ');
});
