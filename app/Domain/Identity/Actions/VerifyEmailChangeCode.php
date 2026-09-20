<?php

namespace App\Domain\Identity\Actions;

use App\Domain\Identity\Models\LoginChallenge;
use App\Domain\Identity\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

final class VerifyEmailChangeCode
{
    public function __construct(private readonly ApplyEmailChange $apply) {}

    public function __invoke(User $user, string $code): User
    {
        $email = $user->pending_email;

        if (! is_string($email) || $email === '') {
            throw ValidationException::withMessages([
                'code' => __('auth.invalid_code'),
            ]);
        }

        $challenge = LoginChallenge::query()
            ->where('email', $email)
            ->whereNull('consumed_at')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if ($challenge === null || ! Hash::check($code, $challenge->code_hash)) {
            throw ValidationException::withMessages([
                'code' => __('auth.invalid_code'),
            ]);
        }

        return ($this->apply)($user, $challenge);
    }
}
