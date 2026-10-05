<?php

use App\Domain\Identity\Models\User;
use App\Domain\Identity\ProfilePhoto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('members are not staff', function () {
    $member = User::factory()->create();

    expect($member->isStaff())->toBeFalse()
        ->and($member->isAdmin())->toBeFalse()
        ->and($member->isModerator())->toBeFalse();
});

test('moderators are staff but not admins', function () {
    $moderator = User::factory()->moderator()->create();

    expect($moderator->isStaff())->toBeTrue()
        ->and($moderator->isModerator())->toBeTrue()
        ->and($moderator->isAdmin())->toBeFalse();
});

test('admins are staff', function () {
    $admin = User::factory()->admin()->create();

    expect($admin->isStaff())->toBeTrue()
        ->and($admin->isAdmin())->toBeTrue()
        ->and($admin->isModerator())->toBeFalse();
});

test('blank names need a profile', function () {
    $user = User::factory()->make(['name' => '']);

    expect($user->needsProfile())->toBeTrue()
        ->and(User::factory()->make()->needsProfile())->toBeFalse();
});

test('users without a photo use the default profile image', function () {
    $user = User::factory()->create();

    expect($user->photoUrl())->toBe(ProfilePhoto::placeholder());
});

test('users with a photo use that image', function () {
    $user = User::factory()->create(['photo_path' => 'directory/ada.jpg']);

    expect($user->photoUrl())->toBe(Storage::disk('public')->url('directory/ada.jpg'));
});

test('a new user is missing every profile field but the name', function () {
    $user = User::factory()->create();

    expect($user->hasCompleteProfile())->toBeFalse()
        ->and($user->missingProfileFields())->toBe(['photo', 'title', 'company', 'mobile_number']);
});

test('a blank name is reported as missing too', function () {
    $user = User::factory()->create(['name' => '']);

    expect($user->missingProfileFields())->toBe(['name', 'photo', 'title', 'company', 'mobile_number']);
});

test('each unfilled profile field is reported on its own', function (string $column, string $field) {
    $user = User::factory()->withCompleteProfile()->create([$column => null]);

    expect($user->hasCompleteProfile())->toBeFalse()
        ->and($user->missingProfileFields())->toBe([$field]);
})->with([
    ['photo_path', 'photo'],
    ['title', 'title'],
    ['company', 'company'],
    ['mobile_number', 'mobile_number'],
]);

test('a filled name title company photo and mobile number is a complete profile', function () {
    $user = User::factory()->withCompleteProfile()->create();

    expect($user->hasCompleteProfile())->toBeTrue()
        ->and($user->missingProfileFields())->toBe([]);
});

test('the mobile number never serialises with the user', function () {
    $user = User::factory()->withCompleteProfile()->create();

    expect($user->toArray())->not->toHaveKey('mobile_number')
        ->and($user->toJson())->not->toContain((string) $user->mobile_number);
});

test('only a listed user is listed', function () {
    expect(User::factory()->make()->isListed())->toBeFalse()
        ->and(User::factory()->pendingInDirectory()->make()->isListed())->toBeFalse()
        ->and(User::factory()->listedInDirectory()->make()->isListed())->toBeTrue();
});
