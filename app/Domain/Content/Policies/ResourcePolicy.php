<?php

namespace App\Domain\Content\Policies;

use App\Domain\Content\Models\Resource;
use App\Domain\Identity\Models\User;

class ResourcePolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Resource $resource): bool
    {
        return $resource->isPublished() || $user?->isStaff() === true;
    }

    public function create(User $user): bool
    {
        return $user->isStaff();
    }

    public function update(User $user, Resource $resource): bool
    {
        return $user->isStaff();
    }

    public function delete(User $user, Resource $resource): bool
    {
        return $user->isStaff();
    }

    public function manage(User $user): bool
    {
        return $user->isStaff();
    }
}
