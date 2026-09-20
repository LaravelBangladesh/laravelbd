<?php

use App\Domain\Events\Models\Speaker;
use App\Domain\Events\Policies\SpeakerPolicy;
use App\Domain\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('only staff can manage speakers', function () {
    $policy = new SpeakerPolicy;
    $speaker = Speaker::factory()->create();
    $member = User::factory()->create();
    $moderator = User::factory()->moderator()->create();

    expect($policy->viewAny($member))->toBeFalse()
        ->and($policy->create($member))->toBeFalse()
        ->and($policy->update($member, $speaker))->toBeFalse()
        ->and($policy->delete($member, $speaker))->toBeFalse()
        ->and($policy->viewAny($moderator))->toBeTrue()
        ->and($policy->create($moderator))->toBeTrue()
        ->and($policy->update($moderator, $speaker))->toBeTrue()
        ->and($policy->delete($moderator, $speaker))->toBeTrue();
});
