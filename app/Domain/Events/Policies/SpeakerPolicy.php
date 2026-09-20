<?php

namespace App\Domain\Events\Policies;

use App\Domain\Events\Models\Speaker;
use App\Domain\Identity\Models\User;

class SpeakerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff();
    }

    public function create(User $user): bool
    {
        return $user->isStaff();
    }

    public function update(User $user, Speaker $speaker): bool
    {
        return $user->isStaff();
    }

    public function delete(User $user, Speaker $speaker): bool
    {
        return $user->isStaff();
    }
}
