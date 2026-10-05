<?php

use App\Domain\Directory\Models\Company;
use App\Domain\Directory\Policies\CompanyPolicy;
use App\Domain\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('anyone can view published companies and only staff manage them', function () {
    $policy = new CompanyPolicy;
    $published = Company::factory()->published()->create();
    $draft = Company::factory()->create();
    $member = User::factory()->create();
    $moderator = User::factory()->moderator()->create();

    expect($policy->viewAny(null))->toBeTrue()
        ->and($policy->view(null, $published))->toBeTrue()
        ->and($policy->view($member, $draft))->toBeFalse()
        ->and($policy->view($moderator, $draft))->toBeTrue()
        ->and($policy->create($member))->toBeFalse()
        ->and($policy->create($moderator))->toBeTrue()
        ->and($policy->update($member, $draft))->toBeFalse()
        ->and($policy->update($moderator, $draft))->toBeTrue()
        ->and($policy->delete($member, $draft))->toBeFalse()
        ->and($policy->delete($moderator, $draft))->toBeTrue()
        ->and($policy->manage($member))->toBeFalse()
        ->and($policy->manage($moderator))->toBeTrue();
});
