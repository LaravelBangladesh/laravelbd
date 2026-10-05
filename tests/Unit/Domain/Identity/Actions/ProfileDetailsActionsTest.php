<?php

use App\Domain\Directory\Models\Company;
use App\Domain\Identity\Actions\ChangeDirectoryVisibility;
use App\Domain\Identity\Actions\UpdateProfileDetails;
use App\Domain\Identity\Data\ProfileDetailsData;
use App\Domain\Identity\Enums\DirectoryVisibility;
use App\Domain\Identity\Models\User;
use App\Domain\Shared\Contracts\ImageStorage;
use App\Domain\Shared\Data\UploadedImage;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function profileDetails(array $overrides = []): ProfileDetailsData
{
    return ProfileDetailsData::fromValidated([
        'name' => 'Ada Lovelace',
        'title' => 'Mathematician',
        'company' => 'Analytical Co',
        'mobile_number' => '+8801712345678',
        ...$overrides,
    ]);
}

test('updating the profile fills it and gives it a slug', function () {
    $user = User::factory()->create(['name' => 'Ada Lovelace']);

    $updated = app(UpdateProfileDetails::class)($user, profileDetails(), null);

    expect($updated->fresh()?->title)->toBe('Mathematician')
        ->and($updated->mobile_number)->toBe('+8801712345678')
        ->and($updated->slug)->toBe('ada-lovelace');
});

test('the slug skips one held by a company and follows a rename', function () {
    Company::factory()->create(['slug' => 'ada-lovelace']);
    $user = User::factory()->create(['name' => 'Ada Lovelace']);

    $updated = app(UpdateProfileDetails::class)($user, profileDetails(), null);

    expect($updated->slug)->toBe('ada-lovelace-2');

    $updated = app(UpdateProfileDetails::class)($user, profileDetails(), null);

    expect($updated->slug)->toBe('ada-lovelace-2');

    $renamed = app(UpdateProfileDetails::class)($user, profileDetails(['name' => 'Ada King']), null);

    expect($renamed->slug)->toBe('ada-king');
});

test('editing a listed profile keeps it listed', function () {
    $user = User::factory()->listedInDirectory()->create();

    $updated = app(UpdateProfileDetails::class)($user, profileDetails(), null);

    expect($updated->fresh()?->directory_status)->toBe(DirectoryVisibility::Listed);
});

test('a new photo replaces the old one', function () {
    $user = User::factory()->create(['photo_path' => 'directory/old.png']);

    $images = Mockery::mock(ImageStorage::class);
    $images->shouldReceive('delete')->once()->with('directory/old.png');
    $images->shouldReceive('put')->once()->with('bytes', 'me.png', 'directory')->andReturn('directory/me.png');
    app()->instance(ImageStorage::class, $images);

    $updated = app(UpdateProfileDetails::class)($user, profileDetails(), new UploadedImage('bytes', 'me.png'));

    expect($updated->photo_path)->toBe('directory/me.png');
});

test('listing a profile stamps the date once and hiding clears it', function () {
    $user = User::factory()->create(['name' => 'Ada Lovelace']);
    $change = app(ChangeDirectoryVisibility::class);

    $change($user, DirectoryVisibility::Pending);

    expect($user->fresh()?->directory_status)->toBe(DirectoryVisibility::Pending)
        ->and($user->directory_published_at)->toBeNull()
        ->and($user->slug)->toBe('ada-lovelace');

    $change($user, DirectoryVisibility::Listed);
    $listedAt = $user->directory_published_at;

    $change($user, DirectoryVisibility::Listed);

    expect($listedAt)->not->toBeNull()
        ->and($user->directory_published_at?->toDateTimeString())->toBe($listedAt?->toDateTimeString());

    $change($user, DirectoryVisibility::Hidden);

    expect($user->fresh()?->directory_status)->toBe(DirectoryVisibility::Hidden)
        ->and($user->directory_published_at)->toBeNull();
});
