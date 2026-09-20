<?php

use App\Domain\Directory\Actions\CreateDirectoryListing;
use App\Domain\Directory\Actions\DeleteDirectoryListing;
use App\Domain\Directory\Actions\UpdateDirectoryListing;
use App\Domain\Directory\Data\DirectoryListingData;
use App\Domain\Directory\Enums\DirectoryKind;
use App\Domain\Directory\Enums\DirectoryStatus;
use App\Domain\Directory\Models\DirectoryListing;
use App\Domain\Identity\Models\User;
use App\Domain\Shared\Contracts\ImageStorage;
use App\Domain\Shared\Data\UploadedImage;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function listingData(array $overrides = []): DirectoryListingData
{
    return DirectoryListingData::fromValidated([
        'name' => 'Anik Rahman',
        ...$overrides,
    ]);
}

test('create directory listing stores a slugged draft', function () {
    $user = User::factory()->create();

    $listing = app(CreateDirectoryListing::class)(listingData(), null, $user->id);

    expect($listing->slug)->toBe('anik-rahman')
        ->and($listing->created_by)->toBe($user->id)
        ->and($listing->user_id)->toBeNull()
        ->and($listing->kind)->toBe(DirectoryKind::Person)
        ->and($listing->status)->toBe(DirectoryStatus::Draft)
        ->and($listing->photo_path)->toBeNull();
});

test('create directory listing stores an owner and a photo', function () {
    $user = User::factory()->create();

    $images = Mockery::mock(ImageStorage::class);
    $images->shouldReceive('put')->once()->with('bytes', 'me.png', 'directory')->andReturn('directory/me.png');
    app()->instance(ImageStorage::class, $images);

    $listing = app(CreateDirectoryListing::class)(
        listingData(['status' => DirectoryStatus::Published->value]),
        new UploadedImage('bytes', 'me.png'),
        $user->id,
        $user->id,
    );

    expect($listing->photo_path)->toBe('directory/me.png')
        ->and($listing->user_id)->toBe($user->id)
        ->and($listing->published_at)->not->toBeNull();
});

test('create directory listing disambiguates a duplicate slug', function () {
    DirectoryListing::factory()->create(['slug' => 'anik-rahman']);

    $listing = app(CreateDirectoryListing::class)(listingData(), null, null);

    expect($listing->slug)->toBe('anik-rahman-2');
});

test('update directory listing rewrites attributes and keeps the published date', function () {
    $published = now()->subWeek();
    $listing = DirectoryListing::factory()->create([
        'status' => DirectoryStatus::Published,
        'published_at' => $published,
    ]);

    $updated = app(UpdateDirectoryListing::class)(
        $listing,
        listingData(['name' => 'Renamed Person', 'status' => DirectoryStatus::Published->value]),
        null,
    );

    expect($updated->name)->toBe('Renamed Person')
        ->and($updated->slug)->toBe('renamed-person')
        ->and($updated->published_at?->toDateTimeString())->toBe($published->toDateTimeString());
});

test('update directory listing replaces an existing photo', function () {
    $listing = DirectoryListing::factory()->create(['photo_path' => 'directory/old.png']);

    $images = Mockery::mock(ImageStorage::class);
    $images->shouldReceive('delete')->once()->with('directory/old.png');
    $images->shouldReceive('put')->once()->andReturn('directory/new.png');
    app()->instance(ImageStorage::class, $images);

    $updated = app(UpdateDirectoryListing::class)(
        $listing,
        listingData(),
        new UploadedImage('bytes', 'new.png'),
    );

    expect($updated->photo_path)->toBe('directory/new.png')
        ->and($updated->published_at)->toBeNull();
});

test('delete directory listing removes the photo and the row', function () {
    $listing = DirectoryListing::factory()->create(['photo_path' => 'directory/old.png']);

    $images = Mockery::mock(ImageStorage::class);
    $images->shouldReceive('delete')->once()->with('directory/old.png');
    app()->instance(ImageStorage::class, $images);

    app(DeleteDirectoryListing::class)($listing);

    expect(DirectoryListing::query()->whereKey($listing->id)->exists())->toBeFalse();
});
