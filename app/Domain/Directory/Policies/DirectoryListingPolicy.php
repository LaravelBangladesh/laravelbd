<?php

namespace App\Domain\Directory\Policies;

use App\Domain\Directory\Models\DirectoryListing;
use App\Domain\Identity\Models\User;

class DirectoryListingPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, DirectoryListing $listing): bool
    {
        return $listing->isPublished()
            || $user?->isStaff() === true
            || ($user !== null && $listing->user_id === $user->id);
    }

    public function create(User $user): bool
    {
        return $user->isStaff() || $user->directoryListing()->doesntExist();
    }

    public function update(User $user, DirectoryListing $listing): bool
    {
        return $user->isStaff() || $listing->user_id === $user->id;
    }

    public function delete(User $user, DirectoryListing $listing): bool
    {
        return $user->isStaff();
    }

    public function manage(User $user): bool
    {
        return $user->isStaff();
    }
}
