<?php

use App\Domain\Identity\Actions\CreateGuestUser;
use App\Domain\Identity\Data\GuestUserData;
use App\Domain\Identity\Enums\DirectoryVisibility;
use App\Domain\Identity\Enums\UserRole;
use App\Domain\Identity\Models\User;
use App\Domain\Shared\Data\UploadedImage;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('creates a hidden, unverified member with the guest profile and photo', function () {
    $user = app(CreateGuestUser::class)(
        new GuestUserData('Grace Hopper', 'Grace@Example.com', 'Rear Admiral', 'Navy'),
        new UploadedImage('binary', 'grace.jpg'),
    );

    expect($user->wasRecentlyCreated)->toBeTrue()
        ->and($user->email)->toBe('grace@example.com')
        ->and($user->role)->toBe(UserRole::Member)
        ->and($user->email_verified_at)->toBeNull()
        ->and($user->fresh()?->directory_status)->toBe(DirectoryVisibility::Hidden)
        ->and($user->slug)->toBe('grace-hopper')
        ->and($user->title)->toBe('Rear Admiral')
        ->and($user->company)->toBe('Navy')
        ->and($user->photo_path)->toStartWith('directory/');
});

test('creates a guest without a photo', function () {
    $user = app(CreateGuestUser::class)(new GuestUserData('Ada Lovelace', 'ada@example.com', null, null));

    expect($user->photo_path)->toBeNull();
});

test('returns the existing user for a known email and leaves their profile alone', function () {
    $existing = User::factory()->create(['email' => 'ada@example.com', 'name' => 'Ada Lovelace', 'title' => 'Mathematician']);

    $user = app(CreateGuestUser::class)(
        new GuestUserData('Someone Else', 'ADA@example.com', 'Other', 'Other Co'),
        new UploadedImage('binary', 'ada.jpg'),
    );

    expect($user->is($existing))->toBeTrue()
        ->and($user->wasRecentlyCreated)->toBeFalse()
        ->and($user->fresh()?->name)->toBe('Ada Lovelace')
        ->and($user->fresh()?->title)->toBe('Mathematician')
        ->and($user->fresh()?->photo_path)->toBeNull()
        ->and(User::query()->count())->toBe(1);
});
