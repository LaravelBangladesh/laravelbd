<?php

namespace App\Domain\Identity\Actions;

use App\Domain\Identity\Models\User;

final class ReactivateUser
{
    public function __invoke(User $user): void
    {
        $user->restore();
    }
}
