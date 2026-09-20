<?php

namespace App\Domain\Directory\Actions;

use App\Domain\Directory\Data\DirectoryListingData;
use App\Domain\Directory\Models\DirectoryListing;
use App\Domain\Identity\Models\User;
use App\Domain\Shared\Data\UploadedImage;

final class UpdateOwnDirectoryProfile
{
    public function __construct(private readonly UpdateDirectoryListing $update) {}

    public function __invoke(User $user, DirectoryListing $listing, DirectoryListingData $data, ?UploadedImage $photo): DirectoryListing
    {
        $listing = ($this->update)($listing, $data, $photo);

        $user->update(['name' => $data->name]);

        return $listing;
    }
}
