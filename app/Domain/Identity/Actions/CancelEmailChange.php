<?php

namespace App\Domain\Identity\Actions;

use App\Domain\Identity\Models\LoginChallenge;
use App\Domain\Identity\Models\User;

final class CancelEmailChange
{
    public function __invoke(User $user): void
    {
        if (is_string($user->pending_email) && $user->pending_email !== '') {
            LoginChallenge::query()
                ->where('email', $user->pending_email)
                ->whereNull('consumed_at')
                ->update(['consumed_at' => now()]);
        }

        $user->forceFill(['pending_email' => null])->save();
    }
}
