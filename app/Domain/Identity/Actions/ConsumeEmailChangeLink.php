<?php

namespace App\Domain\Identity\Actions;

use App\Domain\Identity\Models\LoginChallenge;
use App\Domain\Identity\Models\User;
use Illuminate\Validation\ValidationException;

final class ConsumeEmailChangeLink
{
    public function __construct(private readonly ApplyEmailChange $apply) {}

    public function __invoke(string $token): User
    {
        $challenge = LoginChallenge::query()
            ->where('token_hash', hash('sha256', $token))
            ->whereNull('consumed_at')
            ->where('expires_at', '>', now())
            ->first();

        if ($challenge === null) {
            throw ValidationException::withMessages([
                'token' => __('auth.invalid_link'),
            ]);
        }

        $user = User::query()
            ->where('pending_email', $challenge->email)
            ->first();

        if ($user === null) {
            throw ValidationException::withMessages([
                'token' => __('auth.invalid_link'),
            ]);
        }

        return ($this->apply)($user, $challenge);
    }
}
