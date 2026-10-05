<?php

use App\Domain\Identity\ProfilePhoto;

test('the placeholder is the public profile image', function () {
    expect(ProfilePhoto::placeholder())->toEndWith('/images/profile-placeholder.svg');
});
