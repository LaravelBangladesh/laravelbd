<?php

namespace App\Domain\Identity\Actions;

use App\Domain\Identity\Models\LoginChallenge;
use App\Domain\Identity\Models\User;
use Illuminate\Validation\ValidationException;

/**
 * Moves a verified pending address onto the user and closes the challenge.
 */
final class ApplyEmailChange
{
    public function __invoke(User $user, LoginChallenge $challenge): User
    {
        if (self::emailTaken($challenge->email, $user)) {
            throw ValidationException::withMessages([
                'email' => __('account.email_taken'),
            ]);
        }

        $challenge->forceFill(['consumed_at' => now()])->save();

        $user->forceFill([
            'email' => $challenge->email,
            'pending_email' => null,
            'email_verified_at' => now(),
        ])->save();

        return $user;
    }

    public static function emailTaken(string $email, User $user): bool
    {
        return User::query()
            ->where('email', $email)
            ->whereKeyNot($user->id)
            ->exists();
    }
}
