<?php

use App\Domain\Directory\Models\DirectoryListing;
use App\Domain\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('a listing links the staff member who created it and the member it belongs to', function () {
    $author = User::factory()->moderator()->create();
    $owner = User::factory()->create();

    $listing = DirectoryListing::factory()->create([
        'created_by' => $author->id,
        'user_id' => $owner->id,
    ]);

    expect($listing->creator?->id)->toBe($author->id)
        ->and($listing->user?->id)->toBe($owner->id);
});

test('a staff authored listing has no owning member', function () {
    $listing = DirectoryListing::factory()->create(['user_id' => null]);

    expect($listing->user)->toBeNull();
});
