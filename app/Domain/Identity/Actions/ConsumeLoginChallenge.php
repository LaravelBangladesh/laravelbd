<?php

namespace App\Domain\Identity\Actions;

use App\Domain\Identity\Enums\UserRole;
use App\Domain\Identity\Models\LoginChallenge;
use App\Domain\Identity\Models\User;

/**
 * Marks the challenge consumed and returns the matching user, creating one on
 * first sign-in.
 */
final class ConsumeLoginChallenge
{
    public function __invoke(LoginChallenge $challenge): User
    {
        $challenge->forceFill(['consumed_at' => now()])->save();

        $user = User::query()->where('email', $challenge->email)->first();

        if ($user === null) {
            $user = User::query()->make([
                'name' => filled($challenge->name) ? $challenge->name : '',
                'email' => $challenge->email,
                'role' => UserRole::Member,
                'locale' => app()->getLocale(),
            ]);

            $user->forceFill(['email_verified_at' => now()])->save();

            return $user;
        }

        if ($user->email_verified_at === null) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        return $user;
    }
}
