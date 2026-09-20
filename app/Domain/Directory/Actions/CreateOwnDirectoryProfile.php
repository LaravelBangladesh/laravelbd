<?php

namespace App\Domain\Directory\Actions;

use App\Domain\Directory\Data\DirectoryListingData;
use App\Domain\Directory\Models\DirectoryListing;
use App\Domain\Identity\Models\User;
use App\Domain\Shared\Data\UploadedImage;

final class CreateOwnDirectoryProfile
{
    public function __construct(private readonly CreateDirectoryListing $create) {}

    public function __invoke(User $user, DirectoryListingData $data, ?UploadedImage $photo): DirectoryListing
    {
        $listing = ($this->create)($data, $photo, $user->id, $user->id);

        $user->update(['name' => $data->name]);

        return $listing;
    }
}
