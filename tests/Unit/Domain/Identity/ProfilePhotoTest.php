<?php

use App\Domain\Directory\Enums\DirectoryKind;
use App\Domain\Directory\Models\DirectoryListing;
use App\Domain\Identity\ProfilePhoto;

test('the placeholder is the public profile image', function () {
    expect(ProfilePhoto::placeholder())->toEndWith('/images/profile-placeholder.svg');
});

test('person listings without a photo use the placeholder', function () {
    $listing = new DirectoryListing([
        'kind' => DirectoryKind::Person,
        'name' => 'Ada Lovelace',
    ]);

    expect($listing->photoUrl())->toBe(ProfilePhoto::placeholder());
});

test('company listings without a photo stay empty', function () {
    $listing = new DirectoryListing([
        'kind' => DirectoryKind::Company,
        'name' => 'Analytical Engine',
    ]);

    expect($listing->photoUrl())->toBeNull();
});
