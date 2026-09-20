<?php

namespace App\Domain\Identity\Actions;

use App\Domain\Identity\Models\LoginChallenge;
use App\Domain\Identity\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

final class VerifyLoginCode
{
    public function __construct(private readonly ConsumeLoginChallenge $consume) {}

    public function __invoke(string $email, string $code): User
    {
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

        return ($this->consume)($challenge);
    }
}
