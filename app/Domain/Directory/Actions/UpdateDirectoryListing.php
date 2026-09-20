<?php

namespace App\Domain\Directory\Actions;

use App\Domain\Directory\Data\DirectoryListingData;
use App\Domain\Directory\Models\DirectoryListing;
use App\Domain\Shared\Contracts\ImageStorage;
use App\Domain\Shared\Data\UploadedImage;
use App\Domain\Shared\UniqueSlug;

final class UpdateDirectoryListing
{
    public function __construct(private readonly ImageStorage $images) {}

    public function __invoke(DirectoryListing $listing, DirectoryListingData $data, ?UploadedImage $photo): DirectoryListing
    {
        $listing->fill($data->attributes($listing->published_at));
        $listing->slug = UniqueSlug::make($data->name, 'directory_listings', $listing->id);

        if ($photo !== null) {
            $this->images->delete($listing->photo_path);
            $listing->photo_path = $this->images->put($photo->contents, $photo->name, 'directory');
        }

        $listing->save();

        return $listing;
    }
}
