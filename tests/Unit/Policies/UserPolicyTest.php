<?php

use App\Domain\Identity\Models\User;
use App\Domain\Identity\Policies\UserPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('listed profiles are public and others only reach their owner and staff', function () {
    $policy = new UserPolicy;
    $listed = User::factory()->listedInDirectory()->create();
    $pending = User::factory()->pendingInDirectory()->create();
    $member = User::factory()->create();
    $moderator = User::factory()->moderator()->create();

    expect($policy->view(null, $listed))->toBeTrue()
        ->and($policy->view(null, $pending))->toBeFalse()
        ->and($policy->view($member, $pending))->toBeFalse()
        ->and($policy->view($pending, $pending))->toBeTrue()
        ->and($policy->view($moderator, $pending))->toBeTrue();
});

test('only staff can edit another member profile', function () {
    $policy = new UserPolicy;
    $member = User::factory()->create();

    expect($policy->update(User::factory()->create(), $member))->toBeFalse()
        ->and($policy->update($member, $member))->toBeFalse()
        ->and($policy->update(User::factory()->moderator()->create(), $member))->toBeTrue();
});
