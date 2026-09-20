<?php

namespace App\Domain\Directory\Actions;

use App\Domain\Directory\Data\DirectoryListingData;
use App\Domain\Directory\Models\DirectoryListing;
use App\Domain\Shared\Contracts\ImageStorage;
use App\Domain\Shared\Data\UploadedImage;
use App\Domain\Shared\UniqueSlug;

final class CreateDirectoryListing
{
    public function __construct(private readonly ImageStorage $images) {}

    public function __invoke(DirectoryListingData $data, ?UploadedImage $photo, ?string $createdBy, ?string $userId = null): DirectoryListing
    {
        $listing = new DirectoryListing;
        $listing->fill($data->attributes(null));
        $listing->slug = UniqueSlug::make($data->name, 'directory_listings');
        $listing->created_by = $createdBy;
        $listing->user_id = $userId;

        if ($photo !== null) {
            $listing->photo_path = $this->images->put($photo->contents, $photo->name, 'directory');
        }

        $listing->save();

        return $listing;
    }
}
