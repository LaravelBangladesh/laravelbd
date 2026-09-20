<?php

use App\Domain\Cfp\Enums\ProposalStatus;
use App\Domain\Cfp\Models\TalkProposal;
use App\Domain\Events\Enums\SessionKind;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\EventSession;
use App\Domain\Identity\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('members cannot review proposals', function () {
    $member = User::factory()->create();

    $this->actingAs($member)
        ->get(route('admin.proposals.index'))
        ->assertForbidden();
});

test('staff can accept a proposal', function () {
    $event = Event::factory()->published()->create(['title_en' => 'April meetup']);
    $proposal = TalkProposal::factory()->for($event)->create(['title_en' => 'HTTP Kernel']);
    $moderator = User::factory()->moderator()->create();

    $this->actingAs($moderator)
        ->get(route('admin.proposals.show', $proposal))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/proposals/show')
            ->where('proposal.id', $proposal->id)
            ->has('events', 1));

    $this->actingAs($moderator)
        ->patch(route('admin.proposals.update', $proposal), [
            'status' => 'accepted',
            'event_id' => $event->id,
            'notes' => 'Great fit for April.',
        ])
        ->assertRedirect(route('admin.proposals.index'));

    $proposal->refresh();
    $session = EventSession::query()->where('event_id', $event->id)->with('speakers')->first();

    expect($proposal->status)->toBe(ProposalStatus::Accepted)
        ->and($proposal->notes)->toBe('Great fit for April.')
        ->and($proposal->event_id)->toBe($event->id)
        ->and($proposal->event_session_id)->toBe($session?->id)
        ->and($session?->title_en)->toBe('HTTP Kernel')
        ->and($session?->kind)->toBe(SessionKind::Talk)
        ->and($session?->speakers)->toHaveCount(1);
});

test('staff cannot accept a proposal without an event', function () {
    $proposal = TalkProposal::factory()->create();
    $moderator = User::factory()->moderator()->create();

    $this->actingAs($moderator)
        ->patch(route('admin.proposals.update', $proposal), [
            'status' => 'accepted',
        ])
        ->assertSessionHasErrors('event_id');

    expect($proposal->fresh()?->status)->toBe(ProposalStatus::Submitted)
        ->and(EventSession::query()->count())->toBe(0);
});

test('the proposals list can be filtered by event', function () {
    $wanted = Event::factory()->acceptingProposals()->create(['title_en' => 'Wanted event']);
    $other = Event::factory()->acceptingProposals()->create(['title_en' => 'Other event']);
    TalkProposal::factory()->for($wanted)->create(['title_en' => 'Kept proposal']);
    TalkProposal::factory()->for($other)->create(['title_en' => 'Filtered out']);
    $moderator = User::factory()->moderator()->create();

    $this->actingAs($moderator)
        ->get(route('admin.proposals.index', ['event' => $wanted->id]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/proposals/index')
            ->where('event', $wanted->id)
            ->has('proposals', 1)
            ->where('proposals.0.title', 'Kept proposal')
            ->where('proposals.0.event.title', 'Wanted event')
            ->has('events', 2));
});

test('an unknown event filter is ignored', function () {
    TalkProposal::factory()->create();
    $moderator = User::factory()->moderator()->create();

    $this->actingAs($moderator)
        ->get(route('admin.proposals.index', ['event' => 'not-an-event']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/proposals/index')
            ->where('event', null)
            ->has('proposals', 1));
});

test('the event filter lists events with proposals or an open cfp', function () {
    Event::factory()->acceptingProposals()->create(['title_en' => 'Open cfp']);
    $withProposals = Event::factory()->published()->create(['title_en' => 'Has proposals']);
    TalkProposal::factory()->for($withProposals)->create();
    Event::factory()->published()->create(['title_en' => 'Neither']);
    $moderator = User::factory()->moderator()->create();

    $this->actingAs($moderator)
        ->get(route('admin.proposals.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('events', 2));
});
