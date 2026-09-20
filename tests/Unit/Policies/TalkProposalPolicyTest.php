<?php

use App\Domain\Cfp\Models\TalkProposal;
use App\Domain\Cfp\Policies\TalkProposalPolicy;
use App\Domain\Events\Models\Event;
use App\Domain\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('anyone can view accepted proposals and staff can view submitted ones', function () {
    $policy = new TalkProposalPolicy;
    $accepted = TalkProposal::factory()->accepted()->create();
    $submitted = TalkProposal::factory()->create();
    $member = $submitted->submitter;
    $other = User::factory()->create();
    $moderator = User::factory()->moderator()->create();

    expect($policy->view(null, $accepted))->toBeTrue()
        ->and($policy->view($other, $submitted))->toBeFalse()
        ->and($policy->view($member, $submitted))->toBeTrue()
        ->and($policy->view($moderator, $submitted))->toBeTrue()
        ->and($policy->manage($member))->toBeFalse()
        ->and($policy->manage($moderator))->toBeTrue();
});

test('only staff may create update or delete proposals', function () {
    $policy = new TalkProposalPolicy;
    $proposal = TalkProposal::factory()->create();
    $member = User::factory()->create();
    $moderator = User::factory()->moderator()->create();

    expect($policy->viewAny(null))->toBeTrue()
        ->and($policy->update($member, $proposal))->toBeFalse()
        ->and($policy->update($moderator, $proposal))->toBeTrue()
        ->and($policy->delete($member, $proposal))->toBeFalse()
        ->and($policy->delete($moderator, $proposal))->toBeTrue();
});

test('a member may propose only while an event accepts proposals', function () {
    $policy = new TalkProposalPolicy;
    $member = User::factory()->withCompleteProfile()->create();

    expect($policy->create($member, Event::factory()->acceptingProposals()->create()))->toBeTrue()
        ->and($policy->create($member, Event::factory()->cfpClosed()->create()))->toBeFalse()
        ->and($policy->create($member, Event::factory()->published()->create()))->toBeFalse();
});

test('a member with an incomplete profile may not propose', function () {
    $policy = new TalkProposalPolicy;
    $member = User::factory()->create();

    expect($policy->create($member, Event::factory()->acceptingProposals()->create()))->toBeFalse();
});
