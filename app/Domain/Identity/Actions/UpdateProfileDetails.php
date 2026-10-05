<?php

namespace App\Domain\Identity\Actions;

use App\Domain\Identity\Data\ProfileDetailsData;
use App\Domain\Identity\Models\User;
use App\Domain\Shared\Contracts\ImageStorage;
use App\Domain\Shared\Data\UploadedImage;

/**
 * Saves the profile a person shares across the site. The directory status is
 * left alone, so editing a listed profile keeps it listed.
 */
final class UpdateProfileDetails
{
    public function __construct(private readonly ImageStorage $images) {}

    public function __invoke(User $user, ProfileDetailsData $data, ?UploadedImage $photo): User
    {
        $user->fill($data->attributes());
        $user->refreshSlug();

        if ($photo !== null) {
            $this->images->delete($user->photo_path);
            $user->photo_path = $this->images->put($photo->contents, $photo->name, 'directory');
        }

        $user->save();

        return $user;
    }
}
