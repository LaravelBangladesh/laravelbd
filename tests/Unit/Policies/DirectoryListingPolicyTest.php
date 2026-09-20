<?php

use App\Domain\Directory\Models\DirectoryListing;
use App\Domain\Directory\Policies\DirectoryListingPolicy;
use App\Domain\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('anyone can view published listings and staff can view drafts', function () {
    $policy = new DirectoryListingPolicy;
    $published = DirectoryListing::factory()->published()->create();
    $draft = DirectoryListing::factory()->create();
    $owner = User::factory()->create();
    $ownedDraft = DirectoryListing::factory()->create(['user_id' => $owner->id]);
    $member = User::factory()->create();
    $moderator = User::factory()->moderator()->create();

    expect($policy->view(null, $published))->toBeTrue()
        ->and($policy->view($member, $draft))->toBeFalse()
        ->and($policy->view($owner, $ownedDraft))->toBeTrue()
        ->and($policy->view($moderator, $draft))->toBeTrue()
        ->and($policy->create($member))->toBeTrue()
        ->and($policy->create($owner))->toBeFalse()
        ->and($policy->update($owner, $ownedDraft))->toBeTrue()
        ->and($policy->update($member, $ownedDraft))->toBeFalse()
        ->and($policy->manage($member))->toBeFalse()
        ->and($policy->manage($moderator))->toBeTrue();
});
