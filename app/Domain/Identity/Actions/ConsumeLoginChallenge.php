<?php

namespace App\Domain\Identity\Actions;

use App\Domain\Identity\Enums\UserRole;
use App\Domain\Identity\Models\LoginChallenge;
use App\Domain\Identity\Models\User;
use Illuminate\Validation\ValidationException;

/**
 * Marks the challenge consumed and returns the matching user, creating one on
 * first sign-in.
 */
final class ConsumeLoginChallenge
{
    public function __invoke(LoginChallenge $challenge): User
    {
        $challenge->forceFill(['consumed_at' => now()])->save();

        $user = User::query()->withTrashed()->where('email', $challenge->email)->first();

        // A code sent before the account was deactivated must not sign it in,
        // nor create a second account on the same email.
        if ($user?->trashed() === true) {
            throw ValidationException::withMessages([
                'code' => __('auth.account_deactivated'),
            ]);
        }

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
