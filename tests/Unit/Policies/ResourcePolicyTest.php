<?php

use App\Domain\Content\Models\Resource;
use App\Domain\Content\Policies\ResourcePolicy;
use App\Domain\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('anyone can view published resources and staff can view drafts', function () {
    $policy = new ResourcePolicy;
    $published = Resource::factory()->published()->create();
    $draft = Resource::factory()->create();
    $member = User::factory()->create();
    $moderator = User::factory()->moderator()->create();

    expect($policy->view(null, $published))->toBeTrue()
        ->and($policy->view($member, $draft))->toBeFalse()
        ->and($policy->view($moderator, $draft))->toBeTrue()
        ->and($policy->manage($member))->toBeFalse()
        ->and($policy->manage($moderator))->toBeTrue();
});
