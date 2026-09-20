<?php

namespace App\Domain\Directory\Actions;

use App\Domain\Directory\Models\DirectoryListing;
use App\Domain\Shared\Contracts\ImageStorage;

final class DeleteDirectoryListing
{
    public function __construct(private readonly ImageStorage $images) {}

    public function __invoke(DirectoryListing $listing): void
    {
        $this->images->delete($listing->photo_path);
        $listing->delete();
    }
}
