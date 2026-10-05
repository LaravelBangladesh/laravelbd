<?php

namespace App\Domain\Identity\Policies;

use App\Domain\Identity\Models\User;

class UserPolicy
{
    /**
     * A directory profile is public once listed; before that only its owner
     * and staff can open it.
     */
    public function view(?User $viewer, User $user): bool
    {
        return $user->isListed()
            || $viewer?->isStaff() === true
            || $viewer?->id === $user->id;
    }

    public function viewAny(User $actor): bool
    {
        return $actor->isStaff();
    }

    /**
     * Staff add guests, such as speakers who have not signed in yet.
     */
    public function create(User $actor): bool
    {
        return $actor->isStaff();
    }

    public function update(User $actor, User $user): bool
    {
        return $actor->isStaff();
    }
}
