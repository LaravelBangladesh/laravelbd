<?php

use App\Domain\Cfp\Models\TalkProposal;
use App\Domain\Directory\Models\DirectoryListing;
use App\Domain\Events\Enums\RegistrationStatus;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\EventRegistration;
use App\Domain\Identity\Enums\UserRole;
use App\Domain\Identity\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests cannot visit the admin area', function () {
    $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
});

test('members cannot visit the admin area', function () {
    $member = User::factory()->create();

    $this->actingAs($member)
        ->get(route('admin.dashboard'))
        ->assertForbidden();
});

test('staff can view the dashboard', function () {
    $moderator = User::factory()->moderator()->create();

    $this->actingAs($moderator)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/dashboard')
            ->where('stats.staff', 1)
            ->where('stats.members', 0));
});

test('moderators can view users but cannot change roles', function () {
    $moderator = User::factory()->moderator()->create();
    $member = User::factory()->create();

    $this->actingAs($moderator)
        ->get(route('admin.users.index'))
        ->assertOk();

    $this->actingAs($moderator)
        ->patch(route('admin.users.role', $member), [
            'role' => UserRole::Moderator->value,
        ])
        ->assertForbidden();
});

test('admins can assign roles', function () {
    $admin = User::factory()->admin()->create();
    $member = User::factory()->create();

    $this->actingAs($admin)
        ->patch(route('admin.users.role', $member), [
            'role' => UserRole::Moderator->value,
        ])
        ->assertRedirect();

    expect($member->fresh()?->role)->toBe(UserRole::Moderator);
});

test('the dashboard reports pending work and the next event', function () {
    $moderator = User::factory()->moderator()->create();

    $event = Event::factory()->published()->create([
        'title_en' => 'Next up',
        'capacity' => 50,
        'starts_at' => now()->addDays(3),
        'ends_at' => now()->addDays(3)->addHours(3),
    ]);
    Event::factory()->published()->create([
        'starts_at' => now()->addDays(40),
        'ends_at' => now()->addDays(40)->addHours(3),
    ]);
    EventRegistration::factory()->create([
        'event_id' => $event->id,
        'status' => RegistrationStatus::Registered,
    ]);

    TalkProposal::factory()->create(['title_en' => 'A pending talk']);
    TalkProposal::factory()->accepted()->create();
    DirectoryListing::factory()->create();
    DirectoryListing::factory()->published()->create();

    $this->actingAs($moderator)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/dashboard')
            ->where('stats.upcoming_events', 2)
            ->where('stats.pending_proposals', 1)
            ->where('stats.draft_listings', 1)
            ->where('stats.next_event_registrations', 1)
            ->where('nextEvent.title', 'Next up')
            ->where('nextEvent.capacity', 50)
            ->where('nextEvent.registered_count', 1)
            ->has('recentProposals', 2));
});

test('the dashboard handles having no events or proposals', function () {
    $moderator = User::factory()->moderator()->create();

    $this->actingAs($moderator)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/dashboard')
            ->where('stats.upcoming_events', 0)
            ->where('nextEvent', null)
            ->has('recentProposals', 0));
});
