<?php

use App\Domain\Cfp\Enums\ProposalStatus;
use App\Domain\Cfp\Models\TalkProposal;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('accepted returns only accepted proposals', function () {
    $accepted = TalkProposal::factory()->accepted()->create();
    $submitted = TalkProposal::factory()->create(['status' => ProposalStatus::Submitted]);

    expect(TalkProposal::query()->accepted()->pluck('id')->all())
        ->toContain($accepted->id)
        ->not->toContain($submitted->id);
});

test('pending returns only submitted proposals', function () {
    $submitted = TalkProposal::factory()->create(['status' => ProposalStatus::Submitted]);
    $accepted = TalkProposal::factory()->accepted()->create();

    expect(TalkProposal::query()->pending()->pluck('id')->all())
        ->toContain($submitted->id)
        ->not->toContain($accepted->id);
});

test('recent returns the newest proposals up to the limit', function () {
    TalkProposal::factory()->count(3)->create();

    expect(TalkProposal::query()->recent(2)->get())->toHaveCount(2);
});
