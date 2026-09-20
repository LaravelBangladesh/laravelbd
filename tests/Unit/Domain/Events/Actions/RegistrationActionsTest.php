<?php

use App\Domain\Events\Actions\CancelRegistration;
use App\Domain\Events\Actions\CancelRegistrationForAttendee;
use App\Domain\Events\Actions\RegisterForEvent;
use App\Domain\Events\Enums\RegistrationStatus;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\EventRegistration;
use App\Domain\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

test('registers a member and is idempotent', function () {
    $event = Event::factory()->published()->create();
    $user = User::factory()->withCompleteProfile()->create();
    $register = app(RegisterForEvent::class);

    $first = $register($event, $user);
    $second = $register($event, $user);

    expect($first->is($second))->toBeTrue()
        ->and($first->status)->toBe(RegistrationStatus::Registered)
        ->and(EventRegistration::query()->where('event_id', $event->id)->count())->toBe(1);
});

test('waitlists when the event is full and cancels an active signup', function () {
    $event = Event::factory()->published()->create(['capacity' => 1]);
    EventRegistration::factory()->create([
        'event_id' => $event->id,
        'status' => RegistrationStatus::Registered,
    ]);
    $user = User::factory()->withCompleteProfile()->create();

    $registration = app(RegisterForEvent::class)($event, $user);
    app(CancelRegistration::class)($event, $user);

    expect($registration->status)->toBe(RegistrationStatus::Waitlisted)
        ->and($registration->fresh()?->status)->toBe(RegistrationStatus::Cancelled);
});

test('rejects registration for a draft event', function () {
    $event = Event::factory()->create();
    $user = User::factory()->create();

    app(RegisterForEvent::class)($event, $user);
})->throws(ValidationException::class);

test('rejects registration for a past event', function () {
    $event = Event::factory()->published()->past()->create();
    $user = User::factory()->create();

    app(RegisterForEvent::class)($event, $user);
})->throws(ValidationException::class);

test('reopens a cancelled signup', function () {
    $event = Event::factory()->published()->create();
    $user = User::factory()->withCompleteProfile()->create();

    app(RegisterForEvent::class)($event, $user);
    app(CancelRegistration::class)($event, $user);
    $again = app(RegisterForEvent::class)($event, $user);

    expect($again->status)->toBe(RegistrationStatus::Registered)
        ->and(EventRegistration::query()->where('event_id', $event->id)->count())->toBe(1);
});

test('cancels a registration on behalf of an attendee', function () {
    $registration = EventRegistration::factory()->create([
        'status' => RegistrationStatus::Registered,
    ]);

    app(CancelRegistrationForAttendee::class)($registration);

    expect($registration->fresh()?->status)->toBe(RegistrationStatus::Cancelled);
});

test('rejects registration when the profile is incomplete', function () {
    $event = Event::factory()->published()->create();
    $user = User::factory()->create();

    expect(fn () => app(RegisterForEvent::class)($event, $user))
        ->toThrow(ValidationException::class, __('profile.incomplete'));

    expect(EventRegistration::query()->count())->toBe(0);
});
