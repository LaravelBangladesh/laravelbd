<?php

use App\Domain\Cfp\Models\TalkProposal;
use App\Domain\Events\Models\Event;
use App\Domain\Identity\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('the old global cfp page is gone', function () {
    $this->get('/cfp')->assertNotFound();
});

test('members see the proposal form for an event accepting proposals', function () {
    $event = Event::factory()->acceptingProposals()->create(['title_en' => 'April meetup']);
    $member = User::factory()->withCompleteProfile()->create();

    $this->actingAs($member)
        ->get(route('events.cfp.create', $event))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('events/cfp')
            ->where('event.slug', $event->slug)
            ->where('event.title', 'April meetup')
            ->has('kinds', 3));
});

test('guests are sent to log in before proposing', function () {
    $event = Event::factory()->acceptingProposals()->create();

    $this->get(route('events.cfp.create', $event))->assertRedirect(route('login'));
});

test('members cannot open the form for an event that is not accepting proposals', function () {
    $event = Event::factory()->cfpClosed()->create();
    $member = User::factory()->withCompleteProfile()->create();

    $this->actingAs($member)
        ->get(route('events.cfp.create', $event))
        ->assertForbidden();
});

test('members can submit a proposal while the window is open', function () {
    $event = Event::factory()->acceptingProposals()->create();
    $member = User::factory()->withCompleteProfile()->create();

    $this->actingAs($member)
        ->post(route('events.cfp.store', $event), [
            'title_en' => 'Testing HTTP',
            'title_bn' => '',
            'abstract_en' => 'How we test Laravel apps.',
            'abstract_bn' => '',
            'kind' => 'talk',
        ])
        ->assertRedirect(route('account.edit'));

    $proposal = TalkProposal::query()->where('title_en', 'Testing HTTP')->first();

    expect($proposal)->not->toBeNull()
        ->and($proposal?->user_id)->toBe($member->id)
        ->and($proposal?->event_id)->toBe($event->id)
        ->and($proposal?->title_bn)->toBeNull()
        ->and($proposal?->abstract_bn)->toBeNull()
        ->and($proposal?->status->value)->toBe('submitted');
});

test('members cannot submit once the window has closed', function () {
    $event = Event::factory()->cfpClosed()->create();
    $member = User::factory()->withCompleteProfile()->create();

    $this->actingAs($member)
        ->post(route('events.cfp.store', $event), [
            'title_en' => 'Too late',
            'abstract_en' => 'The window closed.',
            'kind' => 'talk',
        ])
        ->assertForbidden();

    expect(TalkProposal::query()->count())->toBe(0);
});

test('guests cannot submit a proposal', function () {
    $event = Event::factory()->acceptingProposals()->create();

    $this->post(route('events.cfp.store', $event), [
        'title_en' => 'Testing HTTP',
        'abstract_en' => 'How we test Laravel apps.',
        'kind' => 'talk',
    ])->assertRedirect(route('login'));
});

test('a proposal needs a title an abstract and a kind', function () {
    $event = Event::factory()->acceptingProposals()->create();
    $member = User::factory()->withCompleteProfile()->create();

    $this->actingAs($member)
        ->post(route('events.cfp.store', $event), [])
        ->assertSessionHasErrors(['title_en', 'abstract_en', 'kind']);

    expect(TalkProposal::query()->count())->toBe(0);
});
