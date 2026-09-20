<?php

use App\Domain\Directory\Actions\CreateOwnDirectoryProfile;
use App\Domain\Directory\Actions\UpdateOwnDirectoryProfile;
use App\Domain\Directory\Data\DirectoryListingData;
use App\Domain\Directory\Enums\DirectoryStatus;
use App\Domain\Directory\Models\DirectoryListing;
use App\Domain\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('create own profile owns the listing and syncs the user name', function () {
    $user = User::factory()->create(['name' => 'Old Name']);

    $listing = app(CreateOwnDirectoryProfile::class)(
        $user,
        DirectoryListingData::fromValidated(['name' => 'New Name']),
        null,
    );

    expect($listing->user_id)->toBe($user->id)
        ->and($listing->created_by)->toBe($user->id)
        ->and($listing->status)->toBe(DirectoryStatus::Draft)
        ->and($user->fresh()?->name)->toBe('New Name');
});

test('update own profile rewrites the listing and syncs the user name', function () {
    $user = User::factory()->create(['name' => 'Old Name']);
    $listing = DirectoryListing::factory()->create([
        'user_id' => $user->id,
        'status' => DirectoryStatus::Published,
        'published_at' => now()->subWeek(),
    ]);

    $updated = app(UpdateOwnDirectoryProfile::class)(
        $user,
        $listing,
        DirectoryListingData::fromValidated([
            'name' => 'New Name',
            'status' => DirectoryStatus::Published->value,
        ]),
        null,
    );

    expect($updated->name)->toBe('New Name')
        ->and($updated->isPublished())->toBeTrue()
        ->and($user->fresh()?->name)->toBe('New Name');
});
