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

test('only staff can list users and add guests', function () {
    $policy = new UserPolicy;
    $member = User::factory()->create();
    $moderator = User::factory()->moderator()->create();

    expect($policy->viewAny($member))->toBeFalse()
        ->and($policy->create($member))->toBeFalse()
        ->and($policy->viewAny($moderator))->toBeTrue()
        ->and($policy->create($moderator))->toBeTrue();
});

test('a deactivated user cannot be edited until reactivated', function () {
    $policy = new UserPolicy;
    $member = User::factory()->create();
    $member->delete();

    expect($policy->update(User::factory()->moderator()->create(), $member))->toBeFalse();
});

test('only admins deactivate, never themselves or another admin', function () {
    $policy = new UserPolicy;
    $admin = User::factory()->admin()->create();
    $member = User::factory()->create();
    $moderator = User::factory()->moderator()->create();

    expect($policy->delete($admin, $member))->toBeTrue()
        ->and($policy->delete($admin, $moderator))->toBeTrue()
        ->and($policy->delete($moderator, $member))->toBeFalse()
        ->and($policy->delete($admin, $admin))->toBeFalse()
        ->and($policy->delete($admin, User::factory()->admin()->create()))->toBeFalse();

    $member->delete();

    expect($policy->delete($admin, $member))->toBeFalse();
});

test('only admins reactivate, and only deactivated users', function () {
    $policy = new UserPolicy;
    $admin = User::factory()->admin()->create();
    $member = User::factory()->create();

    expect($policy->restore($admin, $member))->toBeFalse();

    $member->delete();

    expect($policy->restore($admin, $member))->toBeTrue()
        ->and($policy->restore(User::factory()->moderator()->create(), $member))->toBeFalse();
});
