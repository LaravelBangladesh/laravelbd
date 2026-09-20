<?php

use App\Domain\Events\Models\Event;
use App\Domain\Events\Policies\EventPolicy;
use App\Domain\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('anyone can view a published event and staff can view drafts', function () {
    $policy = new EventPolicy;
    $published = Event::factory()->published()->create();
    $draft = Event::factory()->create();
    $member = User::factory()->create();
    $moderator = User::factory()->moderator()->create();

    expect($policy->view(null, $published))->toBeTrue()
        ->and($policy->view($member, $published))->toBeTrue()
        ->and($policy->view($member, $draft))->toBeFalse()
        ->and($policy->view($moderator, $draft))->toBeTrue();
});

test('only staff can manage events and only upcoming published events accept rsvps', function () {
    $policy = new EventPolicy;
    $event = Event::factory()->published()->create();
    $past = Event::factory()->published()->past()->create();
    $member = User::factory()->withCompleteProfile()->create();
    $moderator = User::factory()->moderator()->create();

    expect($policy->create($member))->toBeFalse()
        ->and($policy->update($member, $event))->toBeFalse()
        ->and($policy->delete($member, $event))->toBeFalse()
        ->and($policy->manage($member))->toBeFalse()
        ->and($policy->create($moderator))->toBeTrue()
        ->and($policy->update($moderator, $event))->toBeTrue()
        ->and($policy->manage($moderator))->toBeTrue()
        ->and($policy->rsvp($member, $event))->toBeTrue()
        ->and($policy->rsvp($member, $past))->toBeFalse()
        ->and($policy->cancelRsvp($member, $event))->toBeTrue()
        ->and($policy->cancelRsvp($member, $past))->toBeFalse()
        ->and($policy->viewAny(null))->toBeTrue();
});

test('an incomplete profile may not rsvp but may still cancel', function () {
    $policy = new EventPolicy;
    $event = Event::factory()->published()->create();
    $member = User::factory()->create();

    expect($policy->rsvp($member, $event))->toBeFalse()
        ->and($policy->cancelRsvp($member, $event))->toBeTrue();
});

test('registration can be closed for an upcoming published event', function () {
    $policy = new EventPolicy;
    $closed = Event::factory()->published()->registrationClosed()->create();
    $member = User::factory()->withCompleteProfile()->create();

    expect($policy->rsvp($member, $closed))->toBeFalse()
        ->and($policy->cancelRsvp($member, $closed))->toBeTrue();
});
