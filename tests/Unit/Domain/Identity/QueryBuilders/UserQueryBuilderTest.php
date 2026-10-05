<?php

use App\Domain\Cfp\Enums\ProposalStatus;
use App\Domain\Cfp\Models\TalkProposal;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\EventSession;
use App\Domain\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('directory scopes split users by their directory status', function () {
    $hidden = User::factory()->create();
    $pending = User::factory()->pendingInDirectory()->create();
    $listed = User::factory()->listedInDirectory()->create();

    expect(User::query()->listedInDirectory()->pluck('id')->all())->toBe([$listed->id])
        ->and(User::query()->pendingDirectory()->pluck('id')->all())->toBe([$pending->id])
        ->and(User::query()->inDirectory()->pluck('id')->all())
        ->toContain($pending->id, $listed->id)
        ->not->toContain($hidden->id);
});

test('alphabetical orders users by name ascending', function () {
    User::factory()->create(['name' => 'Zara']);
    User::factory()->create(['name' => 'Anik']);

    expect(User::query()->alphabetical()->pluck('name')->all())->toBe(['Anik', 'Zara']);
});

test('speakers are users with an accepted proposal or a roster place, staff included', function () {
    $accepted = User::factory()->create();
    TalkProposal::factory()->create(['user_id' => $accepted->id, 'status' => ProposalStatus::Accepted]);
    $submitted = User::factory()->create();
    TalkProposal::factory()->create(['user_id' => $submitted->id, 'status' => ProposalStatus::Submitted]);
    $host = User::factory()->admin()->create();
    Event::factory()->create()->speakers()->attach($host, ['role' => 'host']);
    $moderator = User::factory()->create();
    EventSession::factory()->create()->speakers()->attach($moderator, ['role' => 'moderator']);
    User::factory()->create();

    expect(User::query()->speakers()->pluck('id')->all())
        ->toEqualCanonicalizing([$accepted->id, $host->id, $moderator->id]);
});
